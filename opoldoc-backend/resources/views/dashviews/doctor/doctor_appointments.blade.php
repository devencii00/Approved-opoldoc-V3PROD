<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">My Schedule</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Time &amp; days</span>
    </div>
    <p class="text-xs text-slate-500 mb-3">
        View your appointments across days and times. Use this as your working schedule for consultations.
    </p>

    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
        <div class="flex-1">
            <label for="doctor_appointment_search" class="block text-[0.7rem] text-slate-600 mb-1">Search appointments</label>
            <input id="doctor_appointment_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Patient name, ID or reason">
        </div>
        <div class="w-full md:w-40">
            <label for="doctor_appointment_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
            <select id="doctor_appointment_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="date_desc">Newest first</option>
                <option value="date_asc">Oldest first</option>
                <option value="patient_asc">Patient A–Z</option>
                <option value="patient_desc">Patient Z–A</option>
            </select>
        </div>
    </div>

    <div class="overflow-auto max-h-[28rem] show-scrollbar">
        <table class="min-w-full text-left text-xs text-slate-600">
            <thead>
                <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                    <th class="py-2 pr-4 font-semibold">ID</th>
                    <th class="py-2 pr-4 font-semibold">Patient</th>
                    <th class="py-2 pr-4 font-semibold">Date</th>
                    <th class="py-2 pr-4 font-semibold">Time</th>
                    <th class="py-2 pr-4 font-semibold">Reason</th>
                    <th class="py-2 pr-4 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($doctorRecentAppointments ?? [] as $appointment)
                    @php
                        $patientParts = array_filter([
                            optional($appointment->patient)->firstname,
                            optional($appointment->patient)->middlename,
                            optional($appointment->patient)->lastname,
                        ], function ($v) {
                            return (string) $v !== '';
                        });
                        $patientName = trim(implode(' ', $patientParts));
                        $statusName = $appointment->status ? ucfirst(str_replace('_', ' ', $appointment->status)) : '';
                        $dateKey = optional($appointment->appointment_datetime)->format('Y-m-d') ?? '';
                        $timeKey = optional($appointment->appointment_datetime)->format('H:i') ?? '';
                    @endphp
                    <tr class="border-b border-slate-50 last:border-0 doctor-appointment-row"
                        data-appointment-id="{{ $appointment->appointment_id }}"
                        data-patient="{{ strtolower($patientName) }}"
                        data-reason="{{ strtolower($appointment->reason_for_visit ?? '') }}"
                        data-date="{{ $dateKey }}">
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">#{{ $appointment->appointment_id }}</td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                            @if ($patientName)
                                {{ $patientName }}
                            @else
                                <span class="text-slate-400">Patient #{{ $appointment->patient_id }}</span>
                            @endif
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            {{ $dateKey }}
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            {{ $timeKey }}
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            {{ \Illuminate\Support\Str::limit($appointment->reason_for_visit ?? 'No reason specified', 60) }}
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            @if ($statusName)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.68rem] font-medium border bg-slate-50 border-slate-100 text-slate-700">
                                    {{ ucfirst($statusName) }}
                                </span>
                            @else
                                <span class="text-[0.7rem] text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-[0.78rem] text-slate-400">
                            No appointments found yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('doctor_appointment_search')
        var sortSelect = document.getElementById('doctor_appointment_sort')
        var rows = Array.prototype.slice.call(document.querySelectorAll('.doctor-appointment-row'))

        function applyDoctorAppointmentFilters() {
            var query = searchInput ? searchInput.value.toLowerCase().trim() : ''

            rows.forEach(function (row) {
                var id = row.getAttribute('data-appointment-id') || ''
                var patient = row.getAttribute('data-patient') || ''
                var reason = row.getAttribute('data-reason') || ''

                var matches = true
                if (query) {
                    matches =
                        ('#' + id).indexOf(query) !== -1 ||
                        patient.indexOf(query) !== -1 ||
                        reason.indexOf(query) !== -1
                }

                row.style.display = matches ? '' : 'none'
            })

            applyDoctorAppointmentSort()
        }

        function applyDoctorAppointmentSort() {
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
            searchInput.addEventListener('input', applyDoctorAppointmentFilters)
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', applyDoctorAppointmentSort)
        }

        applyDoctorAppointmentFilters()
    })
</script>