@php
    $metrics = $receptionMetrics ?? [];
    $sectionKey = $section ?? 'overview';

    $newRegistrationsToday = (int) ($metrics['newRegistrationsToday'] ?? 0);
    $appointmentsToday = (int) ($metrics['appointmentsToday'] ?? 0);
    $walkInsToday = (int) ($metrics['walkInsToday'] ?? 0);
    $pendingQueueRequests = (int) ($metrics['pendingQueueRequests'] ?? 0);
    $waitingInQueue = (int) ($metrics['waitingCount'] ?? 0);
    $currentQueueCount = (int) ($metrics['currentQueueCount'] ?? 0);
    $transactionsToday = (float) ($metrics['transactionsToday'] ?? 0);
    $activeCallNextDoctors = collect($receptionDoctorSlots ?? [])
        ->map(function ($slot) {
            $doctor = optional($slot)->doctor;
            return (object) [
                'doctor_id' => (int) ($slot->doctor_id ?? 0),
                'doctor_name' => (string) (
                    optional(optional($doctor)->personalInformation)->full_name
                    ?? $slot->doctor_name
                    ?? 'Doctor'
                ),
                'doctor_specialization' => (string) (
                    optional($doctor)->specialization
                    ?? $slot->doctor_specialization
                    ?? ''
                ),
                'slot_start' => $slot->start_time ?? null,
                'slot_end' => $slot->end_time ?? null,
            ];
        })
        ->filter(function ($slot) {
            return $slot->doctor_id > 0;
        })
        ->values();
@endphp

