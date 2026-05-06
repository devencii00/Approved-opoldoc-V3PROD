<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Transactions records</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Billing</span>
    </div>
    <p class="text-xs text-slate-500 mb-3">
        Recent billing transactions with links to their related visits and patients.
    </p>

    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
        <div class="flex-1">
            <label for="admin_txn_search" class="block text-[0.7rem] text-slate-600 mb-1">Search</label>
            <input id="admin_txn_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Transaction ID, patient, reference number">
        </div>
        <div class="w-full md:w-40">
            <label for="admin_txn_status_filter" class="block text-[0.7rem] text-slate-600 mb-1">Payment status</label>
            <select id="admin_txn_status_filter" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="">All</option>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="w-full md:w-40">
            <label for="admin_txn_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
            <select id="admin_txn_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
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
                    <th class="py-2 pr-4 font-semibold">Patient</th>
                    <th class="py-2 pr-4 font-semibold">Amount</th>
                    <th class="py-2 pr-4 font-semibold">Status</th>
                    <th class="py-2 pr-4 font-semibold">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($adminRecentTransactions ?? [] as $transaction)
                    @php
                        $appointment = $transaction->appointment;
                        $patient = $appointment ? $appointment->patient : null;
                        $patientName = $patient ? trim(($patient->firstname ?? '') . ' ' . ($patient->lastname ?? '')) : '';
                        $status = strtolower($transaction->payment_status ?? '');
                        $statusColors = [
                            'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                            'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-100',
                        ];
                        $statusClass = $statusColors[$status] ?? 'bg-slate-50 text-slate-600 border-slate-100';
                    @endphp
                    <tr class="border-b border-slate-50 last:border-0 admin-txn-row"
                        data-txn-id="{{ $transaction->transaction_id }}"
                        data-patient="{{ strtolower($patientName) }}"
                        data-ref="{{ strtolower($transaction->reference_number ?? '') }}"
                        data-status="{{ $status }}"
                        data-date="{{ optional($transaction->transaction_datetime)->format('Y-m-d') ?? '' }}"
                        data-amount="{{ (float) $transaction->amount }}">
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                            #{{ $transaction->transaction_id }}
                            @if ($transaction->reference_number)
                                <div class="text-[0.7rem] text-slate-400">
                                    Ref: {{ $transaction->reference_number }}
                                </div>
                            @endif
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                            @if ($patientName)
                                {{ $patientName }}
                            @else
                                <span class="text-slate-400">Unknown</span>
                            @endif
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                            ₱{{ number_format((float) $transaction->amount, 2) }}
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem]">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.68rem] font-medium border {{ $statusClass }}">
                                {{ $status ? ucfirst($status) : 'Unknown' }}
                            </span>
                        </td>
                        <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                            {{ optional($transaction->transaction_datetime)->format('Y-m-d') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-[0.78rem] text-slate-400">
                            No transactions recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('admin_txn_search')
        var statusFilter = document.getElementById('admin_txn_status_filter')
        var sortSelect = document.getElementById('admin_txn_sort')
        var rows = Array.prototype.slice.call(document.querySelectorAll('.admin-txn-row'))

        function applyTxnFilters() {
            var query = searchInput ? searchInput.value.toLowerCase().trim() : ''
            var status = statusFilter ? statusFilter.value.toLowerCase() : ''

            rows.forEach(function (row) {
                var id = row.getAttribute('data-txn-id') || ''
                var patient = row.getAttribute('data-patient') || ''
                var ref = row.getAttribute('data-ref') || ''
                var rowStatus = row.getAttribute('data-status') || ''

                var matchesSearch = true
                if (query) {
                    matchesSearch =
                        ('#' + id).indexOf(query) !== -1 ||
                        patient.indexOf(query) !== -1 ||
                        ref.indexOf(query) !== -1
                }

                var matchesStatus = true
                if (status) {
                    matchesStatus = rowStatus === status
                }

                row.style.display = matchesSearch && matchesStatus ? '' : 'none'
            })

            applyTxnSort()
        }

        function applyTxnSort() {
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
            searchInput.addEventListener('input', applyTxnFilters)
        }
        if (statusFilter) {
            statusFilter.addEventListener('change', applyTxnFilters)
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', applyTxnSort)
        }

        applyTxnFilters()
    })
</script>
