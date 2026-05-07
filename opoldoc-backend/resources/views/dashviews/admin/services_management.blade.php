<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Services Management</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Services</span>
    </div>
    <p class="text-xs text-slate-500 mb-4">
        Add services, edit details, delete entries, and update prices for the clinic.
    </p>

    <div id="adminServiceError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
    <div id="adminServiceSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>

    <form id="adminAddServiceForm" class="mb-4 grid gap-2 grid-cols-1 md:grid-cols-5 items-end">
        <div>
            <label for="admin_service_name" class="block text-[0.7rem] text-slate-600 mb-1">Service name</label>
            <input id="admin_service_name" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" required>
        </div>
        <div class="md:col-span-2">
            <label for="admin_service_description" class="block text-[0.7rem] text-slate-600 mb-1">Description (optional)</label>
            <input id="admin_service_description" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
        </div>
        <div>
            <label for="admin_service_duration" class="block text-[0.7rem] text-slate-600 mb-1">Duration (minutes)</label>
            <input id="admin_service_duration" type="number" min="1" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="e.g. 30">
        </div>
        <div class="flex items-end gap-2">
            <div class="flex-1">
                <label for="admin_service_price" class="block text-[0.7rem] text-slate-600 mb-1">Price (optional)</label>
                <input id="admin_service_price" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
            </div>
            <button type="submit" id="adminAddServiceBtn" class="inline-flex h-[34px] items-center justify-center px-4 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors disabled:opacity-60 disabled:hover:bg-cyan-600">
                <span id="adminAddServiceSpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin mr-2"></span>
                <span>Add Service</span>
            </button>
        </div>
    </form>

    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
        <div class="flex-1">
            <label for="admin_service_search" class="block text-[0.7rem] text-slate-600 mb-1">Search services</label>
            <input id="admin_service_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Search by name or description">
        </div>
        <div class="w-full md:w-44">
            <label for="admin_service_status_filter" class="block text-[0.7rem] text-slate-600 mb-1">Status</label>
            <select id="admin_service_status_filter" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="w-full md:w-40">
            <label for="admin_service_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
            <select id="admin_service_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="name_asc">Name A–Z</option>
                <option value="name_desc">Name Z–A</option>
                <option value="price_asc">Price low–high</option>
                <option value="price_desc">Price high–low</option>
                <option value="created_desc">Newest first</option>
                <option value="created_asc">Oldest first</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto scrollbar-hidden">
        <table class="min-w-full text-left text-xs text-slate-600">
            <thead>
                <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                    <th class="py-2 pr-4 font-semibold">ID</th>
                    <th class="py-2 pr-4 font-semibold">Service</th>
                    <th class="py-2 pr-4 font-semibold">Description</th>
                    <th class="py-2 pr-4 font-semibold">Duration</th>
                    <th class="py-2 pr-4 font-semibold">Price</th>
                    <th class="py-2 pr-4 font-semibold">Status</th>
                    <th class="py-2 pr-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody id="admin_service_table_body">
                <tr>
                    <td colspan="7" class="py-4 text-center text-[0.78rem] text-slate-400">
                        Loading services…
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="adminServiceConfirmOverlay" class="hidden fixed inset-0 z-[70] bg-slate-900/40 items-center justify-center p-4">
    <div class="w-full max-w-sm rounded-2xl bg-white border border-slate-200 shadow-[0_12px_30px_rgba(15,23,42,0.24)] p-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-700">
                <x-lucide-info class="w-[18px] h-[18px]" />
            </div>
            <div class="flex-1">
                <div class="text-sm font-semibold text-slate-900">Confirm</div>
                <div id="adminServiceConfirmMessage" class="text-[0.78rem] text-slate-600 mt-0.5">Are you sure?</div>
                <div id="adminServiceConfirmDetails" class="hidden text-[0.78rem] text-slate-600 mt-2"></div>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button" id="adminServiceConfirmCancel" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
            <button type="button" id="adminServiceConfirmOk" class="px-3 py-2 rounded-xl bg-slate-900 text-white text-[0.78rem] font-semibold hover:bg-slate-800">Confirm</button>
        </div>
    </div>
</div>

