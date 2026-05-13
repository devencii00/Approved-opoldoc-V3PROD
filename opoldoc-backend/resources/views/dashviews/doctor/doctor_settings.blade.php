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
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="doctor_profile_firstname" class="block text-[0.7rem] text-slate-500 mb-1">First name</label>
                        <input id="doctor_profile_firstname" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    </div>
                    <div>
                        <label for="doctor_profile_lastname" class="block text-[0.7rem] text-slate-500 mb-1">Last name</label>
                        <input id="doctor_profile_lastname" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    </div>
                </div>
                <div>
                    <label for="doctor_profile_middlename" class="block text-[0.7rem] text-slate-500 mb-1">Middle name (optional)</label>
                    <input id="doctor_profile_middlename" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div>
                    <label for="doctor_profile_address" class="block text-[0.7rem] text-slate-500 mb-1">Address</label>
                    <input id="doctor_profile_address" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div>
                    <label for="doctor_profile_contact" class="block text-[0.7rem] text-slate-500 mb-1">Contact number</label>
                    <input id="doctor_profile_contact" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="09xx xxx xxxx">
                </div>
                <div class="flex items-center justify-between pt-1">
                    <p id="doctorProfileNotice" class="text-[0.68rem] text-slate-400"></p>
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
                    <h3 class="text-xs font-semibold text-slate-900">Profile Picture</h3>
                    <p class="text-[0.7rem] text-slate-500">Optional profile picture.</p>
                </div>
                <x-lucide-image class="w-[18px] h-[18px] text-slate-700" />
            </div>

            <form id="doctorSettingsProfPathForm" class="space-y-3">
                <div>
                    <label for="doctor_prof_path_file" class="block text-[0.7rem] text-slate-500 mb-1">Upload profile picture</label>
                    <input id="doctor_prof_path_file" type="file" accept="image/*" class="block w-full text-[0.78rem] text-slate-700 file:mr-3 file:rounded-lg file:border file:border-slate-200 file:bg-white file:px-3 file:py-1.5 file:text-[0.78rem] file:font-semibold file:text-slate-700 hover:file:bg-slate-50">
                </div>
                <div>
                    <div class="text-[0.7rem] text-slate-500 mb-1">Current picture</div>
                    <div id="doctor_prof_path_preview" class="flex items-center justify-center h-24 rounded-lg border border-dashed border-slate-300 bg-white text-[0.72rem] text-slate-400">
                        No picture uploaded yet.
                    </div>
                </div>
                <div class="flex items-center justify-between pt-1">
                    <p class="text-[0.68rem] text-slate-400"></p>
                    <button type="button" id="doctor_prof_path_save" class="inline-flex items-center gap-1 rounded-xl border border-cyan-500/40 bg-cyan-50 px-3 py-1.5 text-[0.72rem] font-semibold text-cyan-700 hover:bg-cyan-100">
                        <span id="doctorProfPathSaveSpinner" class="hidden w-3.5 h-3.5 border-2 border-cyan-700/30 border-t-cyan-700 rounded-full animate-spin"></span>
                        <x-lucide-save id="doctorProfPathSaveIcon" class="w-[16px] h-[16px]" />
                        <span id="doctorProfPathSaveLabel">Save picture</span>
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
                        <span id="doctorSignatureSaveSpinner" class="hidden w-3.5 h-3.5 border-2 border-cyan-700/30 border-t-cyan-700 rounded-full animate-spin"></span>
                        <x-lucide-save id="doctorSignatureSaveIcon" class="w-[16px] h-[16px]" />
                        <span id="doctorSignatureSaveLabel">Save signature</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/60 lg:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-xs font-semibold text-slate-900">Account password</h3>
                    <p class="text-[0.7rem] text-slate-500">Verify your current password before setting a new one.</p>
                </div>
                <x-lucide-lock class="w-[18px] h-[18px] text-slate-700" />
            </div>

            <div id="doctorAccountIdle" class="rounded-2xl border border-slate-200 bg-white p-4">
                <button type="button" id="doctor_account_start" class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/40 bg-cyan-50 px-3 py-2 text-[0.78rem] font-semibold text-cyan-700 hover:bg-cyan-100">
                    <x-lucide-key class="w-[18px] h-[18px]" />
                    Change password
                </button>
            </div>

            <div id="doctorAccountVerifyStep" class="hidden rounded-2xl border border-slate-200 bg-white p-4">
                <div class="text-[0.72rem] font-semibold text-slate-900 mb-3">Verify current password</div>
                <div>
                    <label for="doctor_current_password" class="block text-[0.7rem] text-slate-500 mb-1">Current password</label>
                    <input id="doctor_current_password" type="password" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div class="mt-3 flex items-center justify-end gap-2">
                    <button type="button" id="doctor_account_cancel" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="button" id="doctor_account_verify" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-900 text-white text-[0.78rem] font-semibold hover:bg-slate-800">
                        <span id="doctorAccountVerifySpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        <span id="doctorAccountVerifyLabel">Verify</span>
                    </button>
                </div>
            </div>

            <div id="doctorAccountChangeStep" class="hidden rounded-2xl border border-slate-200 bg-white p-4">
                <div class="text-[0.72rem] font-semibold text-slate-900 mb-3">Set new password</div>
                <div class="space-y-3">
                    <div>
                        <label for="doctor_new_password" class="block text-[0.7rem] text-slate-500 mb-1">New password</label>
                        <input id="doctor_new_password" type="password" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    </div>
                    <div>
                        <label for="doctor_confirm_password" class="block text-[0.7rem] text-slate-500 mb-1">Confirm new password</label>
                        <input id="doctor_confirm_password" type="password" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-end gap-2">
                    <button type="button" id="doctor_account_back" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Back</button>
                    <button type="button" id="doctor_account_save" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700">
                        <span id="doctorAccountSaveSpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        Save new password
                    </button>
                </div>
            </div>
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

        var profileFirstName = document.getElementById('doctor_profile_firstname')
        var profileLastName = document.getElementById('doctor_profile_lastname')
        var profileMiddleName = document.getElementById('doctor_profile_middlename')
        var profileAddress = document.getElementById('doctor_profile_address')
        var profileContact = document.getElementById('doctor_profile_contact')
        var profileSave = document.getElementById('doctor_profile_save')
        var profileNotice = document.getElementById('doctorProfileNotice')

        var profPathFile = document.getElementById('doctor_prof_path_file')
        var profPathPreview = document.getElementById('doctor_prof_path_preview')
        var profPathSave = document.getElementById('doctor_prof_path_save')
        var profPathSaveSpinner = document.getElementById('doctorProfPathSaveSpinner')
        var profPathSaveIcon = document.getElementById('doctorProfPathSaveIcon')
        var profPathSaveLabel = document.getElementById('doctorProfPathSaveLabel')

        var signatureFile = document.getElementById('doctor_signature_file')
        var signaturePreview = document.getElementById('doctor_signature_preview')
        var signatureSave = document.getElementById('doctor_signature_save')
        var signatureSaveSpinner = document.getElementById('doctorSignatureSaveSpinner')
        var signatureSaveIcon = document.getElementById('doctorSignatureSaveIcon')
        var signatureSaveLabel = document.getElementById('doctorSignatureSaveLabel')

        var currentPassword = document.getElementById('doctor_current_password')
        var newPassword = document.getElementById('doctor_new_password')
        var confirmPassword = document.getElementById('doctor_confirm_password')
        var accountSave = document.getElementById('doctor_account_save')
        var accountStart = document.getElementById('doctor_account_start')
        var accountCancel = document.getElementById('doctor_account_cancel')
        var accountVerify = document.getElementById('doctor_account_verify')
        var accountBack = document.getElementById('doctor_account_back')
        var verifySpinner = document.getElementById('doctorAccountVerifySpinner')
        var verifyLabel = document.getElementById('doctorAccountVerifyLabel')
        var saveSpinner = document.getElementById('doctorAccountSaveSpinner')
        var accountIdle = document.getElementById('doctorAccountIdle')
        var accountVerifyStep = document.getElementById('doctorAccountVerifyStep')
        var accountChangeStep = document.getElementById('doctorAccountChangeStep')
        var passwordVerifyToken = null
        var cooldownTimer = null

        var passwordTokenKey = 'opol_doctor_pw_verify_token'
        var passwordTokenExpKey = 'opol_doctor_pw_verify_expires_at'
        var passwordCooldownUntilKey = 'opol_doctor_pw_verify_cooldown_until'

        function safeLocalGet(key) { try { return window.localStorage ? window.localStorage.getItem(key) : null } catch (_) { return null } }
        function safeLocalSet(key, value) { try { if (window.localStorage) window.localStorage.setItem(key, value) } catch (_) {} }
        function safeLocalRemove(key) { try { if (window.localStorage) window.localStorage.removeItem(key) } catch (_) {} }

        function persistPasswordToken(token, expiresInSeconds) {
            if (!token) return
            var ms = parseInt(String(expiresInSeconds || 0), 10)
            ms = isNaN(ms) || ms < 1 ? 600 : ms
            var exp = Date.now() + ms * 1000
            safeLocalSet(passwordTokenKey, String(token))
            safeLocalSet(passwordTokenExpKey, String(exp))
        }

        function clearPasswordToken() {
            safeLocalRemove(passwordTokenKey)
            safeLocalRemove(passwordTokenExpKey)
            passwordVerifyToken = null
        }

        function setAccountStep(step) {
            if (accountIdle) accountIdle.classList.toggle('hidden', step !== 'idle')
            if (accountVerifyStep) accountVerifyStep.classList.toggle('hidden', step !== 'verify')
            if (accountChangeStep) accountChangeStep.classList.toggle('hidden', step !== 'change')
        }

        function restorePasswordTokenIfAny() {
            var token = safeLocalGet(passwordTokenKey)
            var expRaw = safeLocalGet(passwordTokenExpKey)
            if (!token || !expRaw) {
                clearPasswordToken()
                return
            }
            var exp = parseInt(String(expRaw || ''), 10)
            if (isNaN(exp) || exp <= Date.now()) {
                clearPasswordToken()
                return
            }
            passwordVerifyToken = String(token)
            setAccountStep('change')
        }

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
            if (typeof apiFetch !== 'function') return
            apiFetch(apiUrl('/api/user'), { method: 'GET' })
                .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data } }) })
                .then(function (result) {
                    if (!result.ok || !result.data) return
                    currentDoctorId = result.data.user_id ? String(result.data.user_id) : currentDoctorId
                    if (profileFirstName) profileFirstName.value = result.data.firstname || ''
                    if (profileLastName) profileLastName.value = result.data.lastname || ''
                    if (profileMiddleName) profileMiddleName.value = result.data.middlename || ''
                    if (profileAddress) profileAddress.value = result.data.address || ''
                    if (profileContact) profileContact.value = result.data.contact_number || ''

                    var profUrl = result.data.prof_path_url ? String(result.data.prof_path_url) : ''
                    renderImagePreview(profPathPreview, profUrl, 'Profile Picture', 'No picture uploaded yet.', true)
                })
                .catch(function () {})
        }

        function saveProfile() {
            if (typeof apiFetch !== 'function') return
            var fn = profileFirstName ? profileFirstName.value.trim() : ''
            var ln = profileLastName ? profileLastName.value.trim() : ''
            if (!fn && !ln) {
                window.alert('Please enter a first name or last name.')
                return
            }

            profileSave.disabled = true
            apiFetch(apiUrl('/api/users/' + currentDoctorId), {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    firstname: fn,
                    lastname: ln,
                    middlename: profileMiddleName ? profileMiddleName.value.trim() : '',
                    address: profileAddress ? profileAddress.value.trim() : '',
                    contact_number: profileContact ? profileContact.value.trim() : ''
                })
            })
                .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data } }) })
                .then(function (result) {
                    if (!result.ok) {
                        window.alert(result.data && result.data.message ? result.data.message : 'Failed to save profile.')
                        return
                    }
                    if (profileNotice) {
                        profileNotice.textContent = 'Profile updated.'
                        setTimeout(function () { profileNotice.textContent = '' }, 3000)
                    }
                })
                .catch(function () { window.alert('Network error') })
                .finally(function () { profileSave.disabled = false })
        }

        function loadServerSignature() {
            if (typeof apiFetch !== 'function') return

            apiFetch(apiUrl('/api/user'), { method: 'GET' })
                .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data } }) })
                .then(function (result) {
                    if (!result.ok || !result.data) return
                    currentDoctorId = result.data.user_id ? String(result.data.user_id) : currentDoctorId
                    var url = result.data.signature_url ? String(result.data.signature_url) : ''
                    renderImagePreview(signaturePreview, url, 'Signature', 'No signature uploaded yet.', false)
                })
                .catch(function () {})
        }

        function cacheBustedUrl(url) {
            var raw = String(url || '').trim()
            if (!raw) return ''
            if (/^(blob:|data:)/i.test(raw)) return raw
            return raw + (raw.indexOf('?') === -1 ? '?v=' : '&v=') + String(Date.now())
        }

        function renderImagePreview(container, imageUrl, altText, emptyText, rounded) {
            if (!container) return
            var normalized = String(imageUrl || '').trim()
            if (!normalized) {
                container.textContent = emptyText || 'No image uploaded yet.'
                container.classList.remove('text-slate-700')
                container.classList.add('text-slate-400')
                return
            }
            var src = cacheBustedUrl(normalized)
            container.innerHTML = '<img alt="' + String(altText || 'Image') + '" src="' + src + '" class="max-h-20 max-w-full object-contain' + (rounded ? ' rounded-lg' : '') + '">'
            container.classList.remove('text-slate-400')
            container.classList.add('text-slate-700')
            var img = container.querySelector('img')
            if (img) {
                img.addEventListener('error', function () {
                    container.textContent = emptyText || 'No image uploaded yet.'
                    container.classList.remove('text-slate-700')
                    container.classList.add('text-slate-400')
                }, { once: true })
            }
        }

        function setUploadButtonState(button, spinner, icon, labelNode, busy, busyLabel, idleLabel) {
            if (button) button.disabled = !!busy
            if (spinner) spinner.classList.toggle('hidden', !busy)
            if (icon) icon.classList.toggle('hidden', !!busy)
            if (labelNode) labelNode.textContent = busy ? busyLabel : idleLabel
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
            if (!passwordVerifyToken) {
                window.alert('Please verify your current password first.')
                setAccountStep('verify')
                return
            }

            var next = newPassword ? newPassword.value : ''
            var confirm = confirmPassword ? confirmPassword.value : ''

            if (!next || !confirm) {
                window.alert('Please complete all password fields.')
                return
            }
            if (next !== confirm) {
                window.alert('New password and confirmation do not match.')
                return
            }

            var confirmed = window.confirm('Are you sure you want to change your password?')
            if (!confirmed) return

            if (saveSpinner) saveSpinner.classList.remove('hidden')
            if (accountSave) accountSave.disabled = true

            apiFetch(apiUrl('/api/users/me/password/change'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    token: passwordVerifyToken,
                    password: next,
                    password_confirmation: confirm
                })
            })
                .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data } }) })
                .then(function (result) {
                    if (!result.ok) {
                        window.alert(result.data && result.data.message ? result.data.message : 'Failed to update password.')
                        if (result.data && result.data.code === 'PASSWORD_VERIFY_REQUIRED') {
                            clearPasswordToken()
                            setAccountStep('verify')
                        }
                        return
                    }
                    clearPasswordToken()
                    if (currentPassword) currentPassword.value = ''
                    if (newPassword) newPassword.value = ''
                    if (confirmPassword) confirmPassword.value = ''
                    window.alert('Password updated successfully.')
                    setAccountStep('idle')
                })
                .catch(function () { window.alert('Network error') })
                .finally(function () {
                    if (saveSpinner) saveSpinner.classList.add('hidden')
                    if (accountSave) accountSave.disabled = false
                })
        }

        function verifyCurrentPassword() {
            var current = currentPassword ? currentPassword.value : ''
            if (!current) {
                window.alert('Please enter your current password.')
                return
            }

            if (verifySpinner) verifySpinner.classList.remove('hidden')
            if (accountVerify) accountVerify.disabled = true

            apiFetch(apiUrl('/api/users/me/password/verify'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ current_password: current })
            })
                .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data } }) })
                .then(function (result) {
                    if (!result.ok) {
                        window.alert(result.data && result.data.message ? result.data.message : 'Verification failed.')
                        return
                    }
                    passwordVerifyToken = result.data.token
                    persistPasswordToken(passwordVerifyToken, result.data.expires_in || 600)
                    if (currentPassword) currentPassword.value = ''
                    setAccountStep('change')
                })
                .catch(function () { window.alert('Network error') })
                .finally(function () {
                    if (verifySpinner) verifySpinner.classList.add('hidden')
                    if (accountVerify) accountVerify.disabled = false
                })
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

        if (profPathSave) {
            if (profPathFile) {
                profPathFile.addEventListener('change', function () {
                    if (!profPathFile.files || !profPathFile.files.length) return
                    var localUrl = URL.createObjectURL(profPathFile.files[0])
                    renderImagePreview(profPathPreview, localUrl, 'Profile Picture', 'No picture uploaded yet.', true)
                })
            }
            profPathSave.addEventListener('click', function () {
                if (!profPathFile || !profPathFile.files || profPathFile.files.length === 0) {
                    window.alert('Please choose a profile picture first.')
                    return
                }
                if (typeof apiFetch !== 'function') {
                    window.alert('API client is not available.')
                    return
                }

                var file = profPathFile.files[0]
                var formData = new FormData()
                formData.append('prof_path', file)

                setUploadButtonState(profPathSave, profPathSaveSpinner, profPathSaveIcon, profPathSaveLabel, true, 'Saving...', 'Save picture')

                apiFetch(apiUrl('/api/users/me/profile-picture'), {
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
                            var validationMessage = result.data && result.data.errors && typeof result.data.errors === 'object'
                                ? Object.keys(result.data.errors).map(function (key) {
                                    var list = Array.isArray(result.data.errors[key]) ? result.data.errors[key] : [result.data.errors[key]]
                                    return list.filter(Boolean).join(' ')
                                }).filter(Boolean).join(' ')
                                : ''
                            var msg = validationMessage || ((result.data && result.data.message) ? String(result.data.message) : 'Unable to upload profile picture.')
                            window.alert(msg)
                            loadDoctorSettings()
                            return
                        }
                        var url = result.data && result.data.prof_path_url ? String(result.data.prof_path_url) : ''
                        renderImagePreview(profPathPreview, url, 'Profile Picture', 'No picture uploaded yet.', true)
                        loadDoctorSettings()
                        if (profPathFile) profPathFile.value = ''
                    })
                    .catch(function () {
                        window.alert('Network error while uploading picture.')
                        loadDoctorSettings()
                    })
                    .finally(function () {
                        setUploadButtonState(profPathSave, profPathSaveSpinner, profPathSaveIcon, profPathSaveLabel, false, 'Saving...', 'Save picture')
                    })
            })
        }

        if (signatureSave) {
            if (signatureFile) {
                signatureFile.addEventListener('change', function () {
                    if (!signatureFile.files || !signatureFile.files.length) return
                    var localUrl = URL.createObjectURL(signatureFile.files[0])
                    renderImagePreview(signaturePreview, localUrl, 'Signature', 'No signature uploaded yet.', false)
                })
            }
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

                setUploadButtonState(signatureSave, signatureSaveSpinner, signatureSaveIcon, signatureSaveLabel, true, 'Saving...', 'Save signature')

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
                            loadServerSignature()
                            return
                        }
                        var url = result.data && result.data.signature_url ? String(result.data.signature_url) : ''
                        renderImagePreview(signaturePreview, url, 'Signature', 'No signature uploaded yet.', false)
                        loadServerSignature()
                        if (signatureFile) signatureFile.value = ''
                    })
                    .catch(function () {
                        window.alert('Network error while uploading signature.')
                        loadServerSignature()
                    })
                    .finally(function () {
                        setUploadButtonState(signatureSave, signatureSaveSpinner, signatureSaveIcon, signatureSaveLabel, false, 'Saving...', 'Save signature')
                    })
            })
        }

        if (accountSave) {
            accountSave.addEventListener('click', function () {
                handlePasswordChange()
            })
        }

        if (accountStart) {
            accountStart.addEventListener('click', function () {
                clearPasswordToken()
                setAccountStep('verify')
            })
        }
        if (accountCancel) {
            accountCancel.addEventListener('click', function () {
                clearPasswordToken()
                if (currentPassword) currentPassword.value = ''
                setAccountStep('idle')
            })
        }
        if (accountBack) {
            accountBack.addEventListener('click', function () {
                clearPasswordToken()
                if (newPassword) newPassword.value = ''
                if (confirmPassword) confirmPassword.value = ''
                setAccountStep('verify')
            })
        }
        if (accountVerify) {
            accountVerify.addEventListener('click', function () {
                verifyCurrentPassword()
            })
        }

        setAccountStep('idle')
        restorePasswordTokenIfAny()

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
