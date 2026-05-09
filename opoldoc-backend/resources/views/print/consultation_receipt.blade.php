<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consultation Receipt — Opol Doctors Clinic</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="no-print sticky top-0 z-10 bg-white/90 backdrop-blur border-b border-slate-200 px-4 py-3">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-3">
            <div class="text-sm font-semibold text-slate-900">Consultation Receipt</div>
            <button type="button" id="consultationPrintBtn" class="px-3 py-2 rounded-xl bg-slate-900 text-white text-[0.78rem] font-semibold hover:bg-slate-800">Print</button>
        </div>
    </div>

    <div class="max-w-4xl mx-auto p-4 md:p-6">
        <div id="consultationPrintError" class="hidden mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[0.85rem] text-red-700"></div>

        <div class="bg-white border border-slate-200 rounded-3xl p-5 md:p-7">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-[0.72rem] uppercase tracking-widest text-slate-400">Opol Doctors Clinic</div>
                    <div class="text-lg font-semibold text-slate-900 mt-1">Consultation Summary</div>
                    <div id="consultationMeta" class="text-[0.78rem] text-slate-500 mt-1">Loading…</div>
                </div>
                <div class="text-right">
                    <div class="text-[0.72rem] text-slate-400">Transaction ID</div>
                    <div class="text-sm font-semibold text-slate-900">#{{ $transactionId }}</div>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-[0.85rem]">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <div class="text-[0.7rem] uppercase tracking-widest text-slate-400">Patient</div>
                    <div id="consultationPatientName" class="text-sm font-semibold text-slate-900 mt-1">—</div>
                    <div id="consultationPatientInfo" class="text-[0.78rem] text-slate-600 mt-1">—</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <div class="text-[0.7rem] uppercase tracking-widest text-slate-400">Doctor</div>
                    <div id="consultationDoctorName" class="text-sm font-semibold text-slate-900 mt-1">—</div>
                    <div id="consultationDoctorInfo" class="text-[0.78rem] text-slate-600 mt-1">—</div>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-[0.75rem] uppercase tracking-widest text-slate-400 mb-2">Diagnosis</div>
                    <div id="consultationDiagnosis" class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-[0.85rem] text-slate-700 whitespace-pre-line">—</div>
                </div>
                <div>
                    <div class="text-[0.75rem] uppercase tracking-widest text-slate-400 mb-2">Treatment Notes</div>
                    <div id="consultationTreatment" class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-[0.85rem] text-slate-700 whitespace-pre-line">—</div>
                </div>
            </div>

            <div class="mt-5">
                <div class="text-[0.75rem] uppercase tracking-widest text-slate-400 mb-2">Prescription Items</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-[0.82rem] text-slate-700">
                        <thead>
                            <tr class="border-b border-slate-200 text-[0.7rem] uppercase tracking-widest text-slate-400">
                                <th class="py-2 pr-4 font-semibold">Medicine</th>
                                <th class="py-2 pr-4 font-semibold">Dosage</th>
                                <th class="py-2 pr-4 font-semibold">Frequency</th>
                                <th class="py-2 pr-4 font-semibold">Duration</th>
                                <th class="py-2 pr-0 font-semibold">Instructions</th>
                            </tr>
                        </thead>
                        <tbody id="consultationItemsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var transactionId = {{ (int) $transactionId }};
            var errorBox = document.getElementById('consultationPrintError');
            var printBtn = document.getElementById('consultationPrintBtn');
            var metaEl = document.getElementById('consultationMeta');
            var patientNameEl = document.getElementById('consultationPatientName');
            var patientInfoEl = document.getElementById('consultationPatientInfo');
            var doctorNameEl = document.getElementById('consultationDoctorName');
            var doctorInfoEl = document.getElementById('consultationDoctorInfo');
            var diagnosisEl = document.getElementById('consultationDiagnosis');
            var treatmentEl = document.getElementById('consultationTreatment');
            var itemsBody = document.getElementById('consultationItemsBody');

            function showError(message) {
                if (!errorBox) return;
                errorBox.textContent = message || '';
                errorBox.classList.toggle('hidden', !message);
            }

            function escapeHtml(value) {
                return String(value == null ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function apiFetch(path, options) {
                if (window.apiFetch) return window.apiFetch(path, options || {});
                var token = null;
                try { token = window.localStorage ? window.localStorage.getItem('api_token') : null; } catch (_) { token = null; }
                var headers = (options && options.headers) ? Object.assign({}, options.headers) : {};
                if (token) headers['Authorization'] = 'Bearer ' + token;
                if (!headers['Accept']) headers['Accept'] = 'application/json';
                return fetch(path, Object.assign({}, options, { headers: headers }));
            }

            function nameForUser(u, fallback) {
                if (!u) return fallback || '—';
                var parts = [u.firstname, u.middlename, u.lastname].filter(function (v) { return String(v || '').trim() !== ''; });
                var name = parts.join(' ').trim();
                return name || fallback || ('User #' + (u.user_id || ''));
            }

            function medicineName(item) {
                var med = item && item.medicine ? item.medicine : null;
                if (med) {
                    var generic = med.generic_name || '';
                    var brand = med.brand_name || '';
                    if (generic && brand) return generic + ' (' + brand + ')';
                    return generic || brand || ('Medicine #' + (med.medicine_id || ''));
                }
                return item && item.medicine_name ? item.medicine_name : ('Medicine #' + (item && item.medicine_id ? item.medicine_id : ''));
            }

            function render() {
                showError('');
                apiFetch("{{ url('/api/transactions') }}/" + encodeURIComponent(String(transactionId)), { method: 'GET' })
                    .then(function (res) {
                        return res.text().then(function (txt) {
                            var data = null;
                            try { data = txt ? JSON.parse(txt) : null; } catch (_) { data = null; }
                            return { ok: res.ok, data: data };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok || !result.data) {
                            showError('Unable to load consultation receipt. Please ensure you are logged in.');
                            return;
                        }

                        var tx = result.data;
                        var appt = tx.appointment || null;
                        var patient = appt && appt.patient ? appt.patient : null;
                        var doctor = appt && appt.doctor ? appt.doctor : null;
                        var prescriptions = tx.prescriptions || [];
                        var allItems = [];

                        prescriptions.forEach(function (rx) {
                            (rx.items || []).forEach(function (item) {
                                allItems.push(item);
                            });
                            if (!doctor && rx.doctor) doctor = rx.doctor;
                        });

                        var dt = tx.visit_datetime || tx.transaction_datetime || '';
                        if (metaEl) metaEl.textContent = dt ? ('Visit: ' + String(dt).replace('T', ' ').slice(0, 16)) : '—';

                        if (patientNameEl) patientNameEl.textContent = nameForUser(patient, 'Patient');
                        if (patientInfoEl) {
                            var patientMeta = [];
                            if (patient && patient.sex) patientMeta.push(patient.sex);
                            if (patient && patient.birthdate) patientMeta.push(String(patient.birthdate).slice(0, 10));
                            if (appt && appt.appointment_id) patientMeta.push('Appointment #' + appt.appointment_id);
                            patientInfoEl.textContent = patientMeta.length ? patientMeta.join(' • ') : '—';
                        }

                        if (doctorNameEl) doctorNameEl.textContent = nameForUser(doctor, 'Doctor');
                        if (doctorInfoEl) {
                            var doctorMeta = [];
                            if (doctor && doctor.specialization) doctorMeta.push(doctor.specialization);
                            if (doctor && doctor.license_number) doctorMeta.push('Lic: ' + doctor.license_number);
                            doctorInfoEl.textContent = doctorMeta.length ? doctorMeta.join(' • ') : '—';
                        }

                        if (diagnosisEl) diagnosisEl.textContent = tx.diagnosis ? String(tx.diagnosis) : 'No diagnosis recorded.';
                        if (treatmentEl) treatmentEl.textContent = tx.treatment_notes ? String(tx.treatment_notes) : 'No treatment notes recorded.';

                        if (itemsBody) {
                            if (!allItems.length) {
                                itemsBody.innerHTML = '<tr><td colspan="5" class="py-3 text-slate-500 text-[0.85rem]">No prescription items recorded.</td></tr>';
                            } else {
                                itemsBody.innerHTML = allItems.map(function (item) {
                                    return '' +
                                        '<tr class="border-b border-slate-100 last:border-0">' +
                                            '<td class="py-2 pr-4 font-semibold text-slate-900">' + escapeHtml(medicineName(item)) + '</td>' +
                                            '<td class="py-2 pr-4">' + escapeHtml(item.dosage || '—') + '</td>' +
                                            '<td class="py-2 pr-4">' + escapeHtml(item.frequency || '—') + '</td>' +
                                            '<td class="py-2 pr-4">' + escapeHtml(item.duration || '—') + '</td>' +
                                            '<td class="py-2 pr-0">' + escapeHtml(item.instructions || '—') + '</td>' +
                                        '</tr>';
                                }).join('');
                            }
                        }
                    });
            }

            if (printBtn) {
                printBtn.addEventListener('click', function () {
                    window.print();
                });
            }

            render();
        })();
    </script>
</body>
</html>
