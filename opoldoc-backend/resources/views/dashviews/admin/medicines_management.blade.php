<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Medicines</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Management</span>
    </div>
    <p class="text-xs text-slate-500 mb-4">
        Manage medicine catalog entries and activate/deactivate them.
    </p>

    <div id="adminMedicineError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
    <div id="adminMedicineSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>

    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 mb-4">
        <div class="flex items-center justify-between mb-3">
            <div class="text-[0.8rem] font-semibold text-slate-900" id="admin_medicine_form_title">Add medicine</div>
            <button type="button" id="admin_medicine_form_toggle" class="text-[0.72rem] font-semibold text-slate-600 hover:text-slate-900">Hide</button>
        </div>

        <div id="admin_medicine_form_body">
            <form id="adminMedicineForm" class="grid gap-2 grid-cols-1 md:grid-cols-2">
                <div>
                    <label for="admin_medicine_generic" class="block text-[0.7rem] text-slate-600 mb-1">Generic name</label>
                    <input id="admin_medicine_generic" type="text" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Required">
                </div>
                <div>
                    <label for="admin_medicine_brand" class="block text-[0.7rem] text-slate-600 mb-1">Brand name</label>
                    <input id="admin_medicine_brand" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Optional">
                </div>
                <div class="md:col-span-2">
                    <label for="admin_medicine_indications" class="block text-[0.7rem] text-slate-600 mb-1">Indications</label>
                    <textarea id="admin_medicine_indications" rows="2" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Optional"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="admin_medicine_contraindications" class="block text-[0.7rem] text-slate-600 mb-1">Contraindications</label>
                    <textarea id="admin_medicine_contraindications" rows="2" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Optional"></textarea>
                </div>
                <div class="md:col-span-2 flex items-center justify-end gap-2">
                    <button type="submit" id="admin_medicine_save" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors disabled:opacity-60 disabled:hover:bg-cyan-600">
                        <span id="admin_medicine_save_spinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        <span id="admin_medicine_save_label">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
        <div class="flex-1">
            <label for="admin_medicine_search" class="block text-[0.7rem] text-slate-600 mb-1">Search</label>
            <input id="admin_medicine_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Generic or brand name">
        </div>
        <div class="w-full md:w-40">
            <label for="admin_medicine_active_filter" class="block text-[0.7rem] text-slate-600 mb-1">Active</label>
            <select id="admin_medicine_active_filter" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="">All</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        <div class="w-full md:w-44">
            <label for="admin_medicine_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
            <select id="admin_medicine_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="created_desc">Newest first</option>
                <option value="created_asc">Oldest first</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto scrollbar-hidden mb-4">
        <table class="min-w-full text-left text-xs text-slate-600">
            <thead>
                <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                    <th class="py-2 pr-4 font-semibold">Generic name</th>
                    <th class="py-2 pr-4 font-semibold">Brand name</th>
                    <th class="py-2 pr-4 font-semibold">Indications</th>
                    <th class="py-2 pr-4 font-semibold">Contraindications</th>
                    <th class="py-2 pr-4 font-semibold">Active</th>
                    <th class="py-2 pr-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody id="admin_medicine_table_body">
                <tr>
                    <td colspan="6" class="py-4 text-center text-[0.78rem] text-slate-400">
                        Loading medicines…
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="adminMedicineDuplicateOverlay" class="hidden fixed inset-0 z-[75] bg-slate-900/40 items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-[0_12px_30px_rgba(15,23,42,0.24)] overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <div class="text-sm font-semibold text-slate-900">Similar medicine found</div>
                <div class="text-[0.72rem] text-slate-500">A similar medicine is already stored.</div>
            </div>
            <button type="button" id="adminMedicineDuplicateClose" class="text-slate-400 hover:text-slate-600">
                <x-lucide-x class="w-[20px] h-[20px]" />
            </button>
        </div>
        <div class="p-5">
            <div class="text-[0.78rem] text-slate-700 mb-3">
                There’s a similar medicine already stored:
            </div>
            <div id="adminMedicineDuplicateDetails" class="space-y-2"></div>
            <div class="mt-4 text-[0.78rem] text-slate-700">
                Are you sure you want to store this medicine?
            </div>
            <div class="mt-4 flex items-center justify-end gap-2">
                <button type="button" id="adminMedicineDuplicateCancel" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="button" id="adminMedicineDuplicateConfirm" class="px-3 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700">Store anyway</button>
            </div>
        </div>
    </div>
