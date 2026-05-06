<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-sm font-semibold text-slate-900">Verification Oversight</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Patients</span>
    </div>
    <p class="text-xs text-slate-500 mb-4">
        Review verification requests, view uploaded documents, override status, and audit changes.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <div class="text-[0.68rem] uppercase tracking-widest text-slate-400">Pending</div>
            <div id="admin_verif_stat_pending" class="mt-1 text-xl font-semibold text-slate-900">—</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <div class="text-[0.68rem] uppercase tracking-widest text-slate-400">Approved</div>
            <div id="admin_verif_stat_approved" class="mt-1 text-xl font-semibold text-slate-900">—</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <div class="text-[0.68rem] uppercase tracking-widest text-slate-400">Rejected</div>
            <div id="admin_verif_stat_rejected" class="mt-1 text-xl font-semibold text-slate-900">—</div>
        </div>
    </div>

    <div id="adminVerifError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
        <div class="flex-1">
            <label for="admin_verif_search" class="block text-[0.7rem] text-slate-600 mb-1">Search</label>
            <input id="admin_verif_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Patient name, email, or verification ID">
        </div>
        <div class="w-full md:w-44">
            <label for="admin_verif_status_filter" class="block text-[0.7rem] text-slate-600 mb-1">Status</label>
            <select id="admin_verif_status_filter" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="w-full md:w-44">
            <label for="admin_verif_type_filter" class="block text-[0.7rem] text-slate-600 mb-1">Type</label>
            <select id="admin_verif_type_filter" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="">All</option>
                <option value="senior">Senior</option>
                <option value="pwd">PWD</option>
                <option value="pregnant">Pregnant</option>
            </select>
        </div>
        <div class="w-full md:w-40">
            <label for="admin_verif_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
            <select id="admin_verif_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="date_desc">Newest first</option>
                <option value="date_asc">Oldest first</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto scrollbar-hidden">
        <table class="min-w-full text-left text-xs text-slate-600">
            <thead>
                <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                    <th class="py-2 pr-4 font-semibold">ID</th>
                    <th class="py-2 pr-4 font-semibold">Patient</th>
                    <th class="py-2 pr-4 font-semibold">Type</th>
                    <th class="py-2 pr-4 font-semibold">Status</th>
                    <th class="py-2 pr-4 font-semibold">Uploaded</th>
                    <th class="py-2 pr-4 font-semibold">Verified by</th>
                    <th class="py-2 pr-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody id="admin_verif_table_body">
                <tr>
                    <td colspan="7" class="py-4 text-center text-[0.78rem] text-slate-400">
                        Loading verifications…
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-3 hidden rounded-lg border border-slate-200 bg-slate-50 px-3 py-3" id="admin_verif_logs_panel">
        <div class="flex items-center justify-between mb-2">
            <div class="text-[0.8rem] font-semibold text-slate-900" id="admin_verif_logs_title">Audit logs</div>
            <button type="button" id="admin_verif_logs_close" class="text-[0.72rem] font-semibold text-slate-600 hover:text-slate-900">Close</button>
        </div>
        <div id="admin_verif_logs_body" class="text-[0.78rem] text-slate-700"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var errorBox = document.getElementById('adminVerifError')
        var statPending = document.getElementById('admin_verif_stat_pending')
        var statApproved = document.getElementById('admin_verif_stat_approved')
        var statRejected = document.getElementById('admin_verif_stat_rejected')

        var searchInput = document.getElementById('admin_verif_search')
        var statusFilter = document.getElementById('admin_verif_status_filter')
        var typeFilter = document.getElementById('admin_verif_type_filter')
        var sortSelect = document.getElementById('admin_verif_sort')
        var tableBody = document.getElementById('admin_verif_table_body')

        var logsPanel = document.getElementById('admin_verif_logs_panel')
        var logsTitle = document.getElementById('admin_verif_logs_title')
        var logsBody = document.getElementById('admin_verif_logs_body')
        var logsClose = document.getElementById('admin_verif_logs_close')

        var currentPage = 1
        var lastPayload = null

        function showError(message) {
            if (!errorBox) return
            if (!message) {
                errorBox.classList.add('hidden')
                errorBox.textContent = ''
                return
            }
            errorBox.textContent = message
            errorBox.classList.remove('hidden')
        }

        function statusBadge(status) {
            var key = String(status || '').toLowerCase()
            var map = {
                pending: 'bg-amber-50 text-amber-700 border-amber-100',
                approved: 'bg-emerald-50 text-emerald-700 border-emerald-100',
                rejected: 'bg-rose-50 text-rose-700 border-rose-100'
            }
            var cls = map[key] || 'bg-slate-50 text-slate-600 border-slate-100'
            var label = key ? (key.charAt(0).toUpperCase() + key.slice(1)) : 'Unknown'
            return '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.68rem] font-medium border ' + cls + '">' + label + '</span>'
        }

        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
        }

        function getPatientLabel(v) {
            var p = v && v.patient ? v.patient : null
            if (!p) return 'Unknown'
            var name = ((p.firstname || '') + ' ' + (p.lastname || '')).trim()
            if (name) return name
            if (p.email) return p.email
            return 'Patient #' + p.user_id
        }

        function getVerifierLabel(v) {
            var u = v && v.verifier ? v.verifier : null
            if (!u) return '—'
            var name = ((u.firstname || '') + ' ' + (u.lastname || '')).trim()
            if (name) return name
            if (u.email) return u.email
            return 'User #' + u.user_id
        }

        function loadStats() {
            apiFetch("{{ url('/api/patient-verifications-stats') }}", { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    })
                })
                .then(function (result) {
                    if (!result.ok) return
                    if (statPending) statPending.textContent = String(result.data.pending ?? '0')
                    if (statApproved) statApproved.textContent = String(result.data.approved ?? '0')
                    if (statRejected) statRejected.textContent = String(result.data.rejected ?? '0')
                })
                .catch(function () {})
        }

        function buildQuery(page) {
            var params = []
            params.push('per_page=25')
            params.push('page=' + encodeURIComponent(page || 1))

            var status = statusFilter ? statusFilter.value : ''
            if (status) params.push('status=' + encodeURIComponent(status))

            var type = typeFilter ? typeFilter.value : ''
            if (type) params.push('type=' + encodeURIComponent(type))

            return params.join('&')
        }

        function loadVerifications(page) {
            currentPage = page || 1
            showError('')
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="7" class="py-4 text-center text-[0.78rem] text-slate-400">Loading verifications…</td></tr>'
            }

            apiFetch("{{ url('/api/patient-verifications') }}?" + buildQuery(currentPage), { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        showError('Failed to load verifications.')
                        if (tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="py-4 text-center text-[0.78rem] text-slate-400">No data.</td></tr>'
                        return
                    }
                    lastPayload = result.data
                    renderVerifications()
                })
                .catch(function () {
                    showError('Network error while loading verifications.')
                    if (tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="py-4 text-center text-[0.78rem] text-slate-400">No data.</td></tr>'
                })
        }

        function renderVerifications() {
            if (!tableBody) return
            var payload = lastPayload || {}
            var items = Array.isArray(payload.data) ? payload.data : []

            var query = searchInput ? searchInput.value.toLowerCase().trim() : ''
            if (query) {
                items = items.filter(function (v) {
                    var id = String(v.verification_id || '')
                    var patientLabel = getPatientLabel(v).toLowerCase()
                    var patientEmail = v && v.patient && v.patient.email ? String(v.patient.email).toLowerCase() : ''
                    return ('#' + id).indexOf(query) !== -1 || patientLabel.indexOf(query) !== -1 || patientEmail.indexOf(query) !== -1
                })
            }

            var sort = sortSelect ? sortSelect.value : 'date_desc'
            items.sort(function (a, b) {
                var da = (a.created_at || '')
                var db = (b.created_at || '')
                if (da < db) return sort === 'date_asc' ? -1 : 1
                if (da > db) return sort === 'date_asc' ? 1 : -1
                return 0
            })

            if (!items.length) {
                tableBody.innerHTML = '<tr><td colspan="7" class="py-4 text-center text-[0.78rem] text-slate-400">No verifications found.</td></tr>'
                return
            }

            var html = ''
            items.forEach(function (v) {
                var id = v.verification_id
                var patientLabel = escapeHtml(getPatientLabel(v))
                var type = escapeHtml(v.type || '—')
                var status = v.status || ''
                var uploaded = v.created_at ? escapeHtml(String(v.created_at).slice(0, 10)) : '—'
                var verifier = escapeHtml(getVerifierLabel(v))
                var hasDoc = !!v.document_path

                html += '<tr class="border-b border-slate-50 last:border-0">' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">#' + id + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-700">' + patientLabel + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">' + type + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem]">' + statusBadge(status) + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">' + uploaded + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">' + verifier + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem]">' +
                        '<div class="flex flex-wrap items-center gap-2">' +
                            (hasDoc ? '<button type="button" class="text-[0.72rem] font-semibold text-slate-700 hover:text-slate-900 admin-verif-doc" data-id="' + id + '">View document</button>' : '<span class="text-[0.72rem] font-semibold text-slate-400">No document</span>') +
                            '<button type="button" class="text-[0.72rem] font-semibold text-emerald-700 hover:text-emerald-800 admin-verif-set" data-id="' + id + '" data-status="approved">Approve</button>' +
                            '<button type="button" class="text-[0.72rem] font-semibold text-rose-700 hover:text-rose-800 admin-verif-set" data-id="' + id + '" data-status="rejected">Reject</button>' +
                            '<button type="button" class="text-[0.72rem] font-semibold text-amber-700 hover:text-amber-800 admin-verif-set" data-id="' + id + '" data-status="pending">Set pending</button>' +
                            '<button type="button" class="text-[0.72rem] font-semibold text-cyan-700 hover:text-cyan-800 admin-verif-logs" data-id="' + id + '">Audit logs</button>' +
                        '</div>' +
                    '</td>' +
                '</tr>'
            })

            tableBody.innerHTML = html
            bindRowActions()
        }

        function bindRowActions() {
            var docButtons = document.querySelectorAll('.admin-verif-doc')
            docButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-id')
                    if (!id) return
                    apiFetch("{{ url('/api/patient-verifications') }}/" + id + "/document", { method: 'GET' })
                        .then(function (response) {
                            if (!response.ok) {
                                return Promise.reject(new Error('failed'))
                            }
                            return response.blob()
                        })
                        .then(function (blob) {
                            var url = URL.createObjectURL(blob)
                            window.open(url, '_blank', 'noopener')
                            setTimeout(function () { URL.revokeObjectURL(url) }, 60000)
                        })
                        .catch(function () {
                            showError('Unable to open the document.')
                        })
                })
            })

            var setButtons = document.querySelectorAll('.admin-verif-set')
            setButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-id')
                    var status = this.getAttribute('data-status')
                    if (!id || !status) return
                    var remarks = window.prompt('Remarks (optional)', '') || ''

                    apiFetch("{{ url('/api/patient-verifications') }}/" + id, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ status: status, remarks: remarks })
                    })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                return { ok: response.ok, data: data }
                            })
                        })
                        .then(function (result) {
                            if (!result.ok) {
                                showError('Failed to update verification status.')
                                return
                            }
                            loadStats()
                            loadVerifications(currentPage)
                        })
                        .catch(function () {
                            showError('Network error while updating verification status.')
                        })
                })
            })

            var logsButtons = document.querySelectorAll('.admin-verif-logs')
            logsButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-id')
                    if (!id) return
                    showAuditLogs(id)
                })
            })
        }

        function showAuditLogs(verificationId) {
            if (!logsPanel || !logsBody || !logsTitle) return
            logsTitle.textContent = 'Audit logs — Verification #' + verificationId
            logsBody.textContent = 'Loading audit logs…'
            logsPanel.classList.remove('hidden')

            apiFetch("{{ url('/api/patient-verifications') }}/" + verificationId + "/audit-logs", { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        logsBody.textContent = 'Failed to load audit logs.'
                        return
                    }
                    var logs = Array.isArray(result.data) ? result.data : []
                    if (!logs.length) {
                        logsBody.textContent = 'No audit logs for this verification yet.'
                        return
                    }
                    var html = '<div class="space-y-2">'
                    logs.forEach(function (l) {
                        var user = l.user ? (((l.user.firstname || '') + ' ' + (l.user.lastname || '')).trim() || l.user.email || ('User #' + l.user.user_id)) : '—'
                        var when = l.created_at ? String(l.created_at).replace('T', ' ').slice(0, 19) : ''
                        var details = ''
                        try {
                            var parsed = l.details ? JSON.parse(l.details) : null
                            if (parsed) details = escapeHtml(JSON.stringify(parsed))
                        } catch (e) {
                            details = escapeHtml(l.details || '')
                        }

                        html += '<div class="rounded-lg border border-slate-200 bg-white px-3 py-2">' +
                            '<div class="flex items-center justify-between gap-3">' +
                                '<div class="text-[0.78rem] font-semibold text-slate-900">' + escapeHtml(l.action || 'log') + '</div>' +
                                '<div class="text-[0.72rem] text-slate-500">' + escapeHtml(when) + '</div>' +
                            '</div>' +
                            '<div class="mt-1 text-[0.74rem] text-slate-600"><span class="font-semibold text-slate-700">User:</span> ' + escapeHtml(user) + '</div>' +
                            (details ? '<div class="mt-1 text-[0.74rem] text-slate-600 break-words"><span class="font-semibold text-slate-700">Details:</span> ' + details + '</div>' : '') +
                        '</div>'
                    })
                    html += '</div>'
                    logsBody.innerHTML = html
                })
                .catch(function () {
                    logsBody.textContent = 'Network error while loading audit logs.'
                })
        }

        if (logsClose && logsPanel) {
            logsClose.addEventListener('click', function () {
                logsPanel.classList.add('hidden')
            })
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                renderVerifications()
            })
        }
        if (statusFilter) {
            statusFilter.addEventListener('change', function () {
                loadVerifications(1)
                loadStats()
            })
        }
        if (typeFilter) {
            typeFilter.addEventListener('change', function () {
                loadVerifications(1)
                loadStats()
            })
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                renderVerifications()
            })
        }

        loadStats()
        loadVerifications(1)
    })
</script>
