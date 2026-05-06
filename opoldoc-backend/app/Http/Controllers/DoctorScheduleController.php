<?php

namespace App\Http\Controllers;

use App\Models\DoctorSchedule;
use App\Models\LogEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DoctorScheduleController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'doctor_id' => ['nullable', 'integer', 'exists:users,user_id'],
            'available_only' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = (int) ($data['per_page'] ?? $request->query('per_page', 50));
        if ($perPage < 1) {
            $perPage = 50;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $currentUser = $request->user();
        $doctorId = $data['doctor_id'] ?? $request->query('doctor_id');
        $availableOnly = $request->boolean('available_only');

        if ($currentUser && $currentUser->role === 'doctor') {
            $doctorId = $doctorId ?: $currentUser->user_id;
            if ((int) $doctorId !== (int) $currentUser->user_id) {
                abort(403);
            }
        }

        try {
            $result = DoctorSchedule::query()
                ->with(['doctor'])
                ->when($doctorId, function ($q) use ($doctorId) {
                    $q->where('doctor_id', (int) $doctorId);
                })
                ->when($availableOnly, function ($q) {
                    $q->where('is_available', true);
                })
                ->orderByRaw("CASE day_of_week WHEN 'mon' THEN 1 WHEN 'tue' THEN 2 WHEN 'wed' THEN 3 WHEN 'thu' THEN 4 WHEN 'fri' THEN 5 WHEN 'sat' THEN 6 WHEN 'sun' THEN 7 ELSE 8 END")
                ->orderBy('start_time')
                ->orderBy('schedule_id')
                ->paginate($perPage);

            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error('DoctorSchedule index error', [
                'message' => $e->getMessage(),
                'doctor_id' => $doctorId,
                'user_id' => $currentUser?->user_id,
                'role' => $currentUser?->role,
            ]);

            return response()->json([
                'message' => config('app.debug') ? ('Failed to load schedules: '.$e->getMessage()) : 'Failed to load schedules.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser || $currentUser->role !== 'admin') {
            abort(403);
        }

        $data = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:users,user_id'],
            'from_day' => ['required', 'in:mon,tue,wed,thu,fri,sat,sun'],
            'to_day' => ['required', 'in:mon,tue,wed,thu,fri,sat,sun'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'slot_minutes' => ['sometimes', 'integer', 'min:15', 'max:240'],
            'room_number' => ['nullable', 'integer', 'min:1'],
            'max_patients' => ['nullable', 'integer', 'min:1'],
        ]);

        $slotMinutes = (int) ($data['slot_minutes'] ?? 60);

        $startMinutes = $this->minutesFromTime($data['start_time']);
        $endMinutes = $this->minutesFromTime($data['end_time']);

        if ($startMinutes === null || $endMinutes === null || $endMinutes <= $startMinutes) {
            return response()->json(['message' => 'End time must be after start time.'], 422);
        }

        $diff = $endMinutes - $startMinutes;
        if ($diff % $slotMinutes !== 0) {
            return response()->json([
                'message' => 'Time range must be divisible by slot minutes.',
            ], 422);
        }

        $days = $this->daysBetween($data['from_day'], $data['to_day']);
        $doctorId = (int) $data['doctor_id'];

        $result = DB::transaction(function () use ($doctorId, $days, $startMinutes, $endMinutes, $slotMinutes, $data) {
            $created = 0;
            $updated = 0;
            $slotIds = [];

            foreach ($days as $dayKey) {
                for ($m = $startMinutes; $m < $endMinutes; $m += $slotMinutes) {
                    $slotStart = $this->timeFromMinutes($m);
                    $slotEnd = $this->timeFromMinutes($m + $slotMinutes);

                    $existing = DoctorSchedule::query()
                        ->where('doctor_id', $doctorId)
                        ->where('day_of_week', $dayKey)
                        ->whereTime('start_time', $slotStart)
                        ->whereTime('end_time', $slotEnd)
                        ->first();

                    if ($existing) {
                        $existing->max_patients = $data['max_patients'] ?? null;
                        $existing->room_number = array_key_exists('room_number', $data) ? $data['room_number'] : $existing->room_number;
                        $existing->save();
                        $updated++;
                        $slotIds[] = (int) $existing->schedule_id;
                        continue;
                    }

                    $row = DoctorSchedule::create([
                        'doctor_id' => $doctorId,
                        'day_of_week' => $dayKey,
                        'start_time' => $slotStart,
                        'end_time' => $slotEnd,
                        'room_number' => $data['room_number'] ?? null,
                        'max_patients' => $data['max_patients'] ?? null,
                        'is_available' => true,
                    ]);

                    $created++;
                    $slotIds[] = (int) $row->schedule_id;
                }
            }

            $slots = DoctorSchedule::query()
                ->with(['doctor'])
                ->whereIn('schedule_id', $slotIds)
                ->orderByRaw("CASE day_of_week WHEN 'mon' THEN 1 WHEN 'tue' THEN 2 WHEN 'wed' THEN 3 WHEN 'thu' THEN 4 WHEN 'fri' THEN 5 WHEN 'sat' THEN 6 WHEN 'sun' THEN 7 ELSE 8 END")
                ->orderBy('start_time')
                ->orderBy('schedule_id')
                ->get();

            return [
                'created' => $created,
                'updated' => $updated,
                'slots' => $slots,
                'slot_ids' => $slotIds,
            ];
        });

        LogEntry::write(
            (int) $currentUser->user_id,
            'doctor_schedule_bulk_upsert',
            'users',
            $doctorId,
            [
                'created' => (int) ($result['created'] ?? 0),
                'updated' => (int) ($result['updated'] ?? 0),
                'from_day' => (string) ($data['from_day'] ?? ''),
                'to_day' => (string) ($data['to_day'] ?? ''),
                'start_time' => (string) ($data['start_time'] ?? ''),
                'end_time' => (string) ($data['end_time'] ?? ''),
                'slot_minutes' => (int) $slotMinutes,
                'room_number' => array_key_exists('room_number', $data) ? $data['room_number'] : null,
                'max_patients' => array_key_exists('max_patients', $data) ? $data['max_patients'] : null,
            ]
        );

        return response()->json([
            'created' => (int) ($result['created'] ?? 0),
            'updated' => (int) ($result['updated'] ?? 0),
            'slots' => $result['slots'] ?? [],
        ], 201);
    }

    public function update(Request $request, DoctorSchedule $doctorSchedule)
    {
        $currentUser = $request->user();
        if (! $currentUser || $currentUser->role !== 'admin') {
            abort(403);
        }

        $before = [
            'day_of_week' => (string) $doctorSchedule->day_of_week,
            'start_time' => (string) $doctorSchedule->start_time,
            'end_time' => (string) $doctorSchedule->end_time,
            'room_number' => $doctorSchedule->room_number,
            'max_patients' => $doctorSchedule->max_patients,
            'is_available' => (bool) $doctorSchedule->is_available,
        ];

        $data = $request->validate([
            'day_of_week' => ['sometimes', 'in:mon,tue,wed,thu,fri,sat,sun'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
            'room_number' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'max_patients' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        $start = array_key_exists('start_time', $data) ? (string) $data['start_time'] : (string) $doctorSchedule->start_time;
        $end = array_key_exists('end_time', $data) ? (string) $data['end_time'] : (string) $doctorSchedule->end_time;
        $startMinutes = $this->minutesFromTime(substr($start, 0, 5));
        $endMinutes = $this->minutesFromTime(substr($end, 0, 5));

        if ($startMinutes === null || $endMinutes === null || $endMinutes <= $startMinutes) {
            return response()->json(['message' => 'End time must be after start time.'], 422);
        }

        $doctorSchedule->update([
            'day_of_week' => $data['day_of_week'] ?? $doctorSchedule->day_of_week,
            'start_time' => $data['start_time'] ?? $doctorSchedule->start_time,
            'end_time' => $data['end_time'] ?? $doctorSchedule->end_time,
            'room_number' => array_key_exists('room_number', $data) ? $data['room_number'] : $doctorSchedule->room_number,
            'max_patients' => array_key_exists('max_patients', $data) ? $data['max_patients'] : $doctorSchedule->max_patients,
            'is_available' => array_key_exists('is_available', $data) ? (bool) $data['is_available'] : $doctorSchedule->is_available,
        ]);

        LogEntry::write(
            (int) $currentUser->user_id,
            'doctor_schedule_updated',
            'doctor_schedules',
            (int) $doctorSchedule->schedule_id,
            [
                'doctor_id' => (int) $doctorSchedule->doctor_id,
                'before' => $before,
                'after' => [
                    'day_of_week' => (string) $doctorSchedule->day_of_week,
                    'start_time' => (string) $doctorSchedule->start_time,
                    'end_time' => (string) $doctorSchedule->end_time,
                    'room_number' => $doctorSchedule->room_number,
                    'max_patients' => $doctorSchedule->max_patients,
                    'is_available' => (bool) $doctorSchedule->is_available,
                ],
                'fields' => array_keys($data),
            ]
        );

        return $doctorSchedule->refresh()->load(['doctor']);
    }

    public function destroy(DoctorSchedule $doctorSchedule)
    {
        $currentUser = request()->user();
        if (! $currentUser || $currentUser->role !== 'admin') {
            abort(403);
        }

        $snapshot = [
            'doctor_id' => (int) $doctorSchedule->doctor_id,
            'day_of_week' => (string) $doctorSchedule->day_of_week,
            'start_time' => (string) $doctorSchedule->start_time,
            'end_time' => (string) $doctorSchedule->end_time,
            'room_number' => $doctorSchedule->room_number,
        ];

        $doctorSchedule->delete();

        LogEntry::write(
            (int) $currentUser->user_id,
            'doctor_schedule_deleted',
            'doctor_schedules',
            (int) $doctorSchedule->schedule_id,
            $snapshot
        );

        return response()->json([
            'message' => 'Schedule deleted',
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser || $currentUser->role !== 'admin') {
            abort(403);
        }

        $data = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:users,user_id'],
            'day_of_week' => ['nullable', 'in:mon,tue,wed,thu,fri,sat,sun'],
            'schedule_ids' => ['nullable', 'array'],
            'schedule_ids.*' => ['integer', 'exists:doctor_schedules,schedule_id'],
        ]);

        $doctorId = (int) $data['doctor_id'];
        $day = array_key_exists('day_of_week', $data) ? $data['day_of_week'] : null;
        $ids = array_key_exists('schedule_ids', $data) && is_array($data['schedule_ids'])
            ? array_values(array_unique(array_map('intval', $data['schedule_ids'])))
            : [];

        $query = DoctorSchedule::query()->where('doctor_id', $doctorId);

        $mode = 'all';
        if (! empty($ids)) {
            $query->whereIn('schedule_id', $ids);
            $mode = 'selected';
        } elseif ($day) {
            $query->where('day_of_week', $day);
            $mode = 'day';
        }

        $deleted = DB::transaction(function () use ($query) {
            return (int) $query->delete();
        });

        LogEntry::write(
            (int) $currentUser->user_id,
            'doctor_schedule_bulk_deleted',
            'users',
            $doctorId,
            [
                'mode' => $mode,
                'day_of_week' => $day ? (string) $day : null,
                'schedule_ids_count' => count($ids),
                'deleted' => $deleted,
            ]
        );

        return response()->json([
            'deleted' => $deleted,
        ]);
    }

    public function bulkAvailability(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser) {
            abort(403);
        }

        $data = $request->validate([
            'schedule_ids' => ['required', 'array', 'min:1'],
            'schedule_ids.*' => ['required', 'integer', 'exists:doctor_schedules,schedule_id'],
            'is_available' => ['required', 'boolean'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['schedule_ids'])));
        $isAvailable = (bool) $data['is_available'];

        $query = DoctorSchedule::query()->whereIn('schedule_id', $ids);

        if ($currentUser->role === 'doctor') {
            $query->where('doctor_id', (int) $currentUser->user_id);
        } elseif ($currentUser->role !== 'admin') {
            abort(403);
        }

        $count = (int) $query->count();
        if ($count !== count($ids)) {
            abort(403);
        }

        $doctorIds = DoctorSchedule::query()
            ->whereIn('schedule_id', $ids)
            ->pluck('doctor_id')
            ->unique()
            ->values()
            ->map(function ($v) { return (int) $v; })
            ->all();

        $updated = (int) DoctorSchedule::query()
            ->whereIn('schedule_id', $ids)
            ->when($currentUser->role === 'doctor', function ($q) use ($currentUser) {
                $q->where('doctor_id', (int) $currentUser->user_id);
            })
            ->update(['is_available' => $isAvailable]);

        if (count($doctorIds) === 1) {
            LogEntry::write(
                (int) $currentUser->user_id,
                'doctor_schedule_availability_bulk_changed',
                'users',
                (int) $doctorIds[0],
                [
                    'schedule_ids_count' => count($ids),
                    'updated' => $updated,
                    'is_available' => $isAvailable,
                ]
            );
        } else {
            LogEntry::write(
                (int) $currentUser->user_id,
                'doctor_schedule_availability_bulk_changed',
                'doctor_schedules',
                null,
                [
                    'doctor_ids' => $doctorIds,
                    'schedule_ids_count' => count($ids),
                    'updated' => $updated,
                    'is_available' => $isAvailable,
                ]
            );
        }

        return response()->json([
            'updated' => $updated,
        ]);
    }

    private function minutesFromTime(string $value): ?int
    {
        $value = trim($value);
        if (! preg_match('/^\d{2}:\d{2}$/', $value)) {
            return null;
        }
        [$h, $m] = array_map('intval', explode(':', $value, 2));
        if ($h < 0 || $h > 23 || $m < 0 || $m > 59) {
            return null;
        }

        return ($h * 60) + $m;
    }

    private function timeFromMinutes(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return sprintf('%02d:%02d', $h, $m);
    }

    private function daysBetween(string $from, string $to): array
    {
        $order = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $fromIndex = array_search($from, $order, true);
        $toIndex = array_search($to, $order, true);

        if ($fromIndex === false || $toIndex === false) {
            return [];
        }

        if ($fromIndex <= $toIndex) {
            return array_slice($order, $fromIndex, $toIndex - $fromIndex + 1);
        }

        return array_merge(
            array_slice($order, $fromIndex),
            array_slice($order, 0, $toIndex + 1)
        );
    }
}
