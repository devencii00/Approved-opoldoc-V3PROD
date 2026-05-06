<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Visit and prescription records</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Clinical</span>
    </div>
    <p class="text-xs text-slate-500 mb-3">
        High-level overview of clinical activity is available through doctor dashboards.
        This admin widget focuses on billing-backed visits.
    </p>

    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
        <div class="flex-1">
            <label for="admin_visit_search" class="block text-[0.7rem] text-slate-600 mb-1">Search visits</label>
            <input id="admin_visit_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Transaction or visit ID">
        </div>
        <div class="w-full md:w-40">
            <label for="admin_visit_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
            <select id="admin_visit_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="date_desc">Newest first</option>
                <option value="date_asc">Oldest first</option>
                <option value="amount_desc">Amount high–low</option>
                <option value="amount_asc">Amount low–high</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto scrollbar-hidden">
        <table class="min-w-full text-left text-xs text-slate-600">
            <thead>
                <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                    <th class="py-2 pr-4 font-semibold">Transaction</th>
                    <th class="py-2 pr-4 font-semibold">Visit</th>
                    <th class="py-2 pr-4 font-semibold">Amount</th>
                    <th class="py-2 pr-4 font-semibold">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($adminRecentTransactions ?? [] as $transaction)
                    <tr class="border-b border-slate-50 last:border-0 admin-visit-row"
                        data-txn-id="{{ $transaction->transaction_id }}"
                        data-visit-id="{{ $transaction->appointment ? $transaction->appointment->appointment_id : '' }}"
                        data-date="{{ optional($transaction->transaction_datetime)->format('Y-m-d') ?? '' }}"
                        data-amount="{{ (float) $transaction->amount }}">
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                            #{{ $transaction->transaction_id }}
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            @if ($transaction->appointment)
                                Appointment #{{ $transaction->appointment->appointment_id }}
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                            ₱{{ number_format((float) $transaction->amount, 2) }}
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            {{ optional($transaction->transaction_datetime)->format('Y-m-d') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-[0.78rem] text-slate-400">
                            No transactions recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('admin_visit_search')
        var sortSelect = document.getElementById('admin_visit_sort')
        var rows = Array.prototype.slice.call(document.querySelectorAll('.admin-visit-row'))

        function applyVisitFilters() {
            var query = searchInput ? searchInput.value.toLowerCase().trim() : ''

            rows.forEach(function (row) {
                var txn = row.getAttribute('data-txn-id') || ''
                var visit = row.getAttribute('data-visit-id') || ''

                var matches = true
                if (query) {
                    matches =
                        ('#' + txn).indexOf(query) !== -1 ||
                        ('#' + visit).indexOf(query) !== -1
                }

                row.style.display = matches ? '' : 'none'
            })

            applyVisitSort()
        }

        function applyVisitSort() {
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
                var da = a.getAttribute('data-date') || ''
                var db = b.getAttribute('data-date') || ''
                var aa = parseFloat(a.getAttribute('data-amount') || '0')
                var ab = parseFloat(b.getAttribute('data-amount') || '0')

                if (value === 'amount_asc' || value === 'amount_desc') {
                    if (aa < ab) return value === 'amount_asc' ? -1 : 1
                    if (aa > ab) return value === 'amount_asc' ? 1 : -1
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
            searchInput.addEventListener('input', applyVisitFilters)
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', applyVisitSort)
        }

        applyVisitFilters()
    })
</script>
