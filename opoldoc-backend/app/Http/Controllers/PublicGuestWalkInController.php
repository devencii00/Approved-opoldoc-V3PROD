<?php

namespace App\Http\Controllers;

use App\Models\DoctorSchedule;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicGuestWalkInController extends Controller
{
    private const CACHE_KEY = 'guest_walkin_active';

    public function redirectToActive()
    {
        $availability = $this->availabilitySnapshot();
        if (! $availability['is_open']) {
            return $this->closedResponse(null, $availability['message']);
        }

        $active = $this->getActiveToken();
        if (! $active) {
            return $this->closedResponse(null, 'No active guest walk-in link. Please ask the receptionist to generate the latest QR code.', 410);
        }

        return redirect('/public/walk-in/guest/'.$active);
    }

    public function qr(?string $token = null)
    {
        $availability = $this->availabilitySnapshot();
        if (! $availability['is_open']) {
            return $this->closedResponse(null, $availability['message']);
        }

        $active = $this->getActiveToken();
        $staticUrl = url('/public/walk-in/guest');

        if ($token === null) {
            if ($active === null) {
                return $this->closedResponse(null, 'No active guest walk-in link. Please ask the receptionist to generate the latest QR code.', 410);
            }
            $token = $active;
        }

        if ($active === null || $token !== $active) {
            return $this->closedResponse($token, 'This link has expired. Please ask the receptionist for the latest QR code.', 410);
        }

        $activeUrl = url('/public/walk-in/guest/'.$token);

        return view('public.guest_walk_in_qr', [
            'staticUrl' => $staticUrl,
            'activeUrl' => $activeUrl,
        ]);
    }

    public function page(string $token)
    {
        $availability = $this->availabilitySnapshot();
        if (! $availability['is_open']) {
            return $this->closedResponse($token, $availability['message']);
        }

        $active = $this->getActiveToken();
        if (! $active || $token !== $active) {
            return $this->closedResponse($token, 'This link has expired. Please ask the receptionist for the latest QR code.', 410);
        }

        $services = Service::query()
            ->where(function ($q) {
                $q->whereNull('is_active')->orWhere('is_active', true);
            })
            ->orderBy('service_name')
            ->get(['service_id', 'service_name', 'duration_minutes', 'price']);

        $doctors = User::query()
            ->where('role', 'doctor')
            ->with(['doctorSchedules'])
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->get(['user_id', 'firstname', 'middlename', 'lastname', 'specialization']);

        $doctorPayload = $doctors->map(function (User $doctor) {
            $availability = $this->doctorAvailabilitySnapshot((int) $doctor->user_id);
            $doctor->is_available = $availability['is_available'];
            return $doctor;
        });

        return view('public.guest_walk_in', [
            'token' => $token,
            'staticUrl' => url('/public/walk-in/guest'),
            'qrUrl' => url('/public/walk-in/display/'.$token),
            'activeUrl' => url('/public/walk-in/guest/'.$token),
            'services' => $services,
            'doctors' => $doctorPayload,
            'serverTime' => now()->toIso8601String(),
            'clinicHours' => [
                'start' => '08:00',
                'end' => '17:00',
            ],
        ]);
    }

    public function submit(Request $request, string $token)
    {
        $active = $this->getActiveToken();
        if (! $active || $token !== $active) {
            return response()->json([
                'message' => 'This link has expired. Please ask the receptionist for the latest QR code.',
            ], 410);
        }

        $availability = $this->availabilitySnapshot();
        if (! $availability['is_open']) {
            return response()->json([
                'message' => $availability['message'],
            ], 423);
        }

        $doctorId = (int) $request->input('doctor_id');
        if ($doctorId > 0 && ! $this->doctorIsSchedulableNow($doctorId)) {
            return response()->json([
                'message' => 'Selected doctor has no schedule on this time.',
            ], 422);
        }

        return app(WalkInController::class)->storeGuest($request);
    }

    public function checkDuplicates(Request $request, string $token)
    {
        $active = $this->getActiveToken();
        if (! $active || $token !== $active) {
            return response()->json([
                'message' => 'This link has expired. Please ask the receptionist for the latest QR code.',
            ], 410);
        }

        $data = $request->validate([
            'firstname' => ['required', 'string'],
            'middlename' => ['required', 'string'],
            'lastname' => ['required', 'string'],
        ]);

        $first = trim((string) $data['firstname']);
        $middle = trim((string) $data['middlename']);
        $last = trim((string) $data['lastname']);

        if ($first === '' || $middle === '' || $last === '') {
            return response()->json([
                'match_count' => 0,
                'similar_in_queue' => false,
                'previous_doctor_id' => null,
                'previous_service_ids' => [],
            ]);
        }

        $likeFirst = '%'.$first.'%';
        $likeMiddle = '%'.$middle.'%';
        $likeLast = '%'.$last.'%';

        $patientIds = User::query()
            ->where('role', 'patient')
            ->where('firstname', 'like', $likeFirst)
            ->where('middlename', 'like', $likeMiddle)
            ->where('lastname', 'like', $likeLast)
            ->limit(25)
            ->pluck('user_id')
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0)
            ->values()
            ->all();

        if (! count($patientIds)) {
            return response()->json([
                'match_count' => 0,
                'similar_in_queue' => false,
                'previous_doctor_id' => null,
                'previous_service_ids' => [],
            ]);
        }

        $date = now()->toDateString();

        $similarInQueue = Queue::query()
            ->whereDate('queue_datetime', $date)
            ->whereIn('status', ['waiting', 'serving'])
            ->whereHas('appointment', function ($q) use ($patientIds) {
                $q->whereIn('patient_id', $patientIds);
            })
            ->exists();

        $lastAppointment = Appointment::query()
            ->with(['services'])
            ->whereIn('patient_id', $patientIds)
            ->orderByDesc('appointment_datetime')
            ->orderByDesc('appointment_id')
            ->first();

        $previousDoctorId = $lastAppointment && $lastAppointment->doctor_id ? (int) $lastAppointment->doctor_id : null;
        $previousServiceIds = [];
        if ($lastAppointment) {
            foreach (($lastAppointment->services ?? []) as $service) {
                $sid = (int) ($service->service_id ?? 0);
                if ($sid > 0) {
                    $previousServiceIds[] = $sid;
                }
            }
            $previousServiceIds = array_values(array_unique($previousServiceIds));
        }

        return response()->json([
            'match_count' => count($patientIds),
            'similar_in_queue' => $similarInQueue,
            'previous_doctor_id' => $previousDoctorId,
            'previous_service_ids' => $previousServiceIds,
        ]);
    }

    private function closedResponse(?string $token, string $message, int $status = 200)
    {
        return response()->view('public.guest_walk_in_closed', [
            'token' => $token,
            'message' => $message,
            'qrUrl' => url('/public/walk-in/display'),
        ], $status);
    }

    private function availabilitySnapshot(): array
    {
        $now = now();
        $time = $now->format('H:i:s');
        $dayKey = strtolower($now->format('D'));

        if ($dayKey === 'sun') {
            return [
                'is_open' => false,
                'message' => 'Clinic is closed during sunday, come back tommorrow',
            ];
        }

        $isClinicHours = $time >= '08:00:00' && $time < '17:00:00';
        if (! $isClinicHours) {
            return [
                'is_open' => false,
                'message' => 'The clinic is currently closed. Please return at 8:00 AM.',
            ];
        }

        $hasAnyScheduleNow = DoctorSchedule::query()
            ->where('day_of_week', $dayKey)
            ->where('is_available', true)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>', $time)
            ->exists();

        // if (! $hasAnyScheduleNow) {
        //     return [
        //         'is_open' => false,
        //         'message' => 'The clinic is currently closed. Please return later.',
        //     ];
        // }

        return [
            'is_open' => true,
            'message' => '',
        ];
    }

    private function doctorIsSchedulableNow(int $doctorId): bool
    {
        $availability = $this->doctorAvailabilitySnapshot($doctorId);
        if ($availability['is_available'] === false) {
            return false;
        }

        $now = now();
        $time = $now->format('H:i:s');
        $dayKey = strtolower($now->format('D'));

        return DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('day_of_week', $dayKey)
            ->where('is_available', true)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>', $time)
            ->exists();
    }

    private function doctorAvailabilitySnapshot(int $doctorId): array
    {
        $payload = Cache::store('file')->get('doctor_availability:'.$doctorId);
        if (is_array($payload) && ($payload['is_available'] ?? null) === false) {
            return [
                'is_available' => false,
            ];
        }

        return [
            'is_available' => true,
        ];
    }

    private function getActiveToken(): ?string
    {
        $payload = Cache::get(self::CACHE_KEY);
        if (! is_array($payload)) {
            return null;
        }

        $token = $payload['token'] ?? null;
        if (! is_string($token) || trim($token) === '') {
            return null;
        }

        return $token;
    }
}
