<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Doctor Settings</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Doctor</span>
    </div>
    <p class="text-xs text-slate-500 mb-4">
        Update your profile details, change your password, and optionally upload a signature image.
    </p>

    <div class="grid gap-4 grid-cols-1 lg:grid-cols-3 text-[0.78rem] text-slate-600">
        <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/60 lg:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-xs font-semibold text-slate-900">Profile</h3>
                    <p class="text-[0.7rem] text-slate-500">Basic information shown in patient-facing records.</p>
                </div>
                <x-lucide-circle-user class="w-[18px] h-[18px] text-cyan-600" />
            </div>

            <form id="doctorSettingsProfileForm" class="space-y-3">
                <div>
                    <label for="doctor_profile_name" class="block text-[0.7rem] text-slate-500 mb-1">Display name</label>
                    <input id="doctor_profile_name" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Dr. Juan Dela Cruz">
                </div>
                <div>
                    <label for="doctor_profile_specialization" class="block text-[0.7rem] text-slate-500 mb-1">Specialization</label>
                    <input id="doctor_profile_specialization" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Pediatrics, Internal Medicine">
                </div>
                <div>
                    <label for="doctor_profile_contact" class="block text-[0.7rem] text-slate-500 mb-1">Contact number</label>
                    <input id="doctor_profile_contact" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="09xx xxx xxxx">
                </div>
                <div class="flex items-center justify-between pt-1">
                    <p class="text-[0.68rem] text-slate-400">Profile settings are stored on this device for now.</p>
                    <button type="button" id="doctor_profile_save" class="inline-flex items-center gap-1 rounded-xl border border-cyan-500/40 bg-cyan-50 px-3 py-1.5 text-[0.72rem] font-semibold text-cyan-700 hover:bg-cyan-100">
                        <x-lucide-save class="w-[16px] h-[16px]" />
                        Save profile
                    </button>
                </div>
            </form>
        </div>

        <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/60 lg:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-xs font-semibold text-slate-900">Signature</h3>
                    <p class="text-[0.7rem] text-slate-500">Optional signature image for prescriptions and records.</p>
                </div>
                <x-lucide-hand class="w-[18px] h-[18px] text-slate-700" />
            </div>

            <form id="doctorSettingsSignatureForm" class="space-y-3">
                <div>
                    <label for="doctor_signature_file" class="block text-[0.7rem] text-slate-500 mb-1">Upload signature</label>
                    <input id="doctor_signature_file" type="file" accept="image/*" class="block w-full text-[0.78rem] text-slate-700 file:mr-3 file:rounded-lg file:border file:border-slate-200 file:bg-white file:px-3 file:py-1.5 file:text-[0.78rem] file:font-semibold file:text-slate-700 hover:file:bg-slate-50">
                </div>
                <div>
                    <div class="text-[0.7rem] text-slate-500 mb-1">Current signature</div>
                    <div id="doctor_signature_preview" class="flex items-center justify-center h-24 rounded-lg border border-dashed border-slate-300 bg-white text-[0.72rem] text-slate-400">
                        No signature uploaded yet.
                    </div>
                </div>
                <div class="flex items-center justify-between pt-1">
                    <p class="text-[0.68rem] text-slate-400">Signature is saved to your account for prescriptions and receipts.</p>
                    <button type="button" id="doctor_signature_save" class="inline-flex items-center gap-1 rounded-xl border border-cyan-500/40 bg-cyan-50 px-3 py-1.5 text-[0.72rem] font-semibold text-cyan-700 hover:bg-cyan-100">
                        <x-lucide-save class="w-[16px] h-[16px]" />
                        Save signature
                    </button>
                </div>
            </form>
        </div>

        <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/60 lg:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-xs font-semibold text-slate-900">Account</h3>
                    <p class="text-[0.7rem] text-slate-500">Change your password for the doctor account.</p>
                </div>
                <x-lucide-lock class="w-[18px] h-[18px] text-slate-700" />
            </div>

            <form id="doctorSettingsAccountForm" class="space-y-3">
                <div>
                    <label for="doctor_current_password" class="block text-[0.7rem] text-slate-500 mb-1">Current password</label>
                    <input id="doctor_current_password" type="password" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div>
                    <label for="doctor_new_password" class="block text-[0.7rem] text-slate-500 mb-1">New password</label>
                    <input id="doctor_new_password" type="password" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div>
                    <label for="doctor_confirm_password" class="block text-[0.7rem] text-slate-500 mb-1">Confirm new password</label>
                    <input id="doctor_confirm_password" type="password" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div class="flex items-center justify-between pt-1">
                    <p class="text-[0.68rem] text-slate-400">Password change can be wired to the API endpoint later.</p>
                    <button type="button" id="doctor_account_save" class="inline-flex items-center gap-1 rounded-xl border border-cyan-500/40 bg-cyan-50 px-3 py-1.5 text-[0.72rem] font-semibold text-cyan-700 hover:bg-cyan-100">
                        <x-lucide-save class="w-[16px] h-[16px]" />
                        Change password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-5 border border-slate-100 rounded-2xl p-4 bg-slate-50/60">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-xs font-semibold text-slate-900">Manage schedule availability</h3>
                <p class="text-[0.7rem] text-slate-500">Select time slots to mark yourself available/unavailable.</p>
            </div>
            <button type="button" id="doctor_manage_schedule_open" class="inline-flex items-center gap-1 rounded-xl border border-cyan-500/40 bg-cyan-50 px-3 py-1.5 text-[0.72rem] font-semibold text-cyan-700 hover:bg-cyan-100">
                <x-lucide-clock class="w-[16px] h-[16px]" />
                Manage
            </button>
        </div>
    </div>
