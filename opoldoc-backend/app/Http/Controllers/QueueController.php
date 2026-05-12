<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Conversation;
use App\Models\DoctorSchedule;
use App\Models\MedicalBackground;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class QueueController extends Controller
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

        $query = Queue::with(['appointment.patient', 'appointment.doctor', 'appointment.services']);

        $currentUser = $request->user();
        $isPatient = $currentUser && $currentUser->role === 'patient';

        if ($isPatient) {
            $query->whereHas('appointment', function ($q) use ($currentUser) {
                $q->whereIn('patient_id', $currentUser->accessiblePatientIds());
            });
        }

        if ($request->filled('doctor_id')) {
            $doctorId = $request->query('doctor_id');
            $query->whereHas('appointment', function ($q) use ($doctorId) {
                $q->where('doctor_id', $doctorId);
            });
        }

        if ($request->filled('date')) {
            $date = $request->query('date');
            $query->whereDate('queue_datetime', $date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $paginator = $query
            ->orderByDesc('queue_datetime')
            ->paginate($perPage);

        $defaultMinutesPerPatient = (int) env('QUEUE_MINUTES_PER_PATIENT', 10);
        if ($defaultMinutesPerPatient < 1) {
            $defaultMinutesPerPatient = 10;
        }
        if ($defaultMinutesPerPatient > 120) {
            $defaultMinutesPerPatient = 120;
        }

        $doctorDateCache = [];
        $scoreNow = now();
        $snapshotForDoctorDate = function (int $doctorId, string $date) use (&$doctorDateCache, $defaultMinutesPerPatient, $scoreNow): array {
            $key = $doctorId.'|'.$date;
            if (array_key_exists($key, $doctorDateCache)) {
                return (array) $doctorDateCache[$key];
            }

            $items = Queue::query()
                ->with(['appointment.services'])
                ->whereHas('appointment', function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                })
                ->whereDate('queue_datetime', $date)
                ->whereIn('status', ['waiting', 'serving'])
                ->get();

            $durations = [];
            foreach ($items as $row) {
                $row->loadMissing('appointment.services');
                $total = 0;
                foreach (($row->appointment?->services ?? []) as $service) {
                    $minutes = (int) ($service->duration_minutes ?? 0);
                    if ($minutes > 0) {
                        $total += $minutes;
                    }
                }
                if ($total < 1) {
                    $total = $defaultMinutesPerPatient;
                }
                $durations[] = $total;
            }

            $avg = $defaultMinutesPerPatient;
            if (count($durations)) {
                $avg = (int) round(array_sum($durations) / count($durations));
                if ($avg < 1) {
                    $avg = $defaultMinutesPerPatient;
                }
                if ($avg > 120) {
                    $avg = 120;
                }
            }

            $sorted = $items->sort(function (Queue $a, Queue $b) use ($scoreNow) {
                if ($a->status === 'serving' && $b->status !== 'serving') {
                    return -1;
                }
                if ($b->status === 'serving' && $a->status !== 'serving') {
                    return 1;
                }

                $sa = $a->totalScore($scoreNow);
                $sb = $b->totalScore($scoreNow);
                if ($sa !== $sb) {
                    return $sb <=> $sa;
                }
                $na = (int) ($a->queue_number ?? 999999);
                $nb = (int) ($b->queue_number ?? 999999);
                if ($na !== $nb) {
                    return $na <=> $nb;
                }
                return (int) ($a->queue_id ?? 0) <=> (int) ($b->queue_id ?? 0);
            })->values();

            $positions = [];
            foreach ($sorted as $idx => $row) {
                $positions[(int) $row->queue_id] = (int) $idx + 1;
            }

            $doctorDateCache[$key] = [
                'avg' => $avg,
                'positions' => $positions,
            ];

            return (array) $doctorDateCache[$key];
        };

        $paginator->getCollection()->transform(function (Queue $queue) use ($snapshotForDoctorDate, $defaultMinutesPerPatient) {
            $queue->loadMissing('appointment.services');

            $doctorId = $queue->appointment ? $queue->appointment->doctor_id : null;
            $date = $queue->queue_datetime ? $queue->queue_datetime->toDateString() : now()->toDateString();

            if (! $doctorId) {
                $queue->position = null;
                $queue->estimated_wait_minutes = null;
                $queue->avg_service_minutes = null;

                return $queue;
            }

            $snapshot = $snapshotForDoctorDate((int) $doctorId, $date);
            $positions = is_array($snapshot['positions'] ?? null) ? $snapshot['positions'] : [];
            $position = $positions[(int) $queue->queue_id] ?? null;

            $avgMinutes = (int) ($snapshot['avg'] ?? $defaultMinutesPerPatient);
            if ($avgMinutes < 1) {
                $avgMinutes = $defaultMinutesPerPatient;
            }

            $aheadCount = $position ? max(0, ((int) $position) - 1) : null;
            $estimatedWait = $queue->status === 'serving' ? 0 : ($aheadCount != null ? max(0, $aheadCount * $avgMinutes) : null);

            $queue->position = $position;
            $queue->estimated_wait_minutes = $estimatedWait;
            $queue->avg_service_minutes = $avgMinutes;

            return $queue;
        });

        return $paginator;
    }

    public function activeExists(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser || ! in_array((string) $currentUser->role, ['admin', 'receptionist'], true)) {
            abort(403);
        }

        $request->validate([
            'patient_id' => ['required', 'integer', 'exists:users,user_id'],
        ]);

        $patientId = (int) $request->query('patient_id');
        $date = now()->toDateString();

        $exists = Queue::query()
            ->whereDate('queue_datetime', $date)
            ->whereIn('status', ['waiting', 'serving'])
            ->whereHas('appointment', function ($q) use ($patientId) {
                $q->where('patient_id', $patientId);
            })
            ->exists();

        return response()->json([
            'exists' => $exists,
        ]);
    }

    public function guestRequests(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser || ! in_array((string) $currentUser->role, ['admin', 'receptionist'], true)) {
            abort(403);
        }

        $today = now()->toDateString();

        $requests = Appointment::query()
            ->with(['patient', 'doctor', 'services', 'queue'])
            ->where('appointment_type', 'walk_in')
            ->whereNull('created_by')
            ->whereDate('created_at', $today)
            ->whereIn('status', ['pending', 'cancelled'])
            ->orderByRaw("CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END ASC")
            ->orderByDesc('created_at')
            ->orderByDesc('appointment_id')
            ->get();

        return response()->json($requests);
    }

    public function processGuestRequest(Request $request, Appointment $appointment)
    {
        $currentUser = $request->user();
        if (! $currentUser || ! in_array((string) $currentUser->role, ['admin', 'receptionist'], true)) {
            abort(403);
        }

        $data = $request->validate([
            'action' => ['required', 'in:accept,reject'],
        ]);

        if ((string) $appointment->appointment_type !== 'walk_in' || $appointment->created_by !== null) {
            return response()->json([
                'message' => 'Only public guest walk-in requests can be processed here.',
                'code' => 'INVALID_GUEST_REQUEST',
            ], 422);
        }

        if ((string) $appointment->status !== 'pending') {
            return response()->json([
                'message' => 'This queue request has already been processed.',
                'code' => 'GUEST_REQUEST_ALREADY_PROCESSED',
            ], 422);
        }

        if ($data['action'] === 'reject') {
            $appointment->update(['status' => 'cancelled']);

            return response()->json([
                'message' => 'Guest queue request rejected.',
                'appointment' => $appointment->refresh()->load(['patient', 'doctor', 'services', 'queue']),
            ]);
        }

        $result = DB::transaction(function () use ($appointment) {
            $appointment->refresh();

            $today = now()->toDateString();
            $patientId = (int) ($appointment->patient_id ?? 0);

            if ($patientId > 0) {
                $duplicatePatient = Queue::query()
                    ->whereDate('queue_datetime', $today)
                    ->whereIn('status', ['waiting', 'serving'])
                    ->whereHas('appointment', function ($q) use ($patientId) {
                        $q->where('patient_id', $patientId);
                    })
                    ->exists();

                if ($duplicatePatient) {
                    return [
                        'ok' => false,
                        'message' => 'This patient is already in the queue.',
                        'code' => 'PATIENT_ALREADY_IN_QUEUE',
                    ];
                }
            }

            $existingQueue = Queue::query()
                ->where('appointment_id', (int) $appointment->appointment_id)
                ->whereDate('queue_datetime', $today)
                ->whereIn('status', ['waiting', 'serving'])
                ->first();

            if ($existingQueue) {
                $appointment->update(['status' => 'confirmed']);

                return [
                    'ok' => true,
                    'appointment' => $appointment->refresh()->load(['patient', 'doctor', 'services', 'queue']),
                    'queue' => $existingQueue->load(['appointment.patient', 'appointment.doctor', 'appointment.services']),
                ];
            }

            $queueAt = now();
            $max = Queue::whereDate('queue_datetime', $today)->max('queue_number');
            $queueNumber = ((int) $max) + 1;
            $priorityLevel = Queue::sanitizePriorityLevel($appointment->priority_level) ?? 5;

            $appointment->update([
                'status' => 'confirmed',
                'appointment_datetime' => $appointment->appointment_datetime ?: $queueAt,
                'priority_level' => $priorityLevel,
            ]);

            $queue = Queue::create([
                'appointment_id' => (int) $appointment->appointment_id,
                'queue_number' => $queueNumber,
                'queue_datetime' => $queueAt,
                'status' => 'waiting',
                'priority_level' => $priorityLevel,
            ]);

            return [
                'ok' => true,
                'appointment' => $appointment->refresh()->load(['patient', 'doctor', 'services', 'queue']),
                'queue' => $queue->load(['appointment.patient', 'appointment.doctor', 'appointment.services']),
            ];
        });

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'message' => $result['message'] ?? 'Failed to process guest request.',
                'code' => $result['code'] ?? 'GUEST_REQUEST_PROCESS_FAILED',
            ], 422);
        }

        return response()->json([
            'message' => 'Guest queue request accepted and added to the queue.',
            'appointment' => $result['appointment'],
            'queue' => $result['queue'],
        ]);
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            return response()->json([
                'message' => 'Patients cannot enter the queue directly.',
            ], 403);
        }

        $data = $request->validate([
            'appointment_id' => ['required', 'exists:appointments,appointment_id'],
            'force_duplicate_patient' => ['nullable', 'boolean'],
        ]);

        $queueAt = now();
        $date = $queueAt->toDateString();
        $appointment = Appointment::query()
            ->with(['patient'])
            ->find((int) $data['appointment_id']);

        if (! $appointment) {
            return response()->json([
                'message' => 'Appointment not found.',
            ], 404);
        }

        $duplicateAppointment = Queue::query()
            ->where('appointment_id', (int) $data['appointment_id'])
            ->whereDate('queue_datetime', $date)
            ->whereIn('status', ['waiting', 'serving'])
            ->exists();

        if ($duplicateAppointment) {
            return response()->json([
                'message' => 'This appointment is already in the queue.',
            ], 422);
        }

        $patientId = (int) ($appointment->patient_id ?? 0);
        $forceDuplicatePatient = (bool) ($data['force_duplicate_patient'] ?? false);
        if ($patientId > 0) {
            $duplicatePatient = Queue::query()
                ->whereDate('queue_datetime', $date)
                ->whereIn('status', ['waiting', 'serving'])
                ->whereHas('appointment', function ($q) use ($patientId) {
                    $q->where('patient_id', $patientId);
                })
                ->exists();

            if ($duplicatePatient && ! $forceDuplicatePatient) {
                return response()->json([
                    'message' => 'This patient is already in the queue.',
                ], 422);
            }
        }

        $max = Queue::whereDate('queue_datetime', $date)->max('queue_number');
        $queueNumber = ((int) $max) + 1;

        $queue = Queue::create([
            'appointment_id' => (int) $data['appointment_id'],
            'queue_number' => $queueNumber,
            'queue_datetime' => $queueAt,
            'status' => 'waiting',
        ]);

        return response()->json($queue->load(['appointment.patient', 'appointment.doctor']), 201);
    }

    public function callNext(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser || ! in_array((string) $currentUser->role, ['admin', 'receptionist', 'doctor'], true)) {
            abort(403);
        }

        $data = $request->validate([
            'doctor_id' => ['nullable', 'integer', 'exists:users,user_id'],
        ]);

        $selectedDoctorId = array_key_exists('doctor_id', $data) && $data['doctor_id'] !== null
            ? (int) $data['doctor_id']
            : null;

        $isDoctorUser = (string) $currentUser->role === 'doctor';
        if ($isDoctorUser) {
            $ownDoctorId = (int) $currentUser->user_id;
            if ($selectedDoctorId && $selectedDoctorId !== $ownDoctorId) {
                return response()->json([
                    'message' => 'Doctors can only call patients from their own queue.',
                    'code' => 'DOCTOR_QUEUE_MISMATCH',
                ], 403);
            }

            $selectedDoctorId = $ownDoctorId;
        }

        if ($selectedDoctorId) {
            $isDoctor = User::query()
                ->where('user_id', $selectedDoctorId)
                ->where('role', 'doctor')
                ->exists();
            if (! $isDoctor) {
                return response()->json([
                    'message' => 'Selected doctor is invalid.',
                    'code' => 'INVALID_DOCTOR',
                ], 422);
            }
        }

        $now = now();
        $date = $now->toDateString();

        if ($isDoctorUser && $selectedDoctorId) {
            $doctorServingCount = Queue::query()
                ->whereDate('queue_datetime', $date)
                ->where('status', 'serving')
                ->whereHas('appointment', function ($q) use ($selectedDoctorId) {
                    $q->where('doctor_id', $selectedDoctorId);
                })
                ->count();

            if ($doctorServingCount > 0) {
                return response()->json([
                    'message' => 'You still have a patient marked as serving.',
                    'code' => 'DOCTOR_SLOT_OCCUPIED',
                ], 422);
            }

            $nextForDoctor = DB::transaction(function () use ($date, $selectedDoctorId) {
                $candidate = Queue::query()
                    ->with(['appointment.patient', 'appointment.doctor'])
                    ->whereDate('queue_datetime', $date)
                    ->where('status', 'waiting')
                    ->whereHas('appointment', function ($q) use ($selectedDoctorId) {
                        $q->where('doctor_id', $selectedDoctorId);
                    })
                    ->orderByRaw('COALESCE(queue_number, 999999) ASC')
                    ->orderByRaw('COALESCE(queue_datetime, NOW()) ASC')
                    ->lockForUpdate()
                    ->first();

                if (! $candidate) {
                    return null;
                }

                $candidate->update(['status' => 'serving']);

                return $candidate->refresh()->load(['appointment.patient', 'appointment.doctor']);
            });

            if (! $nextForDoctor) {
                return response()->json([
                    'message' => 'No waiting patients are queued for you right now.',
                    'code' => 'NO_WAITING_FOR_SELECTED_DOCTOR',
                    'meta' => [
                        'selected_doctor_id' => $selectedDoctorId,
                    ],
                ], 422);
            }

            return response()->json([
                'queue' => $nextForDoctor,
                'meta' => [
                    'selected_doctor_id' => $selectedDoctorId,
                ],
            ]);
        }

        $dayKey = strtolower($now->format('D'));
        $time = $now->format('H:i:s');

        $activeDoctorIds = DoctorSchedule::query()
            ->where('day_of_week', $dayKey)
            ->where('is_available', true)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->pluck('doctor_id')
            ->unique()
            ->values()
            ->all();

        if (! count($activeDoctorIds)) {
            $activeDoctorIds = DoctorSchedule::query()
                ->where('day_of_week', $dayKey)
                ->where('is_available', true)
                ->pluck('doctor_id')
                ->unique()
                ->values()
                ->all();
        }

        $activeDoctorIds = array_slice(array_map(fn ($v) => (int) $v, $activeDoctorIds), 0, 4);
        $capacity = count($activeDoctorIds);

        if ($capacity < 1) {
            return response()->json([
                'message' => 'No active doctors are available right now.',
                'code' => 'NO_ACTIVE_DOCTORS',
            ], 422);
        }

        if ($selectedDoctorId) {
            $selectedDoctorIsActive = in_array($selectedDoctorId, $activeDoctorIds, true);
            if (! $selectedDoctorIsActive) {
                return response()->json([
                    'message' => 'Selected doctor is not in an active schedule right now.',
                    'code' => 'DOCTOR_NOT_ACTIVE',
                ], 422);
            }
        }

        $servingDoctorIds = Queue::query()
            ->with('appointment')
            ->whereDate('queue_datetime', $date)
            ->where('status', 'serving')
            ->get()
            ->map(function (Queue $q) {
                return (int) ($q->appointment?->doctor_id ?? 0);
            })
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $occupied = Queue::query()
            ->whereDate('queue_datetime', $date)
            ->where('status', 'serving')
            ->count();
        if ($occupied >= $capacity) {
            return response()->json([
                'message' => 'There are still '.$occupied.'/'.$capacity.' patients currently being served. Please wait until one slot is available.',
                'code' => 'SERVING_SLOTS_FULL',
                'meta' => [
                    'capacity' => $capacity,
                    'occupied' => $occupied,
                ],
            ], 422);
        }

        $availableDoctorIds = array_values(array_diff($activeDoctorIds, $servingDoctorIds));
        if (! $selectedDoctorId && ! count($availableDoctorIds)) {
            return response()->json([
                'message' => 'No serving slots are currently available.',
                'code' => 'NO_AVAILABLE_SLOTS',
            ], 422);
        }

        if ($selectedDoctorId) {
            if (in_array($selectedDoctorId, $servingDoctorIds, true)) {
                return response()->json([
                    'message' => 'Selected doctor is still serving a patient.',
                    'code' => 'DOCTOR_SLOT_OCCUPIED',
                ], 422);
            }
            $availableDoctorIds = [$selectedDoctorId];
        }

        $weight = Queue::waitScoreWeight();
        $next = DB::transaction(function () use ($date, $availableDoctorIds, $weight) {
            $baseExpr = "CASE COALESCE(priority_level, 5)
                WHEN 1 THEN 5000
                WHEN 2 THEN 4000
                WHEN 3 THEN 3000
                WHEN 4 THEN 2000
                ELSE 1000
            END";
            $scoreExpr = "($baseExpr + (TIMESTAMPDIFF(MINUTE, COALESCE(queue_datetime, NOW()), NOW()) * ".((int) $weight)."))";

            $candidate = Queue::query()
                ->with(['appointment.patient', 'appointment.doctor'])
                ->whereDate('queue_datetime', $date)
                ->where('status', 'waiting')
                ->whereHas('appointment', function ($q) use ($availableDoctorIds) {
                    $q->whereIn('doctor_id', $availableDoctorIds);
                })
                ->orderByRaw($scoreExpr.' DESC')
                ->orderByRaw('COALESCE(queue_number, 999999) ASC')
                ->orderByRaw('COALESCE(queue_datetime, NOW()) ASC')
                ->lockForUpdate()
                ->first();

            if (! $candidate) {
                return null;
            }

            $candidate->update(['status' => 'serving']);
            return $candidate->refresh()->load(['appointment.patient', 'appointment.doctor']);
        });

        if (! $next) {
            $waitingForActiveDoctors = Queue::query()
                ->whereDate('queue_datetime', $date)
                ->where('status', 'waiting')
                ->whereHas('appointment', function ($q) use ($activeDoctorIds) {
                    $q->whereIn('doctor_id', $activeDoctorIds);
                })
                ->count();
            $waitingForSelectedDoctor = null;
            if ($selectedDoctorId) {
                $waitingForSelectedDoctor = Queue::query()
                    ->whereDate('queue_datetime', $date)
                    ->where('status', 'waiting')
                    ->whereHas('appointment', function ($q) use ($selectedDoctorId) {
                        $q->where('doctor_id', $selectedDoctorId);
                    })
                    ->count();
            }
            return response()->json([
                'message' => $selectedDoctorId
                    ? (($waitingForSelectedDoctor ?? 0) > 0
                        ? 'Selected doctor has waiting patients but is not available for call-next yet.'
                        : 'No waiting patients are queued for the selected doctor right now.')
                    : ($waitingForActiveDoctors > 0
                        ? 'Waiting patients exist, but all active serving slots are currently occupied.'
                        : 'No waiting patients are eligible to be called right now.'),
                'code' => $selectedDoctorId ? 'NO_WAITING_FOR_SELECTED_DOCTOR' : 'NO_ELIGIBLE_WAITING',
                'meta' => [
                    'capacity' => $capacity,
                    'occupied' => $occupied,
                    'waiting_for_active_doctors' => $waitingForActiveDoctors,
                    'waiting_for_selected_doctor' => $waitingForSelectedDoctor,
                    'selected_doctor_id' => $selectedDoctorId,
                ],
            ], 422);
        }

        return response()->json([
            'queue' => $next,
            'meta' => [
                'capacity' => $capacity,
                'selected_doctor_id' => $selectedDoctorId,
            ],
        ]);
    }

    public function join(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser || $currentUser->role !== 'patient') {
            abort(403);
        }

        $data = $request->validate([
            'doctor_id' => ['required', 'exists:users,user_id'],
            'reason_for_visit' => ['nullable', 'string'],
            'patient_id' => ['sometimes', 'exists:users,user_id'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'exists:services,service_id'],
        ]);

        $doctor = User::query()->find((int) $data['doctor_id']);
        if (! $doctor || $doctor->role !== 'doctor') {
            return response()->json([
                'message' => 'Selected doctor is invalid.',
                'code' => 'INVALID_DOCTOR',
            ], 422);
        }
        if ($this->isDoctorUnavailable((int) $doctor->user_id)) {
            return response()->json([
                'message' => 'Doctor is currently unavailable.',
                'code' => 'DOCTOR_UNAVAILABLE',
            ], 422);
        }

        $targetPatientId = (int) $currentUser->user_id;
        if ($request->filled('patient_id')) {
            $candidate = (int) $request->input('patient_id');
            if (! $currentUser->canAccessPatientId($candidate)) {
                abort(403);
            }
            $targetPatientId = $candidate;
        }

        $today = now()->toDateString();
        $currentDayKey = strtolower(now()->format('D'));
        $currentTime = now()->format('H:i:s');
        $activeExists = Queue::query()
            ->whereDate('queue_datetime', $today)
            ->whereIn('status', ['waiting', 'serving'])
            ->whereHas('appointment', function ($q) use ($targetPatientId) {
                $q->where('patient_id', $targetPatientId);
            })
            ->exists();

        if ($activeExists) {
            return response()->json([
                'message' => 'You already have an active queue entry.',
            ], 422);
        }

        $hasActiveSchedule = DoctorSchedule::query()
            ->where('doctor_id', (int) $data['doctor_id'])
            ->where('day_of_week', $currentDayKey)
            ->where('is_available', true)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->exists();

        if (! $hasActiveSchedule) {
            return response()->json([
                'message' => 'Selected doctor is not in an active schedule right now.',
                'code' => 'DOCTOR_NOT_ACTIVE',
            ], 422);
        }

        $serviceIds = array_values(array_unique(array_map('intval', $data['service_ids'] ?? [])));
        if (! count($serviceIds)) {
            return response()->json([
                'message' => 'Service is required.',
                'code' => 'SERVICE_REQUIRED',
            ], 422);
        }

        $services = Service::query()
            ->whereIn('service_id', $serviceIds)
            ->get();

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

        $doctorSpec = strtolower(trim((string) ($doctor->specialization ?? '')));
        if ($doctorSpec !== '') {
            foreach ($services as $service) {
                $serviceName = (string) ($service->service_name ?? '');
                $serviceCategory = strtolower(trim(explode(':', $serviceName, 2)[0] ?? $serviceName));
                $serviceCategory = trim($serviceCategory);
                if ($serviceCategory === '') {
                    continue;
                }

                $matches = str_contains($doctorSpec, $serviceCategory) || str_contains($serviceCategory, $doctorSpec);
                if (! $matches) {
                    return response()->json([
                        'message' => 'Selected doctor does not match the chosen service.',
                        'code' => 'SPECIALIZATION_MISMATCH',
                    ], 422);
                }
            }
        }

        $priorityLevel = Queue::priorityLevelForPatientId($targetPatientId) ?? 5;

        $result = DB::transaction(function () use ($currentUser, $data, $targetPatientId, $priorityLevel, $serviceIds) {
            $appointment = Appointment::create([
                'patient_id' => $targetPatientId,
                'doctor_id' => (int) $data['doctor_id'],
                'created_by' => $currentUser->user_id,
                'appointment_datetime' => now(),
                'appointment_type' => 'walk_in',
                'status' => 'confirmed',
                'reason_for_visit' => $data['reason_for_visit'] ?? null,
                'priority_level' => $priorityLevel,
            ]);

            $appointment->services()->sync($serviceIds);

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

            return $queue->load(['appointment.patient', 'appointment.doctor', 'appointment.services']);
        });

        return response()->json($result, 201);
    }

    public function show(Request $request, Queue $queue)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            $queue->loadMissing('appointment');
            if (! $queue->appointment || ! $currentUser->canAccessPatientId((int) $queue->appointment->patient_id)) {
                abort(403);
            }
        }

        return $queue->load(['appointment.patient', 'appointment.doctor']);
    }

    public function update(Request $request, Queue $queue)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $previousStatus = (string) $queue->status;
        $previousQueueNumber = (int) $queue->queue_number;

        $data = $request->validate([
            'queue_number' => ['sometimes', 'integer'],
            'queue_datetime' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'in:waiting,serving,consulted,done,cancelled,no_show'],
            'priority_level' => ['sometimes', 'integer'],
        ]);

        $nextStatus = array_key_exists('status', $data) ? $data['status'] : null;

        if ($nextStatus === 'done') {
            $queue->loadMissing('appointment');
            $appointmentId = (int) (optional($queue->appointment)->appointment_id ?? 0);

            $hasPaidTransaction = $appointmentId > 0
                ? Transaction::query()
                    ->where('appointment_id', $appointmentId)
                    ->where('payment_status', 'paid')
                    ->exists()
                : false;

            if (! $hasPaidTransaction) {
                return response()->json([
                    'message' => 'Queue can only be marked done after the appointment payment is recorded.',
                    'code' => 'QUEUE_DONE_REQUIRES_PAYMENT',
                ], 422);
            }
        }

        DB::transaction(function () use ($queue, $data, $nextStatus) {
            $queue->update($data);

            if ($nextStatus === 'serving') {
                $queue->loadMissing('appointment');
                $doctorId = $queue->appointment ? $queue->appointment->doctor_id : null;
                $date = $queue->queue_datetime ? $queue->queue_datetime->toDateString() : null;

                if ($doctorId && $date) {
                    Queue::query()
                        ->where('queue_id', '!=', $queue->queue_id)
                        ->whereHas('appointment', function ($q) use ($doctorId) {
                            $q->where('doctor_id', $doctorId);
                        })
                        ->whereDate('queue_datetime', $date)
                        ->where('status', 'serving')
                        ->update(['status' => 'waiting']);
                }
            }

            if ($nextStatus === 'done') {
                $queue->loadMissing('appointment');
                $appointment = $queue->appointment;

                if ($appointment && (string) $appointment->status !== 'completed') {
                    $appointment->update(['status' => 'completed']);
                }
            }
        });

        $queue->refresh()->load(['appointment.patient', 'appointment.doctor']);

        $statusChanged = array_key_exists('status', $data) && (string) $queue->status !== $previousStatus;
        $queueNumberChanged = array_key_exists('queue_number', $data) && (int) $queue->queue_number !== $previousQueueNumber;

        if (($statusChanged || $queueNumberChanged) && $queue->appointment) {
            $appointment = $queue->appointment;
            $conversation = Conversation::ensureForPatient((int) $appointment->patient_id);

            $messageText = null;
            $notificationTitle = 'Queue Update';
            $notificationBody = null;
            if ($statusChanged) {
                if ($queue->status === 'waiting') {
                    $notificationBody = 'You are now waiting in the queue.';
                } elseif ($queue->status === 'serving') {
                    $notificationBody = 'You are next in queue.';
                } elseif ($queue->status === 'consulted') {
                    $notificationBody = 'Your consultation is done and payment is pending.';
                } elseif ($queue->status === 'done') {
                    $notificationBody = 'Your queue entry is marked as done.';
                } elseif ($queue->status === 'cancelled') {
                    $notificationBody = 'Your queue entry was cancelled.';
                } elseif ($queue->status === 'no_show') {
                    $notificationTitle = 'Queue Missed';
                    $notificationBody = 'You missed your queue number.';
                }
            }

            if (! $notificationBody && $queueNumberChanged) {
                $notificationBody = 'Your queue number is now '.$queue->queue_number.'.';
            }

            if ($notificationBody) {
                $messageText = 'Queue update: '.$notificationBody;
            }

            if ($messageText) {
                Message::create([
                    'conversation_id' => $conversation->conversation_id,
                    'sender' => 'bot',
                    'message_text' => $messageText,
                ]);

                Notification::notifyUsers(
                    [(int) $appointment->patient_id],
                    '['.$notificationTitle.'] '.$notificationBody,
                    'appointment'
                );
            }
        }

        return $queue;
    }

    private function isDoctorUnavailable(int $doctorId): bool
    {
        $payload = Cache::store('file')->get('doctor_availability:'.$doctorId);
        return is_array($payload) && ($payload['is_available'] ?? null) === false;
    }

    public function destroy(Request $request, Queue $queue)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $queue->delete();

        return response()->json([
            'message' => 'Queue entry deleted',
        ]);
    }
}
