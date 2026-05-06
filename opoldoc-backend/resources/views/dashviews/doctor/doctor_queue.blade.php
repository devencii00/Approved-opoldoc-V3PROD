<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Queue</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Today</span>
    </div>
    <p class="text-xs text-slate-500 mb-3">
        Current and recent queue entries for clinic visits.
    </p>

    <div id="doctorQueueError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
        <div class="flex-1">
            <label for="doctor_queue_search" class="block text-[0.7rem] text-slate-600 mb-1">Search queue</label>
            <input id="doctor_queue_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Queue number, patient or date">
        </div>
        <div class="w-full md:w-40 flex flex-col gap-2">
            <div>
                <label for="doctor_queue_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
                <select id="doctor_queue_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="date_desc">Newest first</option>
                    <option value="date_asc">Oldest first</option>
                    <option value="number_asc">Queue number asc</option>
                    <option value="number_desc">Queue number desc</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-[0.78rem] text-slate-600">
            Queue controls:
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 border border-slate-100 text-[0.7rem] text-slate-700">
                ▶ Call next
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 border border-slate-100 text-[0.7rem] text-slate-700">
                ⏸ Serving
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 border border-slate-100 text-[0.7rem] text-slate-700">
                ✅ Done
            </span>
        </div>
        <div class="flex gap-2 justify-start sm:justify-end">
            <button type="button" id="doctorQueueCallNext" class="inline-flex items-center gap-1.5 rounded-xl bg-cyan-600 px-3 py-1.5 text-[0.78rem] font-semibold text-white hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-300">
                <x-lucide-play class="w-[16px] h-[16px]" />
                Call next patient
            </button>
            <button type="button" id="doctorQueueRefresh" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200">
                <x-lucide-refresh-cw class="w-[16px] h-[16px]" />
                Refresh queue
            </button>
        </div>
    </div>

    <div class="overflow-x-auto scrollbar-hidden">
        <table class="min-w-full text-left text-xs text-slate-600">
            <thead>
                <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                    <th class="py-2 pr-4 font-semibold">Queue #</th>
                    <th class="py-2 pr-4 font-semibold">Patient</th>
                    <th class="py-2 pr-4 font-semibold">Date</th>
                    <th class="py-2 pr-4 font-semibold">Priority</th>
                    <th class="py-2 pr-4 font-semibold">Status</th>
                    <th class="py-2 pr-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($doctorRecentQueue ?? [] as $queue)
                    @php
                        $patientParts = array_filter([
                            optional(optional($queue->appointment)->patient)->firstname,
                            optional(optional($queue->appointment)->patient)->middlename,
                            optional(optional($queue->appointment)->patient)->lastname,
                        ], function ($v) {
                            return (string) $v !== '';
                        });
                        $patientName = trim(implode(' ', $patientParts));
                        $statusName = $queue->status ? ucfirst(str_replace('_', ' ', $queue->status)) : '';
                        $priority = $queue->priority_level ?? '';
                        $dateKey = optional($queue->queue_datetime)->format('Y-m-d') ?? '';
                        $statusValue = strtolower($queue->status ?? '');
                    @endphp
                    <tr class="border-b border-slate-50 last:border-0 doctor-queue-row"
                        data-queue-id="{{ $queue->queue_id }}"
                        data-queue-number="{{ $queue->queue_number }}"
                        data-queue-code="{{ $queue->queue_code }}"
                        data-patient="{{ strtolower($patientName) }}"
                        data-date="{{ $dateKey }}"
                        data-status="{{ $statusValue }}">
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">{{ $queue->queue_code ?? $queue->queue_number }}</td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                            @if ($patientName)
                                {{ $patientName }}
                            @else
                                <span class="text-slate-400">Patient</span>
                            @endif
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            {{ $dateKey }}
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            @if ($priority !== '')
                                {{ $priority }}
                            @else
                                <span class="text-[0.7rem] text-slate-400">Normal</span>
                            @endif
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.68rem] font-medium border bg-slate-50 border-slate-100 text-slate-700">
                                {{ $statusName ?: 'Waiting' }}
                            </span>
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            <div class="flex gap-1.5">
                                <button type="button"
                                    class="doctor-queue-serving inline-flex items-center justify-center rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[0.7rem] font-medium text-slate-700 hover:bg-slate-100"
                                    data-queue-id="{{ $queue->queue_id }}">
                                    ⏸ Serving
                                </button>
                                <button type="button"
                                    class="doctor-queue-done inline-flex items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1 text-[0.7rem] font-medium text-emerald-700 hover:bg-emerald-100"
                                    data-queue-id="{{ $queue->queue_id }}">
                                    ✅ Done
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-[0.78rem] text-slate-400">
                            No queue entries found yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('doctor_queue_search')
        var sortSelect = document.getElementById('doctor_queue_sort')
        var rows = Array.prototype.slice.call(document.querySelectorAll('.doctor-queue-row'))
        var callNextButton = document.getElementById('doctorQueueCallNext')
        var refreshButton = document.getElementById('doctorQueueRefresh')
        var errorBox = document.getElementById('doctorQueueError')

        function showQueueError(message) {
            if (!errorBox) return
            if (!message) {
                errorBox.classList.add('hidden')
                errorBox.textContent = ''
                return
            }
            errorBox.textContent = message
            errorBox.classList.remove('hidden')
        }

        function applyDoctorQueueFilters() {
            var query = searchInput ? searchInput.value.toLowerCase().trim() : ''

            rows.forEach(function (row) {
                var number = ((row.getAttribute('data-queue-code') || '') + ' ' + (row.getAttribute('data-queue-number') || '')).trim()
                var patient = row.getAttribute('data-patient') || ''
                var date = row.getAttribute('data-date') || ''

                var matches = true
                if (query) {
                    matches =
                        ('#' + number).indexOf(query) !== -1 ||
                        patient.indexOf(query) !== -1 ||
                        date.indexOf(query) !== -1
                }

                row.style.display = matches ? '' : 'none'
            })

            applyDoctorQueueSort()
        }

        function applyDoctorQueueSort() {
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

                if (value === 'number_asc' || value === 'number_desc') {
                    if (na < nb) return value === 'number_asc' ? -1 : 1
                    if (na > nb) return value === 'number_asc' ? 1 : -1
                    return 0
                }

                if (da < db) return value === 'date_asc' ? -1 : 1
                if (da > db) return value === 'date_asc' ? 1 : -1
                return 0
            })

            visibleRows.forEach(function (row) {
                tbody.appendChild(row)
            })
        }

        function updateQueueStatus(queueId, status, onSuccess) {
            if (!queueId || !window.apiFetch) {
                showQueueError('Unable to update queue status right now.')
                return
            }

            showQueueError('')

            window.apiFetch('{{ url('/api/queues') }}/' + queueId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ status: status }),
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Failed to update queue entry.')
                    }
                    return response.json()
                })
                .then(function () {
                    if (typeof onSuccess === 'function') {
                        onSuccess()
                    }
                })
                .catch(function () {
                    showQueueError('Could not update queue entry. Please try again.')
                })
        }

        function handleCallNext() {
            if (!rows.length) {
                showQueueError('No queue entries available.')
                return
            }

            var waitingRows = rows.filter(function (row) {
                var status = (row.getAttribute('data-status') || '').toLowerCase()
                return status === 'waiting' || status === ''
            })

            if (!waitingRows.length) {
                showQueueError('No patients waiting in the queue.')
                return
            }

            waitingRows.sort(function (a, b) {
                var na = parseInt(a.getAttribute('data-queue-number') || '0', 10)
                var nb = parseInt(b.getAttribute('data-queue-number') || '0', 10)
                if (na < nb) return -1
                if (na > nb) return 1
                return 0
            })

            var nextRow = waitingRows[0]
            var queueId = nextRow.getAttribute('data-queue-id')

            updateQueueStatus(queueId, 'serving', function () {
                rows.forEach(function (row) {
                    if (row !== nextRow && (row.getAttribute('data-status') || '').toLowerCase() === 'serving') {
                        row.setAttribute('data-status', 'waiting')
                        var badge = row.querySelector('td:nth-child(5) span')
                        if (badge) {
                            badge.textContent = 'Waiting'
                        }
                    }
                })

                nextRow.setAttribute('data-status', 'serving')
                var badge = nextRow.querySelector('td:nth-child(5) span')
                if (badge) {
                    badge.textContent = 'Serving'
                }
            })
        }

        function bindRowButtons() {
            var servingButtons = Array.prototype.slice.call(document.querySelectorAll('.doctor-queue-serving'))
            var doneButtons = Array.prototype.slice.call(document.querySelectorAll('.doctor-queue-done'))

            servingButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var queueId = btn.getAttribute('data-queue-id')
                    var row = btn.closest('tr')
                    if (!queueId || !row) return

                    updateQueueStatus(queueId, 'serving', function () {
                        row.setAttribute('data-status', 'serving')
                        var badge = row.querySelector('td:nth-child(5) span')
                        if (badge) {
                            badge.textContent = 'Serving'
                        }
                    })
                })
            })

            doneButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var queueId = btn.getAttribute('data-queue-id')
                    var row = btn.closest('tr')
                    if (!queueId || !row) return

                    updateQueueStatus(queueId, 'done', function () {
                        row.setAttribute('data-status', 'done')
                        var badge = row.querySelector('td:nth-child(5) span')
                        if (badge) {
                            badge.textContent = 'Done'
                        }
                    })
                })
            })
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyDoctorQueueFilters)
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', applyDoctorQueueSort)
        }
        if (callNextButton) {
            callNextButton.addEventListener('click', handleCallNext)
        }
        if (refreshButton) {
            refreshButton.addEventListener('click', function () {
                window.location.reload()
            })
        }

        applyDoctorQueueFilters()
        bindRowButtons()
    })
</script>
