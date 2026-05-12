<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">History</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Past records</span>
    </div>
    <p class="text-xs text-slate-500 mb-3">
        View past patient visits and clinical records for follow-up and review.
    </p>

    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
        <div class="flex-1">
            <label for="doctor_visit_search" class="block text-[0.7rem] text-slate-600 mb-1">Search visits</label>
            <input id="doctor_visit_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Patient name, ID or diagnosis">
        </div>
        <div class="w-full md:w-auto">
            <label class="block text-[0.7rem] text-slate-600 mb-1">Quick filter</label>
            <button id="doctor_visit_today_toggle" type="button" class="w-full md:w-auto inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                Today
            </button>
        </div>
        <div class="w-full md:w-40">
            <label for="doctor_visit_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
            <select id="doctor_visit_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="date_desc">Newest first</option>
                <option value="date_asc">Oldest first</option>
                <option value="patient_asc">Patient A–Z</option>
                <option value="patient_desc">Patient Z–A</option>
            </select>
        </div>
    </div>

   <div class="overflow-x-auto overflow-y-auto scrollbar-hidden h-[300px]">
        <table class="min-w-full text-left text-xs text-slate-600">
            <thead>
                <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                    <th class="py-2 pr-4 font-semibold">Visit ID</th>
                    <th class="py-2 pr-4 font-semibold">Patient</th>
                    <th class="py-2 pr-4 font-semibold">Visit date</th>
                    <th class="py-2 pr-4 font-semibold">Reason</th>
                    <th class="py-2 pr-4 font-semibold">Diagnosis</th>
                    <th class="py-2 pr-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($doctorRecentVisits ?? [] as $visit)
                    @php
                        $patientParts = array_filter([
                            optional(optional($visit->appointment)->patient)->firstname,
                            optional(optional($visit->appointment)->patient)->middlename,
                            optional(optional($visit->appointment)->patient)->lastname,
                        ], function ($v) {
                            return (string) $v !== '';
                        });
                        $patientName = trim(implode(' ', $patientParts));
                        $dateKey = optional($visit->visit_datetime)->format('Y-m-d') ?? (optional($visit->transaction_datetime)->format('Y-m-d') ?? '');
                        $patientId = (int) (optional($visit->appointment)->patient_id ?? 0);
                    @endphp
                    <tr class="border-b border-slate-50 last:border-0 doctor-visit-row"
                        data-visit-id="{{ $visit->transaction_id }}"
                        data-patient-id="{{ $patientId }}"
                        data-patient="{{ strtolower($patientName) }}"
                        data-date="{{ $dateKey }}"
                        data-diagnosis="{{ strtolower($visit->diagnosis ?? '') }}">
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">#{{ $visit->transaction_id }}</td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                            @if ($patientName)
                                {{ $patientName }}
                            @else
                                <span class="text-slate-400">Patient #{{ optional($visit->appointment)->patient_id }}</span>
                            @endif
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            {{ $dateKey }}
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            {{ \Illuminate\Support\Str::limit(optional($visit->appointment)->reason_for_visit ?? '—', 50) }}
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            @if ($visit->diagnosis)
                                {{ \Illuminate\Support\Str::limit($visit->diagnosis, 80) }}
                            @else
                                <span class="text-[0.7rem] text-slate-400">No diagnosis recorded</span>
                            @endif
                        </td>
                        <td class="py-2 pr-4 text-right">
                            @if ($patientId > 0)
                                <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-[0.72rem] font-medium text-slate-700 hover:bg-slate-50 doctor-visit-view">
                                    <x-lucide-panel-right-open class="w-3.5 h-3.5" />
                                    View information
                                </button>
                            @else
                                <span class="text-[0.7rem] text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-[0.78rem] text-slate-400">
                            No visits found yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="doctorVisitInfoOverlay" class="hidden fixed inset-0 z-[70] bg-slate-900/40"></div>
