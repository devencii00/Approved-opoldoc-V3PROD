@php
    $metrics = $doctorMetrics ?? [];
    $appointmentsToday = (int) ($metrics['appointmentsToday'] ?? 0);
    $queueToday = (int) ($metrics['queueToday'] ?? 0);
    $completedToday = (int) ($metrics['completedToday'] ?? 0);
    $pendingPrescriptionsToday = (int) ($metrics['pendingPrescriptionsToday'] ?? 0);
    $unreadNotificationsCount = (int) ($metrics['unreadNotificationsCount'] ?? 0);
    $recentAppointments = $doctorRecentAppointments ?? [];
    $recentVisits = $doctorRecentVisits ?? [];
    $recentQueue = $doctorRecentQueue ?? [];
    $todayAppointments = $doctorTodayAppointments ?? [];
    $todayQueue = $doctorTodayQueue ?? [];
    $recentNotifications = $doctorRecentNotifications ?? collect();
    $todayIso = now()->toDateString();
    $currentUserIdQuery = request()->query('user_id');
    $todayUpcomingAppointments = collect($recentAppointments)
        ->filter(function ($appointment) use ($todayIso) {
            return optional($appointment->appointment_datetime)->format('Y-m-d') === $todayIso;
        })
        ->values();

    $formatUserName = function ($user) {
        if (! $user) {
            return '';
        }
        $parts = array_filter([
            $user->firstname ?? null,
            $user->middlename ?? null,
            $user->lastname ?? null,
        ], function ($v) {
            return (string) $v !== '';
        });
        $name = trim(implode(' ', $parts));
        return $name !== '' ? $name : ('User #' . ($user->user_id ?? ''));
    };

    $sectionKey = $section ?? 'overview';

    $effectiveSectionKey = $sectionKey;

    if ($effectiveSectionKey === 'my-schedule') {
        $effectiveSectionKey = 'appointments';
    } elseif ($effectiveSectionKey === 'history') {
        $effectiveSectionKey = 'visits';
    }

    $sectionTitles = [
        'my-patients' => 'My patients',
        'appointments' => 'My Schedule',
        'queue' => 'Queue',
        'visits' => 'History',
        'history' => 'History',
        'prescriptions' => 'Prescription',
        'my-activity' => 'My activity',
        'consultation' => 'Consultation',
        'settings-doctor' => 'Settings',
    ];

    $sectionSubtitles = [
        'my-patients' => 'Patients you are actively seeing or have seen recently.',
        'appointments' => 'Review upcoming and recent appointments.',
        'queue' => 'See today’s queue and recent queue entries.',
        'visits' => 'View past patient visits and records.',
        'history' => 'View past patient visits and records.',
        'prescriptions' => 'Review prescriptions you have issued.',
        'my-activity' => 'High-level view of your recent clinical activity.',
        'consultation' => 'Consult with a selected patient and record notes.',
        'settings-doctor' => 'Update your profile, password, and signature.',
    ];
@endphp