</div>

<div id="doctorScheduleAvailabilityOverlay" class="hidden fixed inset-0 z-50 bg-slate-900/40 items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white border border-slate-200 shadow-[0_12px_30px_rgba(15,23,42,0.24)] overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3">
            <div>
                <div class="text-sm font-semibold text-slate-900" id="doctorScheduleAvailabilityTitle">Manage Availability</div>
                <div class="text-[0.72rem] text-slate-500">Pick slots and save.</div>
            </div>
            <button type="button" id="doctorScheduleAvailabilityClose" class="text-slate-400 hover:text-slate-600">
                <x-lucide-x class="w-[20px] h-[20px]" />
            </button>
        </div>
        <div class="p-5">
            <div id="doctorScheduleAvailabilityError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-3 items-end">
                <div>
                    <label for="doctorScheduleAvailabilityDayFilter" class="block text-[0.7rem] text-slate-600 mb-1">Filter by day</label>
                    <select id="doctorScheduleAvailabilityDayFilter" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                        <option value="">All days</option>
                        <option value="mon">Mon</option>
                        <option value="tue">Tue</option>
                        <option value="wed">Wed</option>
                        <option value="thu">Thu</option>
                        <option value="fri">Fri</option>
                        <option value="sat">Sat</option>
                        <option value="sun">Sun</option>
                    </select>
                </div>
                <div>
                    <label for="doctorScheduleAvailabilityMode" class="block text-[0.7rem] text-slate-600 mb-1">Action</label>
                    <select id="doctorScheduleAvailabilityMode" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                        <option value="unavailable">Mark unavailable</option>
                        <option value="available">Mark available</option>
                    </select>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" id="doctorScheduleAvailabilitySave" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors w-full disabled:opacity-60 disabled:hover:bg-cyan-600">
                        <span id="doctorScheduleAvailabilitySpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        Save
                    </button>
                </div>
            </div>

            <div id="doctorScheduleAvailabilityList" class="max-h-[55vh] overflow-y-auto scrollbar-hidden space-y-3"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var apiBasePath = "{{ request()->getBasePath() }}"
        function apiUrl(path) {
            return String(apiBasePath || '') + String(path || '')
        }

        function fetchAllDoctorSchedules(doctorId, onSuccess, onFailure) {
            var perPage = 100
            var page = 1
            var all = []

            function fail(message) {
                if (typeof onFailure === 'function') onFailure(message || 'Failed to load schedules.')
            }

            function fetchPage() {
                var url = apiUrl('/api/doctor-schedules') +
                    '?doctor_id=' + encodeURIComponent(doctorId) +
                    '&per_page=' + encodeURIComponent(perPage) +
                    '&page=' + encodeURIComponent(page)

                apiFetch(url, { method: 'GET' })
                    .then(function (response) { return readResponse(response) })
                    .then(function (result) {
                        if (!result.ok || !result.data) {
                            if (result.status === 401) {
                                fail('Session expired. Please log in again.')
                                return
                            }
                            var msg = (result.data && result.data.message) ? String(result.data.message) : 'Failed to load schedules.'
                            if (!result.data && result.raw) {
                                msg += ' HTTP ' + String(result.status || '')
                            }
                            fail(msg)
                            return
                        }

                        var payload = result.data
                        var items = Array.isArray(payload && payload.data) ? payload.data : []
                        all = all.concat(items)

                        var lastPage = parseInt(payload && payload.last_page ? payload.last_page : 1, 10)
                        if (isNaN(lastPage) || lastPage < 1) lastPage = 1

                        if (page < lastPage) {
                            page += 1
                            fetchPage()
                            return
                        }

                        if (typeof onSuccess === 'function') {
                            try {
                                onSuccess(all)
                            } catch (e) {
                                var renderMsg = 'Failed to render schedules.'
                                if (e && e.message) renderMsg += ' ' + String(e.message)
                                fail(renderMsg)
                            }
                        }
                    })
                    .catch(function (err) {
                        var msg = 'Network error while loading schedules.'
                        if (err && err.message) msg += ' ' + String(err.message)
                        fail(msg)
                    })
            }

            fetchPage()
        }

        var profileName = document.getElementById('doctor_profile_name')
        var profileSpecialization = document.getElementById('doctor_profile_specialization')
        var profileContact = document.getElementById('doctor_profile_contact')
        var profileSave = document.getElementById('doctor_profile_save')

        var signatureFile = document.getElementById('doctor_signature_file')
        var signaturePreview = document.getElementById('doctor_signature_preview')
        var signatureSave = document.getElementById('doctor_signature_save')

        var currentPassword = document.getElementById('doctor_current_password')
        var newPassword = document.getElementById('doctor_new_password')
        var confirmPassword = document.getElementById('doctor_confirm_password')
        var accountSave = document.getElementById('doctor_account_save')

        var manageScheduleOpen = document.getElementById('doctor_manage_schedule_open')
        var scheduleAvailabilityOverlay = document.getElementById('doctorScheduleAvailabilityOverlay')
        var scheduleAvailabilityTitle = document.getElementById('doctorScheduleAvailabilityTitle')
        var scheduleAvailabilityClose = document.getElementById('doctorScheduleAvailabilityClose')
        var scheduleAvailabilityError = document.getElementById('doctorScheduleAvailabilityError')
        var scheduleAvailabilityDayFilter = document.getElementById('doctorScheduleAvailabilityDayFilter')
        var scheduleAvailabilityMode = document.getElementById('doctorScheduleAvailabilityMode')
        var scheduleAvailabilityList = document.getElementById('doctorScheduleAvailabilityList')
        var scheduleAvailabilitySave = document.getElementById('doctorScheduleAvailabilitySave')
        var scheduleAvailabilitySpinner = document.getElementById('doctorScheduleAvailabilitySpinner')

        var storageKey = 'opol_doctor_settings'
        var currentDoctorId = null
        var loadedScheduleSlots = []

        function loadDoctorSettings() {
            var raw = null
            try {
                raw = window.localStorage ? window.localStorage.getItem(storageKey) : null
            } catch (_) {
                raw = null
            }
            if (!raw) return

            try {
                var config = JSON.parse(raw)
                if (profileName && config.profile_name) profileName.value = config.profile_name
                if (profileSpecialization && config.profile_specialization) profileSpecialization.value = config.profile_specialization
                if (profileContact && config.profile_contact) profileContact.value = config.profile_contact
            } catch (_) {
            }
        }

        function saveProfile() {
            var raw = null
            try {
                raw = window.localStorage ? window.localStorage.getItem(storageKey) : null
            } catch (_) {
                raw = null
            }
            var config = {}
            if (raw) {
                try {
                    config = JSON.parse(raw) || {}
                } catch (_) {
                    config = {}
                }
            }
            config.profile_name = profileName ? profileName.value.trim() : ''
            config.profile_specialization = profileSpecialization ? profileSpecialization.value.trim() : ''
            config.profile_contact = profileContact ? profileContact.value.trim() : ''

            try {
                if (window.localStorage) {
                    window.localStorage.setItem(storageKey, JSON.stringify(config))
                }
            } catch (_) {
            }
        }

        function loadServerSignature() {
            if (typeof apiFetch !== 'function') return

            apiFetch(apiUrl('/api/user'), { method: 'GET' })
                .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data } }) })
                .then(function (result) {
                    if (!result.ok || !result.data) return
                    currentDoctorId = result.data.user_id ? String(result.data.user_id) : currentDoctorId
                    var url = result.data.signature_url ? String(result.data.signature_url) : ''
                    if (!signaturePreview) return
                    if (url) {
                        signaturePreview.innerHTML = '<img alt="Signature" src="' + url + '" class="max-h-20 max-w-full object-contain">'
                        signaturePreview.classList.remove('text-slate-400')
                    }
                })
                .catch(function () {})
        }

        function readResponse(response) {
            return response.text().then(function (text) {
                var data = null
                try {
                    data = text ? JSON.parse(text) : null
                } catch (e) {
                    data = null
                }
                return { ok: response.ok, status: response.status, data: data, raw: text }
            })
        }

        function showScheduleAvailabilityError(message) {
            if (!scheduleAvailabilityError) return
            scheduleAvailabilityError.textContent = message || ''
            scheduleAvailabilityError.classList.toggle('hidden', !message)
        }

        function setScheduleAvailabilitySubmitting(isSubmitting) {
            if (scheduleAvailabilitySave) scheduleAvailabilitySave.disabled = !!isSubmitting
            if (scheduleAvailabilitySpinner) scheduleAvailabilitySpinner.classList.toggle('hidden', !isSubmitting)
        }

        function openScheduleAvailabilityModal() {
            if (!currentDoctorId) {
                showScheduleAvailabilityError('Unable to identify the current doctor.')
                return
            }
            loadedScheduleSlots = []
            showScheduleAvailabilityError('')
            setScheduleAvailabilitySubmitting(false)
            if (scheduleAvailabilityTitle) scheduleAvailabilityTitle.textContent = 'Manage Availability'
            if (scheduleAvailabilityDayFilter) scheduleAvailabilityDayFilter.value = ''
            if (scheduleAvailabilityMode) scheduleAvailabilityMode.value = 'unavailable'
            if (scheduleAvailabilityList) scheduleAvailabilityList.innerHTML = 'Loading schedules…'

            if (scheduleAvailabilityOverlay) {
                scheduleAvailabilityOverlay.classList.remove('hidden')
                scheduleAvailabilityOverlay.classList.add('flex')
            }

            loadScheduleAvailabilitySlots()
        }

        function closeScheduleAvailabilityModal() {
            if (scheduleAvailabilityOverlay) {
                scheduleAvailabilityOverlay.classList.add('hidden')
                scheduleAvailabilityOverlay.classList.remove('flex')
            }
            loadedScheduleSlots = []
            showScheduleAvailabilityError('')
            setScheduleAvailabilitySubmitting(false)
        }

        function loadScheduleAvailabilitySlots() {
            if (!currentDoctorId || typeof apiFetch !== 'function') return
            if (!scheduleAvailabilityList) return

            scheduleAvailabilityList.innerHTML = 'Loading schedules…'
            loadedScheduleSlots = []

            fetchAllDoctorSchedules(currentDoctorId, function (all) {
                loadedScheduleSlots = Array.isArray(all) ? all : []
                renderDoctorScheduleAvailabilityList()
            }, function (message) {
                showScheduleAvailabilityError(message || 'Failed to load schedules.')
                scheduleAvailabilityList.innerHTML = ''
            })
        }

        function renderDoctorScheduleAvailabilityList() {
            if (!scheduleAvailabilityList) return
            var dayFilter = scheduleAvailabilityDayFilter ? String(scheduleAvailabilityDayFilter.value || '').toLowerCase() : ''
            var dayOrder = [
                { key: 'mon', label: 'Monday' },
                { key: 'tue', label: 'Tuesday' },
                { key: 'wed', label: 'Wednesday' },
                { key: 'thu', label: 'Thursday' },
                { key: 'fri', label: 'Friday' },
                { key: 'sat', label: 'Saturday' },
                { key: 'sun', label: 'Sunday' }
            ]

            var grouped = {}
            for (var i = 0; i < dayOrder.length; i++) {
                grouped[dayOrder[i].key] = []
            }

            var slots = loadedScheduleSlots || []
            for (var x = 0; x < slots.length; x++) {
                var s = slots[x]
                var key = s && s.day_of_week ? String(s.day_of_week).toLowerCase() : ''
                if (!key || !grouped[key]) continue
                if (dayFilter && dayFilter !== key) continue
                grouped[key].push(s)
            }

            for (var j = 0; j < dayOrder.length; j++) {
                var dayKey = dayOrder[j].key
                grouped[dayKey].sort(function (a, b) {
                    var sa = String(a.start_time || '').slice(0, 5)
                    var sb = String(b.start_time || '').slice(0, 5)
                    if (sa < sb) return -1
                    if (sa > sb) return 1
                    return 0
                })
            }

            var html = ''
            for (var k = 0; k < dayOrder.length; k++) {
                var d = dayOrder[k]
                var rows = grouped[d.key] || []
                if (!rows.length) continue
                html += '<div class="rounded-xl border border-slate-200 bg-white p-3">' +
                    '<div class="text-[0.72rem] font-semibold text-slate-900 mb-2">' + d.label + '</div>'

                rows.forEach(function (s) {
                    var start = String(s.start_time || '').slice(0, 5)
                    var end = String(s.end_time || '').slice(0, 5)
                    var label = start + '–' + end
                    var isUnavailable = s.is_available === false
                    var badgeClass = isUnavailable ? 'text-rose-700 bg-rose-50 border-rose-100' : 'text-emerald-700 bg-emerald-50 border-emerald-100'
                    var badgeText = isUnavailable ? 'Unavailable' : 'Available'

                    html += '<label class="flex items-center justify-between gap-3 rounded-lg border border-slate-100 bg-slate-50/60 px-3 py-2 mb-1">' +
                        '<div class="flex items-center gap-2">' +
                            '<input type="checkbox" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" data-schedule-id="' + s.schedule_id + '">' +
                            '<span class="text-[0.78rem] text-slate-700 font-semibold">' + label + '</span>' +
                        '</div>' +
                        '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.68rem] font-semibold border ' + badgeClass + '">' + badgeText + '</span>' +
                    '</label>'
                })

                html += '</div>'
            }

            if (!html) {
                html = '<div class="text-[0.78rem] text-slate-500">No schedules found for the selected filter.</div>'
            }

            scheduleAvailabilityList.innerHTML = html
        }

        function handlePasswordChange() {
            var current = currentPassword ? currentPassword.value : ''
            var next = newPassword ? newPassword.value : ''
            var confirm = confirmPassword ? confirmPassword.value : ''

            if (!current || !next || !confirm) {
                window.alert('Please complete all password fields.')
                return
            }
            if (next !== confirm) {
                window.alert('New password and confirmation do not match.')
                return
            }

            window.alert('Password change wiring to the API can be implemented here.')
        }

        if (profileSave) {
            profileSave.addEventListener('click', function () {
                saveProfile()
                profileSave.classList.add('bg-cyan-100')
                setTimeout(function () {
                    profileSave.classList.remove('bg-cyan-100')
                }, 600)
            })
        }

        if (signatureSave) {
            signatureSave.addEventListener('click', function () {
                if (!signatureFile || !signatureFile.files || signatureFile.files.length === 0) {
                    window.alert('Please choose a signature image first.')
                    return
                }
                if (typeof apiFetch !== 'function') {
                    window.alert('API client is not available.')
                    return
                }

                var file = signatureFile.files[0]
                var formData = new FormData()
                formData.append('signature', file)

                signatureSave.disabled = true

                apiFetch(apiUrl('/api/users/me/signature'), {
                    method: 'POST',
                    body: formData
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data }
                        }).catch(function () {
                            return { ok: response.ok, data: null }
                        })
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            var msg = (result.data && result.data.message) ? String(result.data.message) : 'Unable to upload signature.'
                            window.alert(msg)
                            return
                        }
                        var url = result.data && result.data.signature_url ? String(result.data.signature_url) : ''
                        if (signaturePreview) {
                            if (url) {
                                signaturePreview.innerHTML = '<img alt="Signature" src="' + url + '" class="max-h-20 max-w-full object-contain">'
                                signaturePreview.classList.remove('text-slate-400')
                            } else {
                                signaturePreview.textContent = 'Signature uploaded'
                                signaturePreview.classList.remove('text-slate-400')
                                signaturePreview.classList.add('text-slate-700')
                            }
                        }
                        if (signatureFile) signatureFile.value = ''
                    })
                    .catch(function () {
                        window.alert('Network error while uploading signature.')
                    })
                    .finally(function () {
                        signatureSave.disabled = false
                    })
            })
        }

        if (accountSave) {
            accountSave.addEventListener('click', function () {
                handlePasswordChange()
            })
        }

        if (manageScheduleOpen) {
            manageScheduleOpen.addEventListener('click', function () {
                openScheduleAvailabilityModal()
            })
        }
        if (scheduleAvailabilityClose) {
            scheduleAvailabilityClose.addEventListener('click', function () {
                closeScheduleAvailabilityModal()
            })
        }
        if (scheduleAvailabilityOverlay) {
            scheduleAvailabilityOverlay.addEventListener('click', function (e) {
                if (e.target === scheduleAvailabilityOverlay) {
                    closeScheduleAvailabilityModal()
                }
            })
        }
        if (scheduleAvailabilityDayFilter) {
            scheduleAvailabilityDayFilter.addEventListener('change', function () {
                renderDoctorScheduleAvailabilityList()
            })
        }
        if (scheduleAvailabilitySave) {
            scheduleAvailabilitySave.addEventListener('click', function () {
                showScheduleAvailabilityError('')
                if (!currentDoctorId) {
                    showScheduleAvailabilityError('Unable to identify the current doctor.')
                    return
                }
                if (!scheduleAvailabilityList) {
                    showScheduleAvailabilityError('Schedule list not available.')
                    return
                }

                var checked = scheduleAvailabilityList.querySelectorAll('input[type="checkbox"][data-schedule-id]:checked')
                var ids = []
                checked.forEach(function (c) {
                    var id = c.getAttribute('data-schedule-id')
                    if (id) ids.push(parseInt(id, 10))
                })

                if (!ids.length) {
                    showScheduleAvailabilityError('Select at least one time slot.')
                    return
                }

                var mode = scheduleAvailabilityMode ? String(scheduleAvailabilityMode.value || '') : 'unavailable'
                var isAvailable = mode === 'available'

                var confirmed = window.confirm('Are you sure you want to save this schedule?')
                if (!confirmed) return

                setScheduleAvailabilitySubmitting(true)
                apiFetch(apiUrl('/api/doctor-schedules/bulk-availability'), {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        schedule_ids: ids,
                        is_available: isAvailable
                    })
                })
                    .then(function (response) { return readResponse(response) })
                    .then(function (result) {
                        if (!result.ok) {
                            var msg = (result.data && result.data.message) ? String(result.data.message) : 'Failed to update availability.'
                            showScheduleAvailabilityError(msg)
                            return
                        }
                        loadScheduleAvailabilitySlots()
                    })
                    .catch(function () {
                        showScheduleAvailabilityError('Network error while updating availability.')
                    })
                    .finally(function () {
                        setScheduleAvailabilitySubmitting(false)
                    })
            })
        }

        loadDoctorSettings()
        loadServerSignature()
    })
</script>
