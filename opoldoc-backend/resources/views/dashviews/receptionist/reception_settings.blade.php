<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">reception Settings</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">reception</span>
    </div>
    <p class="text-xs text-slate-500 mb-4">
        Update your profile details, change your password, and optionally upload a signature image.
    </p>

    <div class="grid gap-4 grid-cols-1 lg:grid-cols-2 text-[0.78rem] text-slate-600">
        <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/60 lg:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-xs font-semibold text-slate-900">Profile</h3>
                    <p class="text-[0.7rem] text-slate-500">Basic information shown in patient-facing records.</p>
                </div>
                <x-lucide-circle-user class="w-[18px] h-[18px] text-cyan-600" />
            </div>

            <form id="receptionSettingsProfileForm" class="space-y-3">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="reception_profile_firstname" class="block text-[0.7rem] text-slate-500 mb-1">First name</label>
                        <input id="reception_profile_firstname" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    </div>
                    <div>
                        <label for="reception_profile_lastname" class="block text-[0.7rem] text-slate-500 mb-1">Last name</label>
                        <input id="reception_profile_lastname" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    </div>
                </div>
                <div>
                    <label for="reception_profile_middlename" class="block text-[0.7rem] text-slate-500 mb-1">Middle name (optional)</label>
                    <input id="reception_profile_middlename" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div>
                    <label for="reception_profile_address" class="block text-[0.7rem] text-slate-500 mb-1">Address</label>
                    <input id="reception_profile_address" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div>
                    <label for="reception_profile_contact" class="block text-[0.7rem] text-slate-500 mb-1">Contact number</label>
                    <input id="reception_profile_contact" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="09xx xxx xxxx">
                </div>
                <div class="flex items-center justify-between pt-1">
                    <p id="receptionProfileNotice" class="text-[0.68rem] text-slate-400"></p>
                    <button type="button" id="reception_profile_save" class="inline-flex items-center gap-1 rounded-xl border border-cyan-500/40 bg-cyan-50 px-3 py-1.5 text-[0.72rem] font-semibold text-cyan-700 hover:bg-cyan-100">
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

            <form id="receptionSettingsProfPathForm" class="space-y-3">
                <div>
                    <label for="reception_prof_path_file" class="block text-[0.7rem] text-slate-500 mb-1">Upload profile picture</label>
                    <input id="reception_prof_path_file" type="file" accept="image/*" class="block w-full text-[0.78rem] text-slate-700 file:mr-3 file:rounded-lg file:border file:border-slate-200 file:bg-white file:px-3 file:py-1.5 file:text-[0.78rem] file:font-semibold file:text-slate-700 hover:file:bg-slate-50">
                </div>
                <div>
                    <div class="text-[0.7rem] text-slate-500 mb-1">Current picture</div>
                    <div id="reception_prof_path_preview" class="flex items-center justify-center h-24 rounded-lg border border-dashed border-slate-300 bg-white text-[0.72rem] text-slate-400">
                        No picture uploaded yet.
                    </div>
                </div>
                <div class="flex items-center justify-between pt-1">
                    <p class="text-[0.68rem] text-slate-400"></p>
                    <button type="button" id="reception_prof_path_save" class="inline-flex items-center gap-1 rounded-xl border border-cyan-500/40 bg-cyan-50 px-3 py-1.5 text-[0.72rem] font-semibold text-cyan-700 hover:bg-cyan-100">
                        <x-lucide-save class="w-[16px] h-[16px]" />
                        Save picture
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

            <div id="receptionAccountIdle" class="rounded-2xl border border-slate-200 bg-white p-4">
                <button type="button" id="reception_account_start" class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/40 bg-cyan-50 px-3 py-2 text-[0.78rem] font-semibold text-cyan-700 hover:bg-cyan-100">
                    <x-lucide-key class="w-[18px] h-[18px]" />
                    Change password
                </button>
            </div>

            <div id="receptionAccountVerifyStep" class="hidden rounded-2xl border border-slate-200 bg-white p-4">
                <div class="text-[0.72rem] font-semibold text-slate-900 mb-3">Verify current password</div>
                <div>
                    <label for="reception_current_password" class="block text-[0.7rem] text-slate-500 mb-1">Current password</label>
                    <input id="reception_current_password" type="password" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div class="mt-3 flex items-center justify-end gap-2">
                    <button type="button" id="reception_account_cancel" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="button" id="reception_account_verify" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-900 text-white text-[0.78rem] font-semibold hover:bg-slate-800">
                        <span id="receptionAccountVerifySpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        <span id="receptionAccountVerifyLabel">Verify</span>
                    </button>
                </div>
            </div>

            <div id="receptionAccountChangeStep" class="hidden rounded-2xl border border-slate-200 bg-white p-4">
                <div class="text-[0.72rem] font-semibold text-slate-900 mb-3">Set new password</div>
                <div class="space-y-3">
                    <div>
                        <label for="reception_new_password" class="block text-[0.7rem] text-slate-500 mb-1">New password</label>
                        <input id="reception_new_password" type="password" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    </div>
                    <div>
                        <label for="reception_confirm_password" class="block text-[0.7rem] text-slate-500 mb-1">Confirm new password</label>
                        <input id="reception_confirm_password" type="password" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-end gap-2">
                    <button type="button" id="reception_account_back" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Back</button>
                    <button type="button" id="reception_account_save" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700">
                        <span id="receptionAccountSaveSpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        Save new password
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var apiBasePath = "{{ request()->getBasePath() }}"
        function apiUrl(path) {
            return String(apiBasePath || '') + String(path || '')
        }

        var profileFirstName = document.getElementById('reception_profile_firstname')
        var profileLastName = document.getElementById('reception_profile_lastname')
        var profileMiddleName = document.getElementById('reception_profile_middlename')
        var profileAddress = document.getElementById('reception_profile_address')
        var profileContact = document.getElementById('reception_profile_contact')
        var profileSave = document.getElementById('reception_profile_save')
        var profileNotice = document.getElementById('receptionProfileNotice')

        var profPathFile = document.getElementById('reception_prof_path_file')
        var profPathPreview = document.getElementById('reception_prof_path_preview')
        var profPathSave = document.getElementById('reception_prof_path_save')

        var currentPassword = document.getElementById('reception_current_password')
        var newPassword = document.getElementById('reception_new_password')
        var confirmPassword = document.getElementById('reception_confirm_password')
        var accountSave = document.getElementById('reception_account_save')
        var accountStart = document.getElementById('reception_account_start')
        var accountCancel = document.getElementById('reception_account_cancel')
        var accountVerify = document.getElementById('reception_account_verify')
        var accountBack = document.getElementById('reception_account_back')
        var verifySpinner = document.getElementById('receptionAccountVerifySpinner')
        var verifyLabel = document.getElementById('receptionAccountVerifyLabel')
        var saveSpinner = document.getElementById('receptionAccountSaveSpinner')
        var accountIdle = document.getElementById('receptionAccountIdle')
        var accountVerifyStep = document.getElementById('receptionAccountVerifyStep')
        var accountChangeStep = document.getElementById('receptionAccountChangeStep')
        var passwordVerifyToken = null
        var passwordTokenKey = 'opol_reception_pw_verify_token'
        var passwordTokenExpKey = 'opol_reception_pw_verify_expires_at'

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

        var currentReceptionId = null

        function loadReceptionSettings() {
            if (typeof apiFetch !== 'function') return
            apiFetch(apiUrl('/api/user'), { method: 'GET' })
                .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data } }) })
                .then(function (result) {
                    if (!result.ok || !result.data) return
                    currentReceptionId = result.data.user_id ? String(result.data.user_id) : currentReceptionId
                    if (profileFirstName) profileFirstName.value = result.data.firstname || ''
                    if (profileLastName) profileLastName.value = result.data.lastname || ''
                    if (profileMiddleName) profileMiddleName.value = result.data.middlename || ''
                    if (profileAddress) profileAddress.value = result.data.address || ''
                    if (profileContact) profileContact.value = result.data.contact_number || ''

                    var profUrl = result.data.prof_path_url ? String(result.data.prof_path_url) : ''
                    if (profPathPreview && profUrl) {
                        profPathPreview.innerHTML = '<img alt="Profile Picture" src="' + profUrl + '" class="max-h-20 max-w-full object-contain rounded-lg">'
                        profPathPreview.classList.remove('text-slate-400')
                    }
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
            apiFetch(apiUrl('/api/users/' + currentReceptionId), {
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

                profPathSave.disabled = true

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
                            var msg = (result.data && result.data.message) ? String(result.data.message) : 'Unable to upload profile picture.'
                            window.alert(msg)
                            return
                        }
                        var url = result.data && result.data.prof_path_url ? String(result.data.prof_path_url) : ''
                        if (profPathPreview) {
                            if (url) {
                                profPathPreview.innerHTML = '<img alt="Profile Picture" src="' + url + '" class="max-h-20 max-w-full object-contain rounded-lg">'
                                profPathPreview.classList.remove('text-slate-400')
                            } else {
                                profPathPreview.textContent = 'Picture uploaded'
                                profPathPreview.classList.remove('text-slate-400')
                                profPathPreview.classList.add('text-slate-700')
                            }
                        }
                        if (profPathFile) profPathFile.value = ''
                    })
                    .catch(function () {
                        window.alert('Network error while uploading picture.')
                    })
                    .finally(function () {
                        profPathSave.disabled = false
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
        loadReceptionSettings()
    })
</script>