<div class="space-y-6">
    @if ($sectionKey === 'overview')
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 mb-1">Doctor Dashboard</h1>
            <p class="text-sm text-slate-500">Today’s appointments and queue list for your clinic day.</p>
        </div>

    <div class="grid gap-4 grid-cols-1 lg:grid-cols-3">
            <div class="bg-white border border-slate-200 rounded-[18px] p-5 lg:col-span-2 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-slate-900">Today&apos;s schedule</h2>
                    <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Consultations</span>
                </div>
                <div class="grid gap-3 grid-cols-1 sm:grid-cols-4 text-sm text-slate-600">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-xs text-slate-500 mb-1">Today’s appointments</div>
                        <div class="font-serif font-bold text-xl text-slate-900">{{ number_format($appointmentsToday) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-xs text-slate-500 mb-1">In queue</div>
                        <div class="font-serif font-bold text-xl text-slate-900">{{ number_format($queueToday) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-xs text-slate-500 mb-1">Completed today</div>
                        <div class="font-serif font-bold text-xl text-slate-900">{{ number_format($completedToday) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-xs text-slate-500 mb-1">Pending prescriptions</div>
                        <div class="font-serif font-bold text-xl text-slate-900">{{ number_format($pendingPrescriptionsToday) }}</div>
                    </div>
                </div>

                <div class="mt-5 border border-slate-100 rounded-xl bg-slate-50 overflow-hidden">
                    <div class="max-h-[11.5rem] overflow-auto scrollbar-hidden">
                    <table class="min-w-full text-left text-xs text-slate-600">
                        <thead>
                            <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                                <th class="py-2 px-3 font-semibold">Time</th>
                                <th class="py-2 px-3 font-semibold">Patient</th>
                                <th class="py-2 px-3 font-semibold">Type</th>
                                <th class="py-2 px-3 font-semibold">Status</th>
                                <th class="py-2 px-3 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($todayAppointments as $appointment)
                                @php
                                    $patientName = $formatUserName($appointment->patient);
                                    $time = optional($appointment->appointment_datetime)->format('H:i') ?? '—';
                                    $typeLabel = $appointment->appointment_type ? ucfirst(str_replace('_', '-', $appointment->appointment_type)) : '—';
                                    $statusLabel = $appointment->status ? ucfirst(str_replace('_', ' ', $appointment->status)) : '—';
                                    $statusKey = strtolower((string) ($appointment->status ?? ''));
                                    $showScheduleActions = $statusKey !== 'completed';
                                    $consultationParams = [
                                        'role' => 'doctor',
                                        'section' => 'consultation',
                                        'appointment_id' => $appointment->appointment_id,
                                    ];
                                    if ($currentUserIdQuery) {
                                        $consultationParams['user_id'] = $currentUserIdQuery;
                                    }
                                @endphp
                                <tr class="border-b border-slate-100 last:border-0">
                                    <td class="py-2 px-3 text-[0.78rem] text-slate-500">{{ $time }}</td>
                                    <td class="py-2 px-3 text-[0.78rem] text-slate-700">{{ $patientName }}</td>
                                    <td class="py-2 px-3 text-[0.78rem] text-slate-500">{{ $typeLabel }}</td>
                                    <td class="py-2 px-3 text-[0.78rem] text-slate-500">{{ $statusLabel }}</td>
                                    <td class="py-2 px-3">
                                        @if ($showScheduleActions)
                                            <div class="flex flex-wrap gap-1.5">
                                                <a href="{{ route('dashboard', $consultationParams) }}"
                                                    class="inline-flex items-center justify-center gap-1 rounded-lg border border-slate-200 bg-white px-2 py-1 text-[0.7rem] font-medium text-slate-700 hover:bg-slate-50">
                                                    <x-lucide-eye class="w-3.5 h-3.5" />
                                                    Open
                                                </a>
                                                <a href="{{ route('dashboard', $consultationParams) }}"
                                                    class="inline-flex items-center justify-center gap-1 rounded-lg border border-cyan-200 bg-cyan-50 px-2 py-1 text-[0.7rem] font-semibold text-cyan-700 hover:bg-cyan-100">
                                                    <x-lucide-play class="w-3.5 h-3.5" />
                                                    Start
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-[0.72rem] text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-[0.78rem] text-slate-400">
                                        No appointments scheduled for today.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 grid-cols-1 md:grid-cols-2">
                    <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50">
                        <div class="text-xs font-semibold text-slate-700 mb-1">Recent visits</div>
                        <div class="h-36 overflow-y-auto scrollbar-hidden">
                            @if (count($recentVisits))
                                <ul class="space-y-2 text-xs text-slate-600">
                                    @foreach ($recentVisits as $visit)
                                        @php
                                            $patientName = $formatUserName(optional($visit->appointment)->patient);
                                            $visitDate = optional($visit->visit_datetime)->format('Y-m-d') ?? (optional($visit->transaction_datetime)->format('Y-m-d') ?? '—');
                                        @endphp
                                        <li class="flex items-start justify-between gap-2">
                                            <div>
                                                <div class="font-semibold text-slate-900 text-[0.8rem]">
                                                    {{ $patientName }}
                                                </div>
                                                <div class="text-[0.7rem] text-slate-500">
                                                    {{ \Illuminate\Support\Str::limit($visit->diagnosis ?? 'No diagnosis recorded', 60) }}
                                                </div>
                                            </div>
                                            <div class="text-[0.7rem] text-slate-400 whitespace-nowrap">
                                                {{ $visitDate }}
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-[0.72rem] text-slate-400">No recent visits yet.</p>
                            @endif
                        </div>
                    </div>
                    <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50">
                        <div class="flex items-center justify-between mb-1">
                            <div class="text-xs font-semibold text-slate-700">Upcoming appointments</div>
                            <button type="button" id="doctorUpcomingTodayFilter" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2 py-0.5 text-[0.68rem] font-semibold text-slate-700 hover:bg-slate-50">
                                Today
                            </button>
                        </div>
                        <div class="h-36 overflow-y-auto scrollbar-hidden">
                            <div id="doctorUpcomingAppointmentsAll">
                            @if (count($recentAppointments))
                                <ul class="space-y-2 text-xs text-slate-600">
                                    @foreach ($recentAppointments as $appointment)
                                        @php
                                            $patientName = $formatUserName($appointment->patient);
                                            $dateKey = optional($appointment->appointment_datetime)->format('Y-m-d') ?? '—';
                                            $timeKey = optional($appointment->appointment_datetime)->format('H:i') ?? '—';
                                        @endphp
                                        <li class="flex items-start justify-between gap-2">
                                            <div>
                                                <div class="font-semibold text-slate-900 text-[0.8rem]">
                                                    {{ $patientName }}
                                                </div>
                                                <div class="text-[0.7rem] text-slate-500">
                                                    {{ \Illuminate\Support\Str::limit($appointment->reason_for_visit ?? 'No reason specified', 60) }}
                                                </div>
                                            </div>
                                            <div class="text-[0.7rem] text-slate-400 text-right">
                                                <div>{{ $dateKey }}</div>
                                                <div>{{ $timeKey }}</div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-[0.72rem] text-slate-400">No appointments found.</p>
                            @endif
                            </div>
                            <div id="doctorUpcomingAppointmentsToday" class="hidden">
                            @if (count($todayUpcomingAppointments))
                                <ul class="space-y-2 text-xs text-slate-600">
                                    @foreach ($todayUpcomingAppointments as $appointment)
                                        @php
                                            $patientName = $formatUserName($appointment->patient);
                                            $dateKey = optional($appointment->appointment_datetime)->format('Y-m-d') ?? '—';
                                            $timeKey = optional($appointment->appointment_datetime)->format('H:i') ?? '—';
                                        @endphp
                                        <li class="flex items-start justify-between gap-2">
                                            <div>
                                                <div class="font-semibold text-slate-900 text-[0.8rem]">
                                                    {{ $patientName }}
                                                </div>
                                                <div class="text-[0.7rem] text-slate-500">
                                                    {{ \Illuminate\Support\Str::limit($appointment->reason_for_visit ?? 'No reason specified', 60) }}
                                                </div>
                                            </div>
                                            <div class="text-[0.7rem] text-slate-400 text-right">
                                                <div>{{ $dateKey }}</div>
                                                <div>{{ $timeKey }}</div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-[0.72rem] text-slate-400">No appointments for today.</p>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
                <h2 class="text-sm font-semibold text-slate-900 mb-3">Queue list</h2>
                <div class="max-h-80 overflow-y-auto scrollbar-hidden">
                    @if (count($todayQueue))
                        <ul class="space-y-2 text-xs text-slate-600">
                            @foreach ($todayQueue as $queue)
                                @php
                                    $patientName = $formatUserName(optional(optional($queue->appointment)->patient));
                                    $dateKey = optional($queue->queue_datetime)->format('Y-m-d') ?? '—';
                                    $timeKey = optional($queue->queue_datetime)->format('H:i') ?? '—';
                                    $statusLabel = $queue->status ? ucfirst(str_replace('_', ' ', $queue->status)) : '—';
                                @endphp
                                <li class="flex items-start justify-between gap-2 border-b border-slate-50 pb-2 last:border-0 last:pb-0">
                                    <div>
                                        <div class="font-semibold text-slate-900 text-[0.8rem]">
                                            Queue #{{ $queue->queue_number }}
                                        </div>
                                        <div class="text-[0.7rem] text-slate-500">
                                            {{ $patientName }}
                                        </div>
                                    </div>
                                    <div class="text-right text-[0.7rem] text-slate-400 whitespace-nowrap">
                                        <div>{{ $dateKey }} {{ $timeKey }}</div>
                                        <div>{{ $statusLabel }}</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-[0.72rem] text-slate-400">No queue entries yet.</p>
                    @endif
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var toggleBtn = document.getElementById('doctorUpcomingTodayFilter')
                var allWrap = document.getElementById('doctorUpcomingAppointmentsAll')
                var todayWrap = document.getElementById('doctorUpcomingAppointmentsToday')
                if (!toggleBtn || !allWrap || !todayWrap) return

                var todayOnly = false
                function applyFilterState() {
                    allWrap.classList.toggle('hidden', todayOnly)
                    todayWrap.classList.toggle('hidden', !todayOnly)
                    toggleBtn.classList.toggle('bg-cyan-600', todayOnly)
                    toggleBtn.classList.toggle('text-white', todayOnly)
                    toggleBtn.classList.toggle('border-cyan-600', todayOnly)
                    toggleBtn.classList.toggle('hover:bg-cyan-700', todayOnly)
                    toggleBtn.classList.toggle('bg-white', !todayOnly)
                    toggleBtn.classList.toggle('text-slate-700', !todayOnly)
                    toggleBtn.classList.toggle('border-slate-200', !todayOnly)
                    toggleBtn.classList.toggle('hover:bg-slate-50', !todayOnly)
                }

                toggleBtn.addEventListener('click', function () {
                    todayOnly = !todayOnly
                    applyFilterState()
                })
                applyFilterState()
            })
        </script>
    @else
        @php
            $title = $sectionTitles[$effectiveSectionKey] ?? 'Doctor workspace';
            $subtitle = $sectionSubtitles[$effectiveSectionKey] ?? 'Clinical workspace';
        @endphp

        <div>
            <h1 class="text-2xl font-semibold text-slate-900 mb-1">{{ $title }}</h1>
            <p class="text-sm text-slate-500">{{ $subtitle }}</p>
        </div>

        @if ($effectiveSectionKey === 'my-patients')
            @include('dashviews.doctor.doctor_my_patients')
        @elseif ($effectiveSectionKey === 'appointments')
            @include('dashviews.doctor.doctor_appointments')
        @elseif ($effectiveSectionKey === 'queue')
            @include('dashviews.doctor.doctor_queue')
        @elseif ($effectiveSectionKey === 'visits')
            @include('dashviews.doctor.doctor_visits')
        @elseif ($effectiveSectionKey === 'prescriptions')
            @include('dashviews.doctor.doctor_prescriptions')
        @elseif ($effectiveSectionKey === 'my-activity')
            @include('dashviews.doctor.doctor_my_activity')
        @elseif ($effectiveSectionKey === 'consultation')
            @include('dashviews.doctor.doctor_consultation')
        @elseif ($effectiveSectionKey === 'settings-doctor')
            @include('dashviews.doctor.doctor_settings')
        @endif
    @endif
</div>
