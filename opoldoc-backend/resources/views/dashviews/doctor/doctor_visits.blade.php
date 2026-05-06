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

    <div class="overflow-x-auto scrollbar-hidden">
        <table class="min-w-full text-left text-xs text-slate-600">
            <thead>
                <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                    <th class="py-2 pr-4 font-semibold">Visit ID</th>
                    <th class="py-2 pr-4 font-semibold">Patient</th>
                    <th class="py-2 pr-4 font-semibold">Visit date</th>
                    <th class="py-2 pr-4 font-semibold">Reason</th>
                    <th class="py-2 pr-4 font-semibold">Diagnosis</th>
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
                    @endphp
                    <tr class="border-b border-slate-50 last:border-0 doctor-visit-row"
                        data-visit-id="{{ $visit->transaction_id }}"
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-[0.78rem] text-slate-400">
                            No visits found yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('doctor_visit_search')
        var sortSelect = document.getElementById('doctor_visit_sort')
        var rows = Array.prototype.slice.call(document.querySelectorAll('.doctor-visit-row'))

        function applyDoctorVisitFilters() {
            var query = searchInput ? searchInput.value.toLowerCase().trim() : ''

            rows.forEach(function (row) {
                var id = row.getAttribute('data-visit-id') || ''
                var patient = row.getAttribute('data-patient') || ''
                var diagnosis = row.getAttribute('data-diagnosis') || ''

                var matches = true
                if (query) {
                    matches =
                        ('#' + id).indexOf(query) !== -1 ||
                        patient.indexOf(query) !== -1 ||
                        diagnosis.indexOf(query) !== -1
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

        applyDoctorVisitFilters()
    })
</script>