<div class="space-y-6">
    @if ($sectionKey === 'overview')
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 mb-1">Receptionist workspace</h1>
            <p class="text-sm text-slate-500">Handle registrations, appointments, and the live queue at the front desk.</p>
        </div>

        <div class="grid gap-4 grid-cols-1 lg:grid-cols-3">
            <div class="bg-white border border-slate-200 rounded-[18px] p-5 lg:col-span-2 shadow-[0_2px_10px_rgba(15,23,42,0.04)] flex flex-col h-[600px] overflow-hidden">
                <div class="flex items-center justify-between mb-3 shrink-0">
                    <h2 class="text-sm font-semibold text-slate-900">Today at a glance</h2>
                    <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Front desk</span>
                </div>
                <div class="grid gap-3 grid-cols-1 sm:grid-cols-3 text-sm text-slate-600 shrink-0">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-xs text-slate-500 mb-1">New registrations</div>
                        <div class="font-serif font-bold text-xl text-slate-900">{{ number_format($newRegistrationsToday) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-xs text-slate-500 mb-1">Appointments booked</div>
                        <div class="font-serif font-bold text-xl text-slate-900">{{ number_format($appointmentsToday) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-xs text-slate-500 mb-1">Waiting in queue</div>
                        <div class="font-serif font-bold text-xl text-slate-900">{{ number_format($waitingInQueue) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-xs text-slate-500 mb-1">Walk-ins</div>
                        <div class="font-serif font-bold text-xl text-slate-900">{{ number_format($walkInsToday) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-xs text-slate-500 mb-1">Pending requests</div>
                        <div class="font-serif font-bold text-xl text-slate-900">{{ number_format($pendingQueueRequests) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-xs text-slate-500 mb-1">Current queue count</div>
                        <div class="font-serif font-bold text-xl text-slate-900">{{ number_format($currentQueueCount) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 sm:col-span-3">
                        <div class="text-xs text-slate-500 mb-1">Today&apos;s transactions (paid)</div>
                        <div class="font-serif font-bold text-xl text-slate-900">₱{{ number_format($transactionsToday, 2) }}</div>
                    </div>
                </div>

                <!-- Today's Transactions Table View -->
                <div class="mt-4 flex-1 min-h-0 border border-slate-200 rounded-xl overflow-hidden flex flex-col bg-white">
                    <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 shrink-0 flex justify-between items-center">
                        <h3 class="text-[0.75rem] font-semibold text-slate-700 uppercase tracking-wider">Today's Transactions</h3>
                    </div>
                    <div class="flex-1 overflow-y-auto scrollbar-hidden">
                        <table class="w-full text-left text-[0.75rem] text-slate-600 whitespace-nowrap">
                            <thead class="bg-white text-slate-500  top-0 border-b  border-slate-100 shadow-sm z-10">
                                <tr>
                                    <th class="px-4 py-2.5 font-medium">Date</th>
                                    <th class="px-4 py-2.5 font-medium">Reference</th>
                                    <th class="px-4 py-2.5 font-medium">Patient</th>
                                    <th class="px-4 py-2.5 font-medium">Service Type</th>
                                    <th class="px-4 py-2.5 font-medium">Gross</th>
                                    <th class="px-4 py-2.5 font-medium">Discount</th>
                                    <th class="px-4 py-2.5 font-medium">Net</th>
                                    <th class="px-4 py-2.5 font-medium">Mode</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" id="receptionTodaysTransactionsTableBody">
                                <!-- Placeholder / Empty state -->
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                        No transactions recorded today.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Queue & Schedule Panel -->
<div class="bg-white border border-slate-100 rounded-2xl shadow-xl flex flex-col h-[600px] overflow-hidden">
    <!-- Header with gradient accent -->
    <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-white to-slate-50/50 shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="">
                    <!-- <x-lucide-calendar-clock class="w-4 h-4" /> -->
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800 tracking-tight">Queue & Schedule</h2>
                    <p class="text-[0.7rem] text-slate-500 mt-0.5">Today's patient flow</p>
                </div>
            </div>
            <span class="text-[0.65rem] text-slate-400 uppercase tracking-wider bg-slate-50 px-2 py-1 rounded-full border border-slate-100">Live Preview</span>
        </div>
    </div>

    <!-- Main content container -->
    <div class="flex-1 min-h-0 p-4 bg-white flex flex-col overflow-hidden">
        <!-- Queue Section -->
        <div class="flex-1 min-h-0 flex flex-col overflow-hidden">
            <div class="flex items-start justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-600">
                        <x-lucide-users class="w-3.5 h-3.5" />
                    </div>
                    <div>
                        <div class="text-[0.72rem] font-semibold text-slate-800">Active Queue</div>
                        <div id="receptionNextQueueMeta" class="text-[0.65rem] text-slate-400 mt-0.5">Waiting patients</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select id="receptionNextQueueDoctorSelect" class="w-[250px] rounded-xl border border-slate-200 bg-white px-3 py-2 text-[0.75rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                        <option value="">Auto (any active doctor)</option>
                        @foreach ($activeCallNextDoctors as $doctorState)
                            @php
                                $doctorNameRaw = trim((string) ($doctorState->doctor_name ?? ''));
                                $doctorNameClean = preg_replace('/^\s*dr\.?\s*/i', '', $doctorNameRaw);
                                $doctorNameDisplay = $doctorNameClean !== '' ? $doctorNameClean : 'Doctor';
                                $doctorSpecialization = trim((string) ($doctorState->doctor_specialization ?? ''));
                            @endphp
                            <option value="{{ $doctorState->doctor_id }}">
                                Dr. {{ $doctorNameDisplay }}
                                @if ($doctorSpecialization !== '')
                                    - {{ $doctorSpecialization }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <button type="button" id="receptionNextQueueNextBtn" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-[0.72rem] font-semibold hover:bg-slate-700 transition-all duration-150 shadow-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 disabled:opacity-60 disabled:pointer-events-none">
                        <span id="receptionNextQueueNextSpinner" class="hidden w-3 h-3 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                        <x-lucide-megaphone class="w-3.5 h-3.5" />
                        <span id="receptionNextQueueNextLabel">Call next</span>
                    </button>
                </div>
            </div>

            <div id="receptionNextQueueInlineMessage" class="hidden mt-3 rounded-lg border px-3 py-2 text-[0.7rem]"></div>

            <div class="mt-3 flex-1 min-h-0 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-slate-50">
                <ul id="receptionNextQueue" class="space-y-2">
                    <li class="text-center text-[0.7rem] text-slate-400 py-4">No patients in queue</li>
                </ul>
            </div>
        </div>

        <div class="my-3 border-t border-slate-200"></div>

        <!-- Appointments Section -->
        <div class="flex-1 min-h-0 flex flex-col overflow-hidden">
            <div class="flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-600">
                        <x-lucide-calendar-check class="w-3.5 h-3.5" />
                    </div>
                    <div>
                        <div class="text-[0.72rem] font-semibold text-slate-800">Upcoming Appointments</div>
                        <div id="receptionNextAppointmentsMeta" class="text-[0.65rem] text-slate-400 mt-0.5">Scheduled visits</div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <x-lucide-clock class="w-3 h-3 text-slate-300" />
                    <span class="text-[0.6rem] text-slate-400">Today</span>
                </div>
            </div>

            <div class="mt-3 flex-1 min-h-0 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-slate-50">
                <ul id="receptionNextAppointments" class="space-y-2">
                    <li class="text-center text-[0.7rem] text-slate-400 py-4">No appointments scheduled</li>
                </ul>
            </div>
        </div>
    </div>
</div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var nextApptsList = document.getElementById('receptionNextAppointments')
                var nextApptsMeta = document.getElementById('receptionNextAppointmentsMeta')
                var nextQueueList = document.getElementById('receptionNextQueue')
                var nextQueueMeta = document.getElementById('receptionNextQueueMeta')
                var nextQueueBtn = document.getElementById('receptionNextQueueNextBtn')
                var nextQueueSpinner = document.getElementById('receptionNextQueueNextSpinner')
                var nextQueueLabel = document.getElementById('receptionNextQueueNextLabel')
                var nextQueueInlineMessage = document.getElementById('receptionNextQueueInlineMessage')
                var nextQueueDoctorSelect = document.getElementById('receptionNextQueueDoctorSelect')
                if (typeof apiFetch !== 'function') return

                function escapeHtml(input) {
                    var s = String(input == null ? '' : input)
                    return s
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;')
                }

                function isoDate(d) {
                    var yr = d.getFullYear()
                    var mo = String(d.getMonth() + 1).padStart(2, '0')
                    var da = String(d.getDate()).padStart(2, '0')
                    return yr + '-' + mo + '-' + da
                }

                function parseApiDate(value) {
                    if (!value) return null
                    var raw = String(value)
                    var dt = new Date(raw)
                    if (!isNaN(dt.getTime())) return dt
                    var cleaned = raw.replace(' ', 'T')
                    dt = new Date(cleaned)
                    if (!isNaN(dt.getTime())) return dt
                    return null
                }

                function nameForUser(user) {
                    if (!user) return ''
                    var parts = [user.firstname, user.middlename, user.lastname].filter(function (v) { return String(v || '').trim() !== '' })
                    var name = parts.join(' ').trim()
                    if (!name) name = 'User #' + (user.user_id || '')
                    return name
                }

                function formatTime(dt) {
                    try {
                        return dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    } catch (_) {
                        return ''
                    }
                }

                function showNextQueueMessage(message, tone) {
                    if (!nextQueueInlineMessage) return
                    var kind = String(tone || 'info').toLowerCase()
                    nextQueueInlineMessage.textContent = message || ''
                    nextQueueInlineMessage.className = 'mt-2 rounded-lg border px-2.5 py-2 text-[0.72rem]'
                    if (!message) {
                        nextQueueInlineMessage.classList.add('hidden')
                        return
                    }
                    if (kind === 'error') {
                        nextQueueInlineMessage.classList.add('border-red-200', 'bg-red-50', 'text-red-700')
                    } else if (kind === 'success') {
                        nextQueueInlineMessage.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-700')
                    } else {
                        nextQueueInlineMessage.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-700')
                    }
                }

                function load() {
                    if (nextApptsList) nextApptsList.innerHTML = '<li class="text-[0.78rem] text-slate-400">Loading…</li>'
                    if (nextQueueList) nextQueueList.innerHTML = '<li class="text-[0.78rem] text-slate-400">Loading…</li>'
                    if (nextApptsMeta) nextApptsMeta.textContent = ''
                    if (nextQueueMeta) nextQueueMeta.textContent = ''
                    if (nextQueueBtn) nextQueueBtn.disabled = true

                    var now = new Date()
                    var today = isoDate(now)

                    var queueSnapshotUrl = "{{ route('queue.display.data') }}" + '?date=' + encodeURIComponent(today)
                    var apptsUrl = "{{ url('/api/appointments') }}" + '?start_date=' + encodeURIComponent(today) + '&end_date=' + encodeURIComponent(today) + '&status=confirmed&per_page=100'

                    Promise.all([
                        apiFetch(queueSnapshotUrl, { method: 'GET' }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d } }).catch(function () { return { ok: r.ok, data: null } }) }).catch(function () { return { ok: false, data: null } }),
                        apiFetch(apptsUrl, { method: 'GET' }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d } }).catch(function () { return { ok: r.ok, data: null } }) }).catch(function () { return { ok: false, data: null } })
                    ])
                        .then(function (results) {
                            var queuePayload = null
                            if (results[0] && results[0].ok && results[0].data) {
                                queuePayload = results[0].data
                            }

                            var appts = []
                            if (results[1] && results[1].ok && results[1].data) {
                                appts = Array.isArray(results[1].data.data) ? results[1].data.data : []
                            }

                            if (nextApptsList) {
                                var upcoming = appts.slice().map(function (a) {
                                    var dt3 = parseApiDate(a && a.appointment_datetime ? a.appointment_datetime : null)
                                    return { row: a, dt: dt3 }
                                }).filter(function (x) {
                                    return x.dt && x.dt.getTime() >= now.getTime()
                                }).sort(function (a, b) {
                                    return a.dt.getTime() - b.dt.getTime()
                                }).slice(0, 5)

                                if (!upcoming.length) {
                                    nextApptsList.innerHTML = '<li class="text-[0.78rem] text-slate-500">No upcoming appointments.</li>'
                                } else {
                                    nextApptsList.innerHTML = upcoming.map(function (x) {
                                        var patient = x.row && x.row.patient ? nameForUser(x.row.patient) : 'Patient'
                                        var doctor = x.row && x.row.doctor ? nameForUser(x.row.doctor) : 'Doctor'
                                        var t = formatTime(x.dt)
                                        return '<li class="flex items-start justify-between gap-3">' +
                                            '<div class="text-slate-700"><span class="font-semibold">' + escapeHtml(t) + '</span> ' + escapeHtml(patient) + '</div>' +
                                            '<div class="text-[0.72rem] text-slate-500 whitespace-nowrap">' + escapeHtml(doctor) + '</div>' +
                                        '</li>'
                                    }).join('')
                                }
                            }

                            if (nextQueueList) {
                                function queueLabel(q) {
                                    if (q && q.queue_code) return String(q.queue_code)
                                    var n = q && q.queue_number != null ? String(q.queue_number) : ''
                                    while (n.length && n.length < 3) n = '0' + n
                                    return n || '---'
                                }

                                var serving = queuePayload && Array.isArray(queuePayload.now_serving) ? queuePayload.now_serving : []
                                var next = queuePayload && Array.isArray(queuePayload.next) ? queuePayload.next.slice(0, 5) : []
                                var waitingCount = queuePayload && queuePayload.counts && queuePayload.counts.waiting != null
                                    ? parseInt(String(queuePayload.counts.waiting), 10)
                                    : next.length
                                if (isNaN(waitingCount) || waitingCount < 0) waitingCount = next.length

                                var nowServingLabels = serving.slice(0, 4).map(function (q) {
                                    var patientName = q && q.patient && q.patient.name ? String(q.patient.name) : 'Patient'
                                    var doctorName = q && q.doctor && q.doctor.name ? String(q.doctor.name) : ''
                                    return queueLabel(q) + ' ' + patientName + (doctorName ? (' — ' + doctorName) : '')
                                })

                                var html = ''
                               const labels = nowServingLabels.length 
    ? nowServingLabels.map(label => `<span class="bg-slate-100 px-2 py-0.5 rounded text-slate-800">${escapeHtml(label)}</span>`).join(' ')
    : '<span class="text-slate-400">—</span>';

html += `
<li class="text-[0.78rem] flex items-start gap-2 py-2">
    <span class="font-semibold text-slate-700 mt-0.5">Now serving:</span>
    <div class="flex flex-wrap gap-1">
        ${labels}
    </div>
</li>`;

if (!next.length) {
    html += '<li class="text-[0.78rem] text-slate-500 py-2">No one waiting.</li>';
} else {
    // Start the container with the single header
    html += `
    <li class="flex items-start gap-2 py-2 border-t border-slate-50 mt-1">
        <span class="text-[0.78rem] font-semibold text-slate-700 mt-1 shrink-0">Next in line:</span>
        <div class="flex flex-col gap-2 w-full">
            ${next.map(function (q) {
                var nm = q && q.patient && q.patient.name ? String(q.patient.name) : 'Patient';
                var qn = queueLabel(q);
                var doctorName = q && q.doctor && q.doctor.name ? String(q.doctor.name) : 'Doctor';
                var est = q && q.estimated_wait_minutes != null ? parseInt(String(q.estimated_wait_minutes), 10) : null;
                
                var estLabel = (est != null && !isNaN(est) && est > 0) 
                    ? `<span class="text-slate-400 text-[0.7rem] block mt-0.5">(est. ${est} min)</span>` 
                    : '';

                return `
                <div class="text-[0.78rem] leading-tight">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-slate-700 px-1.5 py-0.5 rounded font-medium border border-indigo-100 text-[0.7rem]">
                            #${escapeHtml(qn)}
                        </span>
                        <span class="text-slate-800 font-medium">${escapeHtml(nm)}</span>
                        <span class="text-slate-400">— ${escapeHtml(doctorName)}</span>
                    </div>
                    ${estLabel}
                </div>`;
            }).join('')}
        </div>
    </li>`;
}


                                nextQueueList.innerHTML = html
                                if (nextQueueBtn) nextQueueBtn.disabled = waitingCount < 1
                                if (nextQueueBtn) nextQueueBtn.setAttribute('data-can-call', waitingCount > 0 ? '1' : '0')
                                if (nextQueueLabel) nextQueueLabel.textContent = 'Call next'
                            }

                            var stamp = 'Updated ' + now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                            if (nextApptsMeta) nextApptsMeta.textContent = stamp
                            if (nextQueueMeta) nextQueueMeta.textContent = stamp
                        })
                        .catch(function () {
                            if (nextApptsList) nextApptsList.innerHTML = '<li class="text-[0.78rem] text-slate-500">Unable to load.</li>'
                            if (nextQueueList) nextQueueList.innerHTML = '<li class="text-[0.78rem] text-slate-500">Unable to load.</li>'
                            if (nextQueueBtn) nextQueueBtn.disabled = true
                            showNextQueueMessage('Unable to refresh queue snapshot right now.', 'error')
                        })
                }

                function setNextQueueSubmitting(isSubmitting) {
                    if (nextQueueSpinner) nextQueueSpinner.classList.toggle('hidden', !isSubmitting)
                    if (nextQueueBtn) {
                        if (isSubmitting) {
                            nextQueueBtn.disabled = true
                        } else {
                            var canCall = String(nextQueueBtn.getAttribute('data-can-call') || '') === '1'
                            nextQueueBtn.disabled = !canCall
                        }
                    }
                }

                function readApiResult(response) {
                    return response.json().then(function (d) {
                        return { ok: response.ok, data: d }
                    }).catch(function () {
                        return { ok: response.ok, data: null }
                    })
                }

                function callNextOnce(doctorId) {
                    var payload = {}
                    if (doctorId) payload.doctor_id = doctorId
                    return apiFetch("{{ url('/api/queues/call-next') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    })
                        .then(readApiResult)
                        .then(function (result) {
                            if (!result.ok) {
                                var code = result && result.data && result.data.code ? String(result.data.code) : ''
                                var graceful = code === 'SERVING_SLOTS_FULL' || code === 'NO_AVAILABLE_SLOTS' || code === 'NO_ELIGIBLE_WAITING' || code === 'NO_ACTIVE_DOCTORS'
                                if (graceful) return { called: false, tone: 'error', message: 'No available slots to call next right now.' }
                                var message = (result && result.data && result.data.message) ? String(result.data.message) : 'Unable to call next right now.'
                                throw new Error(message)
                            }
                            return { called: true, message: 'Next patient is now serving.' }
                        })
                }

                if (nextQueueBtn) {
                    nextQueueBtn.addEventListener('click', function () {
                        var canCall = String(nextQueueBtn.getAttribute('data-can-call') || '') === '1'
                        if (!canCall) return

                        var selectedDoctorId = nextQueueDoctorSelect ? parseInt(String(nextQueueDoctorSelect.value || '').trim(), 10) : 0
                        setNextQueueSubmitting(true)

                        callNextOnce(selectedDoctorId)
                            .then(function (state) {
                                var message = state && state.message ? state.message : 'Call next finished.'
                                var tone = state && state.tone ? String(state.tone) : (state && state.called ? 'success' : 'info')
                                showNextQueueMessage(message, tone)
                                load()
                            })
                            .catch(function (err) {
                                var message = (err && err.message) ? err.message : 'Network error while calling next.'
                                showNextQueueMessage(message, 'error')
                            })
                            .finally(function () {
                                setNextQueueSubmitting(false)
                            })
                    })
                }

                load()
            })
        </script>
    @else
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 mb-1">Receptionist workspace</h1>
            <p class="text-sm text-slate-500">Front desk tools for queue, registrations, appointments, and billing.</p>
        </div>

        @if ($sectionKey === 'queue-management')
            @include('dashviews.receptionist.reception_queue_management')
        @elseif ($sectionKey === 'register-patient')
            @include('dashviews.receptionist.reception_register_patient')
        @elseif ($sectionKey === 'book-appointment')
            @include('dashviews.receptionist.reception_book_appointment')
        @elseif ($sectionKey === 'walk-ins')
            @include('dashviews.receptionist.reception_walk_ins')
        @elseif ($sectionKey === 'record-payment')
            @include('dashviews.receptionist.reception_record_payment')
        @elseif ($sectionKey === 'verification-oversight')
            @include('dashviews.receptionist.reception_verification_approvals')
        @elseif ($sectionKey === 'settings-reception')
            @include('dashviews.receptionist.reception_settings')
        @elseif ($sectionKey === 'messages')
            <div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Patient messages</h2>
                        <p class="text-xs text-slate-500">Chat with patients for doctor reassignment and queue updates.</p>
                    </div>
                    <button type="button" id="receptionMessagesRefresh" class="px-3 py-2 rounded-xl bg-slate-900 text-white text-[0.75rem] font-semibold hover:bg-slate-800">Refresh</button>
                </div>

                <div id="receptionMessagesError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

                <form id="receptionMessagesOpenForm" class="grid gap-2 grid-cols-1 md:grid-cols-2 items-end mb-4">
                    <div>
                        <label for="receptionMessagesPatientId" class="block text-[0.7rem] text-slate-600 mb-1">Patient ID</label>
                        <input id="receptionMessagesPatientId" type="number" min="1" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Patient ID">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="w-full md:w-auto px-4 py-2.5 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors">Open chat</button>
                    </div>
                </form>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-1 border border-slate-100 rounded-2xl overflow-hidden">
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-100">
                            <div class="text-xs font-semibold text-slate-700">Conversations</div>
                        </div>
                        <div id="receptionConversationList" class="max-h-[520px] overflow-y-auto scrollbar-hidden bg-white"></div>
                    </div>

                    <div class="lg:col-span-2 border border-slate-100 rounded-2xl overflow-hidden flex flex-col">
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <div id="receptionConversationTitle" class="text-xs font-semibold text-slate-700">Select a conversation</div>
                                <div id="receptionConversationMeta" class="text-[0.7rem] text-slate-500"></div>
                            </div>
                        </div>

                        <div id="receptionMessageList" class="flex-1 bg-white p-4 space-y-2 overflow-y-auto scrollbar-hidden"></div>

                        <form id="receptionSendMessageForm" class="border-t border-slate-100 bg-white p-3 flex gap-2 items-end">
                            <div class="flex-1">
                                <label for="receptionMessageText" class="sr-only">Message</label>
                                <textarea id="receptionMessageText" rows="2" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type a message…" disabled></textarea>
                            </div>
                            <button id="receptionSendMessageBtn" type="submit" class="px-4 py-2.5 rounded-xl bg-slate-900 text-white text-[0.78rem] font-semibold hover:bg-slate-800 disabled:opacity-60 disabled:hover:bg-slate-900" disabled>Send</button>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var errorBox = document.getElementById('receptionMessagesError')
                    var refreshBtn = document.getElementById('receptionMessagesRefresh')
                    var conversationList = document.getElementById('receptionConversationList')
                    var messageList = document.getElementById('receptionMessageList')
                    var titleEl = document.getElementById('receptionConversationTitle')
                    var metaEl = document.getElementById('receptionConversationMeta')
                    var openForm = document.getElementById('receptionMessagesOpenForm')
                    var patientIdInput = document.getElementById('receptionMessagesPatientId')
                    var sendForm = document.getElementById('receptionSendMessageForm')
                    var messageText = document.getElementById('receptionMessageText')
                    var sendBtn = document.getElementById('receptionSendMessageBtn')

                    var conversations = []
                    var selectedConversation = null

                    function showError(message) {
                        if (!errorBox) return
                        errorBox.textContent = message || ''
                        errorBox.classList.toggle('hidden', !message)
                    }

                    function escapeHtml(input) {
                        var s = String(input == null ? '' : input)
                        return s
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#039;')
                    }

                    function nameForUser(user) {
                        if (!user) return ''
                        var parts = [user.firstname, user.middlename, user.lastname].filter(function (v) { return String(v || '').trim() !== '' })
                        var name = parts.join(' ').trim()
                        if (!name) name = 'User #' + (user.user_id || '')
                        return name
                    }

                    function setSelectedConversation(convo) {
                        selectedConversation = convo || null
                        if (!selectedConversation) {
                            if (titleEl) titleEl.textContent = 'Select a conversation'
                            if (metaEl) metaEl.textContent = ''
                            if (messageText) messageText.disabled = true
                            if (sendBtn) sendBtn.disabled = true
                            if (messageList) messageList.innerHTML = ''
                            return
                        }

                        var patientName = nameForUser(selectedConversation.user)
                        var meta = ['Conversation #' + selectedConversation.conversation_id]

                        if (titleEl) titleEl.textContent = patientName
                        if (metaEl) metaEl.textContent = meta.join(' · ')
                        if (messageText) messageText.disabled = false
                        if (sendBtn) sendBtn.disabled = false
                        loadMessages(selectedConversation.conversation_id)
                    }

                    function renderConversations() {
                        if (!conversationList) return
                        if (!conversations.length) {
                            conversationList.innerHTML = '<div class="p-4 text-[0.78rem] text-slate-400">No conversations yet.</div>'
                            return
                        }

                        var html = ''
                        conversations.forEach(function (c) {
                            var patientName = escapeHtml(nameForUser(c.user))
                            var subtitle = ['Conversation #' + c.conversation_id]
                            var isActive = selectedConversation && String(selectedConversation.conversation_id) === String(c.conversation_id)
                            html += '<button type="button" class="w-full text-left px-4 py-3 border-b border-slate-100 hover:bg-slate-50 ' + (isActive ? 'bg-slate-50' : '') + '" data-conversation-id="' + c.conversation_id + '">' +
                                '<div class="flex items-start justify-between gap-3">' +
                                    '<div>' +
                                        '<div class="text-[0.8rem] font-semibold text-slate-800">' + patientName + '</div>' +
                                        '<div class="text-[0.7rem] text-slate-500 mt-0.5">' + escapeHtml(subtitle.join(' · ')) + '</div>' +
                                    '</div>' +
                                    '<div class="text-[0.7rem] text-slate-400">' + (c.messages_count != null ? ('(' + c.messages_count + ')') : '') + '</div>' +
                                '</div>' +
                            '</button>'
                        })
                        conversationList.innerHTML = html

                        var buttons = conversationList.querySelectorAll('button[data-conversation-id]')
                        buttons.forEach(function (btn) {
                            btn.addEventListener('click', function () {
                                var id = this.getAttribute('data-conversation-id')
                                var convo = conversations.find(function (x) { return String(x.conversation_id) === String(id) })
                                setSelectedConversation(convo || null)
                                renderConversations()
                            })
                        })
                    }

                    function loadConversations(selectConversationId) {
                        showError('')
                        if (conversationList) conversationList.innerHTML = '<div class="p-4 text-[0.78rem] text-slate-400">Loading…</div>'

                        apiFetch("{{ url('/api/conversations') }}?per_page=50", { method: 'GET' })
                            .then(function (response) {
                                return response.json().then(function (data) { return { ok: response.ok, data: data } })
                            })
                            .then(function (result) {
                                if (!result.ok) {
                                    showError('Failed to load conversations.')
                                    if (conversationList) conversationList.innerHTML = ''
                                    return
                                }
                                var payload = result.data
                                conversations = Array.isArray(payload.data) ? payload.data : (Array.isArray(payload) ? payload : [])
                                if (selectConversationId) {
                                    var convo = conversations.find(function (x) { return String(x.conversation_id) === String(selectConversationId) })
                                    if (convo) selectedConversation = convo
                                }
                                renderConversations()
                                if (selectedConversation) {
                                    setSelectedConversation(selectedConversation)
                                } else {
                                    setSelectedConversation(null)
                                }
                            })
                            .catch(function () {
                                showError('Network error while loading conversations.')
                                if (conversationList) conversationList.innerHTML = ''
                            })
                    }

                    function loadMessages(conversationId) {
                        if (!messageList || !conversationId) return
                        messageList.innerHTML = '<div class="text-[0.78rem] text-slate-400">Loading messages…</div>'

                        apiFetch("{{ url('/api/conversations') }}/" + encodeURIComponent(conversationId) + "/messages?per_page=100", { method: 'GET' })
                            .then(function (response) {
                                return response.json().then(function (data) { return { ok: response.ok, data: data } })
                            })
                            .then(function (result) {
                                if (!result.ok) {
                                    messageList.innerHTML = '<div class="text-[0.78rem] text-red-500">Failed to load messages.</div>'
                                    return
                                }
                                var payload = result.data
                                var items = Array.isArray(payload.data) ? payload.data : (Array.isArray(payload) ? payload : [])
                                items = items.slice().reverse()
                                if (!items.length) {
                                    messageList.innerHTML = '<div class="text-[0.78rem] text-slate-400">No messages yet.</div>'
                                    return
                                }

                                var html = ''
                                items.forEach(function (m) {
                                    var isPatient = m.sender === 'user'
                                    var bubbleClass = isPatient ? 'bg-slate-100 text-slate-800' : 'bg-cyan-600 text-white'
                                    var alignClass = isPatient ? 'justify-start' : 'justify-end'
                                    var senderName = isPatient ? 'Patient' : 'Receptionist/System'
                                    html += '<div class="flex ' + alignClass + '">' +
                                        '<div class="max-w-[85%] rounded-2xl px-3 py-2 ' + bubbleClass + '">' +
                                            '<div class="text-[0.68rem] opacity-80 mb-1">' + escapeHtml(senderName) + '</div>' +
                                            '<div class="text-[0.8rem] whitespace-pre-wrap break-words">' + escapeHtml(m.message_text || '') + '</div>' +
                                        '</div>' +
                                    '</div>'
                                })
                                messageList.innerHTML = html
                                messageList.scrollTop = messageList.scrollHeight
                            })
                            .catch(function () {
                                messageList.innerHTML = '<div class="text-[0.78rem] text-red-500">Network error while loading messages.</div>'
                            })
                    }

                    if (refreshBtn) {
                        refreshBtn.addEventListener('click', function () {
                            loadConversations(selectedConversation ? selectedConversation.conversation_id : null)
                        })
                    }

                    if (openForm) {
                        openForm.addEventListener('submit', function (e) {
                            e.preventDefault()
                            showError('')
                            var pid = patientIdInput ? String(patientIdInput.value || '').trim() : ''
                            if (!pid) {
                                showError('Patient ID is required to open a chat.')
                                return
                            }

                            var body = { patient_id: parseInt(pid, 10) }

                            apiFetch("{{ url('/api/conversations') }}", {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(body)
                            })
                                .then(function (response) {
                                    return response.json().then(function (data) { return { ok: response.ok, data: data } })
                                })
                                .then(function (result) {
                                    if (!result.ok) {
                                        showError('Failed to open conversation.')
                                        return
                                    }
                                    var convo = result.data
                                    loadConversations(convo && convo.conversation_id ? convo.conversation_id : null)
                                })
                                .catch(function () {
                                    showError('Network error while opening conversation.')
                                })
                        })
                    }

                    if (sendForm) {
                        sendForm.addEventListener('submit', function (e) {
                            e.preventDefault()
                            showError('')
                            if (!selectedConversation) return
                            var text = messageText ? String(messageText.value || '').trim() : ''
                            if (!text) return

                            if (sendBtn) sendBtn.disabled = true

                            apiFetch("{{ url('/api/conversations') }}/" + encodeURIComponent(selectedConversation.conversation_id) + "/messages", {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ message_text: text })
                            })
                                .then(function (response) {
                                    return response.json().then(function (data) { return { ok: response.ok, data: data } })
                                })
                                .then(function (result) {
                                    if (!result.ok) {
                                        showError('Failed to send message.')
                                        return
                                    }
                                    if (messageText) messageText.value = ''
                                    loadMessages(selectedConversation.conversation_id)
                                    loadConversations(selectedConversation.conversation_id)
                                })
                                .catch(function () {
                                    showError('Network error while sending message.')
                                })
                                .finally(function () {
                                    if (sendBtn) sendBtn.disabled = false
                                })
                        })
                    }

                    loadConversations()
                })
            </script>
        @endif
    @endif
</div>
