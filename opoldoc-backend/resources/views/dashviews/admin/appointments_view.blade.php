<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Appointments</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Monitoring</span>
    </div>
    <p class="text-xs text-slate-500 mb-4">
        Global appointment monitoring with filters by date, doctor, and status.
    </p>

    <div id="adminAppointmentsError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

    <div class="mb-3 grid grid-cols-1 md:grid-cols-5 gap-2 md:items-end">
        <div>
            <label for="admin_appt_date" class="block text-[0.7rem] text-slate-600 mb-1">Date</label>
            <input id="admin_appt_date" type="date" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
        </div>
        <div>
            <label for="admin_appt_doctor" class="block text-[0.7rem] text-slate-600 mb-1">Doctor</label>
            <select id="admin_appt_doctor" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="">All doctors</option>
            </select>
        </div>
        <div>
            <label for="admin_appt_status" class="block text-[0.7rem] text-slate-600 mb-1">Status</label>
            <select id="admin_appt_status" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="">All statuses</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="no_show">No-show</option>
            </select>
        </div>
        <div>
            <label for="admin_appt_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
            <select id="admin_appt_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="newest" selected>Newest first</option>
                <option value="oldest">Oldest first</option>
            </select>
        </div>
        <div>
            <label for="admin_appt_search" class="block text-[0.7rem] text-slate-600 mb-1">Search</label>
            <input id="admin_appt_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Patient/doctor name">
        </div>
    </div>

   <div class="overflow-auto scrollbar-hidden h-[300px]">
        <table class="min-w-full text-left text-xs text-slate-600">
            <thead>
                <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                    <th class="py-2 pr-4 font-semibold">ID</th>
                    <th class="py-2 pr-4 font-semibold">Datetime</th>
                    <th class="py-2 pr-4 font-semibold">Patient</th>
                    <th class="py-2 pr-4 font-semibold">Doctor</th>
                    <th class="py-2 pr-4 font-semibold">Type</th>
                    <th class="py-2 pr-4 font-semibold">Status</th>
                    <th class="py-2 pr-4 font-semibold">Reason</th>
                </tr>
            </thead>
            <tbody id="admin_appt_table_body">
                <tr>
                    <td colspan="7" class="py-4 text-center text-[0.78rem] text-slate-400">
                        Loading appointments…
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var errorBox = document.getElementById('adminAppointmentsError')
        var dateInput = document.getElementById('admin_appt_date')
        var doctorSelect = document.getElementById('admin_appt_doctor')
        var statusSelect = document.getElementById('admin_appt_status')
        var sortSelect = document.getElementById('admin_appt_sort')
        var searchInput = document.getElementById('admin_appt_search')
        var tableBody = document.getElementById('admin_appt_table_body')

        var appointments = []
        var doctors = []

        function showError(message) {
            if (!errorBox) return
            if (!message) {
                errorBox.textContent = ''
                errorBox.classList.add('hidden')
                return
            }
            errorBox.textContent = message
            errorBox.classList.remove('hidden')
        }

        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
        }

        function personLabel(u, fallback) {
            if (!u) return fallback || '—'
            var name = ((u.firstname || '') + ' ' + (u.lastname || '')).trim()
            if (name) return name
            if (u.email) return u.email
            return fallback || ('User #' + u.user_id)
        }

        function statusBadge(status) {
            var key = String(status || '').toLowerCase()
            var map = {
                pending: 'bg-amber-50 text-amber-700 border-amber-100',
                confirmed: 'bg-cyan-50 text-cyan-700 border-cyan-100',
                completed: 'bg-emerald-50 text-emerald-700 border-emerald-100',
                cancelled: 'bg-slate-50 text-slate-600 border-slate-100',
                no_show: 'bg-rose-50 text-rose-700 border-rose-100'
            }
            var cls = map[key] || 'bg-slate-50 text-slate-600 border-slate-100'
            var label = key ? key.replace('_', ' ') : 'Unknown'
            label = label.charAt(0).toUpperCase() + label.slice(1)
            return '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.68rem] font-medium border ' + cls + '">' + escapeHtml(label) + '</span>'
        }

        function loadDoctors() {
            apiFetch("{{ url('/api/doctors') }}?per_page=100", { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    })
                })
                .then(function (result) {
                    if (!result.ok) return
                    doctors = Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    renderDoctorOptions()
                })
                .catch(function () {})
        }

        function renderDoctorOptions() {
            if (!doctorSelect) return
            var selected = doctorSelect.value
            var html = '<option value="">All doctors</option>'
            doctors.forEach(function (d) {
                html += '<option value="' + d.user_id + '">' + escapeHtml(personLabel(d, 'Doctor #' + d.user_id)) + '</option>'
            })
            doctorSelect.innerHTML = html
            doctorSelect.value = selected
        }

        function loadAppointments() {
            if (!tableBody) return
            tableBody.innerHTML = '<tr><td colspan="7" class="py-4 text-center text-[0.78rem] text-slate-400">Loading appointments…</td></tr>'
            showError('')
            apiFetch("{{ url('/api/appointments') }}?per_page=100", { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        showError('Failed to load appointments.')
                        appointments = []
                        renderAppointments()
                        return
                    }
                    appointments = Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    renderAppointments()
                })
                .catch(function () {
                    showError('Network error while loading appointments.')
                    appointments = []
                    renderAppointments()
                })
        }

        function renderAppointments() {
            if (!tableBody) return

            var selectedDate = dateInput ? dateInput.value : ''
            var selectedDoctor = doctorSelect ? doctorSelect.value : ''
            var selectedStatus = statusSelect ? statusSelect.value : ''
            var selectedSort = sortSelect ? sortSelect.value : 'newest'
            var query = searchInput ? searchInput.value.toLowerCase().trim() : ''

            var filtered = appointments.slice()
            if (selectedDate) {
                filtered = filtered.filter(function (a) {
                    var dt = a.appointment_datetime ? String(a.appointment_datetime).slice(0, 10) : ''
                    return dt === selectedDate
                })
            }
            if (selectedDoctor) {
                filtered = filtered.filter(function (a) {
                    return String(a.doctor_id || (a.doctor && a.doctor.user_id) || '') === String(selectedDoctor)
                })
            }
            if (selectedStatus) {
                filtered = filtered.filter(function (a) {
                    return String(a.status || '') === selectedStatus
                })
            }
            if (query) {
                filtered = filtered.filter(function (a) {
                    var p = personLabel(a.patient, '').toLowerCase()
                    var d = personLabel(a.doctor, '').toLowerCase()
                    return p.indexOf(query) !== -1 || d.indexOf(query) !== -1
                })
            }

            filtered.sort(function (a, b) {
                var da = a.appointment_datetime || ''
                var db = b.appointment_datetime || ''
                if (selectedSort === 'oldest') {
                    if (da < db) return -1
                    if (da > db) return 1
                    return 0
                }
                if (da < db) return 1
                if (da > db) return -1
                return 0
            })

            if (!filtered.length) {
                tableBody.innerHTML = '<tr><td colspan="7" class="py-4 text-center text-[0.78rem] text-slate-400">No appointments found.</td></tr>'
                return
            }

            var html = ''
            filtered.forEach(function (a) {
                var dt = a.appointment_datetime ? String(a.appointment_datetime).replace('T', ' ').slice(0, 16) : '—'
                var patient = personLabel(a.patient, 'Patient #' + (a.patient_id || ''))
                var doctor = personLabel(a.doctor, 'Doctor #' + (a.doctor_id || ''))
                html += '<tr class="border-b border-slate-50 last:border-0">' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">#' + escapeHtml(a.appointment_id || '') + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-700">' + escapeHtml(dt) + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-700">' + escapeHtml(patient) + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-700">' + escapeHtml(doctor) + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">' + escapeHtml(a.appointment_type || '—') + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem]">' + statusBadge(a.status) + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">' + (a.reason_for_visit ? escapeHtml(a.reason_for_visit) : '<span class="text-slate-400">—</span>') + '</td>' +
                '</tr>'
            })
            tableBody.innerHTML = html
        }

        if (dateInput) dateInput.addEventListener('change', renderAppointments)
        if (doctorSelect) doctorSelect.addEventListener('change', renderAppointments)
        if (statusSelect) statusSelect.addEventListener('change', renderAppointments)
        if (sortSelect) sortSelect.addEventListener('change', renderAppointments)
        if (searchInput) searchInput.addEventListener('input', renderAppointments)

        loadDoctors()
        loadAppointments()
    })
</script>
