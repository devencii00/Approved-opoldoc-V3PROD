<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prescription Receipt — Opol Doctors Clinic</title>
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
        <div class="max-w-3xl mx-auto flex items-center justify-between gap-3">
            <div class="text-sm font-semibold text-slate-900">Prescription Receipt</div>
            <div class="flex items-center gap-2">
                <button type="button" id="rxPrintBtn" class="px-3 py-2 rounded-xl bg-slate-900 text-white text-[0.78rem] font-semibold hover:bg-slate-800">Print</button>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto p-4 md:p-6">
        <div id="rxError" class="hidden mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[0.85rem] text-red-700"></div>

        <div class="bg-white border border-slate-200 rounded-3xl p-5 md:p-7">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-[0.72rem] uppercase tracking-widest text-slate-400">Opol Doctors Clinic</div>
                    <div class="text-lg font-semibold text-slate-900 mt-1">Prescription</div>
                    <div id="rxMeta" class="text-[0.78rem] text-slate-500 mt-1">Loading…</div>
                </div>
                <div class="text-right">
                    <div class="text-[0.72rem] text-slate-400">Prescription ID</div>
                    <div class="text-sm font-semibold text-slate-900">#{{ $prescriptionId }}</div>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-[0.85rem]">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <div class="text-[0.7rem] uppercase tracking-widest text-slate-400">Patient</div>
                    <div id="rxPatientName" class="text-sm font-semibold text-slate-900 mt-1">—</div>
                    <div id="rxPatientInfo" class="text-[0.78rem] text-slate-600 mt-1">—</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <div class="text-[0.7rem] uppercase tracking-widest text-slate-400">Doctor</div>
                    <div id="rxDoctorName" class="text-sm font-semibold text-slate-900 mt-1">—</div>
                    <div id="rxDoctorInfo" class="text-[0.78rem] text-slate-600 mt-1">—</div>
                </div>
            </div>

            <div class="mt-5">
                <div class="text-[0.75rem] uppercase tracking-widest text-slate-400 mb-2">Medicines</div>
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
                        <tbody id="rxItemsBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="mt-7 grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                <div>
                    <div class="text-[0.75rem] uppercase tracking-widest text-slate-400 mb-2">Notes</div>
                    <div id="rxNotes" class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-[0.85rem] text-slate-700 whitespace-pre-line">—</div>
                </div>
                <div class="text-right">
                    <div class="text-[0.75rem] uppercase tracking-widest text-slate-400 mb-2">Doctor Signature</div>
                    <div class="rounded-2xl border border-slate-100 bg-white px-4 py-3">
                        <div id="rxSignatureBox" class="h-20 flex items-center justify-center text-[0.78rem] text-slate-400">No signature</div>
                        <div id="rxSignatureName" class="mt-2 text-[0.85rem] font-semibold text-slate-900">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var prescriptionId = {{ (int) $prescriptionId }};
            var errorBox = document.getElementById('rxError');
            var printBtn = document.getElementById('rxPrintBtn');

            var rxMeta = document.getElementById('rxMeta');
            var patientName = document.getElementById('rxPatientName');
            var patientInfo = document.getElementById('rxPatientInfo');
            var doctorName = document.getElementById('rxDoctorName');
            var doctorInfo = document.getElementById('rxDoctorInfo');
            var itemsBody = document.getElementById('rxItemsBody');
            var notesBox = document.getElementById('rxNotes');
            var sigBox = document.getElementById('rxSignatureBox');
            var sigName = document.getElementById('rxSignatureName');

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
                return 'Medicine #' + (item && item.medicine_id ? item.medicine_id : '');
            }

            function renderSignature(doctorUser) {
                if (!sigBox || !sigName) return;
                var docName = nameForUser(doctorUser, 'Doctor');
                sigName.textContent = docName;

                var signatureUrl = doctorUser && doctorUser.signature_url ? String(doctorUser.signature_url) : '';
                if (!signatureUrl) {
                    sigBox.textContent = 'No signature';
                    return;
                }

                sigBox.innerHTML = '<img alt="Signature" src="' + escapeHtml(signatureUrl) + '" class="max-h-16 max-w-full object-contain">';
            }

            function load() {
                showError('');
                apiFetch("{{ url('/api/prescriptions') }}/" + encodeURIComponent(String(prescriptionId)), { method: 'GET' })
                    .then(function (res) {
                        return res.text().then(function (txt) {
                            var data = null;
                            try { data = txt ? JSON.parse(txt) : null; } catch (_) { data = null; }
                            return { ok: res.ok, status: res.status, data: data };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok || !result.data) {
                            showError('Unable to load prescription. Please ensure you are logged in.');
                            return;
                        }

                        var rx = result.data;
                        var tx = rx.transaction || null;
                        var appt = tx && tx.appointment ? tx.appointment : null;
                        var patient = appt && appt.patient ? appt.patient : null;
                        var doctor = rx.doctor || null;
                        var items = rx.items || [];

                        var dt = rx.prescribed_datetime ? String(rx.prescribed_datetime).replace('T', ' ').slice(0, 16) : '';
                        if (rxMeta) rxMeta.textContent = dt ? ('Prescribed: ' + dt) : '—';

                        if (patientName) patientName.textContent = nameForUser(patient, 'Patient');
                        if (patientInfo) {
                            var meta = [];
                            if (patient && patient.sex) meta.push(patient.sex);
                            if (patient && patient.birthdate) meta.push(String(patient.birthdate).slice(0, 10));
                            if (appt && appt.appointment_id) meta.push('Appointment #' + appt.appointment_id);
                            patientInfo.textContent = meta.length ? meta.join(' • ') : '—';
                        }

                        if (doctorName) doctorName.textContent = nameForUser(doctor, 'Doctor');
                        if (doctorInfo) {
                            var dmeta = [];
                            if (doctor && doctor.specialization) dmeta.push(doctor.specialization);
                            if (doctor && doctor.license_number) dmeta.push('Lic: ' + doctor.license_number);
                            doctorInfo.textContent = dmeta.length ? dmeta.join(' • ') : '—';
                        }

                        if (notesBox) notesBox.textContent = rx.notes ? String(rx.notes) : '—';

                        if (itemsBody) {
                            if (!items.length) {
                                itemsBody.innerHTML = '<tr><td colspan="5" class="py-3 text-slate-500 text-[0.85rem]">No medicines listed.</td></tr>';
                            } else {
                                itemsBody.innerHTML = items.map(function (it) {
                                    return '' +
                                        '<tr class="border-b border-slate-100 last:border-0">' +
                                            '<td class="py-2 pr-4 font-semibold text-slate-900">' + escapeHtml(medicineName(it)) + '</td>' +
                                            '<td class="py-2 pr-4">' + escapeHtml(it.dosage || '—') + '</td>' +
                                            '<td class="py-2 pr-4">' + escapeHtml(it.frequency || '—') + '</td>' +
                                            '<td class="py-2 pr-4">' + escapeHtml(it.duration || '—') + '</td>' +
                                            '<td class="py-2 pr-0">' + escapeHtml(it.instructions || '—') + '</td>' +
                                        '</tr>';
                                }).join('');
                            }
                        }

                        renderSignature(doctor);
                    })
                    .catch(function () {
                        showError('Network error while loading prescription.');
                    });
            }

            if (printBtn) {
                printBtn.addEventListener('click', function () {
                    window.print();
                });
            }

            load();
        })();
    </script>
</body>
</html>
