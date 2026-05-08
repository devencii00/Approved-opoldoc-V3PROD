<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Reports & analytics</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Summary</span>
    </div>
    <p class="text-xs text-slate-500 mb-3">
        Transactions, revenue, appointment analytics, and basic no-show tracking for the clinic.
    </p>

    @php
        $metrics = $adminMetrics ?? [];
        $reports = $adminReports ?? [];
        $recentTransactions = $adminRecentTransactions ?? collect();
    @endphp

    <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <label for="admin_analytics_focus" class="block text-[0.7rem] text-slate-600 mb-1">Focus</label>
            <select id="admin_analytics_focus" class="w-full md:w-56 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="all">All</option>
                <option value="patients">Patients</option>
                <option value="staff">Staff</option>
                <option value="compliance">Compliance</option>
            </select>
        </div>
        <div class="text-[0.72rem] text-slate-500 md:text-right">
            Updated based on live system records.
        </div>
    </div>

    <div class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 admin-analytics-card" data-group="patients">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[0.78rem] text-slate-500">Total patients</span>
                <x-lucide-users class="w-[17px] h-[17px] text-cyan-600" />
            </div>
            <div class="font-serif font-bold text-xl text-slate-900">
                {{ number_format((int) ($metrics['patientCount'] ?? 0)) }}
            </div>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 admin-analytics-card" data-group="staff">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[0.78rem] text-slate-500">Active doctors</span>
                <x-lucide-stethoscope class="w-[17px] h-[17px] text-cyan-600" />
            </div>
            <div class="font-serif font-bold text-xl text-slate-900">
                {{ number_format((int) ($metrics['doctorCount'] ?? 0)) }}
            </div>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 admin-analytics-card" data-group="compliance">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[0.78rem] text-slate-500">Pending verifications</span>
                <x-lucide-badge-check class="w-[17px] h-[17px] text-amber-500" />
            </div>
            <div class="font-serif font-bold text-xl text-slate-900">
                {{ number_format((int) ($metrics['pendingVerificationsCount'] ?? 0)) }}
            </div>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 admin-analytics-card" data-group="compliance">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[0.78rem] text-slate-500">Total audit entries</span>
                <x-lucide-folder class="w-[17px] h-[17px] text-slate-600" />
            </div>
            <div class="font-serif font-bold text-xl text-slate-900">
                {{ number_format((int) ($metrics['recentLogsCount'] ?? 0)) }}
            </div>
        </div>
    </div>

    <div class="grid gap-4 grid-cols-1 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <div class="border border-slate-100 rounded-2xl overflow-hidden bg-white shadow-sm h-fit">
            <div class="flex w-full bg-white border-b border-slate-100">
                <button id="adminReportsTabTransactions" type="button" class="flex-1 px-4 py-3 text-xs font-semibold border-r border-slate-100 bg-slate-900 text-white transition-colors">
                    Transactions
                </button>
                <button id="adminReportsTabAppointments" type="button" class="flex-1 px-4 py-3 text-xs font-semibold bg-white text-slate-700 hover:bg-slate-50 transition-colors">
                    Appointments
                </button>
            </div>

            <div id="adminReportsPanelTransactions" class="p-4">
                <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between mb-3">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center flex-wrap">
                        <div>
                            <label for="admin_txn_search" class="block text-[0.7rem] text-slate-600 mb-1">Search</label>
                            <input id="admin_txn_search" type="text" class="w-full sm:w-40 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Date, mode, status…" />
                        </div>
                        <div>
                            <label for="admin_txn_date_from" class="block text-[0.7rem] text-slate-600 mb-1">From</label>
                            <input id="admin_txn_date_from" type="date" class="w-full sm:w-36 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" />
                        </div>
                        <div>
                            <label for="admin_txn_date_to" class="block text-[0.7rem] text-slate-600 mb-1">To</label>
                            <input id="admin_txn_date_to" type="date" class="w-full sm:w-36 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" />
                        </div>
                        <div>
                            <label for="admin_txn_mode" class="block text-[0.7rem] text-slate-600 mb-1">Mode</label>
                            <select id="admin_txn_mode" class="w-full sm:w-32 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                                <option value="all">All</option>
                                <option value="cash">Cash</option>
                                <option value="hmo">HMO</option>
                            </select>
                        </div>
                        <div>
                            <label for="admin_txn_status" class="block text-[0.7rem] text-slate-600 mb-1">Status</label>
                            <select id="admin_txn_status" class="w-full sm:w-32 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                                <option value="all">All</option>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </div>
                    <div class="md:text-right shrink-0">
                        <label for="admin_txn_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
                        <select id="admin_txn_sort" class="w-full md:w-48 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                            <option value="when_desc">Newest</option>
                            <option value="when_asc">Oldest</option>
                            <option value="amount_desc">Amount (high)</option>
                            <option value="amount_asc">Amount (low)</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto scrollbar-hidden">
                    <table class="min-w-full text-left text-xs text-slate-600">
                        <thead>
                            <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                                <th class="py-2 pr-4 font-semibold">When</th>
                                <th class="py-2 pr-4 font-semibold">Amount</th>
                                <th class="py-2 pr-4 font-semibold">Mode</th>
                                <th class="py-2 pr-4 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody id="admin_txn_tbody">
                            @forelse ($recentTransactions as $txn)
                                @php
                                    $txnTimestamp = optional($txn->transaction_datetime)->getTimestamp() ?? 0;
                                    $txnStatusLower = strtolower($txn->payment_status ?? '');
                                @endphp
                                <tr class="border-b border-slate-50 last:border-0 admin-txn-row" data-when="{{ $txnTimestamp }}" data-amount="{{ (float) ($txn->amount ?? 0) }}" data-mode="{{ strtolower((string) ($txn->payment_mode ?? '')) }}" data-status="{{ $txnStatusLower }}">
                                    <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                                        {{ optional($txn->transaction_datetime)->format('Y-m-d H:i') ?? '—' }}
                                    </td>
                                    <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                                        ₱{{ number_format((float) ($txn->amount ?? 0), 2) }}
                                    </td>
                                    <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                                        {{ $txn->payment_mode ?? '—' }}
                                    </td>
                                    <td class="py-2 pr-4 text-[0.78rem]">
                                        @php
                                            $status = strtolower($txn->payment_status ?? '');
                                            $statusLabel = $txn->payment_status ?? '—';
                                            $statusClasses = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-[0.68rem] font-semibold ';
                                            if ($status === 'paid') {
                                                $statusClasses .= 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                                            } elseif ($status === 'pending') {
                                                $statusClasses .= 'bg-amber-50 text-amber-700 border border-amber-100';
                                            } elseif ($status === 'failed') {
                                                $statusClasses .= 'bg-rose-50 text-rose-700 border border-rose-100';
                                            } else {
                                                $statusClasses .= 'bg-slate-50 text-slate-600 border border-slate-100';
                                            }
                                        @endphp
                                        <span class="{{ $statusClasses }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr class="admin-txn-empty">
                                    <td colspan="4" class="py-4 text-center text-[0.78rem] text-slate-400">
                                        No transactions recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="admin_txn_empty_filtered" class="hidden">
                                <td colspan="4" class="py-4 text-center text-[0.78rem] text-slate-400">
                                    No results.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="adminReportsPanelAppointments" class="p-4 hidden">
                <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between mb-3">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center flex-wrap">
                        <div>
                            <label for="admin_appt_search" class="block text-[0.7rem] text-slate-600 mb-1">Search</label>
                            <input id="admin_appt_search" type="text" class="w-full sm:w-40 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Search…" />
                        </div>
                        <div>
                            <label for="admin_appt_type_filter" class="block text-[0.7rem] text-slate-600 mb-1">Type</label>
                            <select id="admin_appt_type_filter" class="w-full sm:w-32 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                                <option value="all">All</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="walk-in">Walk-in</option>
                            </select>
                        </div>
                        <div>
                            <label for="admin_appt_status_filter" class="block text-[0.7rem] text-slate-600 mb-1">Status</label>
                            <select id="admin_appt_status_filter" class="w-full sm:w-32 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                                <option value="all">All</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no_show">No-show</option>
                            </select>
                        </div>
                        <div>
                            <label for="admin_appt_filter" class="block text-[0.7rem] text-slate-600 mb-1">Count Filter</label>
                            <select id="admin_appt_filter" class="w-full sm:w-32 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                                <option value="all">All</option>
                                <option value="nonzero">Non-zero only</option>
                            </select>
                        </div>
                    </div>
                    <div class="md:text-right shrink-0">
                        <label for="admin_appt_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
                        <select id="admin_appt_sort" class="w-full md:w-48 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                            <option value="count_desc">Count (high)</option>
                            <option value="count_asc">Count (low)</option>
                            <option value="type_asc">Type (A–Z)</option>
                            <option value="type_desc">Type (Z–A)</option>
                            <option value="status_asc">Status (A–Z)</option>
                            <option value="status_desc">Status (Z–A)</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto scrollbar-hidden">
                    <table class="min-w-full text-left text-xs text-slate-600">
                        <thead>
                            <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                                <th class="py-2 pr-4 font-semibold">Type</th>
                                <th class="py-2 pr-4 font-semibold">Status</th>
                                <th class="py-2 pr-4 font-semibold">Count</th>
                            </tr>
                        </thead>
                        <tbody id="admin_appt_tbody">
                            @php
                                $statusRows = $reports['appointmentsByStatusToday'] ?? collect();
                            @endphp
                            @forelse ($statusRows as $row)
                                @php
                                    $status = $row->status ?? 'unknown';
                                    $statusLabel = ucfirst(str_replace('_', ' ', $status));
                                    $type = $row->appointment_type ?? 'unknown';
                                    $typeLabel = ucfirst(str_replace('_', '-', $type));
                                    $countValue = (int) ($row->total_count ?? 0);
                                @endphp
                                <tr class="border-b border-slate-50 last:border-0 admin-appt-row" data-status="{{ strtolower((string) $statusLabel) }}" data-type="{{ strtolower((string) $typeLabel) }}" data-count="{{ $countValue }}">
                                    <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                                        {{ $typeLabel }}
                                    </td>
                                    <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                                        {{ $statusLabel }}
                                    </td>
                                    <td class="py-2 pr-4 text-[0.78rem] text-slate-900">
                                        {{ $countValue }}
                                    </td>
                                </tr>
                            @empty
                                <tr class="admin-appt-empty">
                                    <td colspan="3" class="py-4 text-center text-[0.78rem] text-slate-400">
                                        No appointments recorded for today.
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="admin_appt_empty_filtered" class="hidden">
                                <td colspan="3" class="py-4 text-center text-[0.78rem] text-slate-400">
                                    No results.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid gap-4 grid-cols-1">
            <div class="border border-slate-100 rounded-2xl p-4 flex flex-col h-full">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-semibold text-slate-900">Revenue</h3>
                    <span class="text-[0.68rem] text-slate-400 uppercase tracking-widest">Summary</span>
                </div>
                <div class="space-y-3 flex-1 flex flex-col justify-center">
                    <div class="flex items-center justify-between rounded-2xl bg-white border border-slate-100 px-4 py-3">
                        <div>
                            <p class="text-[0.7rem] text-slate-500 mb-0.5">Today</p>
                            <p class="font-serif font-bold text-lg text-slate-900">
                                ₱{{ number_format((float) ($metrics['revenueToday'] ?? 0), 2) }}
                            </p>
                        </div>
                        <x-lucide-calendar class="w-[22px] h-[22px] text-cyan-600" />
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-white border border-slate-100 px-4 py-3">
                        <div>
                            <p class="text-[0.7rem] text-slate-500 mb-0.5">This month</p>
                            <p class="font-serif font-bold text-lg text-slate-900">
                                ₱{{ number_format((float) ($metrics['revenueThisMonth'] ?? 0), 2) }}
                            </p>
                        </div>
                        <x-lucide-chart-column class="w-[22px] h-[22px] text-emerald-600" />
                    </div>
                </div>
            </div>

            <div class="border border-slate-100 rounded-2xl p-4 flex flex-col h-full">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-semibold text-slate-900">No-show tracking</h3>
                    <span class="text-[0.68rem] text-slate-400 uppercase tracking-widest">Today</span>
                </div>
                @php
                    $noShowToday = (int) ($reports['noShowToday'] ?? 0);
                @endphp
                <div class="flex items-center justify-between rounded-2xl bg-white border border-slate-100 px-4 py-3 flex-1">
                    <div>
                        <p class="text-[0.7rem] text-slate-500 mb-0.5">No-shows today</p>
                        <p class="font-serif font-bold text-2xl text-slate-900">
                            {{ $noShowToday }}
                        </p>
                    </div>
                    <x-lucide-calendar-x class="w-[28px] h-[28px] text-amber-500" />
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var focusSelect = document.getElementById('admin_analytics_focus')
        var cards = Array.prototype.slice.call(document.querySelectorAll('.admin-analytics-card'))

        var tabTransactions = document.getElementById('adminReportsTabTransactions')
        var tabAppointments = document.getElementById('adminReportsTabAppointments')
        var panelTransactions = document.getElementById('adminReportsPanelTransactions')
        var panelAppointments = document.getElementById('adminReportsPanelAppointments')

        var txnSearch = document.getElementById('admin_txn_search')
        var txnDateFrom = document.getElementById('admin_txn_date_from')
        var txnDateTo = document.getElementById('admin_txn_date_to')
        var txnMode = document.getElementById('admin_txn_mode')
        var txnStatus = document.getElementById('admin_txn_status')
        var txnSort = document.getElementById('admin_txn_sort')
        var txnBody = document.getElementById('admin_txn_tbody')
        var txnEmptyFiltered = document.getElementById('admin_txn_empty_filtered')

        var apptSearch = document.getElementById('admin_appt_search')
        var apptTypeFilter = document.getElementById('admin_appt_type_filter')
        var apptStatusFilter = document.getElementById('admin_appt_status_filter')
        var apptFilter = document.getElementById('admin_appt_filter')
        var apptSort = document.getElementById('admin_appt_sort')
        var apptBody = document.getElementById('admin_appt_tbody')
        var apptEmptyFiltered = document.getElementById('admin_appt_empty_filtered')

        function setTabButtonActive(btn, isActive, isLeft) {
            if (!btn) return
            btn.classList.remove('bg-slate-900', 'text-white', 'bg-white', 'text-slate-700', 'hover:bg-slate-50', 'bg-cyan-600')
            if (isLeft) {
                btn.classList.toggle('border-r', true)
                btn.classList.toggle('border-slate-100', true)
            }
            if (isActive) {
                btn.classList.add('bg-cyan-600', 'text-white')
            } else {
                btn.classList.add('bg-white', 'text-slate-700', 'hover:bg-slate-50')
            }
        }

        function setActiveReportTab(key) {
            var isTransactions = key !== 'appointments'
            if (panelTransactions) panelTransactions.classList.toggle('hidden', !isTransactions)
            if (panelAppointments) panelAppointments.classList.toggle('hidden', isTransactions)
            setTabButtonActive(tabTransactions, isTransactions, true)
            setTabButtonActive(tabAppointments, !isTransactions, false)
            try {
                localStorage.setItem('admin_reports_tab', isTransactions ? 'transactions' : 'appointments')
            } catch (e) {}
        }

        function normalizeText(value) {
            return (value || '').toString().toLowerCase().trim()
        }

        function applyTransactionFilters() {
            if (!txnBody) return
            var q = normalizeText(txnSearch ? txnSearch.value : '')
            var dateFrom = txnDateFrom && txnDateFrom.value ? new Date(txnDateFrom.value).getTime() / 1000 : null
            var dateTo = txnDateTo && txnDateTo.value ? new Date(txnDateTo.value).getTime() / 1000 : null
            var mode = normalizeText(txnMode ? txnMode.value : 'all')
            var status = normalizeText(txnStatus ? txnStatus.value : 'all')
            var rows = Array.prototype.slice.call(txnBody.querySelectorAll('tr.admin-txn-row'))
            var visibleCount = 0

            rows.forEach(function (row) {
                var rowTimestamp = parseInt(row.getAttribute('data-when') || '0', 10)
                var rowMode = normalizeText(row.getAttribute('data-mode'))
                var rowStatus = normalizeText(row.getAttribute('data-status'))
                var rowText = normalizeText(row.textContent)

                var matchesQuery = !q || rowText.indexOf(q) !== -1
                var matchesMode = mode === 'all' || rowMode === mode
                var matchesStatus = status === 'all' || rowStatus === status

                var matchesDate = true
                if (dateFrom && rowTimestamp < dateFrom) matchesDate = false
                if (dateTo && rowTimestamp > dateTo + 86400) matchesDate = false

                var show = matchesQuery && matchesMode && matchesStatus && matchesDate
                row.classList.toggle('hidden', !show)
                if (show) visibleCount++
            })

            if (txnEmptyFiltered) {
                var hasRows = rows.length > 0
                txnEmptyFiltered.classList.toggle('hidden', !hasRows || visibleCount > 0)
            }
        }

        function sortTransactionRows() {
            if (!txnBody) return
            var rows = Array.prototype.slice.call(txnBody.querySelectorAll('tr.admin-txn-row'))
            if (!rows.length) return

            var sortKey = (txnSort ? txnSort.value : 'when_desc') || 'when_desc'
            var factor = sortKey.endsWith('_asc') ? 1 : -1
            var type = sortKey.replace(/_(asc|desc)$/, '')

            rows.sort(function (a, b) {
                var av = 0
                var bv = 0
                if (type === 'amount') {
                    av = parseFloat(a.getAttribute('data-amount') || '0') || 0
                    bv = parseFloat(b.getAttribute('data-amount') || '0') || 0
                } else {
                    av = parseInt(a.getAttribute('data-when') || '0', 10) || 0
                    bv = parseInt(b.getAttribute('data-when') || '0', 10) || 0
                }
                if (av === bv) return 0
                return av > bv ? factor : -factor
            })

            rows.forEach(function (row) {
                txnBody.appendChild(row)
            })
        }

        function applyAppointmentFilters() {
            if (!apptBody) return
            var q = normalizeText(apptSearch ? apptSearch.value : '')
            var typeFilter = normalizeText(apptTypeFilter ? apptTypeFilter.value : 'all')
            var statusFilter = normalizeText(apptStatusFilter ? apptStatusFilter.value : 'all')
            var filter = normalizeText(apptFilter ? apptFilter.value : 'all')
            var rows = Array.prototype.slice.call(apptBody.querySelectorAll('tr.admin-appt-row'))
            var visibleCount = 0

            rows.forEach(function (row) {
                var statusText = normalizeText(row.getAttribute('data-status'))
                var typeText = normalizeText(row.getAttribute('data-type'))
                var countVal = parseInt(row.getAttribute('data-count') || '0', 10) || 0

                var matchesQuery = !q || statusText.indexOf(q) !== -1 || typeText.indexOf(q) !== -1
                var matchesType = typeFilter === 'all' || typeText === typeFilter
                var matchesStatus = statusFilter === 'all' || statusText === statusFilter
                var matchesFilter = filter === 'all' || countVal > 0

                var show = matchesQuery && matchesType && matchesStatus && matchesFilter
                row.classList.toggle('hidden', !show)
                if (show) visibleCount++
            })

            if (apptEmptyFiltered) {
                var hasRows = rows.length > 0
                apptEmptyFiltered.classList.toggle('hidden', !hasRows || visibleCount > 0)
            }
        }

        function sortAppointmentRows() {
            if (!apptBody) return
            var rows = Array.prototype.slice.call(apptBody.querySelectorAll('tr.admin-appt-row'))
            if (!rows.length) return
            var sortKey = (apptSort ? apptSort.value : 'count_desc') || 'count_desc'
            var factor = sortKey.endsWith('_asc') ? 1 : -1
            var type = sortKey.replace(/_(asc|desc)$/, '')

            rows.sort(function (a, b) {
                if (type === 'status') {
                    var as = normalizeText(a.getAttribute('data-status'))
                    var bs = normalizeText(b.getAttribute('data-status'))
                    if (as === bs) return 0
                    return as > bs ? factor : -factor
                }
                if (type === 'type') {
                    var at = normalizeText(a.getAttribute('data-type'))
                    var bt = normalizeText(b.getAttribute('data-type'))
                    if (at === bt) return 0
                    return at > bt ? factor : -factor
                }
                var av = parseInt(a.getAttribute('data-count') || '0', 10) || 0
                var bv = parseInt(b.getAttribute('data-count') || '0', 10) || 0
                if (av === bv) return 0
                return av > bv ? factor : -factor
            })

            rows.forEach(function (row) {
                apptBody.appendChild(row)
            })
        }

        function applyAnalyticsFilter() {
            var value = focusSelect ? focusSelect.value : 'all'
            cards.forEach(function (card) {
                var group = card.getAttribute('data-group') || ''
                if (value === 'all') {
                    card.style.display = ''
                } else {
                    card.style.display = group === value ? '' : 'none'
                }
            })
        }

        if (focusSelect) {
            focusSelect.addEventListener('change', applyAnalyticsFilter)
        }

        if (tabTransactions) {
            tabTransactions.addEventListener('click', function () { setActiveReportTab('transactions') })
        }
        if (tabAppointments) {
            tabAppointments.addEventListener('click', function () { setActiveReportTab('appointments') })
        }

        if (txnSort) txnSort.addEventListener('change', function () { sortTransactionRows(); applyTransactionFilters() })
        if (txnSearch) txnSearch.addEventListener('input', applyTransactionFilters)
        if (txnDateFrom) txnDateFrom.addEventListener('change', applyTransactionFilters)
        if (txnDateTo) txnDateTo.addEventListener('change', applyTransactionFilters)
        if (txnMode) txnMode.addEventListener('change', applyTransactionFilters)
        if (txnStatus) txnStatus.addEventListener('change', applyTransactionFilters)

        if (apptSort) apptSort.addEventListener('change', function () { sortAppointmentRows(); applyAppointmentFilters() })
        if (apptSearch) apptSearch.addEventListener('input', applyAppointmentFilters)
        if (apptTypeFilter) apptTypeFilter.addEventListener('change', applyAppointmentFilters)
        if (apptStatusFilter) apptStatusFilter.addEventListener('change', applyAppointmentFilters)
        if (apptFilter) apptFilter.addEventListener('change', applyAppointmentFilters)

        applyAnalyticsFilter()

        sortTransactionRows()
        applyTransactionFilters()

        sortAppointmentRows()
        applyAppointmentFilters()

        var savedTab = null
        try {
            savedTab = localStorage.getItem('admin_reports_tab')
        } catch (e) {}
        setActiveReportTab(savedTab === 'appointments' ? 'appointments' : 'transactions')
    })
</script>