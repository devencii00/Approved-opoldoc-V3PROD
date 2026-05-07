@php
    $queueItems = collect($receptionQueue ?? []);
    $servingItems = $queueItems->where('status', 'serving')->values()->take(4)->values();
    $waitingItems = $queueItems
        ->filter(function ($row) {
            return ($row->status ?? null) === 'waiting';
        })
        ->sortBy(function ($row) {
            $priority = (int) ($row->priority_level ?? 5);
            $number = (int) ($row->queue_number ?? 999999);
            return str_pad((string) $priority, 6, '0', STR_PAD_LEFT) . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
        })
        ->values();
    $nextItems = $waitingItems->take(5);
@endphp

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">Queue management</h2>
            <p class="text-xs text-slate-500">Add patients to the queue and monitor today&apos;s flow.</p>
        </div>
        <div class="flex items-center gap-2">
            <button id="receptionCallNextButton" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-[0.8rem] font-semibold hover:bg-slate-800 transition-colors">
                <x-lucide-megaphone class="w-[18px] h-[18px]" />
                Call next
            </button>
            <button id="receptionRefreshQueueButton" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-800 text-[0.8rem] font-semibold hover:bg-slate-200 transition-colors border border-slate-200">
                <x-lucide-refresh-cw class="w-[18px] h-[18px]" />
                Refresh
            </button>
            <a href="{{ route('queue.display', ['date' => now()->toDateString()]) }}" target="_blank" id="receptionPublicQueueLinkButton" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-slate-800 text-[0.8rem] font-semibold hover:bg-slate-50 transition-colors border border-slate-200">
                <x-lucide-link class="w-[18px] h-[18px]" />
                Public link
            </a>
            
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-900">Today&apos;s queue</h3>
            <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Front desk</span>
        </div>

        <form id="receptionAddQueueForm" class="mb-4 grid gap-2 grid-cols-1 md:grid-cols-2 items-end">
            <div>
                <!-- <label for="reception_add_queue_appointment_id" class="block text-[0.7rem] text-slate-600 mb-1">Appointment</label> -->
                <div class="relative">
                    <!-- <input id="reception_queue_appointment_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Click to select walk-in appointment">
                    <input id="reception_add_queue_appointment_id" type="hidden" required>
                    <div id="receptionQueueAppointmentResults" class="hidden absolute left-0 right-0 top-full mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain z-50"></div> -->
                </div>
                <div id="receptionQueueAppointmentPreview" class="hidden mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.78rem] text-slate-700 break-words"></div>
            </div>
            <div class="flex items-end">
                <!-- <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors">
                    Add to queue
                </button> -->
            </div>
        </form>

        <div id="receptionQueueError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
        <div id="receptionQueueSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>

        <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
            <div class="flex-1">
                <label for="reception_queue_search" class="block text-[0.7rem] text-slate-600 mb-1">Search queue</label>
                <input id="reception_queue_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Queue number, patient or doctor">
            </div>
            <div class="w-full md:w-40">
                <label for="reception_queue_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
                <select id="reception_queue_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="priority">Priority</option>
                    <option value="newest">Newest</option>
                    <option value="oldest">Oldest</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto scrollbar-hidden">
            <table class="min-w-full text-left text-xs text-slate-600">
                <thead>
                    <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                        <th class="py-2 pr-4 font-semibold">Queue #</th>
                        <th class="py-2 pr-4 font-semibold">Patient</th>
                        <th class="py-2 pr-4 font-semibold">Doctor</th>
                        <th class="py-2 pr-4 font-semibold">Priority</th>
                        <th class="py-2 pr-4 font-semibold">Date</th>
                        <th class="py-2 pr-4 font-semibold">Status</th>
                        <th class="py-2 pr-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($queueItems as $queue)
                        @php
                            $patientName = optional(optional($queue->appointment)->patient)->personalInformation->full_name ?? '';
                            $doctorName = optional(optional($queue->appointment)->doctor)->personalInformation->full_name ?? '';
                            $statusName = (string) ($queue->status ?? '');
                            $dateKey = $queue->queue_datetime ? $queue->queue_datetime->format('Y-m-d H:i') : '';
                            $queueId = $queue->queue_id ?? null;
                            $priority = (int) ($queue->priority_level ?? 5);
                            $waitMinutes = $queue->queue_datetime ? $queue->queue_datetime->diffInMinutes(now()) : 0;
                        @endphp
                        <tr class="border-b border-slate-50 last:border-0 reception-queue-row"
                            data-queue-number="{{ $queue->queue_number }}"
                            data-queue-code="{{ $queue->queue_code }}"
                            data-patient="{{ strtolower($patientName) }}"
                            data-doctor="{{ strtolower($doctorName) }}"
                            data-date="{{ $dateKey }}"
                            data-status="{{ strtolower($statusName) }}"
                            data-priority="{{ $priority }}"
                            @if ($queueId)
                                data-queue-id="{{ $queueId }}"
                            @endif>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">{{ $queue->queue_code ?? $queue->queue_number }}</td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                                @if ($patientName)
                                    {{ $patientName }}
                                @else
                                    <span class="text-slate-400">Patient</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                                @if ($doctorName)
                                    {{ $doctorName }}
                                @else
                                    <span class="text-[0.7rem] text-slate-400">Doctor</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">{{ $priority }}</td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                                {{ $dateKey }}
                            </td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                                @if ($statusName)
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.68rem] font-medium border bg-slate-50 border-slate-100 text-slate-700">
                                        {{ ucfirst(str_replace('_', ' ', $statusName)) }}
                                    </span>
                                @else
                                    <span class="text-[0.7rem] text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-[0.78rem] text-right text-slate-500">
                                @if ($queueId ?? null)
                                    @if (in_array(strtolower($statusName), ['done', 'cancelled', 'no_show'], true))
                                        <span class="text-[0.7rem] text-slate-400">—</span>
                                    @else
                                        <div class="inline-flex items-center gap-1.5">
                                            @if (strtolower($statusName) !== 'serving')
                                                <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2 py-1 text-[0.7rem] text-slate-600 hover:bg-slate-50 reception-queue-status" data-queue-id="{{ $queueId }}" data-status="serving">
                                                    <x-lucide-play class="w-[16px] h-[16px]" />
                                                    Serving
                                                </button>
                                                @if (strtolower($statusName) === 'waiting' && $waitMinutes >= 5)
                                                    <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2 py-1 text-[0.7rem] text-rose-700 hover:bg-rose-50 reception-queue-status" data-queue-id="{{ $queueId }}" data-status="no_show">
                                                        <x-lucide-user-x class="w-[16px] h-[16px]" />
                                                        No show
                                                    </button>
                                                @endif
                                            @else
                                                <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 px-2 py-1 text-[0.7rem] text-emerald-700 hover:bg-emerald-50 reception-queue-status" data-queue-id="{{ $queueId }}" data-status="done">
                                                    <x-lucide-check class="w-[16px] h-[16px]" />
                                                    Done
                                                </button>
                                                <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2 py-1 text-[0.7rem] text-rose-700 hover:bg-rose-50 reception-queue-status" data-queue-id="{{ $queueId }}" data-status="no_show">
                                                    <x-lucide-user-x class="w-[16px] h-[16px]" />
                                                    No show
                                                </button>
                                            @endif

                                            <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2 py-1 text-[0.7rem] text-slate-600 hover:bg-slate-50 reception-queue-config" data-queue-id="{{ $queueId }}" data-priority-level="{{ $priority }}">
                                                <x-lucide-settings class="w-[16px] h-[16px]" />
                                                Config
                                            </button>

                                            <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2 py-1 text-[0.7rem] text-slate-600 hover:bg-red-50 hover:border-red-200 hover:text-red-700 reception-queue-remove" data-queue-id="{{ $queueId }}">
                                                <x-lucide-x class="w-[16px] h-[16px]" />
                                                Cancel
                                            </button>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-[0.7rem] text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-[0.78rem] text-slate-400">
                                No queue entries for today.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="queueDisplayOverlay" class="hidden fixed inset-0 z-50 bg-slate-900/95 flex flex-col">
    <div class="flex items-center justify-between px-8 py-4 border-b border-slate-700">
        <div>
            <div class="text-[0.8rem] text-slate-400 uppercase tracking-widest">Opol Clinic</div>
            <div class="text-lg font-semibold text-white">Queue display</div>
        </div>
        <div class="flex items-center gap-2">
            <button id="queueDisplayFullscreenButton" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 text-slate-100 text-[0.78rem] font-semibold hover:bg-slate-700">
                <x-lucide-fullscreen class="w-[18px] h-[18px]" />
                Full screen
            </button>
            <button id="queueDisplayCloseButton" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-700 text-slate-100 text-[0.78rem] font-semibold hover:bg-slate-600">
                <x-lucide-x class="w-[18px] h-[18px]" />
                Close
            </button>
        </div>
    </div>

    <div class="flex-1 flex flex-col lg:flex-row">
        <div class="flex-1 flex items-center justify-center p-6">
            <div class="w-full max-w-xl" id="queueDisplayNowServing">
                <div class="text-[0.85rem] text-cyan-300 uppercase tracking-[0.3em] mb-3">Now serving</div>
                @if ($servingItems->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach ($servingItems as $serving)
                            @php
                                $servingPatient = optional(optional($serving->appointment)->patient)->personalInformation->full_name ?? 'Patient';
                                $servingDoctor = optional(optional($serving->appointment)->doctor)->personalInformation->full_name ?? null;
                                $servingLabel = $serving->queue_code ?? $serving->queue_number;
                            @endphp
                            <div class="rounded-3xl bg-slate-800/80 border border-slate-600/80 px-6 py-6 shadow-[0_0_40px_rgba(8,47,73,0.9)]">
                                <div class="text-[0.9rem] text-slate-300 mb-2">Queue</div>
                                <div class="text-5xl md:text-6xl font-serif font-bold text-white tracking-[0.18em]">
                                    {{ $servingLabel }}
                                </div>
                                <div class="mt-4 text-[0.95rem] text-slate-100 font-semibold">
                                    {{ $servingPatient }}
                                </div>
                                <div class="mt-1 text-[0.8rem] text-slate-400">
                                    @if ($servingDoctor)
                                        {{ $servingDoctor }}
                                    @else
                                        Waiting for doctor assignment
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-3xl bg-slate-800/80 border border-slate-600/80 px-6 py-8 text-center text-slate-300">
                        No queue is currently being served.
                    </div>
                @endif
            </div>
        </div>

        <div class="w-full lg:w-[420px] border-t lg:border-t-0 lg:border-l border-slate-700 bg-slate-950/70 p-6">
            <div class="flex items-center justify-between mb-3">
                <div class="text-[0.8rem] text-slate-400 uppercase tracking-[0.25em]">Next in line</div>
                <div class="text-[0.75rem] text-slate-500" id="queueDisplayNextCount">{{ $nextItems->count() }} shown</div>
            </div>
            <div class="space-y-3 max-h-full overflow-y-auto scrollbar-hidden" id="queueDisplayNextList">
                @forelse ($nextItems as $queue)
                    @php
                        $patientName = optional(optional($queue->appointment)->patient)->personalInformation->full_name ?? 'Patient';
                        $doctorName = optional(optional($queue->appointment)->doctor)->personalInformation->full_name ?? null;
                        $statusName = (string) ($queue->status ?? '');
                    @endphp
                    <div class="rounded-2xl bg-slate-800/60 border border-slate-600/70 px-4 py-3 flex items-center justify-between">
                        <div>
                                    <div class="text-[0.75rem] text-slate-400 mb-1">Queue #{{ $queue->queue_code ?? $queue->queue_number }}</div>
                            <div class="text-[0.9rem] text-slate-100 font-semibold">{{ $patientName }}</div>
                            <div class="text-[0.75rem] text-slate-400">
                                @if ($doctorName)
                                    Doctor: {{ $doctorName }}
                                @else
                                    Doctor not assigned
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            @if ($statusName)
                                <div class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.65rem] font-semibold bg-cyan-500/10 text-cyan-300 border border-cyan-500/40">
                                    {{ strtoupper($statusName) }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-[0.8rem] text-slate-400">
                        No additional queue entries.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div id="receptionQueueConfigOverlay" class="hidden fixed inset-0 z-[60] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-[0_20px_80px_rgba(15,23,42,0.35)] overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-900">Queue Config</div>
                <div id="receptionQueueConfigMeta" class="mt-0.5 text-[0.78rem] text-slate-500"></div>
            </div>
            <button id="receptionQueueConfigClose" type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                <x-lucide-x class="w-[20px] h-[20px]" />
            </button>
        </div>
        <div class="px-5 py-4">
            <div id="receptionQueueConfigError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label for="receptionQueueConfigPriority" class="block text-[0.7rem] text-slate-600 mb-1">Priority level</label>
                    <select id="receptionQueueConfigPriority" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                        <option value="1">1 : Emergency</option>
                        <option value="2">2 : PWD</option>
                        <option value="3">3 : Pregnant</option>
                        <option value="4">4 : Senior</option>
                        <option value="5">5 : General</option>
                    </select>
                </div>
                <div>
                    <label for="receptionQueueConfigMoveSteps" class="block text-[0.7rem] text-slate-600 mb-1">Move levels</label>
                    <input id="receptionQueueConfigMoveSteps" type="number" min="1" max="4" value="1" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                <button id="receptionQueueConfigMoveUp" type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-900 text-white text-[0.78rem] font-semibold hover:bg-slate-800">
                    <x-lucide-arrow-up class="w-[18px] h-[18px]" />
                    Move up
                </button>
                <button id="receptionQueueConfigMoveDown" type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-[0.78rem] font-semibold hover:bg-slate-50">
                    <x-lucide-arrow-down class="w-[18px] h-[18px]" />
                    Move down
                </button>
                <div class="flex-1"></div>
                <button id="receptionQueueConfigSave" type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700">
                    <x-lucide-save class="w-[18px] h-[18px]" />
                    Save
                </button>
            </div>
        </div>
    </div>
</div>

<div id="receptionConfirmOverlay" class="hidden fixed inset-0 z-[60] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white border border-slate-200 shadow-[0_20px_80px_rgba(15,23,42,0.35)]">
        <div class="px-5 py-4 border-b border-slate-100">
            <div id="receptionConfirmTitle" class="text-sm font-semibold text-slate-900">Confirm</div>
            <div id="receptionConfirmMessage" class="mt-1 text-[0.78rem] text-slate-600"></div>
        </div>
        <div class="px-5 py-4 flex items-center justify-end gap-2">
            <button id="receptionConfirmCancel" type="button" class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-slate-100 text-slate-800 text-[0.78rem] font-semibold hover:bg-slate-200 border border-slate-200">
                Cancel
            </button>
            <button id="receptionConfirmOk" type="button" class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('reception_queue_search')
        var sortSelect = document.getElementById('reception_queue_sort')
        var rows = Array.prototype.slice.call(document.querySelectorAll('.reception-queue-row'))
        var addQueueForm = document.getElementById('receptionAddQueueForm')
        var queueErrorBox = document.getElementById('receptionQueueError')
        var queueSuccessBox = document.getElementById('receptionQueueSuccess')
        var appointmentSearch = document.getElementById('reception_queue_appointment_search')
        var appointmentIdInput = document.getElementById('reception_add_queue_appointment_id')
        var appointmentResults = document.getElementById('receptionQueueAppointmentResults')
        var appointmentPreview = document.getElementById('receptionQueueAppointmentPreview')
        var selectedAppointmentLabel = ''
        var appointmentSearchTimer = null

        var confirmOverlay = document.getElementById('receptionConfirmOverlay')
        var confirmTitle = document.getElementById('receptionConfirmTitle')
        var confirmMessage = document.getElementById('receptionConfirmMessage')
        var confirmCancel = document.getElementById('receptionConfirmCancel')
        var confirmOk = document.getElementById('receptionConfirmOk')
        var confirmResolver = null

        var configOverlay = document.getElementById('receptionQueueConfigOverlay')
        var configClose = document.getElementById('receptionQueueConfigClose')
        var configMeta = document.getElementById('receptionQueueConfigMeta')
        var configError = document.getElementById('receptionQueueConfigError')
        var configPriority = document.getElementById('receptionQueueConfigPriority')
        var configMoveSteps = document.getElementById('receptionQueueConfigMoveSteps')
        var configMoveUp = document.getElementById('receptionQueueConfigMoveUp')
        var configMoveDown = document.getElementById('receptionQueueConfigMoveDown')
        var configSave = document.getElementById('receptionQueueConfigSave')
        var configQueueId = null

        function confirmAction(title, message) {
            return new Promise(function (resolve) {
                confirmResolver = resolve
                if (confirmTitle) confirmTitle.textContent = title || 'Confirm'
                if (confirmMessage) confirmMessage.textContent = message || ''
                if (confirmOverlay) confirmOverlay.classList.remove('hidden')
            })
        }

        function closeConfirm(result) {
            if (confirmOverlay) confirmOverlay.classList.add('hidden')
            if (typeof confirmResolver === 'function') {
                var fn = confirmResolver
                confirmResolver = null
                fn(!!result)
            }
        }

        if (confirmCancel) {
            confirmCancel.addEventListener('click', function () { closeConfirm(false) })
        }
        if (confirmOk) {
            confirmOk.addEventListener('click', function () { closeConfirm(true) })
        }
        if (confirmOverlay) {
            confirmOverlay.addEventListener('click', function (e) {
                if (e.target === confirmOverlay) closeConfirm(false)
            })
        }

        function showConfigError(message) {
            if (!configError) return
            configError.textContent = message || ''
            configError.classList.toggle('hidden', !message)
        }

        function closeQueueConfig() {
            if (configOverlay) configOverlay.classList.add('hidden')
            configQueueId = null
            showConfigError('')
        }

        function openQueueConfig(queueId, priorityLevel, metaText) {
            configQueueId = queueId ? String(queueId) : null
            if (!configQueueId) return
            if (configMeta) configMeta.textContent = metaText || ('Queue #' + String(queueId))
            if (configPriority) {
                var p = parseInt(priorityLevel, 10)
                if (!p || isNaN(p)) p = 5
                if (p < 1) p = 1
                if (p > 5) p = 5
                configPriority.value = String(p)
            }
            if (configMoveSteps) configMoveSteps.value = '1'
            showConfigError('')
            if (configOverlay) configOverlay.classList.remove('hidden')
        }

        function updateQueuePriority(nextLevel) {
            if (!configQueueId || typeof apiFetch !== 'function') return Promise.resolve(false)
            var level = parseInt(nextLevel, 10)
            if (!level || isNaN(level)) level = 5
            if (level < 1) level = 1
            if (level > 5) level = 5

            if (configSave) configSave.disabled = true
            showConfigError('')
            return apiFetch("{{ url('/api/queues') }}/" + encodeURIComponent(configQueueId), {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ priority_level: level })
            })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d } }).catch(function () { return { ok: r.ok, data: null } }) })
                .then(function (res) {
                    if (!res.ok) {
                        showConfigError((res.data && res.data.message) ? res.data.message : 'Failed to update priority.')
                        return false
                    }
                    return true
                })
                .catch(function () {
                    showConfigError('Network error while updating priority.')
                    return false
                })
                .finally(function () {
                    if (configSave) configSave.disabled = false
                })
        }

        if (configClose) configClose.addEventListener('click', closeQueueConfig)
        if (configOverlay) {
            configOverlay.addEventListener('click', function (e) {
                if (e.target === configOverlay) closeQueueConfig()
            })
        }

        function escapeHtml(input) {
            return String(input == null ? '' : input)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
        }

        function normalizeText(value) {
            return String(value == null ? '' : value).toLowerCase().replace(/\s+/g, ' ').trim()
        }

        function localDateIso() {
            var now = new Date()
            var y = now.getFullYear()
            var m = String(now.getMonth() + 1).padStart(2, '0')
            var d = String(now.getDate()).padStart(2, '0')
            return y + '-' + m + '-' + d
        }

        function wordPrefixMatch(value, query) {
            var v = normalizeText(value || '')
            var q = normalizeText(query || '')
            if (!q) return true
            if (!v) return false
            if (v.indexOf(q) === 0) return true
            return v.split(/\s+/).some(function (part) { return part.indexOf(q) === 0 })
        }

        function appointmentLabel(appt) {
            if (!appt) return ''
            var id = appt.appointment_id != null ? appt.appointment_id : ''
            var patient = appt.patient || null
            var doctor = appt.doctor || null
            var pName = patient ? [patient.firstname, patient.middlename, patient.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim() : ''
            var dName = doctor ? [doctor.firstname, doctor.middlename, doctor.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim() : ''
            var when = appt.appointment_datetime ? String(appt.appointment_datetime).replace('T', ' ').slice(0, 16) : 'Queue request'
            return '#' + id + ' — ' + (pName || 'Patient') + ' · ' + (dName || 'Doctor') + ' · ' + when
        }

        function setAppointmentSelection(appt) {
            if (!appointmentIdInput) return
            var id = appt && appt.appointment_id != null ? parseInt(appt.appointment_id, 10) : 0
            if (!id) {
                appointmentIdInput.value = ''
                selectedAppointmentLabel = ''
                if (appointmentPreview) {
                    appointmentPreview.textContent = ''
                    appointmentPreview.classList.add('hidden')
                }
                return
            }

            appointmentIdInput.value = String(id)
            selectedAppointmentLabel = appointmentLabel(appt)
            if (appointmentSearch) appointmentSearch.value = selectedAppointmentLabel

            if (appointmentPreview) {
                appointmentPreview.textContent = selectedAppointmentLabel
                appointmentPreview.classList.remove('hidden')
            }

            if (appointmentResults) {
                appointmentResults.innerHTML = ''
                appointmentResults.classList.add('hidden')
            }
        }

        function renderAppointmentOptions(list) {
            if (!appointmentResults) return
            var items = (list || []).filter(function (a) {
                if (!a) return false
                var t = String(a.appointment_type || '').toLowerCase().trim()
                if (!t) return true
                return t === 'walk_in'
            }).slice(0, 20)
            if (!items.length) {
                appointmentResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No walk-in appointments found.</div>'
                appointmentResults.classList.remove('hidden')
                return
            }

            appointmentResults.innerHTML = items.map(function (a) {
                return '<button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 border-b border-slate-100 last:border-0">' +
                    '<div class="text-[0.78rem] text-slate-800 font-semibold">' + escapeHtml(appointmentLabel(a)) + '</div>' +
                '</button>'
            }).join('')
            appointmentResults.classList.remove('hidden')

            var buttons = appointmentResults.querySelectorAll('button')
            Array.prototype.forEach.call(buttons, function (btn, idx) {
                btn.addEventListener('click', function () {
                    setAppointmentSelection(items[idx])
                })
            })
        }

        function loadAppointmentOptions(search) {
            if (typeof apiFetch !== 'function') return
            var today = localDateIso()
            var url = "{{ url('/api/appointments') }}" + '?per_page=100&start_date=' + encodeURIComponent(today) + '&end_date=' + encodeURIComponent(today) + '&today_only=1&order=latest&appointment_type=walk_in'
            if (search) {
                url += '&search=' + encodeURIComponent(search)
            }
            apiFetch(url, { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    }).catch(function () {
                        return { ok: response.ok, data: null }
                    })
                })
                .then(function (result) {
                    if (!result.ok || !result.data) return
                    var raw = result.data && Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    
                    // Filter only those that are walk_in AND not already in the queue
                    var walkIns = raw.filter(function (a) {
                        if (!a) return false
                        var t = String(a.appointment_type || '').toLowerCase().trim()
                        if (t !== 'walk_in') return false
                        
                        // If there's a queue object, and its status is waiting or serving, it's already in the queue
                        if (a.queue) {
                            var qStatus = String(a.queue.status || '').toLowerCase().trim()
                            if (qStatus === 'waiting' || qStatus === 'serving') return false
                        }
                        
                        return true
                    })
                    
                    renderAppointmentOptions(walkIns)
                })
                .catch(function () {})
        }

        if (appointmentSearch) {
            appointmentSearch.addEventListener('input', function () {
                if (appointmentSearchTimer) clearTimeout(appointmentSearchTimer)
                appointmentSearchTimer = setTimeout(function () {
                    var q = (appointmentSearch.value || '').trim()
                    if (appointmentIdInput && appointmentIdInput.value && selectedAppointmentLabel) {
                        if (normalizeText(q) !== normalizeText(selectedAppointmentLabel)) {
                            setAppointmentSelection(null)
                        }
                    }
                    loadAppointmentOptions(q)
                }, 250)
            })
            appointmentSearch.addEventListener('focus', function () {
                var q = String(appointmentSearch.value || '').trim()
                loadAppointmentOptions(q)
            })
            appointmentSearch.addEventListener('click', function () {
                var q = String(appointmentSearch.value || '').trim()
                loadAppointmentOptions(q)
            })
        }

        document.addEventListener('click', function (e) {
            var target = e.target
            if (appointmentResults && !appointmentResults.classList.contains('hidden')) {
                if (!(appointmentResults.contains(target) || (appointmentSearch && appointmentSearch.contains(target)))) {
                    appointmentResults.classList.add('hidden')
                }
            }
        })

        function applyReceptionQueueFilters() {
            var query = searchInput ? normalizeText(searchInput.value) : ''

            rows.forEach(function (row) {
                var number = ((row.getAttribute('data-queue-code') || '') + ' ' + (row.getAttribute('data-queue-number') || '')).trim()
                var patient = normalizeText(row.getAttribute('data-patient') || '')
                var doctor = normalizeText(row.getAttribute('data-doctor') || '')
                var date = normalizeText(row.getAttribute('data-date') || '')

                var matches = true
                if (query) {
                    matches =
                        ('#' + number).indexOf(query) !== -1 ||
                        wordPrefixMatch(patient, query) ||
                        wordPrefixMatch(doctor, query) ||
                        date.indexOf(query) === 0
                }

                row.style.display = matches ? '' : 'none'
            })

            applyReceptionQueueSort()
        }

        function applyReceptionQueueSort() {
            if (!sortSelect) {
                return
            }
            var value = sortSelect.value
            var tbody = rows.length ? rows[0].parentNode : null
            if (!tbody) {
                return
            }

            var visibleRows = rows.filter(function (row) {
                return row.style.display !== 'none'
            })

            visibleRows.sort(function (a, b) {
                var na = parseInt(a.getAttribute('data-queue-number') || '0', 10)
                var nb = parseInt(b.getAttribute('data-queue-number') || '0', 10)
                var da = a.getAttribute('data-date') || ''
                var db = b.getAttribute('data-date') || ''
                var pa = parseInt(a.getAttribute('data-priority') || '5', 10)
                var pb = parseInt(b.getAttribute('data-priority') || '5', 10)
                var sa = String(a.getAttribute('data-status') || '').toLowerCase()
                var sb = String(b.getAttribute('data-status') || '').toLowerCase()

                function statusRank(s) {
                    if (s === 'serving') return 0
                    if (s === 'waiting') return 1
                    if (s === 'no_show') return 2
                    if (s === 'done') return 3
                    if (s === 'cancelled') return 4
                    return 5
                }
                var ra = statusRank(sa)
                var rb = statusRank(sb)
                if (ra < rb) return -1
                if (ra > rb) return 1

                if (value === 'priority') {
                    if (pa < pb) return -1
                    if (pa > pb) return 1
                    if (na < nb) return -1
                    if (na > nb) return 1
                    return 0
                }

                if (value === 'newest') {
                    if (da < db) return 1
                    if (da > db) return -1
                    if (na < nb) return 1
                    if (na > nb) return -1
                    return 0
                }

                if (da < db) return -1
                if (da > db) return 1
                return 0
            })

            visibleRows.forEach(function (row) {
                tbody.appendChild(row)
            })
        }

        function showQueueError(message) {
            if (!queueErrorBox) return
            queueErrorBox.textContent = message || ''
            if (message) {
                queueErrorBox.classList.remove('hidden')
            } else {
                queueErrorBox.classList.add('hidden')
            }
        }

        function showQueueSuccess(message) {
            if (!queueSuccessBox) return
            queueSuccessBox.textContent = message || ''
            if (message) {
                queueSuccessBox.classList.remove('hidden')
            } else {
                queueSuccessBox.classList.add('hidden')
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyReceptionQueueFilters)
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', applyReceptionQueueSort)
        }

        applyReceptionQueueFilters()

        if (addQueueForm) {
            addQueueForm.addEventListener('submit', function (e) {
                e.preventDefault()

                showQueueError('')
                showQueueSuccess('')

                var appointmentInput = document.getElementById('reception_add_queue_appointment_id')

                var appointmentId = appointmentInput ? parseInt(appointmentInput.value, 10) : 0

                if (!appointmentId) {
                    showQueueError('Appointment ID is required to add to queue.')
                    return
                }

                if (typeof apiFetch !== 'function') {
                    showQueueError('API client is not available.')
                    return
                }

                confirmAction('Add to queue', 'Are you sure you want to add this appointment to the queue?')
                    .then(function (confirmed) {
                        if (!confirmed) return

                        apiFetch("{{ url('/api/queues') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ appointment_id: appointmentId })
                        })
                            .then(function (response) {
                                return response.json().then(function (data) {
                                    return { ok: response.ok, status: response.status, data: data }
                                }).catch(function () {
                                    return { ok: response.ok, status: response.status, data: null }
                                })
                            })
                            .then(function (result) {
                                if (!result.ok) {
                                    var message = 'Failed to add appointment to queue.'
                                    if (result.data && result.data.message) {
                                        message = result.data.message
                                    }
                                    showQueueError(message)
                                    return
                                }

                                showQueueSuccess('Appointment added to queue.')
                                window.location.reload()
                            })
                            .catch(function () {
                                showQueueError('Network error while adding to queue.')
                            })
                    })
            })
        }

        document.querySelectorAll('.reception-queue-config').forEach(function (button) {
            button.addEventListener('click', function () {
                var queueId = button.getAttribute('data-queue-id')
                if (!queueId) return
                var priority = button.getAttribute('data-priority-level') || '5'
                var row = button.closest ? button.closest('tr.reception-queue-row') : null
                var code = row ? (row.getAttribute('data-queue-code') || row.getAttribute('data-queue-number') || queueId) : queueId
                openQueueConfig(queueId, priority, 'Queue #' + String(code))
            })
        })

        if (configSave) {
            configSave.addEventListener('click', function () {
                if (!configQueueId || !configPriority) return
                updateQueuePriority(configPriority.value).then(function (ok) {
                    if (ok) window.location.reload()
                })
            })
        }

        if (configMoveUp) {
            configMoveUp.addEventListener('click', function () {
                if (!configQueueId || !configPriority) return
                var steps = configMoveSteps ? parseInt(configMoveSteps.value, 10) : 1
                if (!steps || isNaN(steps) || steps < 1) steps = 1
                if (steps > 4) steps = 4
                var current = parseInt(configPriority.value, 10)
                if (!current || isNaN(current)) current = 5
                var next = Math.max(1, current - steps)
                configPriority.value = String(next)
                updateQueuePriority(next).then(function (ok) {
                    if (ok) window.location.reload()
                })
            })
        }

        if (configMoveDown) {
            configMoveDown.addEventListener('click', function () {
                if (!configQueueId || !configPriority) return
                var steps = configMoveSteps ? parseInt(configMoveSteps.value, 10) : 1
                if (!steps || isNaN(steps) || steps < 1) steps = 1
                if (steps > 4) steps = 4
                var current = parseInt(configPriority.value, 10)
                if (!current || isNaN(current)) current = 5
                var next = Math.min(5, current + steps)
                configPriority.value = String(next)
                updateQueuePriority(next).then(function (ok) {
                    if (ok) window.location.reload()
                })
            })
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeQueueConfig()
        })

        document.querySelectorAll('.reception-queue-remove').forEach(function (button) {
            button.addEventListener('click', function () {
                var queueId = button.getAttribute('data-queue-id')
                if (!queueId) {
                    return
                }

                confirmAction('Remove queue entry', 'Are you sure you want to remove this queue entry?')
                    .then(function (confirmed) {
                        if (!confirmed) return
                        updateQueueStatus(queueId, 'cancelled', 'Queue entry removed.')
                    })
            })
        })

        function updateQueueStatus(queueId, status, successMessage) {
            if (!queueId) {
                return
            }

            showQueueError('')
            showQueueSuccess('')

            if (typeof apiFetch !== 'function') {
                showQueueError('API client is not available.')
                return
            }

            var url = "{{ url('/api/queues') }}/" + encodeURIComponent(queueId)

            apiFetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: status })
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, status: response.status, data: data }
                    }).catch(function () {
                        return { ok: response.ok, status: response.status, data: null }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        var message = 'Failed to update queue.'
                        if (result.data && result.data.message) {
                            message = result.data.message
                        }
                        showQueueError(message)
                        return
                    }

                    showQueueSuccess(successMessage || 'Queue updated.')
                    window.location.reload()
                })
                .catch(function () {
                    showQueueError('Network error while updating queue.')
                })
        }

        document.querySelectorAll('.reception-queue-status').forEach(function (button) {
            button.addEventListener('click', function () {
                var queueId = button.getAttribute('data-queue-id')
                var status = button.getAttribute('data-status')
                if (!queueId || !status) {
                    return
                }
                var normalized = String(status).toLowerCase()
                if (normalized === 'done') {
                    confirmAction('Mark as done', 'Are you sure you want to mark this queue entry as done?')
                        .then(function (confirmed) {
                            if (!confirmed) return
                            updateQueueStatus(queueId, status, 'Queue entry marked as done.')
                        })
                    return
                }
                if (normalized === 'no_show') {
                    confirmAction('Mark as no-show', 'Are you sure you want to mark this queue entry as no-show?')
                        .then(function (confirmed) {
                            if (!confirmed) return
                            updateQueueStatus(queueId, status, 'Queue entry marked as no-show.')
                        })
                    return
                }

                updateQueueStatus(queueId, status, 'Queue status updated.')
            })
        })

        var refreshButton = document.getElementById('receptionRefreshQueueButton')
        if (refreshButton) {
            refreshButton.addEventListener('click', function () {
                window.location.reload()
            })
        }

        var callNextButton = document.getElementById('receptionCallNextButton')
        if (callNextButton) {
            callNextButton.addEventListener('click', function () {
                showQueueError('')
                showQueueSuccess('')

                if (typeof apiFetch !== 'function') {
                    showQueueError('API client is not available.')
                    return
                }

                apiFetch("{{ url('/api/queues/call-next') }}", { method: 'POST' })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, status: response.status, data: data }
                        }).catch(function () {
                            return { ok: response.ok, status: response.status, data: null }
                        })
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            var message = 'Failed to call next.'
                            if (result.data && result.data.message) {
                                message = result.data.message
                            }
                            showQueueError(message)
                            return
                        }

                        showQueueSuccess('Next patient is now serving.')
                        window.location.reload()
                    })
                    .catch(function () {
                        showQueueError('Network error while calling next.')
                    })
            })
        }

        var publicLinkButton = document.getElementById('receptionPublicQueueLinkButton')
        if (publicLinkButton) {
            publicLinkButton.addEventListener('click', function () {
                var today = localDateIso()
                var link = "{{ route('queue.display') }}" + '?date=' + encodeURIComponent(today)

                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(link)
                    }
                } catch (_) {
                }

                try {
                    window.open(link, '_blank', 'noopener')
                } catch (_) {
                    window.location.href = link
                }
            })
        }

        function displayQueueLabel(item) {
            if (item && item.queue_code) return String(item.queue_code)
            if (item && item.queue_number != null) {
                var n = String(item.queue_number)
                while (n.length < 3) n = '0' + n
                return n
            }
            return '---'
        }

        function roomLabel(roomNumber) {
            if (roomNumber == null) return ''
            var n = parseInt(roomNumber, 10)
            if (isNaN(n) || n < 1) return ''
            return '[ROOM ' + n + ']'
        }

        function waitLabel(minutes) {
            if (minutes == null) return ''
            var n = parseInt(minutes, 10)
            if (isNaN(n) || n < 1) return ''
            return 'Est. wait ' + n + ' mins'
        }

        function buildQueueDisplay(payload) {
            var servingContainer = document.getElementById('queueDisplayNowServing')
            var nextList = document.getElementById('queueDisplayNextList')
            var nextCount = document.getElementById('queueDisplayNextCount')

            var serving = payload && Array.isArray(payload.now_serving) ? payload.now_serving : []
            var next = payload && Array.isArray(payload.next) ? payload.next : []
            var nextItems = next.slice(0, 5)

            if (servingContainer) {
                if (!serving.length) {
                    servingContainer.innerHTML =
                        '<div class="text-[0.85rem] text-cyan-300 uppercase tracking-[0.3em] mb-3">Now serving</div>' +
                        '<div class="rounded-3xl bg-slate-800/80 border border-slate-600/80 px-6 py-8 text-center text-slate-300">' +
                        'No queue is currently being served.' +
                        '</div>'
                } else {
                    var cards = serving.map(function (item) {
                        var qn = displayQueueLabel(item)
                        var patient = item && item.patient && item.patient.name ? item.patient.name : 'Patient'
                        var doctor = item && item.doctor && item.doctor.name ? item.doctor.name : '—'
                        var room = roomLabel(item && item.room_number != null ? item.room_number : null)

                        return '' +
                            '<div class="rounded-3xl bg-slate-800/80 border border-slate-600/80 px-6 py-6 shadow-[0_0_40px_rgba(8,47,73,0.9)]">' +
                                '<div class="text-[0.9rem] text-slate-300 mb-2">Queue</div>' +
                                '<div class="text-5xl md:text-6xl font-serif font-bold text-white tracking-[0.18em]">' + escapeHtml(qn) + '</div>' +
                                '<div class="mt-4 text-[0.95rem] text-slate-100 font-semibold">' + escapeHtml(patient) + '</div>' +
                                '<div class="mt-1 text-[0.8rem] text-slate-400">' + (room ? (escapeHtml(room) + ' ') : '') + escapeHtml(doctor) + '</div>' +
                            '</div>'
                    }).join('')

                    servingContainer.innerHTML =
                        '<div class="text-[0.85rem] text-cyan-300 uppercase tracking-[0.3em] mb-3">Now serving</div>' +
                        '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">' + cards + '</div>'
                }
            }

            if (nextList) {
                if (!nextItems.length) {
                    nextList.innerHTML = '<div class="text-[0.8rem] text-slate-400">No additional queue entries.</div>'
                } else {
                    nextList.innerHTML = nextItems.map(function (q) {
                        var qn = displayQueueLabel(q)
                        var patient = q && q.patient && q.patient.name ? q.patient.name : 'Patient'
                        var doctor = q && q.doctor && q.doctor.name ? q.doctor.name : 'Doctor'
                        var statusName = q && q.status ? String(q.status) : ''
                        var wait = waitLabel(q && q.estimated_wait_minutes != null ? q.estimated_wait_minutes : null)

                        return '' +
                            '<div class="rounded-2xl bg-slate-800/60 border border-slate-600/70 px-4 py-3 flex items-center justify-between gap-4">' +
                                '<div>' +
                                    '<div class="text-[0.75rem] text-slate-400 mb-1">Queue #' + escapeHtml(qn) + '</div>' +
                                    '<div class="text-[0.9rem] text-slate-100 font-semibold">' + escapeHtml(patient) + '</div>' +
                                    '<div class="text-[0.75rem] text-slate-400">' + escapeHtml(doctor) + '</div>' +
                                '</div>' +
                                '<div class="text-right">' +
                                    (wait ? ('<div class="text-[0.72rem] text-slate-400 mb-1">' + escapeHtml(wait) + '</div>') : '') +
                                    (statusName
                                        ? '<div class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.65rem] font-semibold bg-cyan-500/10 text-cyan-300 border border-cyan-500/40">' +
                                            escapeHtml(statusName.toUpperCase()) +
                                          '</div>'
                                        : '') +
                                '</div>' +
                            '</div>'
                    }).join('')
                }
            }

            if (nextCount) {
                nextCount.textContent = nextItems.length + ' shown'
            }
        }

        function fetchQueueSnapshot() {
            if (typeof apiFetch !== 'function') {
                return
            }

            var today = localDateIso()
            var url = "{{ route('queue.display.data') }}" + '?date=' + encodeURIComponent(today)

            apiFetch(url, { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, status: response.status, data: data }
                    }).catch(function () {
                        return { ok: response.ok, status: response.status, data: null }
                    })
                })
                .then(function (result) {
                    if (!result.ok || !result.data) {
                        return
                    }
                    buildQueueDisplay(result.data)
                })
                .catch(function () {
                })
        }

        var displayButton = document.getElementById('receptionDisplayQueueButton')
        var overlay = document.getElementById('queueDisplayOverlay')
        var closeButton = document.getElementById('queueDisplayCloseButton')
        var fullscreenButton = document.getElementById('queueDisplayFullscreenButton')

        if (displayButton && overlay) {
            displayButton.addEventListener('click', function () {
                overlay.classList.remove('hidden')
            })
        }

        function closeOverlay() {
            if (!overlay) {
                return
            }
            overlay.classList.add('hidden')
            if (document.fullscreenElement && document.exitFullscreen) {
                document.exitFullscreen()
            }
        }

        if (closeButton && overlay) {
            closeButton.addEventListener('click', function () {
                closeOverlay()
            })
        }

        if (fullscreenButton && overlay) {
            fullscreenButton.addEventListener('click', function () {
                if (!document.fullscreenElement) {
                    if (overlay.requestFullscreen) {
                        overlay.requestFullscreen()
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen()
                    }
                }
            })
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay && !overlay.classList.contains('hidden')) {
                closeOverlay()
            }
        })

        fetchQueueSnapshot()
        setInterval(fetchQueueSnapshot, 5000)
    })
</script>
