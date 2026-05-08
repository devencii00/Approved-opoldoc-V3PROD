<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>First Login – Update Password</title>
    @vite('resources/css/app.css')
     <link rel="icon" type="image/x-icon" href="/images/opoldoc-weblog.ico">
       <link rel="stylesheet" href="{{ asset('assets/fonts/css/stylefont.css') }}">
    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-100 p-4 font-sans">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <h1 class="font-playfair text-2xl font-bold text-slate-900 mb-1">Set a new password</h1>
        <p class="text-xs text-slate-500 mb-4">
            For security, please change the temporary password you received. Your email is already set.
        </p>

        <div id="staffFirstLoginError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700"></div>
        <div id="staffFirstLoginSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"></div>

        <form id="staffFirstLoginForm" class="space-y-3">
            <div>
                <label for="staff_new_password" class="block text-xs text-slate-600 mb-1">New password</label>
                <div class="relative">
                    <input type="password" id="staff_new_password" class="w-full pr-10 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <button type="button" id="staffTogglePassword" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 text-xl">
                        <x-lucide-eye id="staffTogglePasswordEye" class="w-[20px] h-[20px]" />
                        <x-lucide-eye-off id="staffTogglePasswordEyeOff" class="hidden w-[20px] h-[20px]" />
                    </button>
                </div>
            </div>
            <div>
                <label for="staff_new_password_confirmation" class="block text-xs text-slate-600 mb-1">Confirm new password</label>
                <input type="password" id="staff_new_password_confirmation" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
            </div>
            <button type="submit" class="w-full mt-2 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-700 text-white text-sm font-semibold hover:from-cyan-600 hover:to-cyan-800 transition-colors">
                Save and continue
            </button>
        </form>
    </div>

    <script>
        function staffApiFetch(path, options) {
            var token = null
            try {
                token = window.localStorage ? window.localStorage.getItem('api_token') : null
            } catch (_) {
                token = null
            }
            var headers = (options && options.headers) ? Object.assign({}, options.headers) : {}
            if (token) {
                headers['Authorization'] = 'Bearer ' + token
            }
            if (!headers['Accept']) {
                headers['Accept'] = 'application/json'
            }
            return fetch(path, Object.assign({}, options, { headers: headers }))
        }

        function togglePasswordVisibility(inputId, eyeId, eyeOffId) {
            var input = document.getElementById(inputId)
            var eye = document.getElementById(eyeId)
            var eyeOff = document.getElementById(eyeOffId)
            if (!input || !eye || !eyeOff) {
                return
            }
            var isPassword = input.type === 'password'
            input.type = isPassword ? 'text' : 'password'
            eye.classList.toggle('hidden', isPassword)
            eyeOff.classList.toggle('hidden', !isPassword)
        }

        function isStrongPassword(value) {
            if (!value) {
                return false
            }
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(String(value))
        }

        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('staffFirstLoginForm')
            var errorBox = document.getElementById('staffFirstLoginError')
            var successBox = document.getElementById('staffFirstLoginSuccess')
            var newPasswordInput = document.getElementById('staff_new_password')
            var confirmInput = document.getElementById('staff_new_password_confirmation')
            var toggleBtn = document.getElementById('staffTogglePassword')

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    togglePasswordVisibility('staff_new_password', 'staffTogglePasswordEye', 'staffTogglePasswordEyeOff')
                })
            }

            if (!form) {
                return
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault()

                if (errorBox) {
                    errorBox.classList.add('hidden')
                    errorBox.textContent = ''
                }
                if (successBox) {
                    successBox.classList.add('hidden')
                    successBox.textContent = ''
                }

                var password = newPasswordInput ? newPasswordInput.value : ''
                var confirm = confirmInput ? confirmInput.value : ''

                if (!password || !confirm) {
                    if (errorBox) {
                        errorBox.textContent = 'Please enter and confirm your new password.'
                        errorBox.classList.remove('hidden')
                    }
                    return
                }

                if (password !== confirm) {
                    if (errorBox) {
                        errorBox.textContent = 'Passwords do not match.'
                        errorBox.classList.remove('hidden')
                    }
                    return
                }

                if (!isStrongPassword(password)) {
                    if (errorBox) {
                        errorBox.textContent = 'Password must be at least 8 characters and include uppercase, lowercase, a number, and a symbol.'
                        errorBox.classList.remove('hidden')
                    }
                    return
                }

                var userRef = null
                try {
                    userRef = window.localStorage ? window.localStorage.getItem('current_user_uuid') : null
                } catch (_) {
                    userRef = null
                }

                if (!userRef) {
                    try {
                        userRef = window.localStorage ? window.localStorage.getItem('current_user_id') : null
                    } catch (_) {
                        userRef = null
                    }
                }

                if (!userRef) {
                    if (errorBox) {
                        errorBox.textContent = 'User information is missing. Please sign in again.'
                        errorBox.classList.remove('hidden')
                    }
                    return
                }

                staffApiFetch("{{ url('/api/users') }}/" + userRef, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        password: password,
                        must_change_credentials: false
                    })
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data }
                        })
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            if (errorBox) {
                                var message = result.data && result.data.message ? result.data.message : 'Failed to update password.'
                                errorBox.textContent = message
                                errorBox.classList.remove('hidden')
                            }
                            return
                        }

                        if (successBox) {
                            successBox.textContent = 'Password updated. Redirecting to dashboard...'
                            successBox.classList.remove('hidden')
                        }

                        var role = 'admin'

                        if (result.data && result.data.current_role && result.data.current_role.role_name) {
                            role = String(result.data.current_role.role_name).toLowerCase()
                        }

                        setTimeout(function () {
                            var target = "{{ url('/dashboard') }}/" + role
                            if (result.data && result.data.uuid) {
                                target += '?user_uuid=' + encodeURIComponent(String(result.data.uuid))
                            }
                            window.location.href = target
                        }, 1000)
                    })
                    .catch(function () {
                        if (errorBox) {
                            errorBox.textContent = 'Network error while updating password.'
                            errorBox.classList.remove('hidden')
                        }
                    })
            })
        })
    </script>
</body>
</html>
