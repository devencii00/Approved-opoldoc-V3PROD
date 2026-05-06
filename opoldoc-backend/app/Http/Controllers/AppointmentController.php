<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Conversation;
use App\Models\DoctorSchedule;
use App\Models\LogEntry;
use App\Models\MedicalBackground;
use App\Models\Message;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AppointmentController extends Controller
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

        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'search' => ['nullable', 'string'],
            'order' => ['nullable', 'in:latest,oldest'],
            'service_id' => ['nullable', 'integer', 'exists:services,service_id'],
            'today_only' => ['nullable', 'boolean'],
        ]);

        $query = Appointment::with(['patient', 'doctor', 'queue', 'services']);

        $currentUser = $request->user();

        if ($currentUser && $currentUser->role === 'patient') {
            $query->whereIn('patient_id', $currentUser->accessiblePatientIds());
        } elseif ($request->filled('patient_id')) {
            $query->where('patient_id', $request->query('patient_id'));
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->query('doctor_id'));
        }

        if ($request->filled('appointment_type')) {
            $rawType = strtolower(trim((string) $request->query('appointment_type')));
            $normalizedType = str_replace(['-', ' '], '_', $rawType);

            if (in_array($normalizedType, ['walk_in', 'walkin'], true)) {
                $query->whereIn('appointment_type', ['walk_in', 'walk-in', 'walk in', 'walkin']);
            } elseif ($normalizedType === 'scheduled') {
                $query->where('appointment_type', 'scheduled');
            } else {
                $query->where('appointment_type', $request->query('appointment_type'));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('service_id')) {
            $serviceId = (int) $request->query('service_id');
            $query->whereHas('services', function ($q) use ($serviceId) {
                $q->where('services.service_id', $serviceId);
            });
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $contains = '%'.$search.'%';
            $tokens = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $query->where(function ($q) use ($search, $contains, $tokens) {
                $q->where('reason_for_visit', 'like', $contains);

                if (is_numeric($search)) {
                    $q->orWhere('appointment_id', (int) $search);
                }

                $q->orWhereHas('patient', function ($p) use ($contains, $tokens) {
                    $p->where('email', 'like', $contains)
                        ->orWhere('firstname', 'like', $contains)
                        ->orWhere('lastname', 'like', $contains)
                        ->orWhere('middlename', 'like', $contains)
                        ->orWhere('contact_number', 'like', $contains)
                        ->orWhereRaw("TRIM(CONCAT_WS(' ', firstname, middlename, lastname)) like ?", [$contains]);

                    foreach ($tokens as $token) {
                        $piece = '%'.$token.'%';
                        $p->orWhere(function ($w) use ($piece) {
                            $w->where('firstname', 'like', $piece)
                                ->orWhere('middlename', 'like', $piece)
                                ->orWhere('lastname', 'like', $piece);
                        });
                    }
                });

                $q->orWhereHas('doctor', function ($d) use ($contains, $tokens) {
                    $d->where('email', 'like', $contains)
                        ->orWhere('firstname', 'like', $contains)
                        ->orWhere('lastname', 'like', $contains)
                        ->orWhere('middlename', 'like', $contains)
                        ->orWhere('license_number', 'like', $contains)
                        ->orWhere('specialization', 'like', $contains)
                        ->orWhereRaw("TRIM(CONCAT_WS(' ', firstname, middlename, lastname)) like ?", [$contains]);

                    foreach ($tokens as $token) {
                        $piece = '%'.$token.'%';
                        $d->orWhere(function ($w) use ($piece) {
                            $w->where('firstname', 'like', $piece)
                                ->orWhere('middlename', 'like', $piece)
                                ->orWhere('lastname', 'like', $piece);
                        });
                    }
                });
            });
        }

        if ($request->boolean('queue_request_only')) {
            $query->where('appointment_type', 'scheduled')->whereNull('appointment_datetime');
        }

        if ($request->boolean('upcoming_only')) {
            $query->whereNotNull('appointment_datetime')
                ->where('appointment_datetime', '>=', now());
        }

        if ($request->filled('start_date') || $request->filled('end_date')) {
            $startRaw = $request->query('start_date', null);
            $endRaw = $request->query('end_date', null);

            $start = $startRaw ? Carbon::parse($startRaw)->startOfDay() : null;
            $end = $endRaw ? Carbon::parse($endRaw)->endOfDay() : null;

            if (! $start && $end) {
                $start = $end->copy()->startOfDay();
            }
            if ($start && ! $end) {
                $end = $start->copy()->endOfDay();
            }

            if ($start && $end) {
                $query->whereNotNull('appointment_datetime')
                    ->whereBetween('appointment_datetime', [$start, $end]);
            }
        }

        if ($request->boolean('today_only')) {
            $query->whereNotNull('appointment_datetime')
                ->whereDate('appointment_datetime', now()->toDateString());
        }

        $order = (string) $request->query('order', 'oldest');

        if ($order === 'latest') {
            return $query
                ->orderByRaw('appointment_datetime IS NULL ASC')
                ->orderByDesc('appointment_datetime')
                ->orderByDesc('appointment_id')
                ->paginate($perPage);
        }

        return $query
            ->orderByRaw('appointment_datetime IS NULL ASC')
            ->orderBy('appointment_datetime')
            ->orderByDesc('appointment_id')
            ->paginate($perPage);
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

        $query = Appointment::query()
            ->where('patient_id', $patientId)
            ->whereIn('status', ['pending', 'confirmed']);

        return response()->json([
            'exists' => $query->exists(),
            'count' => $query->count(),
        ]);
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();
        $isPatient = $currentUser && $currentUser->role === 'patient';
        $isReceptionist = $currentUser && $currentUser->role === 'receptionist';

        $queueRequest = $request->boolean('queue_request');

        $data = $request->validate([
            'patient_id' => [$isPatient ? 'sometimes' : 'required', 'exists:users,user_id'],
            'doctor_id' => ['required', 'exists:users,user_id'],
            'appointment_datetime' => ['nullable', 'date'],
            'appointment_type' => ['required', 'in:walk_in,scheduled'],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled,no_show'],
            'reason_for_visit' => ['nullable', 'string'],
            'priority_level' => ['nullable', 'integer'],
            'service_id' => ['nullable', 'exists:services,service_id'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,service_id'],
            'force_duplicate_queue' => ['nullable', 'boolean'],
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

        if ($isPatient) {
            $targetPatientId = $currentUser->user_id;
            if ($request->filled('patient_id')) {
                $candidate = (int) $request->input('patient_id');
                if (! $currentUser->canAccessPatientId($candidate)) {
                    abort(403);
                }
                $targetPatientId = $candidate;
            }

            $data['patient_id'] = $targetPatientId;
            $data['appointment_type'] = 'scheduled';
        }

        if ($isReceptionist && $data['appointment_type'] === 'walk_in') {
            $patientId = (int) ($data['patient_id'] ?? 0);
            $forceDuplicateQueue = (bool) ($data['force_duplicate_queue'] ?? false);
            if ($patientId > 0) {
                $date = now()->toDateString();
                $alreadyQueued = Queue::query()
                    ->whereDate('queue_datetime', $date)
                    ->whereIn('status', ['waiting', 'serving'])
                    ->whereHas('appointment', function ($q) use ($patientId) {
                        $q->where('patient_id', $patientId);
                    })
                    ->exists();

                if ($alreadyQueued && ! $forceDuplicateQueue) {
                    return response()->json([
                        'message' => 'This patient is already in the queue.',
                        'code' => 'PATIENT_ALREADY_IN_QUEUE',
                    ], 422);
                }
            }
        }

        if ($data['appointment_type'] === 'scheduled' && ! $queueRequest && empty($data['appointment_datetime'])) {
            return response()->json([
                'message' => 'Appointment datetime is required.',
            ], 422);
        }

        $serviceIds = [];
        if (array_key_exists('service_ids', $data) && is_array($data['service_ids'])) {
            $serviceIds = array_values(array_filter(array_map(fn ($v) => (int) $v, $data['service_ids']), fn ($v) => $v > 0));
        }
        if (array_key_exists('service_id', $data) && $data['service_id']) {
            $serviceIds[] = (int) $data['service_id'];
        }
        $serviceIds = array_values(array_unique($serviceIds));

        if ($isPatient && ! count($serviceIds)) {
            return response()->json([
                'message' => 'Service is required.',
                'code' => 'SERVICE_REQUIRED',
            ], 422);
        }

        unset($data['service_id'], $data['service_ids'], $data['force_duplicate_queue']);

        $services = collect();
        if (count($serviceIds)) {
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
            if ($data['appointment_type'] === 'walk_in' && $serviceGroups->count() === 1 && in_array((string) $serviceGroups->first(), $blocked, true)) {
                return response()->json([
                    'message' => 'Selected service is only available for scheduled appointments.',
                    'code' => 'SERVICE_SCHEDULED_ONLY',
                ], 422);
            }
        }

        if (! empty($data['appointment_datetime'])) {
            $dt = Carbon::parse($data['appointment_datetime']);
            $doctorId = (int) ($data['doctor_id'] ?? 0);

            if ($data['appointment_type'] === 'scheduled' && $dt->isPast()) {
                return response()->json([
                    'message' => 'Appointment datetime must be in the future.',
                    'code' => 'DATETIME_IN_PAST',
                ], 422);
            }

            $dayKey = strtolower($dt->format('D'));
            $timeValue = $dt->format('H:i:s');

            $isToday = $dt->toDateString() === now()->toDateString();

            $daySchedules = DoctorSchedule::query()
                ->where('doctor_id', $doctorId)
                ->where('day_of_week', $dayKey)
                ->when($isToday, function ($q) {
                    $q->where('is_available', true);
                })
                ->orderBy('start_time')
                ->get();

            if ($daySchedules->isEmpty()) {
                return response()->json([
                    'message' => 'Doctor is not available at the selected time.',
                    'code' => 'DOCTOR_NOT_AVAILABLE',
                ], 422);
            }

            $slotMinutes = 60;
            $intervals = [];

            foreach ($daySchedules as $row) {
                $startStr = (string) $row->start_time;
                $endStr = (string) $row->end_time;

                $startStr = str_contains($startStr, ':') ? $startStr : '';
                $endStr = str_contains($endStr, ':') ? $endStr : '';
                if ($startStr === '' || $endStr === '') {
                    continue;
                }

                $startTime = Carbon::createFromFormat('H:i:s', substr($startStr, 0, 8));
                $endTime = Carbon::createFromFormat('H:i:s', substr($endStr, 0, 8));

                $startMin = ((int) $startTime->format('H')) * 60 + (int) $startTime->format('i');
                $endMin = ((int) $endTime->format('H')) * 60 + (int) $endTime->format('i');

                if ($endMin <= $startMin) {
                    continue;
                }

                $intervals[] = [$startMin, $endMin];
            }

            usort($intervals, fn ($a, $b) => $a[0] <=> $b[0]);

            $merged = [];
            foreach ($intervals as $interval) {
                if (empty($merged)) {
                    $merged[] = $interval;
                    continue;
                }
                $lastIdx = count($merged) - 1;
                [$ls, $le] = $merged[$lastIdx];
                [$cs, $ce] = $interval;
                if ($cs <= $le) {
                    $merged[$lastIdx] = [$ls, max($le, $ce)];
                } else {
                    $merged[] = $interval;
                }
            }

            $selectedMin = ((int) $dt->format('H')) * 60 + (int) $dt->format('i');
            $allowed = [];
            foreach ($merged as [$startMin, $endMin]) {
                for ($m = $startMin; $m + $slotMinutes <= $endMin; $m += $slotMinutes) {
                    $allowed[$m] = true;
                }
            }

            if (! isset($allowed[$selectedMin])) {
                return response()->json([
                    'message' => 'Selected time slot is not available.',
                    'code' => 'SLOT_INVALID',
                ], 422);
            }

            $slotStart = $dt->copy()->seconds(0);
            $slotEnd = $slotStart->copy()->addMinutes($slotMinutes);

            $existingDoctorAppointments = Appointment::query()
                ->where('doctor_id', $doctorId)
                ->whereNotNull('appointment_datetime')
                ->where('appointment_type', 'scheduled')
                ->where('status', '!=', 'cancelled')
                ->whereDate('appointment_datetime', $slotStart->toDateString())
                ->get(['appointment_id', 'appointment_datetime']);

            foreach ($existingDoctorAppointments as $appt) {
                $existingStart = Carbon::parse((string) $appt->appointment_datetime)->seconds(0);
                $existingEnd = $existingStart->copy()->addMinutes($slotMinutes);
                if ($existingStart->lt($slotEnd) && $existingEnd->gt($slotStart)) {
                    return response()->json([
                        'message' => 'Selected time slot already has an appointment.',
                        'code' => 'DOCTOR_CONFLICT',
                    ], 422);
                }
            }

            $patientId = (int) ($data['patient_id'] ?? 0);
            if ($patientId > 0) {
                $existingPatientAppointments = Appointment::query()
                    ->where('patient_id', $patientId)
                    ->whereNotNull('appointment_datetime')
                    ->where('appointment_type', 'scheduled')
                    ->where('status', '!=', 'cancelled')
                    ->whereDate('appointment_datetime', $slotStart->toDateString())
                    ->get(['appointment_id', 'appointment_datetime']);

                foreach ($existingPatientAppointments as $appt) {
                    $existingStart = Carbon::parse((string) $appt->appointment_datetime)->seconds(0);
                    $existingEnd = $existingStart->copy()->addMinutes($slotMinutes);
                    if ($existingStart->lt($slotEnd) && $existingEnd->gt($slotStart)) {
                        return response()->json([
                            'message' => 'Patient already has an appointment in the selected time slot.',
                            'code' => 'PATIENT_CONFLICT',
                        ], 422);
                    }
                }
            }

            if (count($serviceIds)) {
                $doctor = User::query()->find($doctorId);

                if ($doctor) {
                    $doctorSpec = strtolower(trim((string) ($doctor->specialization ?? '')));
                    $doctorSpec = trim($doctorSpec);

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
                }
            }
        }

        if ($data['appointment_type'] === 'scheduled' && $isPatient) {
            $patientId = (int) ($data['patient_id'] ?? 0);
            $hasMedicalBackground = $patientId > 0
                ? MedicalBackground::query()->where('patient_id', $patientId)->exists()
                : false;

            if (! $hasMedicalBackground) {
                return response()->json([
                    'message' => 'Medical background is required before booking an appointment.',
                    'code' => 'MEDICAL_BACKGROUND_REQUIRED',
                ], 428);
            }
        }

        $data['created_by'] = $request->user()->user_id ?? null;

        if ($isReceptionist) {
            $data['status'] = 'confirmed';
        } elseif (! isset($data['status'])) {
            $data['status'] = 'pending';
        }

        if ($data['appointment_type'] === 'walk_in' && (! isset($data['appointment_datetime']) || $data['appointment_datetime'] === null)) {
            $data['appointment_datetime'] = now();
        }

        $appointment = DB::transaction(function () use ($data, $serviceIds) {
            $appointment = Appointment::create($data);
            if (count($serviceIds)) {
                $appointment->services()->sync(array_map(fn ($v) => (int) $v, $serviceIds));
            }
            return $appointment;
        });

        LogEntry::write(
            optional($request->user())->user_id ? (int) $request->user()->user_id : null,
            'appointment_created',
            'appointments',
            (int) $appointment->appointment_id,
            [
                'patient_id' => (int) $appointment->patient_id,
                'doctor_id' => (int) $appointment->doctor_id,
                'status' => (string) ($appointment->status ?? ''),
            ]
        );

        return response()->json($appointment->load(['patient', 'doctor', 'services']), 201);
    }

    public function show(Appointment $appointment)
    {
        $currentUser = request()->user();
        if ($currentUser && $currentUser->role === 'patient') {
            if (! $currentUser->canAccessPatientId((int) $appointment->patient_id)) {
                abort(403);
            }
        }

        return $appointment->load(['patient', 'doctor', 'queue', 'transaction', 'services']);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $previousDoctorId = (int) $appointment->doctor_id;

        $data = $request->validate([
            'patient_id' => ['sometimes', 'exists:users,user_id'],
            'doctor_id' => ['sometimes', 'exists:users,user_id'],
            'appointment_datetime' => ['sometimes', 'date'],
            'appointment_type' => ['sometimes', 'in:walk_in,scheduled'],
            'status' => ['sometimes', 'in:pending,confirmed,completed,cancelled,no_show'],
            'reason_for_visit' => ['sometimes', 'nullable', 'string'],
            'priority_level' => ['sometimes', 'integer'],
            'check_in_time' => ['sometimes', 'nullable', 'date'],
            'service_id' => ['sometimes', 'nullable', 'exists:services,service_id'],
            'service_ids' => ['sometimes', 'array'],
            'service_ids.*' => ['integer', 'exists:services,service_id'],
        ]);

        $serviceIds = null;
        if (array_key_exists('service_ids', $data) || array_key_exists('service_id', $data)) {
            $ids = [];
            if (array_key_exists('service_ids', $data) && is_array($data['service_ids'])) {
                $ids = array_values(array_filter(array_map(fn ($v) => (int) $v, $data['service_ids']), fn ($v) => $v > 0));
            }
            if (array_key_exists('service_id', $data) && $data['service_id']) {
                $ids[] = (int) $data['service_id'];
            }
            $ids = array_values(array_unique($ids));
            unset($data['service_id'], $data['service_ids']);

            $services = collect();
            if (count($ids)) {
                $services = Service::query()
                    ->whereIn('service_id', $ids)
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

                $doctorIdForCheck = array_key_exists('doctor_id', $data) ? (int) $data['doctor_id'] : (int) $appointment->doctor_id;
                $doctor = User::query()->find($doctorIdForCheck);
                if ($doctor) {
                    $doctorSpec = strtolower(trim((string) ($doctor->specialization ?? '')));
                    $doctorSpec = trim($doctorSpec);

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
                }
            }

            $serviceIds = $ids;
        }

        if (array_key_exists('doctor_id', $data)) {
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
        }

        DB::transaction(function () use ($appointment, $data, $serviceIds) {
            $appointment->update($data);
            if ($serviceIds !== null) {
                $appointment->services()->sync(array_map(fn ($v) => (int) $v, $serviceIds));
            }
        });

        $appointment->refresh();

        $doctorChanged = array_key_exists('doctor_id', $data) && (int) $appointment->doctor_id !== $previousDoctorId;
        if ($doctorChanged) {
            $appointment->loadMissing(['patient', 'doctor']);
            $newDoctorName = trim(implode(' ', array_filter([
                $appointment->doctor?->firstname,
                $appointment->doctor?->lastname,
            ])));
            if ($newDoctorName === '') {
                $newDoctorName = 'Doctor #'.$appointment->doctor_id;
            }

            $conversation = Conversation::ensureForPatient((int) $appointment->patient_id);

            Message::create([
                'conversation_id' => $conversation->conversation_id,
                'sender' => 'bot',
                'message_text' => 'Your doctor has been reassigned to '.$newDoctorName.'.',
            ]);
        }

        LogEntry::write(
            optional($request->user())->user_id ? (int) $request->user()->user_id : null,
            'appointment_updated',
            'appointments',
            (int) $appointment->appointment_id,
            [
                'fields' => array_keys($data),
            ]
        );

        return $appointment->load(['patient', 'doctor', 'queue', 'transaction', 'services']);
    }

    private function isDoctorUnavailable(int $doctorId): bool
    {
        $payload = Cache::store('file')->get('doctor_availability:'.$doctorId);
        return is_array($payload) && ($payload['is_available'] ?? null) === false;
    }

    public function destroy(Appointment $appointment)
    {
        $currentUser = request()->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $appointment->delete();

        LogEntry::write(
            optional(request()->user())->user_id ? (int) request()->user()->user_id : null,
            'appointment_deleted',
            'appointments',
            (int) $appointment->appointment_id,
            [
                'patient_id' => (int) $appointment->patient_id,
                'doctor_id' => (int) $appointment->doctor_id,
            ]
        );

        return response()->json([
            'message' => 'Appointment deleted',
        ]);
    }
}
