<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Conversation;
use App\Models\LogEntry;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        if ($perPage < 1) {
            $perPage = 15;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $paginator = User::query()
            ->where('role', 'doctor')
            ->with(['doctorSchedules'])
            ->paginate($perPage);

        $availableOnly = $request->boolean('available_only');

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(function (User $doctor) {
                    $availability = $this->availabilityForDoctorId((int) $doctor->user_id);
                    $doctor->is_available = $availability['is_available'];
                    $doctor->unavailable_reason = $availability['unavailable_reason'];
                    $doctor->unavailable_at = $availability['unavailable_at'];

                    return $doctor;
                })
                ->when($availableOnly, function ($collection) {
                    return $collection->filter(function ($doctor) {
                        return $doctor->is_available !== false;
                    })->values();
                })
        );

        return $paginator;
    }

    public function store(Request $request)
    {
        $request->merge(['role' => 'doctor']);

        return app(UserController::class)->store($request);
    }

    public function show(User $doctor)
    {
        if ($doctor->role !== 'doctor') {
            abort(404);
        }

        $doctor->load(['doctorSchedules']);
        $availability = $this->availabilityForDoctorId((int) $doctor->user_id);
        $doctor->is_available = $availability['is_available'];
        $doctor->unavailable_reason = $availability['unavailable_reason'];
        $doctor->unavailable_at = $availability['unavailable_at'];

        return $doctor;
    }

    public function update(Request $request, User $doctor)
    {
        if ($doctor->role !== 'doctor') {
            abort(404);
        }

        return app(UserController::class)->update($request, $doctor);
    }

    public function destroy(User $doctor)
    {
        if ($doctor->role !== 'doctor') {
            abort(404);
        }

        return app(UserController::class)->destroy($doctor);
    }

    public function setAvailability(Request $request, User $doctor)
    {
        $currentUser = $request->user();
        if (! $currentUser || $currentUser->role === 'patient') {
            abort(403);
        }

        if ($doctor->role !== 'doctor') {
            abort(404);
        }

        $data = $request->validate([
            'is_available' => ['required', 'boolean'],
            'reason' => ['nullable', 'string'],
        ]);

        $isAvailable = (bool) $data['is_available'];
        $reason = isset($data['reason']) ? trim((string) $data['reason']) : null;
        if ($reason === '') {
            $reason = null;
        }

        $result = DB::transaction(function () use ($doctor, $isAvailable, $reason) {
            if ($isAvailable) {
                $this->setDoctorAvailability((int) $doctor->user_id, true, null);

                $availability = $this->availabilityForDoctorId((int) $doctor->user_id);
                $doctor->is_available = $availability['is_available'];
                $doctor->unavailable_reason = $availability['unavailable_reason'];
                $doctor->unavailable_at = $availability['unavailable_at'];

                return [
                    'doctor' => $doctor,
                    'flagged_appointments' => 0,
                ];
            }

            $this->setDoctorAvailability((int) $doctor->user_id, false, $reason);

            $affectedAppointments = Appointment::query()
                ->where('doctor_id', $doctor->user_id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function ($q) {
                    $q->whereNull('appointment_datetime')->orWhere('appointment_datetime', '>=', now());
                })
                ->get();

            $appointmentIds = [];
            foreach ($affectedAppointments as $appointment) {
                $appointmentIds[] = (int) $appointment->appointment_id;

                $conversation = Conversation::ensureForPatient((int) $appointment->patient_id);

                Message::create([
                    'conversation_id' => $conversation->conversation_id,
                    'sender' => 'bot',
                    'message_text' => 'Doctor unavailable, reassign?',
                ]);

                Message::create([
                    'conversation_id' => $conversation->conversation_id,
                    'sender' => 'bot',
                    'message_text' => 'Update: Your doctor is currently unavailable. We will reassign your appointment and notify you.',
                ]);
            }

            $doctorName = trim(implode(' ', array_filter([
                $doctor->firstname,
                $doctor->lastname,
            ])));
            if ($doctorName === '') {
                $doctorName = 'Doctor #'.$doctor->user_id;
            }

            $flaggedCount = count($appointmentIds);
            $previewIds = array_slice($appointmentIds, 0, 20);
            $moreCount = max(0, $flaggedCount - count($previewIds));

            $appointmentListText = $flaggedCount
                ? ('Affected appointment IDs: '.implode(', ', $previewIds).($moreCount ? (' … +'.$moreCount.' more') : ''))
                : 'No upcoming appointments found.';

            $receptionists = User::query()
                ->where('role', 'receptionist')
                ->where('status', 'active')
                ->get();

            foreach ($receptionists as $receptionist) {
                Notification::create([
                    'user_id' => $receptionist->user_id,
                    'type' => 'system',
                    'message' => $doctorName.' was marked unavailable. '.$appointmentListText,
                ]);
            }

            $availability = $this->availabilityForDoctorId((int) $doctor->user_id);
            $doctor->is_available = $availability['is_available'];
            $doctor->unavailable_reason = $availability['unavailable_reason'];
            $doctor->unavailable_at = $availability['unavailable_at'];

            return [
                'doctor' => $doctor,
                'flagged_appointments' => $flaggedCount,
            ];
        });

        LogEntry::write(
            $currentUser ? (int) $currentUser->user_id : null,
            'doctor_availability_changed',
            'users',
            (int) $doctor->user_id,
            [
                'is_available' => $isAvailable,
                'reason' => $reason,
                'flagged_appointments' => (int) ($result['flagged_appointments'] ?? 0),
            ]
        );

        return response()->json($result);
    }

    private function availabilityForDoctorId(int $doctorId): array
    {
        $payload = Cache::store('file')->get($this->doctorAvailabilityKey($doctorId));

        $isAvailable = true;
        $reason = null;
        $at = null;

        if (is_array($payload) && ($payload['is_available'] ?? null) === false) {
            $isAvailable = false;
            $reason = isset($payload['reason']) ? (string) $payload['reason'] : null;
            $at = isset($payload['at']) ? (string) $payload['at'] : null;
        }

        return [
            'is_available' => $isAvailable,
            'unavailable_reason' => $reason,
            'unavailable_at' => $at,
        ];
    }

    private function setDoctorAvailability(int $doctorId, bool $isAvailable, ?string $reason): void
    {
        $key = $this->doctorAvailabilityKey($doctorId);

        if ($isAvailable) {
            Cache::store('file')->forget($key);

            return;
        }

        Cache::store('file')->put($key, [
            'is_available' => false,
            'reason' => $reason,
            'at' => now()->toDateTimeString(),
        ]);
    }

    private function doctorAvailabilityKey(int $doctorId): string
    {
        return 'doctor_availability:'.$doctorId;
    }
}
