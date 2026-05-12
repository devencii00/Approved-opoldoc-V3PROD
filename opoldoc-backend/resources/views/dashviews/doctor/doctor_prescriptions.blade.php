<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Prescriptions</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Medications</span>
    </div>
    <p class="text-xs text-slate-500 mb-3">
        Recent prescriptions with basic medication details.
    </p>

    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
        <div class="flex-1">
            <label for="doctor_prescription_search" class="block text-[0.7rem] text-slate-600 mb-1">Search prescriptions</label>
            <input id="doctor_prescription_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Patient name, ID or notes">
        </div>
        <div class="w-full md:w-40">
            <label for="doctor_prescription_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
            <select id="doctor_prescription_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
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
                    <th class="py-2 pr-4 font-semibold">Prescription ID</th>
                    <th class="py-2 pr-4 font-semibold">Patient</th>
                    <th class="py-2 pr-4 font-semibold">Date</th>
                    <th class="py-2 pr-4 font-semibold">Items</th>
                    <th class="py-2 pr-4 font-semibold">Notes</th>
                    <th class="py-2 pr-4 font-semibold">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($doctorRecentPrescriptions ?? [] as $prescription)
                    @php
                        $patientParts = array_filter([
                            optional(optional(optional($prescription->transaction)->appointment)->patient)->firstname,
                            optional(optional(optional($prescription->transaction)->appointment)->patient)->middlename,
                            optional(optional(optional($prescription->transaction)->appointment)->patient)->lastname,
                        ], function ($v) {
                            return (string) $v !== '';
                        });
                        $patientName = trim(implode(' ', $patientParts));
                        $dateKey = optional($prescription->prescribed_datetime)->format('Y-m-d') ?? '';
                        $itemsCount = $prescription->items ? $prescription->items->count() : 0;
                        $itemPayload = ($prescription->items ?? collect())->map(function ($item) {
                            $medicine = $item->medicine ?? null;
                            $medicineName = trim(implode(' ', array_filter([
                                $medicine->generic_name ?? null,
                                isset($medicine->brand_name) && (string) $medicine->brand_name !== '' ? '(' . $medicine->brand_name . ')' : null,
                            ])));

                            return [
                                'medicine' => $medicineName !== '' ? $medicineName : ('Medicine #' . ($item->medicine_id ?? '')),
                                'dosage' => $item->dosage,
                                'frequency' => $item->frequency,
                                'duration' => $item->duration,
                                'instructions' => $item->instructions,
                            ];
                        })->values();
                    @endphp
                    <tr class="border-b border-slate-50 last:border-0 doctor-prescription-row"
                        data-prescription-id="{{ $prescription->prescription_id }}"
                        data-patient="{{ strtolower($patientName) }}"
                        data-date="{{ $dateKey }}">
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">#{{ $prescription->prescription_id }}</td>
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
                            @if ($itemsCount > 0)
                                {{ $itemsCount }} item{{ $itemsCount === 1 ? '' : 's' }}
                            @else
                                <span class="text-[0.7rem] text-slate-400">No items</span>
                            @endif
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            @if ($prescription->notes)
                                {{ \Illuminate\Support\Str::limit($prescription->notes, 80) }}
                            @else
                                <span class="text-[0.7rem] text-slate-400">No notes</span>
                            @endif
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            <button
                                type="button"
                                class="doctor-prescription-view-items inline-flex items-center justify-center rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-1.5 text-[0.74rem] font-semibold text-cyan-700 hover:bg-cyan-100"
                                data-prescription-id="{{ $prescription->prescription_id }}"
                                data-patient-name="{{ $patientName !== '' ? $patientName : 'Patient' }}"
                                data-prescribed-date="{{ $dateKey !== '' ? $dateKey : '—' }}"
                                data-items='@json($itemPayload)'
                            >
                                View items
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-[0.78rem] text-slate-400">
                            No prescriptions found yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="doctorPrescriptionItemsOverlay" class="hidden fixed inset-0 z-[70] bg-slate-900/40"></div>