<div id="adminServiceEditOverlay" class="hidden fixed inset-0 z-50 bg-slate-900/40 items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-[0_12px_30px_rgba(15,23,42,0.24)] overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <div class="text-sm font-semibold text-slate-900">Edit service</div>
                <div id="adminServiceEditSubtitle" class="text-[0.72rem] text-slate-500">Update service details.</div>
            </div>
            <button type="button" id="adminServiceEditClose" class="text-slate-400 hover:text-slate-600">
                <x-lucide-x class="w-[20px] h-[20px]" />
            </button>
        </div>
        <div class="p-5">
            <div id="adminServiceEditError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
            <form id="adminServiceEditForm" class="grid grid-cols-1 gap-3">
                <div>
                    <label for="adminServiceEditName" class="block text-[0.7rem] text-slate-600 mb-1">Service name</label>
                    <input id="adminServiceEditName" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" required>
                </div>
                <div>
                    <label for="adminServiceEditDescription" class="block text-[0.7rem] text-slate-600 mb-1">Description (optional)</label>
                    <input id="adminServiceEditDescription" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div>
                    <label for="adminServiceEditDuration" class="block text-[0.7rem] text-slate-600 mb-1">Duration (minutes)</label>
                    <input id="adminServiceEditDuration" type="number" min="1" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div>
                    <label for="adminServiceEditPrice" class="block text-[0.7rem] text-slate-600 mb-1">Price (optional)</label>
                    <input id="adminServiceEditPrice" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" id="adminServiceEditCancel" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" id="adminServiceEditSave" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors disabled:opacity-60 disabled:hover:bg-cyan-600">
                        <span id="adminServiceEditSpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        <span>Save changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var errorBox = document.getElementById('adminServiceError')
        var successBox = document.getElementById('adminServiceSuccess')
        var addForm = document.getElementById('adminAddServiceForm')
        var nameInput = document.getElementById('admin_service_name')
        var descInput = document.getElementById('admin_service_description')
        var durationInput = document.getElementById('admin_service_duration')
        var priceInput = document.getElementById('admin_service_price')
        var searchInput = document.getElementById('admin_service_search')
        var statusFilter = document.getElementById('admin_service_status_filter')
        var sortSelect = document.getElementById('admin_service_sort')
        var tableBody = document.getElementById('admin_service_table_body')

        var addServiceBtn = document.getElementById('adminAddServiceBtn')
        var addServiceSpinner = document.getElementById('adminAddServiceSpinner')

        var services = []
        var editingServiceId = null

        var serviceEditOverlay = document.getElementById('adminServiceEditOverlay')
        var serviceEditClose = document.getElementById('adminServiceEditClose')
        var serviceEditCancel = document.getElementById('adminServiceEditCancel')
        var serviceEditForm = document.getElementById('adminServiceEditForm')
        var serviceEditSubtitle = document.getElementById('adminServiceEditSubtitle')
        var serviceEditError = document.getElementById('adminServiceEditError')
        var serviceEditName = document.getElementById('adminServiceEditName')
        var serviceEditDescription = document.getElementById('adminServiceEditDescription')
        var serviceEditDuration = document.getElementById('adminServiceEditDuration')
        var serviceEditPrice = document.getElementById('adminServiceEditPrice')
        var serviceEditSave = document.getElementById('adminServiceEditSave')
        var serviceEditSpinner = document.getElementById('adminServiceEditSpinner')

        var confirmOverlay = document.getElementById('adminServiceConfirmOverlay')
        var confirmMessage = document.getElementById('adminServiceConfirmMessage')
        var confirmDetails = document.getElementById('adminServiceConfirmDetails')
        var confirmOk = document.getElementById('adminServiceConfirmOk')
        var confirmCancel = document.getElementById('adminServiceConfirmCancel')
        var confirmResolver = null
        var confirmCountdownTimer = null
        var confirmOkOriginalText = null
        var serviceErrorTimer = null
        var serviceSuccessTimer = null

        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
        }

        function stopBoxTimer(type) {
            if (type === 'error' && serviceErrorTimer) {
                clearTimeout(serviceErrorTimer)
                serviceErrorTimer = null
            }
            if (type === 'success' && serviceSuccessTimer) {
                clearTimeout(serviceSuccessTimer)
                serviceSuccessTimer = null
            }
        }

        function scheduleBoxDismiss(type, callback) {
            stopBoxTimer(type)
            var timer = setTimeout(function () {
                callback()
            }, 3200)
            if (type === 'error') serviceErrorTimer = timer
            if (type === 'success') serviceSuccessTimer = timer
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

        function showServiceError(message) {
            if (!errorBox) return
            stopBoxTimer('error')
            errorBox.textContent = message || ''
            if (message) {
                errorBox.classList.remove('hidden')
                scheduleBoxDismiss('error', function () { showServiceError('') })
            } else {
                errorBox.classList.add('hidden')
            }
        }

        function showServiceSuccess(message) {
            if (!successBox) return
            stopBoxTimer('success')
            successBox.textContent = message || ''
            if (message) {
                successBox.classList.remove('hidden')
                scheduleBoxDismiss('success', function () { showServiceSuccess('') })
            } else {
                successBox.classList.add('hidden')
            }
        }

        function showInlineBox(el, message) {
            if (!el) return
            el.textContent = message || ''
            el.classList.toggle('hidden', !message)
        }

        function setServiceAddSubmitting(isSubmitting) {
            if (addServiceBtn) addServiceBtn.disabled = !!isSubmitting
            if (addServiceSpinner) addServiceSpinner.classList.toggle('hidden', !isSubmitting)
        }

        function setServiceEditSubmitting(isSubmitting) {
            if (serviceEditSave) serviceEditSave.disabled = !!isSubmitting
            if (serviceEditSpinner) serviceEditSpinner.classList.toggle('hidden', !isSubmitting)
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

        function openServiceEditModal(service) {
            if (!serviceEditOverlay) return
            editingServiceId = service && service.service_id ? String(service.service_id) : null
            showInlineBox(serviceEditError, '')
            setServiceEditSubmitting(false)

            var subtitle = service && service.service_name ? String(service.service_name) : ('Service #' + (service && service.service_id ? service.service_id : ''))
            if (serviceEditSubtitle) {
                serviceEditSubtitle.textContent = 'Editing — ' + subtitle
            }
            if (serviceEditName) serviceEditName.value = service.service_name || ''
            if (serviceEditDescription) serviceEditDescription.value = service.description || ''
            if (serviceEditDuration) serviceEditDuration.value = service.duration_minutes != null ? String(service.duration_minutes) : ''
            if (serviceEditPrice) serviceEditPrice.value = service.price != null ? String(service.price) : ''

            serviceEditOverlay.classList.remove('hidden')
            serviceEditOverlay.classList.add('flex')
        }

        function closeServiceEditModal() {
            if (!serviceEditOverlay) return
            serviceEditOverlay.classList.add('hidden')
            serviceEditOverlay.classList.remove('flex')
            editingServiceId = null
            showInlineBox(serviceEditError, '')
            setServiceEditSubmitting(false)
        }

        if (serviceEditClose) serviceEditClose.addEventListener('click', closeServiceEditModal)
        if (serviceEditCancel) serviceEditCancel.addEventListener('click', closeServiceEditModal)
        if (serviceEditOverlay) {
            serviceEditOverlay.addEventListener('click', function (e) {
                if (e.target === serviceEditOverlay) closeServiceEditModal()
            })
        }

        function loadServices() {
            if (!tableBody) return
            tableBody.innerHTML = '<tr><td colspan="7" class="py-4 text-center text-[0.78rem] text-slate-400">Loading services…</td></tr>'

            apiFetch("{{ url('/api/services') }}?per_page=100", {
                method: 'GET'
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        tableBody.innerHTML = '<tr><td colspan="7" class="py-4 text-center text-[0.78rem] text-red-500">Failed to load services.</td></tr>'
                        return
                    }
                    var payload = result.data
                    services = Array.isArray(payload.data) ? payload.data : payload
                    renderServices()
                })
                .catch(function () {
                    tableBody.innerHTML = '<tr><td colspan="7" class="py-4 text-center text-[0.78rem] text-red-500">Network error while loading services.</td></tr>'
                })
        }

        function renderServices() {
            if (!tableBody) return

            var query = searchInput ? searchInput.value.toLowerCase().trim() : ''
            var status = statusFilter ? String(statusFilter.value || '') : ''
            var sort = sortSelect ? sortSelect.value : 'name_asc'

            var filtered = services.slice().filter(function (service) {
                var name = (service.service_name || '').toLowerCase()
                var description = (service.description || '').toLowerCase()
                if (!query) return true
                return name.indexOf(query) !== -1 || description.indexOf(query) !== -1
            })

            if (status) {
                filtered = filtered.filter(function (service) {
                    var isActive = service && service.is_active !== false
                    return status === 'active' ? isActive : !isActive
                })
            }

            filtered.sort(function (a, b) {
                if (sort === 'price_asc' || sort === 'price_desc') {
                    var pa = parseFloat(a.price || '0')
                    var pb = parseFloat(b.price || '0')
                    if (pa < pb) return sort === 'price_asc' ? -1 : 1
                    if (pa > pb) return sort === 'price_asc' ? 1 : -1
                    return 0
                }

                if (sort === 'created_asc' || sort === 'created_desc') {
                    var ta = a && a.created_at ? Date.parse(String(a.created_at)) : 0
                    var tb = b && b.created_at ? Date.parse(String(b.created_at)) : 0
                    if (isNaN(ta)) ta = 0
                    if (isNaN(tb)) tb = 0
                    if (ta < tb) return sort === 'created_asc' ? -1 : 1
                    if (ta > tb) return sort === 'created_asc' ? 1 : -1
                    return 0
                }

                var na = (a.service_name || '').toLowerCase()
                var nb = (b.service_name || '').toLowerCase()
                if (na < nb) return sort === 'name_asc' ? -1 : 1
                if (na > nb) return sort === 'name_asc' ? 1 : -1
                return 0
            })

            if (!filtered.length) {
                tableBody.innerHTML = '<tr><td colspan="7" class="py-4 text-center text-[0.78rem] text-slate-400">No services found.</td></tr>'
                return
            }

            tableBody.innerHTML = ''

            filtered.forEach(function (service) {
                var tr = document.createElement('tr')
                tr.className = 'border-b border-slate-50 last:border-0'

                var price = service.price != null ? '₱' + parseFloat(service.price).toFixed(2) : '—'
                var duration = service.duration_minutes != null ? String(service.duration_minutes) + ' min' : '—'
                var isActive = service && service.is_active !== false
                var statusText = isActive ? 'Active' : 'Inactive'
                var statusClass = isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-50 text-slate-600 border-slate-100'

                tr.innerHTML =
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">#' + service.service_id + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-700">' + (service.service_name || '') + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-500">' + (service.description || '') + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-700">' + duration + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem] text-slate-700">' + price + '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem]">' +
                        '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.68rem] font-medium border ' + statusClass + '">' + statusText + '</span>' +
                    '</td>' +
                    '<td class="py-2 pr-4 text-[0.78rem]">' +
                        '<div class="flex items-center gap-2">' +
                            '<button type="button" class="px-2 py-1 rounded-md border border-cyan-200 bg-cyan-50 text-cyan-700 hover:bg-cyan-100 text-[0.72rem] font-semibold admin-service-edit" data-service-id="' + service.service_id + '">Edit</button>' +
                            (isActive
                                ? '<button type="button" class="px-2 py-1 rounded-md border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 text-[0.72rem] font-semibold admin-service-disable" data-service-id="' + service.service_id + '">Disable</button>'
                                : '<button type="button" class="px-2 py-1 rounded-md border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-[0.72rem] font-semibold admin-service-enable" data-service-id="' + service.service_id + '">Re-enable</button>'
                            ) +
                        '</div>' +
                    '</td>'

                tableBody.appendChild(tr)
            })

            var editButtons = tableBody.querySelectorAll('.admin-service-edit')
            editButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-service-id')
                    var service = services.find(function (s) { return String(s.service_id) === String(id) })
                    if (!service) return
                    showServiceError('')
                    showServiceSuccess('')
                    openServiceEditModal(service)
                })
            })

            var disableButtons = tableBody.querySelectorAll('.admin-service-disable')
            disableButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-service-id')
                    if (!id) return
                    var self = this
                    var service = services.find(function (s) { return String(s.service_id) === String(id) })
                    if (service && service.is_active === false) return

                    confirmAction('Are you sure you want to disable this service?', { countdownSeconds: 3, confirmText: 'Disable' })
                        .then(function (confirmed) {
                            if (!confirmed) return
                            self.disabled = true
                            self.classList.add('opacity-60', 'cursor-not-allowed')
                            self.textContent = 'Disabling…'

                            apiFetch("{{ url('/api/services') }}/" + id, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ is_active: false })
                            })
                                .then(function (response) {
                                    return response.json().then(function (data) {
                                        return { ok: response.ok, status: response.status, data: data }
                                    }).catch(function () {
                                        return { ok: response.ok, status: response.status, data: null }
                                    })
                                })
                                .then(function (result) {
                                    if (!result.ok) {
                                        showServiceError(readApiMessage(result, 'Failed to disable service.'))
                                        self.disabled = false
                                        self.classList.remove('opacity-60', 'cursor-not-allowed')
                                        self.textContent = 'Disable'
                                        return
                                    }
                                    showServiceSuccess('Service disabled.')
                                    loadServices()
                                })
                                .catch(function () {
                                    showServiceError('Network error while disabling service.')
                                    self.disabled = false
                                    self.classList.remove('opacity-60', 'cursor-not-allowed')
                                    self.textContent = 'Disable'
                                })
                        })
                })
            })

            var enableButtons = tableBody.querySelectorAll('.admin-service-enable')
            enableButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-service-id')
                    if (!id) return
                    var self = this
                    var service = services.find(function (s) { return String(s.service_id) === String(id) })
                    if (service && service.is_active !== false) return

                    confirmAction('Are you sure you want to re-enable this service?', { countdownSeconds: 3, confirmText: 'Re-enable' })
                        .then(function (confirmed) {
                            if (!confirmed) return
                            self.disabled = true
                            self.classList.add('opacity-60', 'cursor-not-allowed')
                            self.textContent = 'Enabling…'

                            apiFetch("{{ url('/api/services') }}/" + id, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ is_active: true })
                            })
                                .then(function (response) {
                                    return response.json().then(function (data) {
                                        return { ok: response.ok, status: response.status, data: data }
                                    }).catch(function () {
                                        return { ok: response.ok, status: response.status, data: null }
                                    })
                                })
                                .then(function (result) {
                                    if (!result.ok) {
                                        showServiceError(readApiMessage(result, 'Failed to re-enable service.'))
                                        self.disabled = false
                                        self.classList.remove('opacity-60', 'cursor-not-allowed')
                                        self.textContent = 'Re-enable'
                                        return
                                    }
                                    showServiceSuccess('Service re-enabled.')
                                    loadServices()
                                })
                                .catch(function () {
                                    showServiceError('Network error while re-enabling service.')
                                    self.disabled = false
                                    self.classList.remove('opacity-60', 'cursor-not-allowed')
                                    self.textContent = 'Re-enable'
                                })
                        })
                })
            })
        }

        if (serviceEditForm) {
            serviceEditForm.addEventListener('submit', function (e) {
                e.preventDefault()
                if (!editingServiceId) return
                if (serviceEditSave && serviceEditSave.disabled) return

                showInlineBox(serviceEditError, '')
                showServiceError('')

                var name = serviceEditName ? String(serviceEditName.value || '').trim() : ''
                var description = serviceEditDescription ? String(serviceEditDescription.value || '').trim() : ''
                var durationRaw = serviceEditDuration ? String(serviceEditDuration.value || '').trim() : ''
                var priceRaw = serviceEditPrice ? String(serviceEditPrice.value || '').trim() : ''

                if (!name) {
                    showInlineBox(serviceEditError, 'Service name is required.')
                    return
                }

                var price = null
                if (priceRaw !== '') {
                    price = parseFloat(priceRaw)
                    if (isNaN(price) || price < 0) {
                        showInlineBox(serviceEditError, 'Price must be a valid number (0 or higher).')
                        return
                    }
                }

                var durationMinutes = null
                if (durationRaw !== '') {
                    durationMinutes = parseInt(durationRaw, 10)
                    if (isNaN(durationMinutes) || durationMinutes < 1) {
                        showInlineBox(serviceEditError, 'Duration must be a valid number (1 minute or higher).')
                        return
                    }
                }

                confirmAction('Are you sure you want to save these changes?')
                    .then(function (confirmed) {
                        if (!confirmed) return

                        setServiceEditSubmitting(true)

                        var body = {
                            service_name: name,
                            description: description || null,
                            duration_minutes: durationRaw === '' ? null : durationMinutes,
                            price: priceRaw === '' ? null : price
                        }

                        apiFetch("{{ url('/api/services') }}/" + editingServiceId, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(body)
                        })
                            .then(function (response) {
                                return response.json().then(function (data) {
                                    return { ok: response.ok, status: response.status, data: data }
                                }).catch(function () {
                                    return { ok: response.ok, status: response.status, data: null }
                                })
                            })
                            .then(function (result) {
                                if (!result.ok) {
                                    if (result.status === 422 && result.data && result.data.errors) {
                                        var firstKey = Object.keys(result.data.errors)[0]
                                        var msg = firstKey && result.data.errors[firstKey] && result.data.errors[firstKey][0]
                                            ? result.data.errors[firstKey][0]
                                            : 'Validation error.'
                                        showInlineBox(serviceEditError, String(msg))
                                    } else {
                                        var msg2 = (result.data && result.data.message) ? result.data.message : 'Failed to update service.'
                                        showInlineBox(serviceEditError, String(msg2))
                                    }
                                    return
                                }

                                closeServiceEditModal()
                                showServiceSuccess('Service updated.')
                                loadServices()
                            })
                            .catch(function () {
                                showInlineBox(serviceEditError, 'Network error while updating service.')
                            })
                            .finally(function () {
                                setServiceEditSubmitting(false)
                            })
                    })
            })
        }

        if (addForm) {
            addForm.addEventListener('submit', function (e) {
                e.preventDefault()
                showServiceError('')
                showServiceSuccess('')

                var name = nameInput ? nameInput.value.trim() : ''
                var description = descInput ? descInput.value.trim() : ''
                var durationRaw = durationInput ? durationInput.value.trim() : ''
                var priceRaw = priceInput ? priceInput.value.trim() : ''

                if (!name) {
                    showServiceError('Service name is required.')
                    return
                }

                var body = {
                    service_name: name
                }
                if (description) {
                    body.description = description
                }
                if (durationRaw !== '') {
                    var durationMinutes = parseInt(durationRaw, 10)
                    if (isNaN(durationMinutes) || durationMinutes < 1) {
                        showServiceError('Duration must be a valid number (1 minute or higher).')
                        return
                    }
                    body.duration_minutes = durationMinutes
                }
                if (priceRaw !== '') {
                    body.price = parseFloat(priceRaw)
                }

                var detailsHtml = '<div class="grid grid-cols-2 gap-x-4 gap-y-1">' +
                    '<div class="text-slate-500">Name:</div><div class="text-slate-800 font-medium">' + escapeHtml(body.service_name) + '</div>' +
                    (body.description ? '<div class="text-slate-500">Description:</div><div class="text-slate-800 font-medium">' + escapeHtml(body.description) + '</div>' : '') +
                    (body.duration_minutes ? '<div class="text-slate-500">Duration:</div><div class="text-slate-800 font-medium">' + escapeHtml(body.duration_minutes) + ' minutes</div>' : '') +
                    (body.price ? '<div class="text-slate-500">Price:</div><div class="text-slate-800 font-medium">₱' + escapeHtml(body.price) + '</div>' : '') +
                '</div>'

                confirmAction('Are you sure you want to add this service?', { confirmText: 'Add', details: detailsHtml })
                    .then(function (confirmed) {
                        if (!confirmed) return

                        setServiceAddSubmitting(true)

                        apiFetch("{{ url('/api/services') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(body)
                        })
                            .then(function (response) {
                                return response.json().then(function (data) {
                                    return { ok: response.ok, data: data }
                                })
                            })
                            .then(function (result) {
                                if (!result.ok) {
                                    showServiceError(readApiMessage(result, 'Failed to add service.'))
                                    return
                                }
                                if (nameInput) nameInput.value = ''
                                if (descInput) descInput.value = ''
                                if (durationInput) durationInput.value = ''
                                if (priceInput) priceInput.value = ''
                                showServiceSuccess('Service added.')
                                loadServices()
                            })
                            .catch(function () {
                                showServiceError('Network error while adding service.')
                            })
                            .finally(function () {
                                setServiceAddSubmitting(false)
                            })
                    })
            })
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                renderServices()
            })
        }
        if (statusFilter) {
            statusFilter.addEventListener('change', function () {
                renderServices()
            })
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                renderServices()
            })
        }

        loadServices()
    })
</script>
