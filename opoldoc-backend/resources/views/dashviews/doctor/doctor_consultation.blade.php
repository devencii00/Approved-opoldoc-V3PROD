@php
    $appointments = $doctorTodayAppointments ?? $doctorRecentAppointments ?? [];
    $initialAppointmentId = request()->query('appointment_id');

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
@endphp

<div class="space-y-4">
    <div>
        <h2 class="text-sm font-semibold text-slate-900">Consultation Workspace</h2>
        <p class="text-xs text-slate-500">Select today’s appointment, review the patient snapshot, and record visit notes + prescriptions.</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-12">
        <div class="lg:col-span-3 bg-white border border-slate-200 rounded-[18px] p-4 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
            <div class="mb-3">
                <label for="consult_appointment" class="block text-[0.7rem] text-slate-600 mb-1">Appointment</label>
                <select id="consult_appointment" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="">Select today’s appointment</option>
                    @foreach ($appointments as $appointment)
                        @php
                            $patientName = $formatUserName($appointment->patient);
                            $labelDate = optional($appointment->appointment_datetime)->format('Y-m-d') ?? '—';
                            $labelTime = optional($appointment->appointment_datetime)->format('H:i') ?? '—';
                        @endphp
                        <option value="{{ $appointment->appointment_id }}" {{ (string) $appointment->appointment_id === (string) $initialAppointmentId ? 'selected' : '' }}>
                            {{ $patientName }} — {{ $labelDate }} {{ $labelTime }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="consultSnapshotLoading" class="hidden mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.75rem] text-slate-600">Loading patient snapshot…</div>
            <div id="consultSnapshotError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

            <div class="border border-slate-100 rounded-xl bg-slate-50 p-3 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="text-[0.7rem] text-slate-400">Patient</div>
                        <div id="consultPatientName" class="text-[0.95rem] font-semibold text-slate-900">—</div>
                        <div id="consultPatientMeta" class="text-[0.72rem] text-slate-500">—</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[0.7rem] text-slate-400">Appointment</div>
                        <div id="consultApptDateTime" class="text-[0.75rem] font-semibold text-slate-700">—</div>
                        <div id="consultApptType" class="text-[0.72rem] text-slate-500">—</div>
                    </div>
                </div>

                <div>
                    <div class="text-[0.7rem] font-semibold text-slate-700 mb-1">Contacts</div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-[0.72rem] text-slate-600">
                        <div>
                            <dt class="text-slate-400">Phone</dt>
                            <dd id="consultPatientPhone" class="font-medium text-slate-800">—</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Email</dt>
                            <dd id="consultPatientEmail" class="font-medium text-slate-800">—</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-slate-400">Address</dt>
                            <dd id="consultPatientAddress" class="font-medium text-slate-800">—</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <div class="text-[0.7rem] font-semibold text-slate-700 mb-1">Dependent</div>
                    <div id="consultDependentBox" class="rounded-lg border border-slate-100 bg-white px-2.5 py-2 text-[0.72rem] text-slate-600">
                        <div id="consultDependentStatus" class="text-slate-500">—</div>
                        <div id="consultParentName" class="font-semibold text-slate-800"></div>
                        <div id="consultParentMeta" class="text-slate-500"></div>
                    </div>
                </div>

                <div>
                    <div class="text-[0.7rem] font-semibold text-slate-700 mb-1">Medical Background</div>
                    <div class="space-y-2">
                        <div>
                            <div class="text-[0.68rem] text-slate-400 mb-1">Drug allergies</div>
                            <div id="consultAllergyDrug" class="flex flex-wrap gap-1"></div>
                        </div>
                        <div>
                            <div class="text-[0.68rem] text-slate-400 mb-1">Food allergies</div>
                            <div id="consultAllergyFood" class="flex flex-wrap gap-1"></div>
                        </div>
                        <div>
                            <div class="text-[0.68rem] text-slate-400 mb-1">Chronic conditions</div>
                            <div id="consultConditions" class="flex flex-wrap gap-1"></div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-lg border border-slate-100 bg-white px-2.5 py-2">
                        <div class="text-[0.68rem] text-slate-400">Last visit</div>
                        <div id="consultLastVisit" class="text-[0.75rem] font-semibold text-slate-800">—</div>
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-white px-2.5 py-2">
                        <div class="text-[0.68rem] text-slate-400">Total visits</div>
                        <div id="consultTotalVisits" class="text-[0.75rem] font-semibold text-slate-800">—</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-6 bg-white border border-slate-200 rounded-[18px] p-4 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Consult + Prescription</h3>
                    <p class="text-xs text-slate-500">Save diagnosis and treatment notes to the visit record, then issue medicines.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="consultClear" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">
                        Clear
                    </button>
                    <button type="button" id="consultPrintPrescription" class="hidden inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">
                        Print receipt
                    </button>
                    <button type="button" id="consultSave" class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-3 py-1.5 text-[0.78rem] font-semibold text-white hover:bg-cyan-700">
                        Save consultation
                    </button>
                </div>
            </div>

            <div id="consultSaveError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
            <div id="consultSaveSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>
            <div id="consultSafetyBox" class="hidden mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[0.75rem] text-amber-800 whitespace-pre-line"></div>

            <div class="grid gap-3 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="consult_diagnosis" class="block text-[0.7rem] text-slate-600 mb-1">Diagnosis</label>
                    <textarea id="consult_diagnosis" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none min-h-[90px]" placeholder="Enter clinical diagnosis"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="consult_treatment" class="block text-[0.7rem] text-slate-600 mb-1">Treatment notes</label>
                    <textarea id="consult_treatment" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none min-h-[120px]" placeholder="Enter treatment plan, follow-up instructions, and other notes"></textarea>
                </div>
                <div class="md:col-span-2 flex items-center justify-between gap-2 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                    <label class="inline-flex items-center gap-2 text-[0.78rem] text-slate-700">
                        <input type="checkbox" id="consultMarkCompleted" checked class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-200">
                        Mark appointment completed
                    </label>
                    <label class="inline-flex items-center gap-2 text-[0.78rem] text-slate-700">
                        <input type="checkbox" id="consultAcknowledgeConflicts" class="rounded border-slate-300 text-amber-600 focus:ring-amber-200">
                        Override safety warnings
                    </label>
                </div>
            </div>

            <div class="mt-4 border-t border-slate-100 pt-4">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div>
                        <h4 class="text-xs font-semibold text-slate-900">Prescription items</h4>
                        <p class="text-[0.72rem] text-slate-500">Add medicines, then save to issue the prescription.</p>
                    </div>
                    <button type="button" id="consultAddMedicine" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">
                        + Add medicine
                    </button>
                </div>

                <div class="overflow-x-auto scrollbar-hidden border border-slate-100 rounded-xl">
                    <table class="min-w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50">
                            <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                                <th class="py-2 px-3 font-semibold">Medicine</th>
                                <th class="py-2 px-3 font-semibold">Dosage</th>
                                <th class="py-2 px-3 font-semibold">Frequency</th>
                                <th class="py-2 px-3 font-semibold">Duration</th>
                                <th class="py-2 px-3 font-semibold">Instructions</th>
                                <th class="py-2 px-3 font-semibold">Remove</th>
                            </tr>
                        </thead>
                        <tbody id="consultPrescriptionBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3 bg-white border border-slate-200 rounded-[18px] p-4 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Patient History</h3>
                    <p class="text-xs text-slate-500">Recent visits for quick context.</p>
                </div>
                <select id="consultHistoryFilter" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-[0.75rem] text-slate-700 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="all">All</option>
                    <option value="with_rx">With prescriptions</option>
                </select>
            </div>

            <div id="consultHistoryLoading" class="hidden mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.75rem] text-slate-600">Loading history…</div>
            <div id="consultHistoryError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

            <div id="consultHistoryTimeline" class="space-y-2 max-h-[38rem] overflow-y-auto pr-1 scrollbar-hidden"></div>
        </div>
    </div>
</div>

<div id="consultSafetyModal" class="hidden fixed inset-0 z-50 bg-slate-900/70">
    <div class="absolute inset-0 flex items-center justify-center px-4 py-6">
        <div class="w-full max-w-lg rounded-3xl bg-white border border-slate-200 shadow-[0_20px_60px_rgba(15,23,42,0.35)] overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3">
                <div>
                    <div class="text-[0.7rem] uppercase tracking-widest text-slate-400">Safety Warning</div>
                    <div class="text-sm font-semibold text-slate-900">Possible allergy conflict detected</div>
                    <div class="text-xs text-slate-500 mt-1">Review before continuing. Override is required to save if conflicts remain.</div>
                </div>
                <button type="button" id="consultSafetyModalClose" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">
                    Close
                </button>
            </div>
            <div class="px-5 py-4">
                <div id="consultSafetyModalBody" class="text-[0.8rem] text-slate-700 whitespace-pre-line"></div>
                <div class="mt-4 flex items-center justify-between gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                    <div class="text-[0.78rem] text-amber-900">
                        Check <span class="font-semibold">Override safety warnings</span> to proceed with saving.
                    </div>
                    <button type="button" id="consultSafetyModalAcknowledge" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-3 py-1.5 text-[0.78rem] font-semibold text-white hover:bg-amber-700">
                        Override
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var appointmentSelect = document.getElementById('consult_appointment')
        var snapshotLoading = document.getElementById('consultSnapshotLoading')
        var snapshotError = document.getElementById('consultSnapshotError')
        var saveError = document.getElementById('consultSaveError')
        var saveSuccess = document.getElementById('consultSaveSuccess')
        var safetyBox = document.getElementById('consultSafetyBox')
        var diagnosisEl = document.getElementById('consult_diagnosis')
        var treatmentEl = document.getElementById('consult_treatment')
        var clearBtn = document.getElementById('consultClear')
        var saveBtn = document.getElementById('consultSave')
        var printBtn = document.getElementById('consultPrintPrescription')
        var addMedBtn = document.getElementById('consultAddMedicine')
        var prescriptionBody = document.getElementById('consultPrescriptionBody')
        var markCompletedEl = document.getElementById('consultMarkCompleted')
        var acknowledgeEl = document.getElementById('consultAcknowledgeConflicts')
        var safetyModal = document.getElementById('consultSafetyModal')
        var safetyModalBody = document.getElementById('consultSafetyModalBody')
        var safetyModalClose = document.getElementById('consultSafetyModalClose')
        var safetyModalAck = document.getElementById('consultSafetyModalAcknowledge')
        var historyFilter = document.getElementById('consultHistoryFilter')
        var historyLoading = document.getElementById('consultHistoryLoading')
        var historyError = document.getElementById('consultHistoryError')
        var historyTimeline = document.getElementById('consultHistoryTimeline')

        var elPatientName = document.getElementById('consultPatientName')
        var elPatientMeta = document.getElementById('consultPatientMeta')
        var elApptDateTime = document.getElementById('consultApptDateTime')
        var elApptType = document.getElementById('consultApptType')
        var elPhone = document.getElementById('consultPatientPhone')
        var elEmail = document.getElementById('consultPatientEmail')
        var elAddress = document.getElementById('consultPatientAddress')
        var elDepStatus = document.getElementById('consultDependentStatus')
        var elParentName = document.getElementById('consultParentName')
        var elParentMeta = document.getElementById('consultParentMeta')
        var elAllergyDrug = document.getElementById('consultAllergyDrug')
        var elAllergyFood = document.getElementById('consultAllergyFood')
        var elConditions = document.getElementById('consultConditions')
        var elLastVisit = document.getElementById('consultLastVisit')
        var elTotalVisits = document.getElementById('consultTotalVisits')

        var state = {
            doctorUserId: null,
            appointmentId: null,
            patientId: null,
            parentUserId: null,
            transactionId: null,
            prescriptionId: null,
            existingItemIds: [],
            medicalBackground: [],
            medicines: [],
            medicinesById: {},
            history: [],
        }

        function setVisible(el, visible) {
            if (!el) return
            if (visible) el.classList.remove('hidden')
            else el.classList.add('hidden')
        }

        function setText(el, text) {
            if (!el) return
            el.textContent = text || '—'
        }

        function setHtml(el, html) {
            if (!el) return
            el.innerHTML = html || ''
        }

        function badge(label, variant) {
            var cls = 'inline-flex items-center rounded-full border px-2 py-0.5 text-[0.68rem] font-medium '
            if (variant === 'danger') cls += 'bg-red-50 border-red-200 text-red-700'
            else if (variant === 'warn') cls += 'bg-amber-50 border-amber-200 text-amber-800'
            else cls += 'bg-white border-slate-200 text-slate-700'
            return '<span class="' + cls + '">' + (label || '—') + '</span>'
        }

        function showSafetyModal(text) {
            if (safetyModalBody) safetyModalBody.textContent = text || ''
            setVisible(safetyModal, true)
        }

        function hideSafetyModal() {
            setVisible(safetyModal, false)
        }

        function api(url, options) {
            if (!window.apiFetch) {
                return Promise.reject(new Error('API client is not available.'))
            }
            return window.apiFetch(url, options || {})
                .then(function (res) {
                    if (!res.ok) {
                        return res.text().then(function (txt) {
                            var err = new Error('Request failed')
                            err.status = res.status
                            err.body = txt
                            throw err
                        })
                    }
                    return res.json()
                })
        }

        function formatName(user) {
            if (!user) return '—'
            var parts = []
            if (user.firstname) parts.push(user.firstname)
            if (user.middlename) parts.push(user.middlename)
            if (user.lastname) parts.push(user.lastname)
            var name = parts.join(' ').trim()
            return name || ('User #' + (user.user_id || ''))
        }

        function medicineDisplayName(med) {
            if (!med) return '—'
            var generic = med.generic_name || ''
            var brand = med.brand_name || ''
            if (generic && brand) return generic + ' (' + brand + ')'
            return generic || brand || ('Medicine #' + (med.medicine_id || ''))
        }

        function computeAgeFromBirthdate(birthdate) {
            if (!birthdate) return ''
            var d = new Date(birthdate)
            if (isNaN(d.getTime())) return ''
            var now = new Date()
            var years = now.getFullYear() - d.getFullYear()
            var m = now.getMonth() - d.getMonth()
            if (m < 0 || (m === 0 && now.getDate() < d.getDate())) years--
            return years >= 0 ? String(years) : ''
        }

        function normalizeString(v) {
            return (v || '').toString().toLowerCase()
        }

        function getPaginatedData(resp) {
            if (!resp) return []
            if (Array.isArray(resp)) return resp
            if (Array.isArray(resp.data)) return resp.data
            return []
        }

        function resetWorkspace() {
            state.patientId = null
            state.parentUserId = null
            state.transactionId = null
            state.prescriptionId = null
            state.existingItemIds = []
            state.medicalBackground = []
            state.history = []
            setText(elPatientName, '—')
            setText(elPatientMeta, '—')
            setText(elApptDateTime, '—')
            setText(elApptType, '—')
            setText(elPhone, '—')
            setText(elEmail, '—')
            setText(elAddress, '—')
            setText(elDepStatus, '—')
            setText(elParentName, '')
            setText(elParentMeta, '')
            setHtml(elAllergyDrug, '')
            setHtml(elAllergyFood, '')
            setHtml(elConditions, '')
            setText(elLastVisit, '—')
            setText(elTotalVisits, '—')
            if (diagnosisEl) diagnosisEl.value = ''
            if (treatmentEl) treatmentEl.value = ''
            if (prescriptionBody) prescriptionBody.innerHTML = ''
            if (historyTimeline) historyTimeline.innerHTML = ''
            setVisible(snapshotError, false)
            setVisible(saveError, false)
            setVisible(saveSuccess, false)
            setVisible(safetyBox, false)
            if (acknowledgeEl) acknowledgeEl.checked = false
            if (printBtn) printBtn.classList.add('hidden')
        }

        function ensureRow(item) {
            if (!prescriptionBody) return
            var tr = document.createElement('tr')
            tr.className = 'border-b border-slate-50 last:border-0'
            tr.innerHTML = '' +
                '<td class="py-2 px-3 min-w-[14rem]">' +
                    '<select class="consult-med w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none"></select>' +
                    '<div class="mt-1 space-y-1">' +
                        '<div class="text-[0.68rem] text-slate-400">Indications: <span class="consult-ind text-slate-600"></span></div>' +
                        '<div class="text-[0.68rem] text-slate-400">Contra: <span class="consult-contra text-slate-600"></span></div>' +
                    '</div>' +
                '</td>' +
                '<td class="py-2 px-3 min-w-[7rem]"><input class="consult-dose w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="e.g. 500mg"></td>' +
                '<td class="py-2 px-3 min-w-[7rem]"><input class="consult-freq w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="e.g. BID"></td>' +
                '<td class="py-2 px-3 min-w-[7rem]"><input class="consult-dur w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="e.g. 7 days"></td>' +
                '<td class="py-2 px-3 min-w-[10rem]"><input class="consult-inst w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="e.g. after meals"></td>' +
                '<td class="py-2 px-3"><button type="button" class="consult-remove inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-2 py-1 text-[0.72rem] font-semibold text-slate-700 hover:bg-slate-50">✕</button></td>'

            var sel = tr.querySelector('.consult-med')
            var ind = tr.querySelector('.consult-ind')
            var contra = tr.querySelector('.consult-contra')
            var removeBtn = tr.querySelector('.consult-remove')

            var opts = ['<option value="">Select</option>']
            state.medicines.forEach(function (m) {
                opts.push('<option value="' + m.medicine_id + '">' + medicineDisplayName(m) + '</option>')
            })
            sel.innerHTML = opts.join('')

            function updateMeta() {
                var id = sel.value
                var med = state.medicinesById[id]
                ind.textContent = med && med.indications ? med.indications : '—'
                contra.textContent = med && med.contraindications ? med.contraindications : '—'
                renderSafety()

                var conflicts = computeConflicts()
                var hasConflict = conflicts.some(function (c) {
                    return normalizeString(c.medicine) === normalizeString(medicineDisplayName(med))
                })

                if (hasConflict) {
                    sel.classList.add('border-red-300')
                    sel.classList.add('bg-red-50')
                    sel.classList.add('focus:border-red-400')
                    sel.classList.add('focus:ring-red-200')

                    var lines = conflicts
                        .filter(function (c) { return normalizeString(c.medicine) === normalizeString(medicineDisplayName(med)) })
                        .slice(0, 8)
                        .map(function (c) { return '• ' + c.medicine + ' vs allergy "' + c.allergy + '"' })
                        .join('\n')
                    showSafetyModal('Possible allergy conflicts:\n' + lines)
                } else {
                    sel.classList.remove('border-red-300')
                    sel.classList.remove('bg-red-50')
                    sel.classList.remove('focus:border-red-400')
                    sel.classList.remove('focus:ring-red-200')
                }
            }

            sel.addEventListener('change', updateMeta)
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    tr.remove()
                    renderSafety()
                })
            }

            if (item) {
                if (item.medicine_id) sel.value = String(item.medicine_id)
                var dose = tr.querySelector('.consult-dose')
                var freq = tr.querySelector('.consult-freq')
                var dur = tr.querySelector('.consult-dur')
                var inst = tr.querySelector('.consult-inst')
                if (dose && item.dosage) dose.value = item.dosage
                if (freq && item.frequency) freq.value = item.frequency
                if (dur && item.duration) dur.value = item.duration
                if (inst && item.instructions) inst.value = item.instructions
            }

            updateMeta()
            prescriptionBody.appendChild(tr)
        }

        function getPrescriptionRows() {
            if (!prescriptionBody) return []
            var trs = Array.prototype.slice.call(prescriptionBody.querySelectorAll('tr'))
            return trs.map(function (tr) {
                return {
                    tr: tr,
                    medicine_id: tr.querySelector('.consult-med') ? tr.querySelector('.consult-med').value : '',
                    dosage: tr.querySelector('.consult-dose') ? tr.querySelector('.consult-dose').value : '',
                    frequency: tr.querySelector('.consult-freq') ? tr.querySelector('.consult-freq').value : '',
                    duration: tr.querySelector('.consult-dur') ? tr.querySelector('.consult-dur').value : '',
                    instructions: tr.querySelector('.consult-inst') ? tr.querySelector('.consult-inst').value : '',
                }
            }).filter(function (r) {
                return r.medicine_id
            })
        }

        function renderBackground(backgrounds) {
            var drug = []
            var food = []
            var cond = []

            backgrounds.forEach(function (b) {
                var cat = normalizeString(b.category)
                if (cat === 'allergy_drug') drug.push(b)
                else if (cat === 'allergy_food') food.push(b)
                else if (cat === 'condition') cond.push(b)
            })

            setHtml(elAllergyDrug, drug.length ? drug.map(function (b) { return badge(b.name || '—', 'danger') }).join(' ') : '<span class="text-[0.72rem] text-slate-400">None recorded</span>')
            setHtml(elAllergyFood, food.length ? food.map(function (b) { return badge(b.name || '—', 'warn') }).join(' ') : '<span class="text-[0.72rem] text-slate-400">None recorded</span>')
            setHtml(elConditions, cond.length ? cond.map(function (b) { return badge(b.name || '—', 'default') }).join(' ') : '<span class="text-[0.72rem] text-slate-400">None recorded</span>')
        }

        function renderHistory() {
            if (!historyTimeline) return
            var filter = historyFilter ? historyFilter.value : 'all'
            var items = state.history.slice()
            if (filter === 'with_rx') {
                items = items.filter(function (tx) {
                    return tx.prescriptions && tx.prescriptions.length
                })
            }

            if (!items.length) {
                historyTimeline.innerHTML = '<div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3 text-[0.78rem] text-slate-500">No visit history found.</div>'
                return
            }

            historyTimeline.innerHTML = items.map(function (tx) {
                var dt = tx.visit_datetime || tx.transaction_datetime || ''
                var dateStr = dt ? dt.toString().slice(0, 10) : '—'
                var timeStr = dt ? dt.toString().slice(11, 16) : ''
                var dx = tx.diagnosis ? tx.diagnosis : 'No diagnosis'
                var notes = tx.treatment_notes ? tx.treatment_notes : ''
                var rx = tx.prescriptions || []
                var rxLines = []
                rx.forEach(function (p) {
                    var items = p.items || []
                    items.forEach(function (it) {
                        var medName = it.medicine ? medicineDisplayName(it.medicine) : ('Medicine #' + it.medicine_id)
                        var line = medName
                        if (it.dosage) line += ' • ' + it.dosage
                        if (it.frequency) line += ' • ' + it.frequency
                        if (it.duration) line += ' • ' + it.duration
                        rxLines.push(line)
                    })
                })
                var rxHtml = rxLines.length
                    ? '<ul class="mt-2 space-y-1 text-[0.72rem] text-slate-600">' + rxLines.slice(0, 6).map(function (l) { return '<li class="flex gap-2"><span class="text-slate-400">•</span><span>' + l + '</span></li>' }).join('') + '</ul>'
                    : '<div class="mt-2 text-[0.72rem] text-slate-400">No prescriptions</div>'

                var notesHtml = notes ? '<div class="mt-2 text-[0.72rem] text-slate-600">' + notes + '</div>' : ''

                return '' +
                    '<div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3">' +
                        '<div class="flex items-start justify-between gap-2">' +
                            '<div>' +
                                '<div class="text-[0.68rem] uppercase tracking-widest text-slate-400">Visit</div>' +
                                '<div class="text-[0.85rem] font-semibold text-slate-900">' + dateStr + (timeStr ? (' ' + timeStr) : '') + '</div>' +
                            '</div>' +
                            '<div class="text-right text-[0.72rem] text-slate-400">#' + (tx.transaction_id || '') + '</div>' +
                        '</div>' +
                        '<div class="mt-2 text-[0.78rem] text-slate-700"><span class="font-semibold">Dx:</span> ' + dx + '</div>' +
                        notesHtml +
                        rxHtml +
                    '</div>'
            }).join('')
        }

        function computeConflicts() {
            var drugAllergies = state.medicalBackground
                .filter(function (b) { return normalizeString(b.category) === 'allergy_drug' })
                .map(function (b) { return normalizeString(b.name) })
                .filter(Boolean)

            if (!drugAllergies.length) return []

            var rows = getPrescriptionRows()
            var conflicts = []
            rows.forEach(function (r) {
                var med = state.medicinesById[r.medicine_id]
                if (!med) return
                var contra = normalizeString(med.contraindications || '')
                var medName = normalizeString(medicineDisplayName(med))
                drugAllergies.forEach(function (a) {
                    if (a && (contra.indexOf(a) !== -1 || medName.indexOf(a) !== -1)) {
                        conflicts.push({ allergy: a, medicine: medicineDisplayName(med) })
                    }
                })
            })
            return conflicts
        }

        function renderSafety() {
            var conflicts = computeConflicts()
            if (!conflicts.length) {
                setVisible(safetyBox, false)
                return
            }
            var lines = conflicts.slice(0, 8).map(function (c) {
                return '• Possible conflict: ' + c.medicine + ' vs allergy "' + c.allergy + '"'
            })
            safetyBox.textContent = 'Safety warnings:\n' + lines.join('\n')
            setVisible(safetyBox, true)
        }

        function loadMedicines() {
            return api('{{ url('/api/medicines') }}?per_page=200').then(function (resp) {
                state.medicines = getPaginatedData(resp)
                state.medicinesById = {}
                state.medicines.forEach(function (m) {
                    state.medicinesById[String(m.medicine_id)] = m
                })
            })
        }

        function loadDoctorUser() {
            return api('{{ url('/api/user') }}').then(function (u) {
                state.doctorUserId = u && u.user_id ? u.user_id : null
            })
        }

        function loadAppointment(appointmentId) {
            setVisible(snapshotError, false)
            setVisible(snapshotLoading, true)
            return api('{{ url('/api/appointments') }}/' + appointmentId).then(function (appt) {
                state.appointmentId = appt.appointment_id
                state.patientId = appt.patient_id
                state.parentUserId = appt.patient && appt.patient.parent_user_id ? appt.patient.parent_user_id : null
                state.transactionId = appt.transaction ? appt.transaction.transaction_id : null
                state.prescriptionId = null
                state.existingItemIds = []

                setText(elPatientName, formatName(appt.patient))
                var sex = appt.patient && appt.patient.sex ? appt.patient.sex : ''
                var age = appt.patient && appt.patient.age ? String(appt.patient.age) : computeAgeFromBirthdate(appt.patient ? appt.patient.birthdate : '')
                var metaParts = []
                if (sex) metaParts.push(sex)
                if (age) metaParts.push(age + ' yrs')
                metaParts.push(appt.patient && appt.patient.is_dependent ? 'Dependent' : 'Regular')
                setText(elPatientMeta, metaParts.filter(Boolean).join(' • ') || '—')

                var dt = appt.appointment_datetime || ''
                var dateStr = dt ? dt.toString().slice(0, 10) : '—'
                var timeStr = dt ? dt.toString().slice(11, 16) : '—'
                setText(elApptDateTime, dateStr + ' ' + timeStr)
                setText(elApptType, appt.appointment_type ? appt.appointment_type.toString().replace('_', '-') : '—')
                setText(elPhone, appt.patient && appt.patient.contact_number ? appt.patient.contact_number : '—')
                setText(elEmail, appt.patient && appt.patient.email ? appt.patient.email : '—')
                setText(elAddress, appt.patient && appt.patient.address ? appt.patient.address : '—')

                if (appt.patient && appt.patient.is_dependent && state.parentUserId) {
                    setText(elDepStatus, 'Dependent of:')
                    return api('{{ url('/api/users') }}/' + state.parentUserId).then(function (parent) {
                        setText(elParentName, formatName(parent))
                        var pPhone = parent && parent.contact_number ? parent.contact_number : ''
                        var pEmail = parent && parent.email ? parent.email : ''
                        setText(elParentMeta, [pPhone, pEmail].filter(Boolean).join(' • '))
                    }).catch(function () {
                        setText(elParentName, 'Parent record unavailable')
                        setText(elParentMeta, '')
                    })
                }

                setText(elDepStatus, appt.patient && appt.patient.is_dependent ? 'Dependent' : 'Not a dependent')
                setText(elParentName, '')
                setText(elParentMeta, '')
            }).catch(function (err) {
                snapshotError.textContent = err && err.body ? err.body : 'Unable to load appointment details.'
                setVisible(snapshotError, true)
                throw err
            }).finally(function () {
                setVisible(snapshotLoading, false)
            })
        }

        function loadMedicalBackground(patientId) {
            return api('{{ url('/api/medical-backgrounds') }}?patient_id=' + patientId + '&per_page=200').then(function (resp) {
                state.medicalBackground = getPaginatedData(resp)
                renderBackground(state.medicalBackground)
                renderSafety()
            })
        }

        function loadHistory(patientId) {
            setVisible(historyError, false)
            setVisible(historyLoading, true)
            return api('{{ url('/api/visits') }}?patient_id=' + patientId + '&per_page=50').then(function (resp) {
                state.history = getPaginatedData(resp)
                setText(elTotalVisits, String(state.history.length))
                var last = state.history.length ? state.history[0] : null
                var dt = last ? (last.visit_datetime || last.transaction_datetime || '') : ''
                setText(elLastVisit, dt ? dt.toString().slice(0, 10) : '—')
                renderHistory()
            }).catch(function (err) {
                historyError.textContent = err && err.body ? err.body : 'Unable to load patient history.'
                setVisible(historyError, true)
            }).finally(function () {
                setVisible(historyLoading, false)
            })
        }

        function loadExistingDraft() {
            if (!state.transactionId) {
                return Promise.resolve()
            }
            return api('{{ url('/api/transactions') }}/' + state.transactionId).then(function (tx) {
                if (diagnosisEl) diagnosisEl.value = tx.diagnosis || ''
                if (treatmentEl) treatmentEl.value = tx.treatment_notes || ''
                var rx = tx.prescriptions && tx.prescriptions.length ? tx.prescriptions[0] : null
                if (rx) {
                    state.prescriptionId = rx.prescription_id
                    state.existingItemIds = (rx.items || []).map(function (it) { return it.item_id })
                    if (prescriptionBody) prescriptionBody.innerHTML = ''
                    if (rx.items && rx.items.length) {
                        rx.items.forEach(function (it) {
                            ensureRow({
                                medicine_id: it.medicine_id,
                                dosage: it.dosage,
                                frequency: it.frequency,
                                duration: it.duration,
                                instructions: it.instructions,
                            })
                        })
                    }
                    if (printBtn) printBtn.classList.toggle('hidden', !state.prescriptionId)
                }
            })
        }

        function updateAppointmentStatusIfNeeded() {
            if (!markCompletedEl || !markCompletedEl.checked) return Promise.resolve()
            if (!state.appointmentId) return Promise.resolve()
            return api('{{ url('/api/appointments') }}/' + state.appointmentId, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: 'completed' }),
            }).catch(function () {})
        }

        function saveAll() {
            setVisible(saveError, false)
            setVisible(saveSuccess, false)

            if (!state.appointmentId) {
                saveError.textContent = 'Select an appointment first.'
                setVisible(saveError, true)
                return
            }

            var conflicts = computeConflicts()
            if (conflicts.length && (!acknowledgeEl || !acknowledgeEl.checked)) {
                saveError.textContent = 'Safety warnings detected. Check "Override safety warnings" to proceed.'
                setVisible(saveError, true)
                return
            }

            var payload = {
                appointment_id: state.appointmentId,
                diagnosis: diagnosisEl ? diagnosisEl.value : '',
                treatment_notes: treatmentEl ? treatmentEl.value : '',
                visit_datetime: new Date().toISOString().slice(0, 19).replace('T', ' '),
            }

            var txPromise = state.transactionId
                ? api('{{ url('/api/transactions') }}/' + state.transactionId, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                })
                : api('{{ url('/api/transactions') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                })

            txPromise.then(function (tx) {
                state.transactionId = tx.transaction_id
                var rxPayload = {
                    transaction_id: state.transactionId,
                    doctor_id: state.doctorUserId,
                    prescribed_datetime: new Date().toISOString().slice(0, 19).replace('T', ' '),
                    notes: null,
                }

                var rxPromise = state.prescriptionId
                    ? api('{{ url('/api/prescriptions') }}/' + state.prescriptionId, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(rxPayload),
                    })
                    : api('{{ url('/api/prescriptions') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(rxPayload),
                    })

                return rxPromise.then(function (rx) {
                    state.prescriptionId = rx.prescription_id

                    var deletes = state.existingItemIds.reduce(function (p, id) {
                        return p.then(function () {
                            return api('{{ url('/api/prescription-items') }}/' + id, { method: 'DELETE' }).catch(function () {})
                        })
                    }, Promise.resolve())

                    return deletes.then(function () {
                        var rows = getPrescriptionRows()
                        return rows.reduce(function (p, row) {
                            return p.then(function () {
                                return api('{{ url('/api/prescription-items') }}', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        prescription_id: state.prescriptionId,
                                        medicine_id: Number(row.medicine_id),
                                        dosage: row.dosage || null,
                                        frequency: row.frequency || null,
                                        duration: row.duration || null,
                                        instructions: row.instructions || null,
                                    }),
                                })
                            })
                        }, Promise.resolve())
                    })
                })
            }).then(function () {
                state.existingItemIds = []
                return updateAppointmentStatusIfNeeded()
            }).then(function () {
                saveSuccess.textContent = 'Saved consultation and prescription successfully.'
                setVisible(saveSuccess, true)
                if (printBtn) printBtn.classList.toggle('hidden', !state.prescriptionId)
                return loadHistory(state.patientId)
            }).catch(function (err) {
                saveError.textContent = err && err.body ? err.body : 'Unable to save consultation.'
                setVisible(saveError, true)
            })
        }

        function handleAppointmentChange() {
            var id = appointmentSelect ? appointmentSelect.value : ''
            resetWorkspace()
            if (!id) return
            state.appointmentId = id
            loadAppointment(id).then(function () {
                return Promise.all([
                    loadMedicalBackground(state.patientId),
                    loadHistory(state.patientId),
                    loadExistingDraft(),
                ])
            }).then(function () {
                if (prescriptionBody && !prescriptionBody.querySelector('tr')) {
                    ensureRow()
                }
                renderSafety()
            }).catch(function () {})
        }

        if (historyFilter) {
            historyFilter.addEventListener('change', renderHistory)
        }

        if (appointmentSelect) {
            appointmentSelect.addEventListener('change', handleAppointmentChange)
        }

        if (safetyModalClose) {
            safetyModalClose.addEventListener('click', hideSafetyModal)
        }
        if (safetyModal) {
            safetyModal.addEventListener('click', function (e) {
                if (e.target === safetyModal) hideSafetyModal()
            })
        }
        if (safetyModalAck) {
            safetyModalAck.addEventListener('click', function () {
                if (acknowledgeEl) acknowledgeEl.checked = true
                hideSafetyModal()
            })
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (diagnosisEl) diagnosisEl.value = ''
                if (treatmentEl) treatmentEl.value = ''
                if (prescriptionBody) prescriptionBody.innerHTML = ''
                ensureRow()
                setVisible(saveSuccess, false)
                setVisible(saveError, false)
                renderSafety()
                if (printBtn) printBtn.classList.add('hidden')
            })
        }

        if (addMedBtn) {
            addMedBtn.addEventListener('click', function () {
                ensureRow()
            })
        }

        if (saveBtn) {
            saveBtn.addEventListener('click', saveAll)
        }

        if (printBtn) {
            printBtn.addEventListener('click', function () {
                if (!state.prescriptionId) return
                var url = "{{ url('/print/prescriptions') }}/" + encodeURIComponent(String(state.prescriptionId))
                window.open(url, '_blank', 'noopener')
            })
        }

        Promise.all([loadDoctorUser(), loadMedicines()]).then(function () {
            if (appointmentSelect && appointmentSelect.value) {
                handleAppointmentChange()
            } else {
                ensureRow()
            }
        }).catch(function () {
            snapshotError.textContent = 'Unable to load medicines or user profile.'
            setVisible(snapshotError, true)
        })
    })
</script>
