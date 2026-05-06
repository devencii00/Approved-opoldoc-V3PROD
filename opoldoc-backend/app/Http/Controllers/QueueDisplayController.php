<?php

namespace App\Http\Controllers;

use App\Models\DoctorSchedule;
use App\Models\Queue;
use Illuminate\Http\Request;

class QueueDisplayController extends Controller
{
    public function page(Request $request)
    {
        $date = now()->toDateString();

        $doctorId = $request->query('doctor_id');

        return view('public.queue_display', [
            'date' => $date,
            'doctorId' => $doctorId,
        ]);
    }

    public function data(Request $request)
    {
        $date = now()->toDateString();

        $doctorId = $request->query('doctor_id');

        $query = Queue::query()
            ->with(['appointment.patient', 'appointment.doctor', 'appointment.services'])
            ->whereDate('queue_datetime', $date);

        if ($doctorId) {
            $query->whereHas('appointment', function ($q) use ($doctorId) {
                $q->where('doctor_id', (int) $doctorId);
            });
        }

        $items = $query->get();

        $activeStatuses = $items->filter(function ($q) {
            $status = (string) ($q->status ?? '');
            return $status === 'waiting' || $status === 'serving';
        })->values();

        $waiting = $activeStatuses
            ->filter(function ($q) {
                return (string) ($q->status ?? '') === 'waiting';
            })
            ->sort(function (Queue $a, Queue $b) {
                if ($a->status === 'serving' && $b->status !== 'serving') {
                    return -1;
                }
                if ($b->status === 'serving' && $a->status !== 'serving') {
                    return 1;
                }

                $now = now();
                $sa = $a->totalScore($now);
                $sb = $b->totalScore($now);
                if ($sa !== $sb) {
                    return $sb <=> $sa;
                }
                $na = (int) ($a->queue_number ?? 999999);
                $nb = (int) ($b->queue_number ?? 999999);
                if ($na !== $nb) {
                    return $na <=> $nb;
                }
                return (int) ($a->queue_id ?? 0) <=> (int) ($b->queue_id ?? 0);
            })
            ->values();

        $defaultMinutesPerPatient = (int) env('QUEUE_MINUTES_PER_PATIENT', 10);
        if ($defaultMinutesPerPatient < 1) {
            $defaultMinutesPerPatient = 10;
        }
        if ($defaultMinutesPerPatient > 120) {
            $defaultMinutesPerPatient = 120;
        }

        $byDoctor = $activeStatuses->groupBy(function ($q) {
            return (int) ($q->appointment?->doctor_id ?? 0);
        });

        $avgMinutesByDoctor = [];
        $sortedByDoctor = [];
        foreach ($byDoctor as $docId => $group) {
            $docId = (int) $docId;
            if ($docId < 1) {
                continue;
            }

            $durations = [];
            foreach ($group as $row) {
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
            $avgMinutesByDoctor[$docId] = $avg;

            $now = now();
            $sortedByDoctor[$docId] = collect($group)->sort(function (Queue $a, Queue $b) use ($now) {
                if ($a->status === 'serving' && $b->status !== 'serving') {
                    return -1;
                }
                if ($b->status === 'serving' && $a->status !== 'serving') {
                    return 1;
                }

                $sa = $a->totalScore($now);
                $sb = $b->totalScore($now);
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
        }

        $estimatedByQueueId = [];
        foreach ($sortedByDoctor as $docId => $sorted) {
            $avg = (int) ($avgMinutesByDoctor[$docId] ?? $defaultMinutesPerPatient);
            foreach ($sorted as $idx => $row) {
                $aheadCount = (int) $idx;
                $estimatedByQueueId[(int) $row->queue_id] = [
                    'ahead_count' => $aheadCount,
                    'avg_service_minutes' => $avg,
                    'estimated_wait_minutes' => ((string) ($row->status ?? '') === 'serving') ? 0 : max(0, $aheadCount * $avg),
                ];
            }
        }

        $next = $waiting->take(10)->values();

        $formatPerson = function ($user, string $fallback) {
            if (! $user) {
                return $fallback;
            }
            $full = $user->personalInformation->full_name ?? null;
            $full = is_string($full) ? trim($full) : '';
            return $full !== '' ? $full : $fallback;
        };

        $roomByDoctor = [];
        if (! $doctorId) {
            $now = now();
            $dayKey = strtolower($now->format('D'));
            $time = $now->format('H:i:s');

            $activeDoctorIds = DoctorSchedule::query()
                ->where('day_of_week', $dayKey)
                ->where('is_available', true)
                ->pluck('doctor_id')
                ->unique()
                ->values()
                ->all();

            $activeDoctorIds = array_slice(array_map(fn ($v) => (int) $v, $activeDoctorIds), 0, 4);

            if (count($activeDoctorIds)) {
                $schedules = DoctorSchedule::query()
                    ->whereIn('doctor_id', $activeDoctorIds)
                    ->where('day_of_week', $dayKey)
                    ->get(['doctor_id', 'room_number', 'start_time', 'end_time', 'is_available'])
                    ->groupBy('doctor_id');

                foreach ($activeDoctorIds as $docId) {
                    $room = null;
                    $group = $schedules->get($docId);
                    $schedule = null;
                    if ($group) {
                        $schedule = $group
                            ->filter(function ($s) use ($time) {
                                return $s->start_time <= $time && $s->end_time >= $time && $s->is_available;
                            })
                            ->sortBy('start_time')
                            ->first();

                        if (! $schedule) {
                            $schedule = $group
                                ->filter(function ($s) use ($time) {
                                    return $s->start_time <= $time && $s->end_time >= $time;
                                })
                                ->sortBy('start_time')
                                ->first();
                        }

                        if (! $schedule) {
                            $schedule = $group
                                ->filter(fn ($s) => (bool) $s->is_available)
                                ->sortBy('start_time')
                                ->first();
                        }

                        if (! $schedule) {
                            $schedule = $group->sortBy('start_time')->first();
                        }
                    }
                    if ($schedule) {
                        $roomValue = $schedule->room_number;
                        $room = $roomValue !== null ? (int) $roomValue : null;
                    }
                    $roomByDoctor[(int) $docId] = $room;
                }
            }
        } else {
            $now = now();
            $dayKey = strtolower($now->format('D'));
            $time = $now->format('H:i:s');
            $schedule = DoctorSchedule::query()
                ->where('doctor_id', (int) $doctorId)
                ->where('day_of_week', $dayKey)
                ->get(['doctor_id', 'room_number', 'start_time', 'end_time', 'is_available']);

            $picked = $schedule
                ->filter(function ($s) use ($time) {
                    return $s->start_time <= $time && $s->end_time >= $time && $s->is_available;
                })
                ->sortBy('start_time')
                ->first();

            if (! $picked) {
                $picked = $schedule
                    ->filter(function ($s) use ($time) {
                        return $s->start_time <= $time && $s->end_time >= $time;
                    })
                    ->sortBy('start_time')
                    ->first();
            }

            if (! $picked) {
                $picked = $schedule->filter(fn ($s) => (bool) $s->is_available)->sortBy('start_time')->first();
            }

            if (! $picked) {
                $picked = $schedule->sortBy('start_time')->first();
            }

            $roomByDoctor[(int) $doctorId] = $picked && $picked->room_number !== null ? (int) $picked->room_number : null;
        }

        $servingItems = $activeStatuses
            ->filter(function ($q) {
                return (string) ($q->status ?? '') === 'serving';
            })
            ->values();

        if (! $doctorId) {
            $activeDoctorIdsForServing = array_keys($roomByDoctor);
            if (count($activeDoctorIdsForServing)) {
                $servingItems = $servingItems
                    ->filter(function ($q) use ($activeDoctorIdsForServing) {
                        $docId = (int) ($q->appointment?->doctor_id ?? 0);
                        return in_array($docId, $activeDoctorIdsForServing, true);
                    })
                    ->sortBy(function ($q) use ($roomByDoctor) {
                        $docId = (int) ($q->appointment?->doctor_id ?? 0);
                        $room = $roomByDoctor[$docId] ?? null;
                        return str_pad((string) ((int) ($room ?? 999)), 6, '0', STR_PAD_LEFT).'-'.str_pad((string) $docId, 10, '0', STR_PAD_LEFT);
                    })
                    ->values()
                    ->take(4)
                    ->values();
            } else {
                $servingItems = $servingItems->take(4)->values();
            }
        } else {
            $servingItems = $servingItems->take(1)->values();
        }

        $payload = [
            'date' => $date,
            'doctor_id' => $doctorId ? (int) $doctorId : null,
            'now_serving' => $servingItems->map(function ($q) use ($formatPerson, $roomByDoctor, $estimatedByQueueId) {
                $docId = (int) ($q->appointment?->doctor_id ?? 0);
                $room = $docId ? ($roomByDoctor[$docId] ?? null) : null;
                $est = $estimatedByQueueId[(int) $q->queue_id] ?? null;
                return [
                    'queue_id' => $q->queue_id,
                    'queue_number' => $q->queue_number,
                    'queue_code' => $q->queue_code,
                    'status' => $q->status,
                    'priority_level' => $q->priority_level,
                    'room_number' => $room,
                    'estimated_wait_minutes' => $est['estimated_wait_minutes'] ?? 0,
                    'avg_service_minutes' => $est['avg_service_minutes'] ?? null,
                    'patient' => [
                        'user_id' => $q->appointment?->patient_id,
                        'name' => $formatPerson($q->appointment?->patient, 'Patient'),
                    ],
                    'doctor' => [
                        'user_id' => $docId ?: null,
                        'name' => $formatPerson($q->appointment?->doctor, 'Doctor'),
                    ],
                ];
            })->all(),
            'next' => $next->map(function ($q) use ($formatPerson, $estimatedByQueueId) {
                $est = $estimatedByQueueId[(int) $q->queue_id] ?? null;
                return [
                    'queue_id' => $q->queue_id,
                    'queue_number' => $q->queue_number,
                    'queue_code' => $q->queue_code,
                    'status' => $q->status,
                    'priority_level' => $q->priority_level,
                    'estimated_wait_minutes' => $est['estimated_wait_minutes'] ?? null,
                    'avg_service_minutes' => $est['avg_service_minutes'] ?? null,
                    'patient' => [
                        'user_id' => $q->appointment?->patient_id,
                        'name' => $formatPerson($q->appointment?->patient, 'Patient'),
                    ],
                    'doctor' => [
                        'user_id' => $q->appointment?->doctor_id,
                        'name' => $formatPerson($q->appointment?->doctor, 'Doctor'),
                    ],
                ];
            })->all(),
            'counts' => [
                'waiting' => $waiting->count(),
                'total' => $items->count(),
            ],
            'generated_at' => now()->toIso8601String(),
        ];

        return response()->json($payload);
    }
}