<aside id="doctorPrescriptionItemsPanel" class="fixed top-0 right-0 z-[75] h-full w-full max-w-xl translate-x-full border-l border-slate-200 bg-white shadow-2xl transition-transform duration-300 ease-out">
    <div class="flex h-full flex-col">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-[0.7rem] uppercase tracking-widest text-slate-400">Prescription Items</div>
                    <h3 id="doctorPrescriptionItemsTitle" class="mt-1 text-base font-semibold text-slate-900">Prescription details</h3>
                    <p id="doctorPrescriptionItemsSubtitle" class="mt-1 text-xs text-slate-500">Review the prescribed medicines and instructions.</p>
                </div>
                <button id="doctorPrescriptionItemsClose" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                    <span class="text-lg leading-none">&times;</span>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4">
            <div id="doctorPrescriptionItemsMeta" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[0.78rem] text-slate-600"></div>
            <div id="doctorPrescriptionItemsList" class="mt-4 space-y-3"></div>
        </div>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('doctor_prescription_search')
        var sortSelect = document.getElementById('doctor_prescription_sort')
        var rows = Array.prototype.slice.call(document.querySelectorAll('.doctor-prescription-row'))
        var viewButtons = Array.prototype.slice.call(document.querySelectorAll('.doctor-prescription-view-items'))
        var overlay = document.getElementById('doctorPrescriptionItemsOverlay')
        var panel = document.getElementById('doctorPrescriptionItemsPanel')
        var closeButton = document.getElementById('doctorPrescriptionItemsClose')
        var panelTitle = document.getElementById('doctorPrescriptionItemsTitle')
        var panelSubtitle = document.getElementById('doctorPrescriptionItemsSubtitle')
        var panelMeta = document.getElementById('doctorPrescriptionItemsMeta')
        var panelList = document.getElementById('doctorPrescriptionItemsList')

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

        function openPanel() {
            if (overlay) overlay.classList.remove('hidden')
            if (panel) panel.classList.remove('translate-x-full')
        }

        function closePanel() {
            if (overlay) overlay.classList.add('hidden')
            if (panel) panel.classList.add('translate-x-full')
        }

        function renderItemCard(item, index) {
            var details = [
                { label: 'Dosage', value: item && item.dosage ? item.dosage : '—' },
                { label: 'Frequency', value: item && item.frequency ? item.frequency : '—' },
                { label: 'Duration', value: item && item.duration ? item.duration : '—' },
                { label: 'Instructions', value: item && item.instructions ? item.instructions : '—' },
            ]

            return '' +
                '<div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">' +
                    '<div class="flex items-start justify-between gap-3">' +
                        '<div>' +
                            '<div class="text-[0.68rem] uppercase tracking-widest text-slate-400">Item ' + escapeHtml(index + 1) + '</div>' +
                            '<div class="mt-1 text-sm font-semibold text-slate-900">' + escapeHtml(item && item.medicine ? item.medicine : 'Medicine') + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="mt-3 grid gap-3 sm:grid-cols-2">' +
                        details.map(function (detail) {
                            return '' +
                                '<div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">' +
                                    '<div class="text-[0.68rem] uppercase tracking-widest text-slate-400">' + escapeHtml(detail.label) + '</div>' +
                                    '<div class="mt-1 text-[0.8rem] text-slate-700 whitespace-pre-line">' + escapeHtml(detail.value) + '</div>' +
                                '</div>'
                        }).join('') +
                    '</div>' +
                '</div>'
        }

        function showPrescriptionItems(button) {
            if (!button) return

            var prescriptionId = button.getAttribute('data-prescription-id') || ''
            var patientName = button.getAttribute('data-patient-name') || 'Patient'
            var prescribedDate = button.getAttribute('data-prescribed-date') || '—'
            var items = []

            try {
                items = JSON.parse(button.getAttribute('data-items') || '[]')
            } catch (error) {
                items = []
            }

            if (panelTitle) panelTitle.textContent = 'Prescription #' + prescriptionId
            if (panelSubtitle) panelSubtitle.textContent = patientName + ' • ' + prescribedDate
            if (panelMeta) {
                panelMeta.innerHTML = '' +
                    '<div><span class="font-semibold text-slate-800">Patient:</span> ' + escapeHtml(patientName) + '</div>' +
                    '<div class="mt-1"><span class="font-semibold text-slate-800">Date:</span> ' + escapeHtml(prescribedDate) + '</div>' +
                    '<div class="mt-1"><span class="font-semibold text-slate-800">Items:</span> ' + escapeHtml(items.length) + '</div>'
            }
            if (panelList) {
                panelList.innerHTML = items.length
                    ? items.map(renderItemCard).join('')
                    : '<div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-4 text-[0.78rem] text-slate-500">No prescription items recorded for this prescription.</div>'
            }

            openPanel()
        }

        function applyDoctorPrescriptionFilters() {
            var query = searchInput ? searchInput.value.toLowerCase().trim() : ''

            rows.forEach(function (row) {
                var id = row.getAttribute('data-prescription-id') || ''
                var patient = row.getAttribute('data-patient') || ''
                var date = row.getAttribute('data-date') || ''

                var matches = true
                if (query) {
                    matches =
                        ('#' + id).indexOf(query) !== -1 ||
                        patient.indexOf(query) !== -1 ||
                        date.indexOf(query) !== -1
                }

                row.style.display = matches ? '' : 'none'
            })

            applyDoctorPrescriptionSort()
        }

        function applyDoctorPrescriptionSort() {
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
            searchInput.addEventListener('input', applyDoctorPrescriptionFilters)
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', applyDoctorPrescriptionSort)
        }
        if (closeButton) {
            closeButton.addEventListener('click', closePanel)
        }
        if (overlay) {
            overlay.addEventListener('click', closePanel)
        }
        viewButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                showPrescriptionItems(button)
            })
        })
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closePanel()
            }
        })

        applyDoctorPrescriptionFilters()
    })
</script>