<aside id="doctorVisitInfoPanel" class="fixed top-0 right-0 z-[75] h-full w-full max-w-2xl translate-x-full border-l border-slate-200 bg-white shadow-2xl transition-transform duration-300 ease-out">
    <div class="flex h-full flex-col">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-[0.7rem] uppercase tracking-widest text-slate-400">Patient Information</div>
                    <h3 id="doctorVisitPanelTitle" class="mt-1 text-base font-semibold text-slate-900">Patient details</h3>
                    <p id="doctorVisitPanelSubtitle" class="mt-1 text-xs text-slate-500">Select a visit to review the patient profile and history.</p>
                </div>
                <button id="doctorVisitPanelClose" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>
        </div>

        <div id="doctorVisitPanelLoading" class="hidden mx-5 mt-4 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.78rem] text-slate-600">
            Loading patient information...
        </div>
        <div id="doctorVisitPanelError" class="hidden mx-5 mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.78rem] text-red-700"></div>

        <div class="flex-1 overflow-y-auto px-5 py-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="text-[0.68rem] uppercase tracking-widest text-slate-400">Patient</div>
                    <div id="doctorVisitPatientName" class="mt-1 text-sm font-semibold text-slate-900">—</div>
                    <div id="doctorVisitPatientMeta" class="mt-1 text-[0.75rem] text-slate-500">—</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="text-[0.68rem] uppercase tracking-widest text-slate-400">Contacts</div>
                    <div id="doctorVisitPatientContacts" class="mt-1 text-[0.75rem] text-slate-600">—</div>
                    <div id="doctorVisitPatientAddress" class="mt-1 text-[0.75rem] text-slate-500">—</div>
                </div>
            </div>

            <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <div class="text-[0.72rem] font-semibold text-slate-800">Medical background</div>
                <div id="doctorVisitMedicalBackground" class="mt-3 flex flex-wrap gap-2"></div>
            </div>

            <div class="mt-4 rounded-2xl border border-slate-200 bg-white">
                <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                    <button type="button" id="doctorVisitTabVisits" class="inline-flex items-center rounded-lg border border-cyan-600 bg-cyan-600 px-3 py-1.5 text-[0.74rem] font-semibold text-white">
                        Visits history
                    </button>
                    <button type="button" id="doctorVisitTabPrescriptions" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.74rem] font-semibold text-slate-700 hover:bg-slate-50">
                        Prescription history
                    </button>
                </div>
                <div id="doctorVisitTabPanelVisits" class="max-h-[26rem] overflow-y-auto px-4 py-3"></div>
                <div id="doctorVisitTabPanelPrescriptions" class="hidden max-h-[26rem] overflow-y-auto px-4 py-3"></div>
            </div>
        </div>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('doctor_visit_search')
        var sortSelect = document.getElementById('doctor_visit_sort')
        var todayToggle = document.getElementById('doctor_visit_today_toggle')
        var rows = Array.prototype.slice.call(document.querySelectorAll('.doctor-visit-row'))
        var viewButtons = Array.prototype.slice.call(document.querySelectorAll('.doctor-visit-view'))
        var overlay = document.getElementById('doctorVisitInfoOverlay')
        var panel = document.getElementById('doctorVisitInfoPanel')
        var closeButton = document.getElementById('doctorVisitPanelClose')
        var loadingBox = document.getElementById('doctorVisitPanelLoading')
        var errorBox = document.getElementById('doctorVisitPanelError')
        var panelTitle = document.getElementById('doctorVisitPanelTitle')
        var panelSubtitle = document.getElementById('doctorVisitPanelSubtitle')
        var patientNameEl = document.getElementById('doctorVisitPatientName')
        var patientMetaEl = document.getElementById('doctorVisitPatientMeta')
        var patientContactsEl = document.getElementById('doctorVisitPatientContacts')
        var patientAddressEl = document.getElementById('doctorVisitPatientAddress')
        var backgroundEl = document.getElementById('doctorVisitMedicalBackground')
        var visitsTab = document.getElementById('doctorVisitTabVisits')
        var prescriptionsTab = document.getElementById('doctorVisitTabPrescriptions')
        var visitsPanel = document.getElementById('doctorVisitTabPanelVisits')
        var prescriptionsPanel = document.getElementById('doctorVisitTabPanelPrescriptions')
        var todayOnly = false
        var activeTab = 'visits'

        function setVisible(el, visible) {
            if (!el) return
            el.classList.toggle('hidden', !visible)
        }

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
        }

        function api(url) {
            if (typeof apiFetch !== 'function') {
                return Promise.reject(new Error('API client is not available.'))
            }

            return apiFetch(url, { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    }).catch(function () {
                        return { ok: response.ok, data: null }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        throw new Error(result.data && result.data.message ? result.data.message : 'Request failed.')
                    }
                    return result.data
                })
        }

        function paginatedRows(payload) {
            if (!payload) return []
            if (Array.isArray(payload.data)) return payload.data
            if (Array.isArray(payload)) return payload
            return []
        }

        function renderBadge(label, tone) {
            var base = 'inline-flex items-center rounded-full border px-2 py-0.5 text-[0.68rem] font-medium '
            if (tone === 'danger') return '<span class="' + base + 'border-red-200 bg-red-50 text-red-700">' + escapeHtml(label) + '</span>'
            if (tone === 'warn') return '<span class="' + base + 'border-amber-200 bg-amber-50 text-amber-800">' + escapeHtml(label) + '</span>'
            return '<span class="' + base + 'border-slate-200 bg-slate-50 text-slate-700">' + escapeHtml(label) + '</span>'
        }

        function setTab(tabName) {
            activeTab = tabName === 'prescriptions' ? 'prescriptions' : 'visits'
            if (visitsTab) {
                visitsTab.classList.toggle('bg-cyan-600', activeTab === 'visits')
                visitsTab.classList.toggle('text-white', activeTab === 'visits')
                visitsTab.classList.toggle('border-cyan-600', activeTab === 'visits')
                visitsTab.classList.toggle('bg-white', activeTab !== 'visits')
                visitsTab.classList.toggle('text-slate-700', activeTab !== 'visits')
                visitsTab.classList.toggle('border-slate-200', activeTab !== 'visits')
            }
            if (prescriptionsTab) {
                prescriptionsTab.classList.toggle('bg-cyan-600', activeTab === 'prescriptions')
                prescriptionsTab.classList.toggle('text-white', activeTab === 'prescriptions')
                prescriptionsTab.classList.toggle('border-cyan-600', activeTab === 'prescriptions')
                prescriptionsTab.classList.toggle('bg-white', activeTab !== 'prescriptions')
                prescriptionsTab.classList.toggle('text-slate-700', activeTab !== 'prescriptions')
                prescriptionsTab.classList.toggle('border-slate-200', activeTab !== 'prescriptions')
            }
            setVisible(visitsPanel, activeTab === 'visits')
            setVisible(prescriptionsPanel, activeTab === 'prescriptions')
        }

        function openPanel() {
            if (overlay) overlay.classList.remove('hidden')
            if (panel) panel.classList.remove('translate-x-full')
        }

        function closePanel() {
            if (overlay) overlay.classList.add('hidden')
            if (panel) panel.classList.add('translate-x-full')
        }

        function renderMedicalBackground(rows) {
            if (!backgroundEl) return
            if (!rows.length) {
                backgroundEl.innerHTML = '<span class="text-[0.74rem] text-slate-400">No medical background recorded.</span>'
                return
            }

            backgroundEl.innerHTML = rows.map(function (item) {
                var category = String(item.category || '')
                var tone = category === 'allergy_drug' ? 'danger' : (category === 'allergy_food' ? 'warn' : 'default')
                return renderBadge(item.name || '—', tone)
            }).join(' ')
        }

        function renderVisitsHistory(rows) {
            if (!visitsPanel) return
            if (!rows.length) {
                visitsPanel.innerHTML = '<div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3 text-[0.78rem] text-slate-500">No visit history found.</div>'
                return
            }

            visitsPanel.innerHTML = rows.map(function (visit) {
                var when = String(visit.visit_datetime || visit.transaction_datetime || '').replace('T', ' ').slice(0, 16) || '—'
                var reason = visit.appointment && visit.appointment.reason_for_visit ? visit.appointment.reason_for_visit : 'No reason recorded'
                var diagnosis = visit.diagnosis || 'No diagnosis recorded'
                var appointmentType = visit.appointment && visit.appointment.appointment_type
                    ? String(visit.appointment.appointment_type).replace(/_/g, '-')
                    : '—'

                return '' +
                    '<div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3 mb-3">' +
                        '<div class="flex items-start justify-between gap-3">' +
                            '<div>' +
                                '<div class="text-[0.72rem] font-semibold text-slate-800">' + escapeHtml(when) + '</div>' +
                                '<div class="mt-1 text-[0.72rem] text-slate-500">Type: ' + escapeHtml(appointmentType) + '</div>' +
                            '</div>' +
                            '<div class="text-[0.72rem] text-slate-400">#' + escapeHtml(visit.transaction_id || '') + '</div>' +
                        '</div>' +
                        '<div class="mt-3 text-[0.76rem] text-slate-700"><span class="font-semibold">Reason:</span> ' + escapeHtml(reason) + '</div>' +
                        '<div class="mt-1 text-[0.76rem] text-slate-700"><span class="font-semibold">Diagnosis:</span> ' + escapeHtml(diagnosis) + '</div>' +
                    '</div>'
            }).join('')
        }

        function renderPrescriptionHistory(visits) {
            if (!prescriptionsPanel) return

            var entries = []
            visits.forEach(function (visit) {
                var prescriptions = Array.isArray(visit.prescriptions) ? visit.prescriptions : []
                prescriptions.forEach(function (prescription) {
                    entries.push({
                        prescribed_datetime: prescription.prescribed_datetime || visit.visit_datetime || visit.transaction_datetime || '',
                        doctor: prescription.doctor,
                        notes: prescription.notes,
                        items: Array.isArray(prescription.items) ? prescription.items : [],
                    })
                })
            })

            if (!entries.length) {
                prescriptionsPanel.innerHTML = '<div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3 text-[0.78rem] text-slate-500">No prescription history found.</div>'
                return
            }

            prescriptionsPanel.innerHTML = entries.map(function (entry) {
                var when = String(entry.prescribed_datetime || '').replace('T', ' ').slice(0, 16) || '—'
                var doctorName = entry.doctor
                    ? [entry.doctor.firstname, entry.doctor.middlename, entry.doctor.lastname].filter(Boolean).join(' ').trim()
                    : 'Doctor'
                var itemsHtml = entry.items.length
                    ? '<ul class="mt-2 space-y-1 text-[0.74rem] text-slate-700">' + entry.items.map(function (item) {
                        var medicineName = item.medicine
                            ? [item.medicine.generic_name, item.medicine.brand_name ? '(' + item.medicine.brand_name + ')' : ''].filter(Boolean).join(' ').trim()
                            : (item.medicine_name || 'Medicine')
                        var line = medicineName
                        if (item.dosage) line += ' • ' + item.dosage
                        if (item.frequency) line += ' • ' + item.frequency
                        if (item.duration) line += ' • ' + item.duration
                        return '<li>' + escapeHtml(line) + '</li>'
                    }).join('') + '</ul>'
                    : '<div class="mt-2 text-[0.74rem] text-slate-400">No items recorded.</div>'

                return '' +
                    '<div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3 mb-3">' +
                        '<div class="flex items-start justify-between gap-3">' +
                            '<div>' +
                                '<div class="text-[0.72rem] font-semibold text-slate-800">' + escapeHtml(when) + '</div>' +
                                '<div class="mt-1 text-[0.72rem] text-slate-500">Prescribed by ' + escapeHtml(doctorName || 'Doctor') + '</div>' +
                            '</div>' +
                        '</div>' +
                        (entry.notes ? '<div class="mt-2 text-[0.74rem] text-slate-700"><span class="font-semibold">Notes:</span> ' + escapeHtml(entry.notes) + '</div>' : '') +
                        itemsHtml +
                    '</div>'
            }).join('')
        }

        function renderPatientSummary(visit, backgrounds, visits) {
            var patient = visit && visit.appointment ? visit.appointment.patient : null
            var patientName = patient
                ? [patient.firstname, patient.middlename, patient.lastname].filter(Boolean).join(' ').trim()
                : 'Patient'
            var birthdate = patient && patient.birthdate ? new Date(patient.birthdate) : null
            var age = ''
            if (birthdate && !isNaN(birthdate.getTime())) {
                var now = new Date()
                age = now.getFullYear() - birthdate.getFullYear()
                var monthDiff = now.getMonth() - birthdate.getMonth()
                if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < birthdate.getDate())) age--
                age = age >= 0 ? String(age) + ' yrs' : ''
            }

            if (panelTitle) panelTitle.textContent = patientName || 'Patient details'
            if (panelSubtitle) panelSubtitle.textContent = 'Review patient information, medical background, and prescription records.'
            if (patientNameEl) patientNameEl.textContent = patientName || 'Patient'
            if (patientMetaEl) {
                var parts = []
                if (patient && patient.sex) parts.push(patient.sex)
                if (age) parts.push(age)
                parts.push(patient && patient.is_dependent ? 'Dependent' : 'Regular')
                if (visit && visit.appointment && visit.appointment.appointment_type) {
                    parts.push(String(visit.appointment.appointment_type).replace(/_/g, '-'))
                }
                patientMetaEl.textContent = parts.join(' • ') || '—'
            }
            if (patientContactsEl) {
                patientContactsEl.textContent = [patient && patient.contact_number ? patient.contact_number : '', patient && patient.email ? patient.email : '']
                    .filter(Boolean)
                    .join(' • ') || '—'
            }
            if (patientAddressEl) {
                patientAddressEl.textContent = patient && patient.address ? patient.address : '—'
            }

            renderMedicalBackground(backgrounds)
            renderVisitsHistory(visits)
            renderPrescriptionHistory(visits)
        }

        function loadVisitInformation(visitId, patientId) {
            setVisible(errorBox, false)
            setVisible(loadingBox, true)
            openPanel()

            Promise.all([
                api("{{ url('/api/visits') }}/" + encodeURIComponent(visitId)),
                api("{{ url('/api/medical-backgrounds') }}?patient_id=" + encodeURIComponent(patientId) + '&per_page=100'),
                api("{{ url('/api/visits') }}?patient_id=" + encodeURIComponent(patientId) + '&per_page=50'),
            ])
                .then(function (results) {
                    var visit = results[0]
                    var backgrounds = paginatedRows(results[1])
                    var visits = paginatedRows(results[2])
                    renderPatientSummary(visit, backgrounds, visits)
                    setTab(activeTab)
                })
                .catch(function (error) {
                    if (errorBox) {
                        errorBox.textContent = error && error.message ? error.message : 'Failed to load patient information.'
                    }
                    setVisible(errorBox, true)
                })
                .finally(function () {
                    setVisible(loadingBox, false)
                })
        }

        function localDateIso() {
            var now = new Date()
            var y = now.getFullYear()
            var m = String(now.getMonth() + 1).padStart(2, '0')
            var d = String(now.getDate()).padStart(2, '0')
            return y + '-' + m + '-' + d
        }

        function applyTodayToggleUi() {
            if (!todayToggle) return
            todayToggle.classList.toggle('bg-cyan-600', todayOnly)
            todayToggle.classList.toggle('text-white', todayOnly)
            todayToggle.classList.toggle('border-cyan-600', todayOnly)
            todayToggle.classList.toggle('hover:bg-cyan-700', todayOnly)
            todayToggle.classList.toggle('bg-white', !todayOnly)
            todayToggle.classList.toggle('text-slate-700', !todayOnly)
            todayToggle.classList.toggle('border-slate-200', !todayOnly)
            todayToggle.classList.toggle('hover:bg-slate-50', !todayOnly)
        }

        function applyDoctorVisitFilters() {
            var query = searchInput ? searchInput.value.toLowerCase().trim() : ''
            var todayKey = localDateIso()

            rows.forEach(function (row) {
                var id = row.getAttribute('data-visit-id') || ''
                var patient = row.getAttribute('data-patient') || ''
                var diagnosis = row.getAttribute('data-diagnosis') || ''
                var date = row.getAttribute('data-date') || ''

                var matches = true
                if (query) {
                    matches =
                        ('#' + id).indexOf(query) !== -1 ||
                        patient.indexOf(query) !== -1 ||
                        diagnosis.indexOf(query) !== -1
                }

                if (matches && todayOnly) {
                    matches = date === todayKey
                }

                row.style.display = matches ? '' : 'none'
            })

            applyDoctorVisitSort()
        }

        function applyDoctorVisitSort() {
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
                var pa = a.getAttribute('data-patient') || ''
                var pb = b.getAttribute('data-patient') || ''
                var da = a.getAttribute('data-date') || ''
                var db = b.getAttribute('data-date') || ''

                if (value === 'patient_asc' || value === 'patient_desc') {
                    if (pa < pb) return value === 'patient_asc' ? -1 : 1
                    if (pa > pb) return value === 'patient_asc' ? 1 : -1
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

        if (searchInput) {
            searchInput.addEventListener('input', applyDoctorVisitFilters)
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', applyDoctorVisitSort)
        }
        if (todayToggle) {
            todayToggle.addEventListener('click', function () {
                todayOnly = !todayOnly
                applyTodayToggleUi()
                applyDoctorVisitFilters()
            })
        }
        if (visitsTab) {
            visitsTab.addEventListener('click', function () {
                setTab('visits')
            })
        }
        if (prescriptionsTab) {
            prescriptionsTab.addEventListener('click', function () {
                setTab('prescriptions')
            })
        }
        if (closeButton) {
            closeButton.addEventListener('click', closePanel)
        }
        if (overlay) {
            overlay.addEventListener('click', closePanel)
        }
        viewButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var row = button.closest('.doctor-visit-row')
                if (!row) return
                var visitId = row.getAttribute('data-visit-id')
                var patientId = row.getAttribute('data-patient-id')
                if (!visitId || !patientId) return
                loadVisitInformation(visitId, patientId)
            })
        })

        setTab('visits')
        applyTodayToggleUi()
        applyDoctorVisitFilters()
    })
</script>
