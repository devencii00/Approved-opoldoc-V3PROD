<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password</title>
    @vite('resources/css/app.css')
     <link rel="icon" type="image/x-icon" href="/images/opoldoc-weblog.ico">
     
       <link rel="stylesheet" href="{{ asset('assets/fonts/css/stylefont.css') }}">
        .font-playfair { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-100 p-4 font-sans">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <h1 class="font-playfair text-2xl font-bold text-slate-900 mb-1">Forgot password</h1>
        <p class="text-xs text-slate-500 mb-4">We will send a one-time code to your email to reset your password.</p>

        <div id="forgotError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700"></div>
        <div id="forgotSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"></div>

        <form id="forgotForm" class="space-y-3">
            <div id="forgotStepEmail">
                <label for="forgot_email" class="block text-xs text-slate-600 mb-1">Email</label>
                <input type="email" id="forgot_email" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" required>
                <button type="submit" class="w-full mt-3 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-700 text-white text-sm font-semibold hover:from-cyan-600 hover:to-cyan-800 transition-colors">
                    Send code
                </button>
            </div>

            <div id="forgotStepCode" class="hidden space-y-3">
                <p class="text-xs text-slate-500">Enter the 5-digit code we sent to your email.</p>
                <div class="flex items-center justify-between gap-2">
                    <input type="text" maxlength="1" class="forgot-code-input w-10 h-10 text-center rounded-lg border border-slate-200 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <input type="text" maxlength="1" class="forgot-code-input w-10 h-10 text-center rounded-lg border border-slate-200 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <input type="text" maxlength="1" class="forgot-code-input w-10 h-10 text-center rounded-lg border border-slate-200 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <input type="text" maxlength="1" class="forgot-code-input w-10 h-10 text-center rounded-lg border border-slate-200 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <input type="text" maxlength="1" class="forgot-code-input w-10 h-10 text-center rounded-lg border border-slate-200 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
            </div>

            <div id="forgotStepPassword" class="hidden space-y-3">
                <div>
                    <label for="forgot_new_password" class="block text-xs text-slate-600 mb-1">New password</label>
                    <input type="password" id="forgot_new_password" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <div>
                    <label for="forgot_new_password_confirmation" class="block text-xs text-slate-600 mb-1">Confirm new password</label>
                    <input type="password" id="forgot_new_password_confirmation" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                </div>
                <button type="submit" class="w-full mt-1 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-700 text-white text-sm font-semibold hover:from-cyan-600 hover:to-cyan-800 transition-colors">
                    Reset password
                </button>
            </div>
        </form>

        <p class="mt-4 text-center text-xs text-slate-500">
            Remembered your password?
            <a href="{{ route('webadmin.login') }}" class="text-cyan-500 hover:text-cyan-600 font-semibold">Back to login</a>
        </p>
    </div>

    <script>
        function forgotApiFetch(path, options) {
            var headers = (options && options.headers) ? Object.assign({}, options.headers) : {}
            if (!headers['Accept']) {
                headers['Accept'] = 'application/json'
            }
            return fetch(path, Object.assign({}, options, { headers: headers }))
        }

        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('forgotForm')
            var errorBox = document.getElementById('forgotError')
            var successBox = document.getElementById('forgotSuccess')
            var emailInput = document.getElementById('forgot_email')
            var stepEmail = document.getElementById('forgotStepEmail')
            var stepCode = document.getElementById('forgotStepCode')
            var stepPassword = document.getElementById('forgotStepPassword')
            var codeInputs = Array.prototype.slice.call(document.querySelectorAll('.forgot-code-input'))
            var newPasswordInput = document.getElementById('forgot_new_password')
            var newPasswordConfirmInput = document.getElementById('forgot_new_password_confirmation')

            var currentStep = 'email'

            codeInputs.forEach(function (input, index) {
                input.addEventListener('input', function () {
                    var value = this.value.replace(/[^0-9]/g, '')
                    this.value = value
                    if (value && index < codeInputs.length - 1) {
                        codeInputs[index + 1].focus()
                    }
                })
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        codeInputs[index - 1].focus()
                    }
                })
            })

            function showError(message) {
                if (!errorBox) return
                errorBox.textContent = message || ''
                if (message) {
                    errorBox.classList.remove('hidden')
                } else {
                    errorBox.classList.add('hidden')
                }
            }

            function showSuccess(message) {
                if (!successBox) return
                successBox.textContent = message || ''
                if (message) {
                    successBox.classList.remove('hidden')
                } else {
                    successBox.classList.add('hidden')
                }
            }

            if (!form) {
                return
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault()
                showError('')
                showSuccess('')

                if (currentStep === 'email') {
                    var email = emailInput ? emailInput.value.trim() : ''
                    if (!email) {
                        showError('Please enter your email.')
                        return
                    }

                    forgotApiFetch("{{ url('/api/password/forgot') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ email: email })
                    })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                return { ok: response.ok, data: data }
                            })
                        })
                        .then(function (result) {
                            if (!result.ok) {
                                var message = result.data && result.data.message ? result.data.message : 'Unable to start reset process.'
                                showError(message)
                                return
                            }

                            showSuccess('If the email exists, a one-time code has been generated.')
                            currentStep = 'code'
                            if (stepEmail) stepEmail.classList.add('hidden')
                            if (stepCode) stepCode.classList.remove('hidden')
                            if (codeInputs.length) codeInputs[0].focus()
                        })
                        .catch(function () {
                            showError('Network error. Please try again.')
                        })
                } else if (currentStep === 'code') {
                    var code = codeInputs.map(function (input) { return input.value || '' }).join('')
                    if (!code || code.length !== 5) {
                        showError('Please enter the 5-digit code.')
                        return
                    }

                    currentStep = 'password'
                    if (stepCode) stepCode.classList.add('hidden')
                    if (stepPassword) stepPassword.classList.remove('hidden')
                    if (newPasswordInput) newPasswordInput.focus()
                    form.setAttribute('data-reset-token', code)
                } else if (currentStep === 'password') {
                    var password = newPasswordInput ? newPasswordInput.value : ''
                    var confirm = newPasswordConfirmInput ? newPasswordConfirmInput.value : ''
                    if (!password || !confirm) {
                        showError('Please enter and confirm your new password.')
                        return
                    }
                    if (password !== confirm) {
                        showError('Passwords do not match.')
                        return
                    }

                    var token = form.getAttribute('data-reset-token') || ''
                    if (!token) {
                        showError('Reset token is missing. Please restart the process.')
                        return
                    }

                    forgotApiFetch("{{ url('/api/password/reset') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            token: token,
                            password: password,
                            password_confirmation: confirm
                        })
                    })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                return { ok: response.ok, data: data }
                            })
                        })
                        .then(function (result) {
                            if (!result.ok) {
                                var message = result.data && result.data.message ? result.data.message : 'Unable to reset password.'
                                showError(message)
                                return
                            }

                            showSuccess('Password has been reset. You can now sign in with your new password.')
                            setTimeout(function () {
                                window.location.href = "{{ route('webadmin.login') }}"
                            }, 1200)
                        })
                        .catch(function () {
                            showError('Network error. Please try again.')
                        })
                }
            })
        })
    </script>
</body>
</html>
