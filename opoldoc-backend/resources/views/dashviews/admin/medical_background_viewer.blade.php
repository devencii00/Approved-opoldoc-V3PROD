<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Medical Background Viewer</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Admin</span>
    </div>
    <p class="text-xs text-slate-500 mb-4">
        Admin-only insight tool for reviewing patient medical background entries.
    </p>

    <div id="adminMedBgError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
        <div class="flex-1">
            <label for="admin_medbg_search" class="block text-[0.7rem] text-slate-600 mb-1">Patient</label>
            <input id="admin_medbg_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Patient name or email">
        </div>
        <div class="w-full md:w-56">
            <label for="admin_medbg_category" class="block text-[0.7rem] text-slate-600 mb-1">Category</label>
            <select id="admin_medbg_category" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="">All categories</option>
                <option value="allergy_food">Food</option>
                <option value="allergy_drug">Drug</option>
                <option value="condition">Condition</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto scrollbar-hidden">
        <table class="min-w-full text-left text-xs text-slate-600">
            <thead>
                <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                    <th class="py-2 pr-4 font-semibold">Patient</th>
                    <th class="py-2 pr-4 font-semibold">Category</th>
                    <th class="py-2 pr-4 font-semibold">Name</th>
                    <th class="py-2 pr-4 font-semibold">Notes</th>
                    <th class="py-2 pr-4 font-semibold">Created</th>
                </tr>
            </thead>
            <tbody id="admin_medbg_table_body">
                <tr>
                    <td colspan="5" class="py-4 text-center text-[0.78rem] text-slate-400">
                        Loading entries…
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var errorBox = document.getElementById('adminMedBgError')
        var searchInput = document.getElementById('admin_medbg_search')
        var categorySelect = document.getElementById('admin_medbg_category')
        var tableBody = document.getElementById('admin_medbg_table_body')

        var rows = []

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

        function categoryLabel(key) {
            var k = String(key || '')
            if (k === 'allergy_food') return 'Food'
            if (k === 'allergy_drug') return 'Drug'
            if (k === 'condition') return 'Condition'
            return k || '—'
        }

        function patientLabel(p) {
            if (!p) return 'Unknown'
            var name = ((p.firstname || '') + ' ' + (p.lastname || '')).trim()
            if (name) return name
            if (p.email) return p.email
            return 'Patient #' + p.user_id
        }

        function loadRows() {
            if (!tableBody) return
            tableBody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-[0.78rem] text-slate-400">Loading entries…</td></tr>'
            showError('')

            apiFetch("{{ url('/api/medical-backgrounds') }}?per_page=100", { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        showError('Failed to load medical background entries.')
                        rows = []
                        renderRows()
                        return
                    }
                    rows = Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    renderRows()
                })
                .catch(function () {
                    showError('Network error while loading medical background entries.')
                    rows = []
                    renderRows()
                })
        }

        function renderRows() {
            if (!tableBody) return
            var query = searchInput ? searchInput.value.toLowerCase().trim() : ''
            var cat = categorySelect ? categorySelect.value : ''

            var filtered = rows.slice()
            if (cat) {
                filtered = filtered.filter(function (r) {
                    return String(r.category || '') === cat
                })
            }
            if (query) {
                filtered = filtered.filter(function (r) {
                    var p = r.patient || null
                    var label = patientLabel(p).toLowerCase()
                    var email = p && p.email ? String(p.email).toLowerCase() : ''
                    return label.indexOf(query) !== -1 || email.indexOf(query) !== -1
                })
            }

            if (!filtered.length) {
                tableBody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-[0.78rem] text-slate-400">No entries found.</td></tr>'
                return
            }

            var html = ''
            filtered.forEach(function (r) {
                var p = r.patient || null
                var created = r.created_at ? String(r.created_at).slice(0, 10) : '—'
                html += '<tr class="border-b border-slate-50 last:border-0">' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-700">' + escapeHtml(patientLabel(p)) + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">' + escapeHtml(categoryLabel(r.category)) + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-700">' + escapeHtml(r.name || '—') + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">' + (r.notes ? escapeHtml(r.notes) : '<span class="text-slate-400">—</span>') + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">' + escapeHtml(created) + '</td>' +
                '</tr>'
            })

            tableBody.innerHTML = html
        }

        if (searchInput) {
            searchInput.addEventListener('input', renderRows)
        }
        if (categorySelect) {
            categorySelect.addEventListener('change', renderRows)
        }

        loadRows()
    })
</script>

