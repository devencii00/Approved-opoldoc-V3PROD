<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WalkInController extends Controller
{
    public function index()
    {
        return Appointment::query()
            ->where('appointment_type', 'walk_in')
            ->paginate();
    }

    public function store(Request $request)
    {
        $request->merge([
            'appointment_type' => 'walk_in',
        ]);

        return app(AppointmentController::class)->store($request);
    }

    public function storeGuest(Request $request)
    {
        return $this->createGuestWalkIn($request, true);
    }

    public function storeGuestRequest(Request $request)
    {
        return $this->createGuestWalkIn($request, false);
    }

    private function createGuestWalkIn(Request $request, bool $createQueue = true)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $data = $request->validate([
            'doctor_id' => ['required', 'exists:users,user_id'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,service_id'],
            'appointment_datetime' => ['nullable', 'date'],
            'reason_for_visit' => ['nullable', 'string'],
            'priority_level' => ['nullable', 'integer'],
            'firstname' => ['required', 'string'],
            'middlename' => ['nullable', 'string'],
            'lastname' => ['required', 'string'],
            'birthdate' => ['nullable', 'date'],
            'sex' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'contact_number' => ['nullable', 'string'],
        ]);

        $serviceIds = array_values(array_unique(array_map('intval', $data['service_ids'] ?? [])));
        if (count($serviceIds)) {
            $services = Service::query()
                ->whereIn('service_id', $serviceIds)
                ->get(['service_id', 'service_name', 'is_active']);

            $inactive = $services->firstWhere('is_active', false);
            if ($inactive) {
                return response()->json([
                    'message' => 'Selected service is inactive.',
                    'code' => 'SERVICE_INACTIVE',
                ], 422);
            }

            $serviceGroups = $services
                ->map(function (Service $service) {
                    $serviceName = (string) ($service->service_name ?? '');
                    $serviceCategory = strtolower(trim(explode(':', $serviceName, 2)[0] ?? $serviceName));
                    return trim($serviceCategory);
                })
                ->filter(fn ($v) => (string) $v !== '')
                ->unique()
                ->values();

            if ($serviceGroups->count() > 1) {
                return response()->json([
                    'message' => 'All selected services must match the first chosen service.',
                    'code' => 'SERVICE_GROUP_MISMATCH',
                ], 422);
            }

            $blocked = ['obsterician - gynecologist', 'obstetrician - gynecologist', 'general surgeon'];
            if ($serviceGroups->count() === 1 && in_array((string) $serviceGroups->first(), $blocked, true)) {
                return response()->json([
                    'message' => 'Selected service is only available for scheduled appointments.',
                    'code' => 'SERVICE_SCHEDULED_ONLY',
                ], 422);
            }
        }

        $plainPassword = Str::random(12);

        $result = DB::transaction(function () use ($currentUser, $data, $plainPassword, $serviceIds, $createQueue) {
            $patient = User::create([
                'email' => null,
                'password_hash' => Hash::make($plainPassword),
                'role' => 'patient',
                'status' => 'active',
                'firstname' => trim((string) $data['firstname']),
                'middlename' => array_key_exists('middlename', $data) ? trim((string) ($data['middlename'] ?? '')) : null,
                'lastname' => trim((string) $data['lastname']),
                'birthdate' => $data['birthdate'] ?? null,
                'sex' => $data['sex'] ?? null,
                'address' => $data['address'] ?? null,
                'contact_number' => $data['contact_number'] ?? null,
                'is_first_login' => true,
                'must_change_credentials' => true,
                'account_activated' => true,
            ]);

            $targetEmail = 'patient'.$patient->user_id.'@mail.com';
            if (User::where('email', $targetEmail)->exists()) {
                $targetEmail = 'patient'.$patient->user_id.'-'.Str::lower(Str::random(4)).'@mail.com';
            }
            $patient->update(['email' => $targetEmail]);

            $appointmentDatetime = ! empty($data['appointment_datetime'])
                ? Carbon::parse($data['appointment_datetime'])
                : now();

            $priorityLevel = Queue::sanitizePriorityLevel($data['priority_level'] ?? null) ?? 5;

            $appointment = Appointment::create([
                'patient_id' => $patient->user_id,
                'doctor_id' => (int) $data['doctor_id'],
                'created_by' => $currentUser?->user_id,
                'appointment_datetime' => $appointmentDatetime,
                'appointment_type' => 'walk_in',
                'status' => $createQueue ? 'confirmed' : 'pending',
                'reason_for_visit' => $data['reason_for_visit'] ?? null,
                'priority_level' => $priorityLevel,
            ]);

            if (count($serviceIds)) {
                $appointment->services()->sync($serviceIds);
            }

            $queue = null;
            if ($createQueue) {
                $queueAt = now();
                $date = $queueAt->toDateString();
                $max = Queue::whereDate('queue_datetime', $date)->max('queue_number');
                $queueNumber = ((int) $max) + 1;

                $queue = Queue::create([
                    'appointment_id' => $appointment->appointment_id,
                    'queue_number' => $queueNumber,
                    'queue_datetime' => $queueAt,
                    'status' => 'waiting',
                    'priority_level' => $priorityLevel,
                ]);
            }

            return [
                'patient' => $patient->refresh(),
                'credentials' => [
                    'email' => $patient->email,
                    'password' => $plainPassword,
                    'generated' => true,
                ],
                'appointment' => $appointment->load(['patient', 'doctor', 'services', 'queue']),
                'queue' => $queue ? $queue->load(['appointment.patient', 'appointment.doctor']) : null,
            ];
        });

        if ($createQueue) {
            Notification::notifyReceptionists('[Walk-in Registered] A new patient was registered.', 'appointment');
        } else {
            Notification::notifyReceptionists('A guest patient submitted a queue request.', 'appointment');
        }

        return response()->json($result, 201);
    }

    public function checkGuestDuplicates(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser || ! in_array((string) $currentUser->role, ['admin', 'receptionist'], true)) {
            abort(403);
        }

        $data = $request->validate([
            'firstname' => ['required', 'string'],
            'middlename' => ['nullable', 'string'],
            'lastname' => ['required', 'string'],
        ]);

        $first = trim((string) $data['firstname']);
        $middle = trim((string) ($data['middlename'] ?? ''));
        $last = trim((string) $data['lastname']);

        if ($first === '' || $last === '') {
            return response()->json([
                'match_count' => 0,
                'similar_in_queue' => false,
                'previous_doctor_id' => null,
                'previous_service_ids' => [],
            ]);
        }

        $likeFirst = '%'.$first.'%';
        $likeLast = '%'.$last.'%';

        $patientQuery = User::query()
            ->where('role', 'patient')
            ->where('firstname', 'like', $likeFirst)
            ->where('lastname', 'like', $likeLast)
            ->when($middle !== '', function ($q) use ($middle) {
                $likeMiddle = '%'.$middle.'%';
                $q->where('middlename', 'like', $likeMiddle);
            }, function ($q) {
                $q->where(function ($inner) {
                    $inner->whereNull('middlename')->orWhere('middlename', '');
                });
            })
            ->limit(25);

        $patientIds = $patientQuery
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

    public function show(Appointment $walk_in)
    {
        if ($walk_in->appointment_type !== 'walk_in') {
            abort(404);
        }

        return $walk_in->load(['patient', 'doctor', 'queue', 'transaction']);
    }
}