</div>
<div id="adminMedicineConfirmOverlay" class="hidden fixed inset-0 z-[70] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4 transition-all duration-200">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden">
        <!-- Header area with refined spacing and visual hierarchy -->
        <div class="px-5 pt-5 pb-3 border-b border-slate-100 bg-gradient-to-r from-white to-slate-50/50">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 flex-shrink-0">
                    <x-lucide-info class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <h3 id="adminMedicineConfirmMessage" class="text-base font-semibold text-slate-800 tracking-tight">Confirm action</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Please review before confirming</p>
                </div>
            </div>
        </div>
        
        <!-- Body with clear, scannable details section -->
        <div class="px-5 py-4 bg-white">
            <div id="adminMedicineConfirmDetails" class="bg-slate-50/80 rounded-xl border border-slate-100 p-4 text-sm text-slate-700 leading-relaxed space-y-2">
                <!-- Dynamic content will be injected here -->
                <div class="flex items-start gap-2 text-slate-600">
                    <x-lucide-alert-circle class="w-3.5 h-3.5 mt-0.5 text-slate-400 flex-shrink-0" />
                    <span class="text-slate-700">Are you sure you want to perform this action?</span>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-200 text-xs text-amber-600 bg-amber-50/50 -mx-2 px-2 py-1.5 rounded-md flex items-center gap-2">
                    <x-lucide-info class="w-3.5 h-3.5" />
                    <span>This action cannot be undone</span>
                </div>
            </div>
        </div>
        
        <!-- Footer with improved button hierarchy and spacing -->
        <div class="px-5 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-2.5">
            <button type="button" id="adminMedicineConfirmCancel" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-slate-200 focus:ring-offset-1">
                Cancel
            </button>
            <button type="button" id="adminMedicineConfirmOk" class="px-5 py-2 rounded-lg bg-cyan-600 text-white text-sm font-semibold hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 shadow-sm transition-all duration-150">
                Confirm
            </button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var errorBox = document.getElementById('adminMedicineError')
        var successBox = document.getElementById('adminMedicineSuccess')
        var searchInput = document.getElementById('admin_medicine_search')
        var activeFilter = document.getElementById('admin_medicine_active_filter')
        var sortSelect = document.getElementById('admin_medicine_sort')
        var tableBody = document.getElementById('admin_medicine_table_body')

        var formTitle = document.getElementById('admin_medicine_form_title')
        var formToggle = document.getElementById('admin_medicine_form_toggle')
        var formBody = document.getElementById('admin_medicine_form_body')
        var form = document.getElementById('adminMedicineForm')
        var genericInput = document.getElementById('admin_medicine_generic')
        var brandInput = document.getElementById('admin_medicine_brand')
        var indicationsInput = document.getElementById('admin_medicine_indications')
        var contraindicationsInput = document.getElementById('admin_medicine_contraindications')
        var saveBtn = document.getElementById('admin_medicine_save')
        var saveSpinner = document.getElementById('admin_medicine_save_spinner')
        var saveLabel = document.getElementById('admin_medicine_save_label')

        var confirmOverlay = document.getElementById('adminMedicineConfirmOverlay')
        var confirmMessage = document.getElementById('adminMedicineConfirmMessage')
        var confirmDetails = document.getElementById('adminMedicineConfirmDetails')
        var confirmOk = document.getElementById('adminMedicineConfirmOk')
        var confirmCancel = document.getElementById('adminMedicineConfirmCancel')
        var confirmResolver = null
        var confirmCountdownTimer = null
        var confirmOkOriginalText = null

        var duplicateOverlay = document.getElementById('adminMedicineDuplicateOverlay')
        var duplicateClose = document.getElementById('adminMedicineDuplicateClose')
        var duplicateCancel = document.getElementById('adminMedicineDuplicateCancel')
        var duplicateConfirm = document.getElementById('adminMedicineDuplicateConfirm')
        var duplicateDetails = document.getElementById('adminMedicineDuplicateDetails')
        var duplicateResolver = null
        var duplicateCountdownTimer = null
        var duplicateConfirmOriginalText = null

        var medicines = []
        var editingId = null
        var medicineErrorTimer = null
        var medicineSuccessTimer = null

        function showError(message) {
            if (!errorBox) return
            if (medicineErrorTimer) {
                clearTimeout(medicineErrorTimer)
                medicineErrorTimer = null
            }
            if (!message) {
                errorBox.textContent = ''
                errorBox.classList.add('hidden')
                return
            }
            errorBox.textContent = message
            errorBox.classList.remove('hidden')
            medicineErrorTimer = setTimeout(function () {
                showError('')
            }, 3200)
        }

        function showSuccess(message) {
            if (!successBox) return
            if (medicineSuccessTimer) {
                clearTimeout(medicineSuccessTimer)
                medicineSuccessTimer = null
            }
            if (!message) {
                successBox.textContent = ''
                successBox.classList.add('hidden')
                return
            }
            successBox.textContent = message
            successBox.classList.remove('hidden')
            medicineSuccessTimer = setTimeout(function () {
                showSuccess('')
            }, 3200)
        }

        function readApiMessage(result, fallback) {
            if (!result) return fallback
            if (result.data && result.data.errors) {
                var all = []
                Object.keys(result.data.errors).forEach(function (key) {
                    var val = result.data.errors[key]
                    if (Array.isArray(val)) {
                        val.forEach(function (item) { all.push(String(item)) })
                    } else if (val != null) {
                        all.push(String(val))
                    }
                })
                if (all.length) return all.join(' ')
            }
            if (result.data && result.data.message) return String(result.data.message)
            return fallback
        }

        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
        }

        function normalizeText(value) {
            return String(value || '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '')
                .trim()
        }

        function setFormExpanded(expanded) {
            if (!formBody || !formToggle) return
            formBody.classList.toggle('hidden', !expanded)
            formToggle.textContent = expanded ? 'Hide' : 'Show'
        }

        function setSaving(isSaving) {
            if (saveBtn) saveBtn.disabled = !!isSaving
            if (saveSpinner) saveSpinner.classList.toggle('hidden', !isSaving)
            if (saveLabel) saveLabel.textContent = editingId ? 'Save changes' : 'Save'
        }

        function stopConfirmCountdown() {
            if (confirmCountdownTimer) {
                clearInterval(confirmCountdownTimer)
                confirmCountdownTimer = null
            }
            if (confirmOk) {
                confirmOk.disabled = false
                confirmOk.classList.remove('opacity-60', 'cursor-not-allowed')
                if (confirmOkOriginalText != null) {
                    confirmOk.textContent = confirmOkOriginalText
                }
            }
            confirmOkOriginalText = null
        }

        function closeConfirm(result) {
            if (confirmOverlay) {
                confirmOverlay.classList.add('hidden')
                confirmOverlay.classList.remove('flex')
            }
            if (confirmDetails) {
                confirmDetails.innerHTML = ''
                confirmDetails.classList.add('hidden')
            }
            stopConfirmCountdown()
            var resolver = confirmResolver
            confirmResolver = null
            if (typeof resolver === 'function') resolver(!!result)
        }

        function confirmAction(message, options) {
            return new Promise(function (resolve) {
                if (!confirmOverlay || !confirmMessage || !confirmOk || !confirmCancel) {
                    resolve(window.confirm(message || 'Are you sure?'))
                    return
                }
                stopConfirmCountdown()
                confirmMessage.textContent = message || 'Are you sure?'

                var confirmText = options && options.confirmText ? String(options.confirmText) : 'Confirm'
                confirmOk.textContent = confirmText
                confirmOkOriginalText = confirmText

                if (confirmDetails) {
                    var details = options && options.details ? options.details : ''
                    if (details) {
                        confirmDetails.innerHTML = details
                        confirmDetails.classList.remove('hidden')
                    } else {
                        confirmDetails.innerHTML = ''
                        confirmDetails.classList.add('hidden')
                    }
                }

                confirmResolver = resolve
                confirmOverlay.classList.remove('hidden')
                confirmOverlay.classList.add('flex')

                var countdownSeconds = options && options.countdownSeconds ? parseInt(String(options.countdownSeconds), 10) : 0
                if (!countdownSeconds || isNaN(countdownSeconds) || countdownSeconds < 1) {
                    return
                }

                confirmOk.disabled = true
                confirmOk.classList.add('opacity-60', 'cursor-not-allowed')

                var remaining = countdownSeconds
                confirmOk.textContent = confirmText + ' (' + remaining + ')'

                confirmCountdownTimer = setInterval(function () {
                    remaining -= 1
                    if (remaining <= 0) {
                        stopConfirmCountdown()
                        return
                    }
                    if (confirmOk) {
                        confirmOk.textContent = confirmText + ' (' + remaining + ')'
                    }
                }, 1000)
            })
        }

        if (confirmOk) confirmOk.addEventListener('click', function () { closeConfirm(true) })
        if (confirmCancel) confirmCancel.addEventListener('click', function () { closeConfirm(false) })
        if (confirmOverlay) {
            confirmOverlay.addEventListener('click', function (e) {
                if (e.target === confirmOverlay) closeConfirm(false)
            })
        }

        function closeDuplicateConfirm(result) {
            if (duplicateOverlay) {
                duplicateOverlay.classList.add('hidden')
                duplicateOverlay.classList.remove('flex')
            }
            if (duplicateDetails) duplicateDetails.innerHTML = ''
            if (duplicateCountdownTimer) {
                clearInterval(duplicateCountdownTimer)
                duplicateCountdownTimer = null
            }
            if (duplicateConfirm) {
                duplicateConfirm.disabled = false
                duplicateConfirm.classList.remove('opacity-60', 'cursor-not-allowed')
                if (duplicateConfirmOriginalText != null) {
                    duplicateConfirm.textContent = duplicateConfirmOriginalText
                }
            }
            duplicateConfirmOriginalText = null
            var resolver = duplicateResolver
            duplicateResolver = null
            if (typeof resolver === 'function') resolver(!!result)
        }

        function openDuplicateConfirm(matches) {
            return new Promise(function (resolve) {
                if (!duplicateOverlay || !duplicateConfirm || !duplicateCancel || !duplicateDetails) {
                    resolve(window.confirm('A similar medicine is already stored. Store anyway?'))
                    return
                }

                duplicateResolver = resolve
                var html = ''
                for (var i = 0; i < matches.length; i++) {
                    var m = matches[i]
                    var g = m && m.generic_name ? String(m.generic_name) : '—'
                    var b = m && m.brand_name ? String(m.brand_name) : '—'
                    var ind = m && m.indications ? String(m.indications) : '—'
                    var con = m && m.contraindications ? String(m.contraindications) : '—'
                    var active = m && m.is_active ? 'Active' : 'Inactive'

                    html += '<div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">' +
                        '<div class="text-[0.78rem] font-semibold text-slate-900">' + escapeHtml(g) + '</div>' +
                        '<div class="text-[0.72rem] text-slate-600 mt-0.5">Brand: ' + escapeHtml(b) + '</div>' +
                        '<div class="text-[0.72rem] text-slate-600 mt-0.5">Indications: ' + escapeHtml(ind) + '</div>' +
                        '<div class="text-[0.72rem] text-slate-600 mt-0.5">Contraindications: ' + escapeHtml(con) + '</div>' +
                        '<div class="text-[0.72rem] text-slate-500 mt-0.5">Status: ' + escapeHtml(active) + '</div>' +
                    '</div>'
                }
                duplicateDetails.innerHTML = html

                if (duplicateConfirm) {
                    if (duplicateCountdownTimer) {
                        clearInterval(duplicateCountdownTimer)
                        duplicateCountdownTimer = null
                    }
                    duplicateConfirmOriginalText = String(duplicateConfirm.textContent || 'Store anyway')
                    duplicateConfirm.disabled = true
                    duplicateConfirm.classList.add('opacity-60', 'cursor-not-allowed')
                    var remaining = 3
                    duplicateConfirm.textContent = duplicateConfirmOriginalText + ' (' + remaining + ')'
                    duplicateCountdownTimer = setInterval(function () {
                        remaining -= 1
                        if (remaining <= 0) {
                            clearInterval(duplicateCountdownTimer)
                            duplicateCountdownTimer = null
                            duplicateConfirm.disabled = false
                            duplicateConfirm.classList.remove('opacity-60', 'cursor-not-allowed')
                            duplicateConfirm.textContent = duplicateConfirmOriginalText
                            return
                        }
                        duplicateConfirm.textContent = duplicateConfirmOriginalText + ' (' + remaining + ')'
                    }, 1000)
                }

                duplicateOverlay.classList.remove('hidden')
                duplicateOverlay.classList.add('flex')
            })
        }

        if (duplicateConfirm) duplicateConfirm.addEventListener('click', function () { closeDuplicateConfirm(true) })
        if (duplicateCancel) duplicateCancel.addEventListener('click', function () { closeDuplicateConfirm(false) })
        if (duplicateClose) duplicateClose.addEventListener('click', function () { closeDuplicateConfirm(false) })
        if (duplicateOverlay) {
            duplicateOverlay.addEventListener('click', function (e) {
                if (e.target === duplicateOverlay) closeDuplicateConfirm(false)
            })
        }

        function resetForm() {
            editingId = null
            if (formTitle) formTitle.textContent = 'Add medicine'
            if (genericInput) genericInput.value = ''
            if (brandInput) brandInput.value = ''
            if (indicationsInput) indicationsInput.value = ''
            if (contraindicationsInput) contraindicationsInput.value = ''
            setFormExpanded(true)
            setSaving(false)
        }

        function loadMedicines() {
            if (!tableBody) return
            tableBody.innerHTML = '<tr><td colspan="6" class="py-4 text-center text-[0.78rem] text-slate-400">Loading medicines…</td></tr>'
            apiFetch("{{ url('/api/medicines') }}?per_page=100", { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        showError('Failed to load medicines.')
                        medicines = []
                        renderMedicines()
                        return
                    }
                    medicines = Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    renderMedicines()
                })
                .catch(function () {
                    showError('Network error while loading medicines.')
                    medicines = []
                    renderMedicines()
                })
        }

        function renderMedicines() {
            if (!tableBody) return

            var query = searchInput ? searchInput.value.toLowerCase().trim() : ''
            var activeVal = activeFilter ? activeFilter.value : ''
            var sortValue = sortSelect ? String(sortSelect.value || 'created_desc') : 'created_desc'

            var filtered = medicines.slice()
            if (query) {
                filtered = filtered.filter(function (m) {
                    var g = String(m.generic_name || '').toLowerCase()
                    var b = String(m.brand_name || '').toLowerCase()
                    return g.indexOf(query) !== -1 || b.indexOf(query) !== -1
                })
            }
            if (activeVal !== '') {
                var expected = activeVal === '1'
                filtered = filtered.filter(function (m) {
                    return !!m.is_active === expected
                })
            }

            filtered.sort(function (a, b) {
                if (sortValue === 'created_asc' || sortValue === 'created_desc') {
                    var ta = a && a.created_at ? Date.parse(String(a.created_at)) : 0
                    var tb = b && b.created_at ? Date.parse(String(b.created_at)) : 0
                    if (isNaN(ta)) ta = 0
                    if (isNaN(tb)) tb = 0
                    if (ta < tb) return sortValue === 'created_asc' ? -1 : 1
                    if (ta > tb) return sortValue === 'created_asc' ? 1 : -1
                    return 0
                }
                return 0
            })

            if (!filtered.length) {
                tableBody.innerHTML = '<tr><td colspan="6" class="py-4 text-center text-[0.78rem] text-slate-400">No medicines found.</td></tr>'
                return
            }

            var html = ''
            filtered.forEach(function (m) {
                var active = !!m.is_active
                var badge = active
                    ? '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.68rem] font-medium border bg-emerald-50 text-emerald-700 border-emerald-100">Active</span>'
                    : '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.68rem] font-medium border bg-slate-50 text-slate-600 border-slate-100">Inactive</span>'

                html += '<tr class="border-b border-slate-50 last:border-0">' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-700">' + escapeHtml(m.generic_name || '—') + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">' + (m.brand_name ? escapeHtml(m.brand_name) : '<span class="text-slate-400">—</span>') + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">' + (m.indications ? escapeHtml(m.indications) : '<span class="text-slate-400">—</span>') + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">' + (m.contraindications ? escapeHtml(m.contraindications) : '<span class="text-slate-400">—</span>') + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem]">' + badge + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem]">' +
                        '<div class="flex items-center gap-2">' +
                            '<button type="button" class="px-2 py-1 rounded-md border border-cyan-200 bg-cyan-50 text-cyan-700 hover:bg-cyan-100 text-[0.72rem] font-semibold admin-medicine-edit" data-id="' + m.medicine_id + '">Edit</button>' +
                            (active
                                ? '<button type="button" class="px-2 py-1 rounded-md border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 text-[0.72rem] font-semibold admin-medicine-toggle" data-id="' + m.medicine_id + '" data-active="0">Deactivate</button>'
                                : '<button type="button" class="px-2 py-1 rounded-md border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-[0.72rem] font-semibold admin-medicine-toggle" data-id="' + m.medicine_id + '" data-active="1">Activate</button>') +
                        '</div>' +
                    '</td>' +
                '</tr>'
            })

            tableBody.innerHTML = html
            bindTableActions()
        }

        function bindTableActions() {
            var editButtons = document.querySelectorAll('.admin-medicine-edit')
            editButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-id')
                    var m = medicines.find(function (x) { return String(x.medicine_id) === String(id) })
                    if (!m) return
                    editingId = m.medicine_id
                    if (formTitle) formTitle.textContent = 'Edit medicine #' + editingId
                    if (genericInput) genericInput.value = m.generic_name || ''
                    if (brandInput) brandInput.value = m.brand_name || ''
                    if (indicationsInput) indicationsInput.value = m.indications || ''
                    if (contraindicationsInput) contraindicationsInput.value = m.contraindications || ''
                    setFormExpanded(true)
                    setSaving(false)
                    showError('')
                    showSuccess('')
                })
            })

            var toggleButtons = document.querySelectorAll('.admin-medicine-toggle')
            toggleButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-id')
                    var active = this.getAttribute('data-active')
                    if (!id || active === null) return
                    var nextActive = active === '1'
                    var self = this

                    function runToggle() {
                        self.disabled = true
                        self.classList.add('opacity-60', 'cursor-not-allowed')
                        self.textContent = nextActive ? 'Activating…' : 'Deactivating…'
                        updateMedicine(id, { is_active: nextActive })
                            .then(function (ok) {
                                if (!ok) {
                                    self.disabled = false
                                    self.classList.remove('opacity-60', 'cursor-not-allowed')
                                    self.textContent = nextActive ? 'Activate' : 'Deactivate'
                                }
                            })
                    }

                    if (!nextActive) {
                        confirmAction('Are you sure you want to deactivate this medicine?', { countdownSeconds: 3, confirmText: 'Deactivate' })
                            .then(function (confirmed) {
                                if (!confirmed) return
                                runToggle()
                            })
                        return
                    }

                    runToggle()
                })
            })
        }

        function updateMedicine(id, body) {
            showError('')
            showSuccess('')
            return apiFetch("{{ url('/api/medicines') }}/" + id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        showError(readApiMessage(result, 'Failed to update medicine.'))
                        return false
                    }
                    if (body && Object.prototype.hasOwnProperty.call(body, 'is_active')) {
                        showSuccess(body.is_active ? 'Medicine activated.' : 'Medicine deactivated.')
                    } else {
                        showSuccess('Medicine updated.')
                    }
                    loadMedicines()
                    return true
                })
                .catch(function () {
                    showError('Network error while updating medicine.')
                    return false
                })
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault()
                if (saveBtn && saveBtn.disabled) return
                showError('')
                showSuccess('')
                var genericName = genericInput ? genericInput.value.trim() : ''
                if (!genericName) {
                    showError('Generic name is required.')
                    return
                }
                var body = {
                    generic_name: genericName,
                    brand_name: brandInput ? brandInput.value.trim() : '',
                    indications: indicationsInput ? indicationsInput.value.trim() : '',
                    contraindications: contraindicationsInput ? contraindicationsInput.value.trim() : ''
                }

                var detailsHtml = '<div class="grid grid-cols-2 gap-x-4 gap-y-1">' +
                    '<div class="text-slate-500">Generic Name:</div><div class="text-slate-800 font-medium">' + escapeHtml(body.generic_name) + '</div>' +
                    (body.brand_name ? '<div class="text-slate-500">Brand Name:</div><div class="text-slate-800 font-medium">' + escapeHtml(body.brand_name) + '</div>' : '') +
                    (body.indications ? '<div class="text-slate-500">Indications:</div><div class="text-slate-800 font-medium">' + escapeHtml(body.indications) + '</div>' : '') +
                    (body.contraindications ? '<div class="text-slate-500">Contraindications:</div><div class="text-slate-800 font-medium">' + escapeHtml(body.contraindications) + '</div>' : '') +
                '</div>'

                if (editingId) {
                    confirmAction('Are you sure you want to save these changes?', { confirmText: 'Save', details: detailsHtml })
                        .then(function (confirmed) {
                            if (!confirmed) return
                            setSaving(true)
                            updateMedicine(editingId, body)
                                .finally(function () {
                                    setSaving(false)
                                    resetForm()
                                })
                        })
                } else {
                    body.is_active = true
                    var duplicates = []
                    var gNorm = normalizeText(body.generic_name)
                    var bNorm = normalizeText(body.brand_name)
                    duplicates = (medicines || []).filter(function (m) {
                        var mg = normalizeText(m && m.generic_name ? m.generic_name : '')
                        var mb = normalizeText(m && m.brand_name ? m.brand_name : '')
                        if (gNorm && mg && (gNorm === mg || gNorm.indexOf(mg) !== -1 || mg.indexOf(gNorm) !== -1)) return true
                        if (bNorm && mb && (bNorm === mb || bNorm.indexOf(mb) !== -1 || mb.indexOf(bNorm) !== -1)) return true
                        return false
                    }).slice(0, 3)

                    var duplicateConfirm = duplicates.length ? openDuplicateConfirm(duplicates) : Promise.resolve(true)

                    duplicateConfirm.then(function (okToStoreAnyway) {
                        if (!okToStoreAnyway) return

                        var saveConfirm = duplicates.length
                            ? Promise.resolve(true)
                            : confirmAction('Are you sure you want to save this medicine?', { confirmText: 'Save', countdownSeconds: 3, details: detailsHtml })

                        saveConfirm.then(function (confirmed) {
                            if (!confirmed) return
                            setSaving(true)

                            apiFetch("{{ url('/api/medicines') }}", {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(body)
                            })
                                .then(function (response) {
                                    return response.json().then(function (data) {
                                        return { ok: response.ok, data: data }
                                    })
                                })
                                .then(function (result) {
                                    if (!result.ok) {
                                        showError(readApiMessage(result, 'Failed to save medicine.'))
                                        return
                                    }
                                    showSuccess('Medicine added.')
                                    resetForm()
                                    loadMedicines()
                                })
                                .catch(function () {
                                    showError('Network error while saving medicine.')
                                })
                                .finally(function () {
                                    setSaving(false)
                                })
                        })
                    })
                }
            })
        }

        if (formToggle) {
            formToggle.addEventListener('click', function () {
                if (!formBody) return
                var expanded = !formBody.classList.contains('hidden')
                setFormExpanded(!expanded)
            })
        }

        if (searchInput) {
            searchInput.addEventListener('input', renderMedicines)
        }
        if (activeFilter) {
            activeFilter.addEventListener('change', renderMedicines)
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', renderMedicines)
        }

        resetForm()
        loadMedicines()
    })
</script>