<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-sm font-semibold text-slate-900">Verification Oversight</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Patients</span>
    </div>
    <p class="text-xs text-slate-500 mb-4">
        Review verification requests, inspect uploaded documents, and decide approval status.
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

<div class="overflow-x-auto overflow-y-auto scrollbar-hidden mb-4 h-[300px]">
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

</div>

<div id="adminVerifDocPanelOverlay" class="hidden fixed inset-0 z-[80] bg-slate-900/40">
    <div id="adminVerifDocPanel" class="absolute top-0 right-0 h-full w-full max-w-[46rem] bg-white border-l border-slate-200 shadow-[-16px_0_40px_rgba(15,23,42,0.2)] transform translate-x-full transition-transform duration-300 ease-out flex flex-col">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-900" id="adminVerifDocPanelTitle">Verification Document</div>
                <div class="text-[0.72rem] text-slate-500 mt-0.5" id="adminVerifDocPanelSubtitle"></div>
            </div>
            <button id="adminVerifDocPanelClose" type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                <x-lucide-x class="w-[18px] h-[18px]" />
            </button>
        </div>
        <div class="p-4 border-b border-slate-100">
            <div class="text-[0.72rem] font-semibold text-slate-600 mb-2 uppercase tracking-wide">Latest Uploaded Document</div>
            <div id="adminVerifDocMainWrap" class="rounded-xl border border-slate-200 bg-slate-50 overflow-hidden h-[16rem] flex items-center justify-center text-[0.78rem] text-slate-500">
                Select a verification record.
            </div>
        </div>
        <div class="p-4 min-h-0 flex-1 overflow-hidden">
            <div class="text-[0.72rem] font-semibold text-slate-600 mb-2 uppercase tracking-wide">Verification History</div>
            <div class="rounded-xl border border-slate-200 overflow-hidden h-full">
                <div class="overflow-auto h-full">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 text-slate-500 sticky top-0">
                            <tr>
                                <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">ID</th>
                                <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Type</th>
                                <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Status</th>
                                <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Remarks</th>
                                <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Document</th>
                                <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Uploaded</th>
                            </tr>
                        </thead>
                        <tbody id="adminVerifHistoryBody" class="divide-y divide-slate-100 bg-white">
                            <tr><td colspan="6" class="px-3 py-4 text-center text-slate-400">No history loaded.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="adminVerifActionOverlay" class="hidden fixed inset-0 z-[90] bg-slate-900/40 items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-[0_12px_30px_rgba(15,23,42,0.24)] p-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-700">
                <x-lucide-shield-check class="w-[18px] h-[18px]" />
            </div>
            <div class="flex-1 min-w-0">
                <div id="adminVerifActionTitle" class="text-sm font-semibold text-slate-900">Confirm Action</div>
                <div id="adminVerifActionMessage" class="text-[0.78rem] text-slate-600 mt-0.5"></div>
            </div>
        </div>
        <div id="adminVerifActionDetails" class="mt-3 text-[0.78rem] text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2"></div>
        <div id="adminVerifActionDocWrap" class="mt-3 rounded-lg border border-slate-200 bg-slate-50 overflow-hidden h-32 flex items-center justify-center text-[0.74rem] text-slate-500">
            No document preview.
        </div>
        <div id="adminVerifRejectReasonWrap" class="hidden mt-3">
            <label for="adminVerifRejectReason" class="block text-[0.7rem] text-slate-600 mb-1">Reject reason</label>
            <select id="adminVerifRejectReason" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="Blurry document">Blurry Document</option>
                <option value="Invalid Document">Invalid Document</option>
                <option value="Expired Document">Expired Document</option>
                <option value="Mismatch Information">Mismatch Information</option>
                <option value="Cropped Document">Cropped Document</option>
            </select>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2">
            <button id="adminVerifActionCancel" type="button" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
            <button id="adminVerifActionConfirm" type="button" class="px-3 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700">Confirm</button>
        </div>
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

        var docPanelOverlay = document.getElementById('adminVerifDocPanelOverlay')
        var docPanel = document.getElementById('adminVerifDocPanel')
        var docPanelClose = document.getElementById('adminVerifDocPanelClose')
        var docPanelTitle = document.getElementById('adminVerifDocPanelTitle')
        var docPanelSubtitle = document.getElementById('adminVerifDocPanelSubtitle')
        var docMainWrap = document.getElementById('adminVerifDocMainWrap')
        var historyBody = document.getElementById('adminVerifHistoryBody')

        var actionOverlay = document.getElementById('adminVerifActionOverlay')
        var actionTitle = document.getElementById('adminVerifActionTitle')
        var actionMessage = document.getElementById('adminVerifActionMessage')
        var actionDetails = document.getElementById('adminVerifActionDetails')
        var actionDocWrap = document.getElementById('adminVerifActionDocWrap')
        var actionCancel = document.getElementById('adminVerifActionCancel')
        var actionConfirm = document.getElementById('adminVerifActionConfirm')
        var rejectReasonWrap = document.getElementById('adminVerifRejectReasonWrap')
        var rejectReasonSelect = document.getElementById('adminVerifRejectReason')

        var currentPage = 1
        var lastPayload = null
        var actionResolver = null
        var actionDelayTimer = null
        var actionCountdownTimer = null
        var actionConfirmDefaultHtml = actionConfirm ? actionConfirm.innerHTML : ''

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
            var name = ((p.firstname || '') + ' ' + (p.middlename || '') + ' ' + (p.lastname || '')).trim().replace(/\s+/g, ' ')
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
                            (hasDoc ? '<button type="button" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-[0.72rem] font-semibold text-slate-700 hover:bg-slate-50 admin-verif-doc" data-id="' + id + '">View document</button>' : '<span class="text-[0.72rem] font-semibold text-slate-400">No document</span>') +
                            '<button type="button" class="px-2.5 py-1 rounded-lg border border-emerald-200 bg-emerald-50 text-[0.72rem] font-semibold text-emerald-700 hover:bg-emerald-100 admin-verif-set" data-id="' + id + '" data-status="approved">Approve</button>' +
                            '<button type="button" class="px-2.5 py-1 rounded-lg border border-rose-200 bg-rose-50 text-[0.72rem] font-semibold text-rose-700 hover:bg-rose-100 admin-verif-set" data-id="' + id + '" data-status="rejected">Reject</button>' +
                        '</div>' +
                    '</td>' +
                '</tr>'
            })

            tableBody.innerHTML = html
            bindRowActions()
        }

        function statusText(value) {
            var key = String(value || '').toLowerCase()
            if (!key) return 'Unknown'
            return key.charAt(0).toUpperCase() + key.slice(1)
        }

        function documentUrl(verificationId) {
            return "{{ url('/api/patient-verifications') }}/" + encodeURIComponent(String(verificationId)) + "/document"
        }

        function openDocPanel() {
            if (!docPanelOverlay || !docPanel) return
            docPanelOverlay.classList.remove('hidden')
            window.requestAnimationFrame(function () {
                docPanel.classList.remove('translate-x-full')
            })
        }

        function closeDocPanel() {
            if (!docPanelOverlay || !docPanel) return
            docPanel.classList.add('translate-x-full')
            setTimeout(function () {
                docPanelOverlay.classList.add('hidden')
            }, 220)
        }

        function setMainDocumentPreview(v) {
            if (!docMainWrap) return
            if (!v || !v.document_path) {
                docMainWrap.innerHTML = '<div class="text-[0.78rem] text-slate-500">No document uploaded for this verification.</div>'
                return
            }
            docMainWrap.innerHTML = '<iframe src="' + escapeHtml(documentUrl(v.verification_id)) + '" class="w-full h-full bg-white" title="Verification document preview"></iframe>'
        }

        function renderHistory(rows) {
            if (!historyBody) return
            var items = Array.isArray(rows) ? rows : []
            if (!items.length) {
                historyBody.innerHTML = '<tr><td colspan="6" class="px-3 py-4 text-center text-slate-400">No verification history found.</td></tr>'
                return
            }

            historyBody.innerHTML = items.map(function (entry) {
                var uploaded = entry && entry.created_at ? String(entry.created_at).slice(0, 10) : '—'
                var remarks = entry && entry.remarks ? String(entry.remarks) : '—'
                var thumb = entry && entry.document_path
                    ? '<div class="w-16 h-12 rounded border border-slate-200 overflow-hidden bg-white"><iframe src="' + escapeHtml(documentUrl(entry.verification_id)) + '" class="w-full h-full" title="History document"></iframe></div>'
                    : '<span class="text-slate-400">No doc</span>'
                return '<tr>' +
                    '<td class="px-3 py-2 text-slate-700 whitespace-nowrap">#' + escapeHtml(entry.verification_id) + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 whitespace-nowrap">' + escapeHtml(entry.type || '—') + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 whitespace-nowrap">' + escapeHtml(statusText(entry.status)) + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 min-w-[14rem]">' + escapeHtml(remarks) + '</td>' +
                    '<td class="px-3 py-2">' + thumb + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 whitespace-nowrap">' + escapeHtml(uploaded) + '</td>' +
                '</tr>'
            }).join('')
        }

        function openDocumentPanelFor(verification) {
            if (!verification) return
            if (docPanelTitle) docPanelTitle.textContent = 'Verification #' + verification.verification_id
            if (docPanelSubtitle) docPanelSubtitle.textContent = getPatientLabel(verification) + ' • ' + statusText(verification.status) + ' • ' + (verification.type || '—')
            setMainDocumentPreview(verification)
            openDocPanel()

            if (historyBody) {
                historyBody.innerHTML = '<tr><td colspan="6" class="px-3 py-4 text-center text-slate-400">Loading verification history...</td></tr>'
            }
            var patientId = verification && verification.patient_id ? verification.patient_id : 0
            if (!patientId) {
                renderHistory([])
                return
            }

            apiFetch("{{ url('/api/patient-verifications') }}?per_page=100&patient_id=" + encodeURIComponent(String(patientId)), { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    }).catch(function () {
                        return { ok: response.ok, data: null }
                    })
                })
                .then(function (result) {
                    if (!result.ok || !result.data) {
                        renderHistory([])
                        return
                    }
                    var rows = Array.isArray(result.data.data) ? result.data.data : []
                    rows.sort(function (a, b) {
                        var da = String(a && a.created_at ? a.created_at : '')
                        var db = String(b && b.created_at ? b.created_at : '')
                        if (da < db) return 1
                        if (da > db) return -1
                        return 0
                    })
                    renderHistory(rows)
                })
                .catch(function () {
                    renderHistory([])
                })
        }

        function closeActionModal(result) {
            if (actionOverlay) {
                actionOverlay.classList.add('hidden')
                actionOverlay.classList.remove('flex')
            }
            if (actionDelayTimer) {
                clearTimeout(actionDelayTimer)
                actionDelayTimer = null
            }
            if (actionCountdownTimer) {
                clearInterval(actionCountdownTimer)
                actionCountdownTimer = null
            }
            if (actionConfirm) {
                actionConfirm.disabled = false
                actionConfirm.innerHTML = actionConfirmDefaultHtml || 'Confirm'
            }
            var resolver = actionResolver
            actionResolver = null
            if (typeof resolver === 'function') resolver(result || null)
        }

        function openActionModal(status, verification) {
            return new Promise(function (resolve) {
                if (!actionOverlay || !actionTitle || !actionMessage || !actionDetails || !actionConfirm || !actionCancel) {
                    resolve(null)
                    return
                }
                var isReject = status === 'rejected'
                actionDetails.innerHTML = '<ul class="space-y-1">' +
                    '<li><strong class="font-semibold text-slate-800">Verification ID:</strong> #' + escapeHtml(verification.verification_id) + '</li>' +
                    '<li><strong class="font-semibold text-slate-800">Patient:</strong> ' + escapeHtml(getPatientLabel(verification)) + '</li>' +
                    '<li><strong class="font-semibold text-slate-800">Type:</strong> ' + escapeHtml(verification.type || '—') + '</li>' +
                    '<li><strong class="font-semibold text-slate-800">Current Status:</strong> ' + escapeHtml(statusText(verification.status)) + '</li>' +
                '</ul>'

                if (verification && verification.document_path) {
                    actionDocWrap.innerHTML = '<iframe src="' + escapeHtml(documentUrl(verification.verification_id)) + '" class="w-full h-full bg-white" title="Verification action preview"></iframe>'
                } else {
                    actionDocWrap.innerHTML = '<div class="text-[0.74rem] text-slate-500">No document preview.</div>'
                }

                if (isReject) {
                    actionTitle.textContent = 'Reject Verification'
                    actionMessage.textContent = "Do you want to reject this patient's verification request?"
                    if (rejectReasonWrap) rejectReasonWrap.classList.remove('hidden')
                } else {
                    actionTitle.textContent = 'Approve Verification'
                    actionMessage.textContent = 'Do you want to approve this patient verification request?'
                    if (rejectReasonWrap) rejectReasonWrap.classList.add('hidden')
                }

                actionResolver = resolve
                actionOverlay.classList.remove('hidden')
                actionOverlay.classList.add('flex')

                actionConfirm.disabled = true
                var countdown = 3
                actionConfirm.innerHTML = '<span class="inline-flex items-center gap-2"><span class="w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span><span>Confirm (' + countdown + 's)</span></span>'
                actionCountdownTimer = setInterval(function () {
                    countdown = countdown - 1
                    if (countdown < 1) {
                        clearInterval(actionCountdownTimer)
                        actionCountdownTimer = null
                        return
                    }
                    actionConfirm.innerHTML = '<span class="inline-flex items-center gap-2"><span class="w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span><span>Confirm (' + countdown + 's)</span></span>'
                }, 1000)
                actionDelayTimer = setTimeout(function () {
                    if (actionCountdownTimer) {
                        clearInterval(actionCountdownTimer)
                        actionCountdownTimer = null
                    }
                    actionConfirm.disabled = false
                    actionConfirm.innerHTML = actionConfirmDefaultHtml || 'Confirm'
                    actionDelayTimer = null
                }, 3000)
            })
        }

        function updateVerificationStatus(id, status, remarks) {
            return apiFetch("{{ url('/api/patient-verifications') }}/" + id, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: status, remarks: remarks || '' })
            }).then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data }
                }).catch(function () {
                    return { ok: response.ok, data: null }
                })
            })
        }

        function bindRowActions() {
            var docButtons = document.querySelectorAll('.admin-verif-doc')
            docButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-id')
                    if (!id) return
                    var payload = lastPayload || {}
                    var list = Array.isArray(payload.data) ? payload.data : []
                    var selected = list.find(function (item) { return String(item.verification_id) === String(id) }) || null
                    if (!selected) {
                        showError('Unable to find the selected verification.')
                        return
                    }
                    openDocumentPanelFor(selected)
                })
            })

            var setButtons = document.querySelectorAll('.admin-verif-set')
            setButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-id')
                    var status = this.getAttribute('data-status')
                    if (!id || !status) return
                    var payload = lastPayload || {}
                    var list = Array.isArray(payload.data) ? payload.data : []
                    var selected = list.find(function (item) { return String(item.verification_id) === String(id) }) || null
                    if (!selected) {
                        showError('Unable to find the selected verification.')
                        return
                    }

                    openActionModal(status, selected)
                        .then(function (result) {
                            if (!result) return
                            var remarks = ''
                            if (status === 'rejected' && rejectReasonSelect) remarks = String(rejectReasonSelect.value || '').trim()
                            return updateVerificationStatus(id, status, remarks)
                                .then(function (updateResult) {
                                    if (!updateResult.ok) {
                                        showError('Failed to update verification status.')
                                        return
                                    }
                                    showError('')
                                    loadStats()
                                    loadVerifications(currentPage)
                                    if (docPanelOverlay && !docPanelOverlay.classList.contains('hidden')) {
                                        openDocumentPanelFor(updateResult.data || selected)
                                    }
                                })
                                .catch(function () {
                                    showError('Network error while updating verification status.')
                                })
                        })
                })
            })
        }

        if (docPanelClose) docPanelClose.addEventListener('click', closeDocPanel)
        if (docPanelOverlay) {
            docPanelOverlay.addEventListener('click', function (e) {
                if (e.target === docPanelOverlay) closeDocPanel()
            })
        }
        if (actionCancel) actionCancel.addEventListener('click', function () { closeActionModal(null) })
        if (actionConfirm) {
            actionConfirm.addEventListener('click', function () {
                closeActionModal({ confirmed: true })
            })
        }
        if (actionOverlay) {
            actionOverlay.addEventListener('click', function (e) {
                if (e.target === actionOverlay) closeActionModal(null)
            })
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDocPanel()
                closeActionModal(null)
            }
        })

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
