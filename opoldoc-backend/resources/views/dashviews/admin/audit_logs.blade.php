<div class="bg-white border border-slate-200 rounded-[18px] shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="grid grid-cols-2 border-b border-slate-200">
        <button type="button" id="adminLogsTabAudit" class="px-4 py-3 text-xs font-semibold text-white bg-cyan-600 border-b-2 border-cyan-700 rounded-tl-[18px]">Logs</button>
        <button type="button" id="adminLogsTabAccess" class="px-4 py-3 text-xs font-semibold text-slate-900 bg-white hover:bg-slate-50 border-l border-slate-200 rounded-tr-[18px]">Record access logs</button>
    </div>

    <div class="p-5 pb-0">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 id="adminLogsHeaderTitle" class="text-sm font-semibold text-slate-900">Logs</h2>
                <p id="adminLogsHeaderDesc" class="text-xs text-slate-500 mt-1">View recent system activities and filter by user or action.</p>
            </div>
            <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Activity</span>
        </div>
    </div>

    <div id="adminLogsPanelAudit" class="p-5 pt-0">
        <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
            <div class="flex-1">
                <label for="admin_audit_search" class="block text-[0.7rem] text-slate-600 mb-1">Filter by user / action</label>
                <input id="admin_audit_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type a user email or action">
            </div>
            <div class="w-full md:w-40">
                <label for="admin_audit_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
                <select id="admin_audit_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="date_desc">Newest first</option>
                    <option value="date_asc">Oldest first</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto scrollbar-hidden">
            <table class="min-w-full text-left text-xs text-slate-600">
                <thead>
                    <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                        <th class="py-2 pr-4 font-semibold">When</th>
                        <th class="py-2 pr-4 font-semibold">User</th>
                        <th class="py-2 pr-4 font-semibold">Action</th>
                        <th class="py-2 pr-4 font-semibold">Record</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($adminRecentAuditLogs ?? [] as $log)
                        <tr class="border-b border-slate-50 last:border-0 admin-audit-row"
                            data-user="{{ strtolower($log->user ? $log->user->email : '') }}"
                            data-table="{{ strtolower($log->table_name ?? '') }}"
                            data-action="{{ strtolower($log->action ?? '') }}"
                            data-date="{{ optional($log->created_at)->format('Y-m-d H:i') ?? '' }}">
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                                {{ optional($log->created_at)->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                                @if ($log->user)
                                    {{ $log->user->email }}
                                @else
                                    <span class="text-slate-400">System</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                                {{ $log->action ?? 'Action' }}
                            </td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                                {{ $log->table_name }} #{{ $log->record_id }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-[0.78rem] text-slate-400">
                                No audit logs recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="adminLogsPanelAccess" class="hidden p-5 pt-0">
        <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
            <div class="flex-1">
                <label for="admin_access_search" class="block text-[0.7rem] text-slate-600 mb-1">Search record access</label>
                <input id="admin_access_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="User, table or record ID">
            </div>
            <div class="w-full md:w-40">
                <label for="admin_access_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
                <select id="admin_access_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="date_desc">Newest first</option>
                    <option value="date_asc">Oldest first</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto scrollbar-hidden">
            <table class="min-w-full text-left text-xs text-slate-600">
                <thead>
                    <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                        <th class="py-2 pr-4 font-semibold">When</th>
                        <th class="py-2 pr-4 font-semibold">User</th>
                        <th class="py-2 pr-4 font-semibold">Record</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($adminRecentAccessLogs ?? [] as $access)
                        <tr class="border-b border-slate-50 last:border-0 admin-access-row"
                            data-user="{{ strtolower($access->user ? $access->user->email : '') }}"
                            data-table="{{ strtolower($access->table_name ?? '') }}"
                            data-record="{{ $access->record_id }}"
                            data-date="{{ optional($access->created_at)->format('Y-m-d H:i') ?? '' }}">
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                                {{ optional($access->created_at)->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-700">
                                @if ($access->user)
                                    {{ $access->user->email }}
                                @else
                                    <span class="text-slate-400">System</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-[0.78rem] text-slate-500">
                                {{ $access->table_name }} #{{ $access->record_id }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-[0.78rem] text-slate-400">
                                No record access logs recorded yet.
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
        var tabAudit = document.getElementById('adminLogsTabAudit')
        var tabAccess = document.getElementById('adminLogsTabAccess')
        var panelAudit = document.getElementById('adminLogsPanelAudit')
        var panelAccess = document.getElementById('adminLogsPanelAccess')
        var headerTitle = document.getElementById('adminLogsHeaderTitle')
        var headerDesc = document.getElementById('adminLogsHeaderDesc')

        function setTabButtonActive(btn, isActive, isLeft) {
            if (!btn) return
            btn.classList.remove('bg-cyan-600', 'text-white', 'border-b-2', 'border-cyan-700', 'bg-white', 'text-slate-900', 'hover:bg-slate-50', 'border-transparent')
            
            if (isActive) {
                btn.classList.add('bg-cyan-600', 'text-white', 'border-b-2', 'border-cyan-700')
            } else {
                btn.classList.add('bg-white', 'text-slate-900', 'hover:bg-slate-50', 'border-b-2', 'border-transparent')
            }
        }

        function setActiveTab(key) {
            var isAudit = key !== 'access'
            if (panelAudit) panelAudit.classList.toggle('hidden', !isAudit)
            if (panelAccess) panelAccess.classList.toggle('hidden', isAudit)
            setTabButtonActive(tabAudit, isAudit, true)
            setTabButtonActive(tabAccess, !isAudit, false)

            if (headerTitle && headerDesc) {
                if (isAudit) {
                    headerTitle.textContent = 'Logs'
                    headerDesc.textContent = 'View recent system activities and filter by user or action.'
                } else {
                    headerTitle.textContent = 'Record access logs'
                    headerDesc.textContent = 'Track when specific records and tables were viewed by users.'
                }
            }

            try {
                localStorage.setItem('admin_logs_tab', isAudit ? 'audit' : 'access')
            } catch (e) {}
        }

        if (tabAudit) tabAudit.addEventListener('click', function () { setActiveTab('audit') })
        if (tabAccess) tabAccess.addEventListener('click', function () { setActiveTab('access') })

        var auditSearch = document.getElementById('admin_audit_search')
        var auditSort = document.getElementById('admin_audit_sort')
        var auditRows = Array.prototype.slice.call(document.querySelectorAll('.admin-audit-row'))

        function applyAuditFilters() {
            var query = auditSearch ? auditSearch.value.toLowerCase().trim() : ''

            auditRows.forEach(function (row) {
                var user = row.getAttribute('data-user') || ''
                var table = row.getAttribute('data-table') || ''
                var action = row.getAttribute('data-action') || ''

                var matches = true
                if (query) {
                    matches =
                        user.indexOf(query) !== -1 ||
                        table.indexOf(query) !== -1 ||
                        action.indexOf(query) !== -1
                }

                row.style.display = matches ? '' : 'none'
            })

            applyAuditSort()
        }

        function applyAuditSort() {
            if (!auditSort) {
                return
            }
            var value = auditSort.value
            var tbody = auditRows.length ? auditRows[0].parentNode : null
            if (!tbody) {
                return
            }

            var visibleRows = auditRows.filter(function (row) {
                return row.style.display !== 'none'
            })

            visibleRows.sort(function (a, b) {
                var da = a.getAttribute('data-date') || ''
                var db = b.getAttribute('data-date') || ''

                if (da < db) return value === 'date_asc' ? -1 : 1
                if (da > db) return value === 'date_asc' ? 1 : -1
                return 0
            })

            visibleRows.forEach(function (row) {
                tbody.appendChild(row)
            })
        }

        if (auditSearch) {
            auditSearch.addEventListener('input', applyAuditFilters)
        }
        if (auditSort) {
            auditSort.addEventListener('change', applyAuditSort)
        }

        applyAuditFilters()

        var accessSearch = document.getElementById('admin_access_search')
        var accessSort = document.getElementById('admin_access_sort')
        var accessRows = Array.prototype.slice.call(document.querySelectorAll('.admin-access-row'))

        function applyAccessFilters() {
            var query = accessSearch ? accessSearch.value.toLowerCase().trim() : ''

            accessRows.forEach(function (row) {
                var user = row.getAttribute('data-user') || ''
                var table = row.getAttribute('data-table') || ''
                var record = row.getAttribute('data-record') || ''

                var matches = true
                if (query) {
                    matches =
                        user.indexOf(query) !== -1 ||
                        table.indexOf(query) !== -1 ||
                        ('#' + record).indexOf(query) !== -1
                }

                row.style.display = matches ? '' : 'none'
            })

            applyAccessSort()
        }

        function applyAccessSort() {
            if (!accessSort) {
                return
            }
            var value = accessSort.value
            var tbody = accessRows.length ? accessRows[0].parentNode : null
            if (!tbody) {
                return
            }

            var visibleRows = accessRows.filter(function (row) {
                return row.style.display !== 'none'
            })

            visibleRows.sort(function (a, b) {
                var da = a.getAttribute('data-date') || ''
                var db = b.getAttribute('data-date') || ''

                if (da < db) return value === 'date_asc' ? -1 : 1
                if (da > db) return value === 'date_asc' ? 1 : -1
                return 0
            })

            visibleRows.forEach(function (row) {
                tbody.appendChild(row)
            })
        }

        if (accessSearch) {
            accessSearch.addEventListener('input', applyAccessFilters)
        }
        if (accessSort) {
            accessSort.addEventListener('change', applyAccessSort)
        }

        applyAccessFilters()

        var initial = 'audit'
        try {
            var stored = localStorage.getItem('admin_logs_tab')
            if (stored === 'access') initial = 'access'
        } catch (e) {}
        setActiveTab(initial)
    })
</script>
