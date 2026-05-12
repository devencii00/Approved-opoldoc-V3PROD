@php
    $doctorQueueItems = collect($doctorTodayQueue ?? [])
        ->sortBy(function ($queue) {
            $status = strtolower((string) ($queue->status ?? ''));
            $rank = match ($status) {
                'serving' => 0,
                'waiting' => 1,
                'consulted' => 2,
                'done' => 3,
                'no_show' => 4,
                'cancelled' => 5,
                default => 6,
            };

            return sprintf(
                '%02d-%06d-%s',
                $rank,
                (int) ($queue->queue_number ?? 999999),
                optional($queue->queue_datetime)->format('Y-m-d H:i:s') ?? ''
            );
        })
        ->values();
    $doctorUserId = (int) ($currentUser->user_id ?? request()->query('user_id') ?? 0);
@endphp

<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">My Queue</h2>
            <p class="text-xs text-slate-500">Only today&apos;s queue entries assigned to you are shown here.</p>
        </div>
        <div class="flex items-center gap-2">
            <button id="doctorQueueCallNextButton" type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-[0.8rem] font-semibold hover:bg-slate-800 transition-colors disabled:opacity-60 disabled:hover:bg-slate-900 min-w-[130px] relative">
                <span id="doctorQueueCallNextSpinner" class="hidden absolute w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                <span id="doctorQueueCallNextContent" class="inline-flex items-center gap-2">
                    <x-lucide-megaphone class="w-[18px] h-[18px]" />
                    Call next
                </span>
            </button>
            <button id="doctorQueueRefreshButton" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-800 text-[0.8rem] font-semibold hover:bg-slate-200 transition-colors border border-slate-200">
                <x-lucide-refresh-cw class="w-[18px] h-[18px]" />
                Refresh
            </button>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
        <div id="doctorQueueError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
        <div id="doctorQueueSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>

        <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
            <div class="flex-1">
                <label for="doctor_queue_search" class="block text-[0.7rem] text-slate-600 mb-1">Search queue</label>
                <input id="doctor_queue_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Queue number or patient">
            </div>
            <div class="w-full md:w-40">
                <label for="doctor_queue_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
                <select id="doctor_queue_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="queue">Queue order</option>
                    <option value="newest">Newest first</option>
                    <option value="oldest">Oldest first</option>
                </select>
            </div>
        </div>

    <div class="overflow-x-auto overflow-y-auto scrollbar-hidden h-[330px]">
            <table class="min-w-full text-left text-xs text-slate-600">
                <thead>
                    <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                        <th class="py-2 pr-4 font-semibold">Queue #</th>
                        <th class="py-2 pr-4 font-semibold">Patient</th>
                        <th class="py-2 pr-4 font-semibold">Date</th>
                        <th class="py-2 pr-4 font-semibold">Time</th>
                        <th class="py-2 pr-4 font-semibold">Status</th>
                        <th class="py-2 pr-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($doctorQueueItems as $queue)
                        @php
                            $patientName = optional(optional($queue->appointment)->patient)->personalInformation->full_name ?? '';
                            $statusName = strtolower((string) ($queue->status ?? ''));
                            $queueId = $queue->queue_id ?? null;
                            $dateKey = optional($queue->queue_datetime)->format('Y-m-d') ?? '';
                            $timeKey = optional($queue->queue_datetime)->format('H:i') ?? '';
                            $dateTimeKey = optional($queue->queue_datetime)->format('Y-m-d H:i:s') ?? '';
                        @endphp
                        <tr class="border-b border-slate-50 last:border-0 doctor-queue-row"
                            data-queue-id="{{ $queueId }}"
                            data-queue-number="{{ $queue->queue_number }}"
                            data-queue-code="{{ $queue->queue_code }}"
                            data-patient="{{ strtolower($patientName) }}"
                            data-status="{{ $statusName }}"
                            data-datetime="{{ $dateTimeKey }}">
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">{{ $queue->queue_code ?? $queue->queue_number }}</td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                                @if ($patientName)
                                    {{ $patientName }}
                                @else
                                    <span class="text-slate-400">Patient</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">{{ $dateKey ?: '—' }}</td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">{{ $timeKey ?: '—' }}</td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.68rem] font-medium border bg-slate-50 border-slate-100 text-slate-700">
                                    {{ ucfirst(str_replace('_', ' ', $statusName ?: 'unknown')) }}
                                </span>
                            </td>
                            <td class="py-2 pr-4 text-right">
                                @if ($queueId && ! in_array($statusName, ['done', 'cancelled', 'no_show'], true))
                                    <div class="inline-flex items-center gap-1.5">
                                        @if ($statusName === 'waiting')
                                            <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2 py-1 text-[0.7rem] text-slate-600 hover:bg-slate-50 doctor-queue-status" data-queue-id="{{ $queueId }}" data-status="serving">
                                                <x-lucide-play class="w-[16px] h-[16px]" />
                                                Serving
                                            </button>
                                        @endif
                                        @if (in_array($statusName, ['waiting', 'serving'], true))
                                            <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2 py-1 text-[0.7rem] text-rose-700 hover:bg-rose-50 doctor-queue-status" data-queue-id="{{ $queueId }}" data-status="no_show">
                                                <x-lucide-user-x class="w-[16px] h-[16px]" />
                                                No show
                                            </button>
                                        @endif
                                        @if ($statusName === 'consulted')
                                            <span class="inline-flex items-center rounded-lg border border-cyan-200 bg-cyan-50 px-2 py-1 text-[0.68rem] font-medium text-cyan-700">
                                                Waiting for payment
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[0.7rem] text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-[0.78rem] text-slate-400">
                                No queue entries assigned to you today.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('doctor_queue_search')
        var sortSelect = document.getElementById('doctor_queue_sort')
        var rows = Array.prototype.slice.call(document.querySelectorAll('.doctor-queue-row'))
        var errorBox = document.getElementById('doctorQueueError')
        var successBox = document.getElementById('doctorQueueSuccess')
        var refreshButton = document.getElementById('doctorQueueRefreshButton')
        var callNextButton = document.getElementById('doctorQueueCallNextButton')
        var callNextSpinner = document.getElementById('doctorQueueCallNextSpinner')
        var callNextContent = document.getElementById('doctorQueueCallNextContent')
        var doctorUserId = {{ $doctorUserId > 0 ? $doctorUserId : 'null' }}

        function showError(message) {
            if (!errorBox) return
            errorBox.textContent = message || ''
            errorBox.classList.toggle('hidden', !message)
        }

        function showSuccess(message) {
            if (!successBox) return
            successBox.textContent = message || ''
            successBox.classList.toggle('hidden', !message)
        }

        function setCallNextSubmitting(isSubmitting) {
            if (callNextButton) callNextButton.disabled = !!isSubmitting
            if (callNextSpinner) callNextSpinner.classList.toggle('hidden', !isSubmitting)
            if (callNextContent) callNextContent.classList.toggle('opacity-0', !!isSubmitting)
        }

        function applyDoctorQueueFilters() {
            var query = searchInput ? String(searchInput.value || '').toLowerCase().trim() : ''

            rows.forEach(function (row) {
                var number = ((row.getAttribute('data-queue-code') || '') + ' ' + (row.getAttribute('data-queue-number') || '')).trim()
                var patient = row.getAttribute('data-patient') || ''
                var matches = true

                if (query) {
                    matches = ('#' + number).indexOf(query) !== -1 || patient.indexOf(query) !== -1
                }

                row.style.display = matches ? '' : 'none'
            })

            applyDoctorQueueSort()
        }

        function applyDoctorQueueSort() {
            if (!sortSelect || !rows.length) return
            var tbody = rows[0].parentNode
            var value = sortSelect.value
            var visibleRows = rows.filter(function (row) {
                return row.style.display !== 'none'
            })

            visibleRows.sort(function (a, b) {
                var qa = parseInt(a.getAttribute('data-queue-number') || '999999', 10)
                var qb = parseInt(b.getAttribute('data-queue-number') || '999999', 10)
                var da = a.getAttribute('data-datetime') || ''
                var db = b.getAttribute('data-datetime') || ''
                var sa = String(a.getAttribute('data-status') || '')
                var sb = String(b.getAttribute('data-status') || '')

                function statusRank(status) {
                    if (status === 'serving') return 0
                    if (status === 'waiting') return 1
                    if (status === 'consulted') return 2
                    if (status === 'done') return 3
                    if (status === 'no_show') return 4
                    if (status === 'cancelled') return 5
                    return 6
                }

                var ra = statusRank(sa)
                var rb = statusRank(sb)
                if (ra !== rb) return ra - rb

                if (value === 'newest') {
                    if (da < db) return 1
                    if (da > db) return -1
                    return qb - qa
                }

                if (value === 'oldest') {
                    if (da < db) return -1
                    if (da > db) return 1
                    return qa - qb
                }

                return qa - qb
            })

            visibleRows.forEach(function (row) {
                tbody.appendChild(row)
            })
        }

        function updateQueueStatus(queueId, status, successMessage) {
            if (!queueId || typeof apiFetch !== 'function') return

            showError('')
            showSuccess('')

            apiFetch("{{ url('/api/queues') }}/" + encodeURIComponent(queueId), {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: status })
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    }).catch(function () {
                        return { ok: response.ok, data: null }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        showError(result.data && result.data.message ? result.data.message : 'Failed to update queue.')
                        return
                    }

                    showSuccess(successMessage || 'Queue updated.')
                    window.location.reload()
                })
                .catch(function () {
                    showError('Network error while updating queue.')
                })
        }

        document.querySelectorAll('.doctor-queue-status').forEach(function (button) {
            button.addEventListener('click', function () {
                var queueId = button.getAttribute('data-queue-id')
                var status = button.getAttribute('data-status')
                if (!queueId || !status) return
                updateQueueStatus(queueId, status, 'Queue status updated.')
            })
        })

        if (searchInput) {
            searchInput.addEventListener('input', applyDoctorQueueFilters)
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', applyDoctorQueueSort)
        }
        if (refreshButton) {
            refreshButton.addEventListener('click', function () {
                window.location.reload()
            })
        }
        if (callNextButton) {
            callNextButton.addEventListener('click', function () {
                if (callNextButton.disabled || typeof apiFetch !== 'function') return

                showError('')
                showSuccess('')
                setCallNextSubmitting(true)

                var payload = {}
                if (doctorUserId) {
                    payload.doctor_id = doctorUserId
                }

                apiFetch("{{ url('/api/queues/call-next') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data }
                        }).catch(function () {
                            return { ok: response.ok, data: null }
                        })
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            showError(result.data && result.data.message ? result.data.message : 'Failed to call next patient.')
                            setCallNextSubmitting(false)
                            return
                        }

                        showSuccess('The next patient in your queue is now marked as serving.')
                        window.location.reload()
                    })
                    .catch(function () {
                        showError('Network error while calling next patient.')
                        setCallNextSubmitting(false)
                    })
            })
        }

        applyDoctorQueueFilters()
    })
</script>
