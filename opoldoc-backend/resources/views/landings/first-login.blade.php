<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Admin Credentials</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('assets/fonts/css/stylefont.css') }}">
    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-100 p-4 font-sans">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <h1 class="font-playfair text-2xl font-bold text-slate-900 mb-1">Update admin credentials</h1>
        <p class="text-xs text-slate-500 mb-4">For security, please update the default administrator email and/or password.</p>

        <div id="errorBox" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700"></div>
        <div id="successBox" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"></div>

        <form id="firstLoginForm" class="space-y-3" novalidate>
            <div>
                <label for="new_email" class="block text-xs text-slate-600 mb-1">New email (optional)</label>
                <div id="newEmailError" class="hidden mb-1 text-[0.7rem] text-red-600"></div>
                <input type="text" id="new_email" inputmode="email" autocomplete="email" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
            </div>
            <div>
                <label for="new_password" class="block text-xs text-slate-600 mb-1">New password</label>
                <div id="newPasswordError" class="hidden mb-1 text-[0.7rem] text-red-600"></div>
                <div class="relative">
                    <input type="password" id="new_password" class="w-full pr-10 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <button type="button" id="toggleNewPassword" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 text-xl">
                        <x-lucide-eye id="toggleNewPasswordEye" class="w-[20px] h-[20px]" />
                        <x-lucide-eye-off id="toggleNewPasswordEyeOff" class="hidden w-[20px] h-[20px]" />
                    </button>
                </div>
            </div>
            <div>
                <label for="new_password_confirmation" class="block text-xs text-slate-600 mb-1">Confirm new password</label>
                <input type="password" id="new_password_confirmation" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
            </div>
            <button id="firstLoginSubmit" type="submit" class="w-full mt-2 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-700 text-white text-sm font-semibold hover:from-cyan-600 hover:to-cyan-800 transition-colors disabled:opacity-70 disabled:hover:from-cyan-500 disabled:hover:to-cyan-700 relative flex items-center justify-center">
                <span id="firstLoginSpinner" class="hidden absolute w-5 h-5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                <span id="firstLoginSubmitLabel">Save and continue</span>
            </button>
        </form>
    </div>

    <script>
        function apiFetch(path, options = {}) {
            const token = window.localStorage ? window.localStorage.getItem('api_token') : null;
            const headers = options.headers ? {...options.headers} : {};
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }
            if (!headers['Accept']) {
                headers['Accept'] = 'application/json';
            }
            return fetch(path, {...options, headers});
        }

        function togglePasswordVisibility(inputId, eyeId, eyeOffId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            const eyeOff = document.getElementById(eyeOffId);
            if (!input || !eye || !eyeOff) {
                return;
            }
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            eye.classList.toggle('hidden', isPassword);
            eyeOff.classList.toggle('hidden', !isPassword);
        }

        function isValidEmail(value) {
            if (!value) {
                return false;
            }
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value).trim());
        }

        function isExampleDotComEmail(value) {
            if (!isValidEmail(value)) {
                return false;
            }
            return String(value).trim().toLowerCase().endsWith('@example.com');
        }

        function isStrongPassword(value) {
            if (!value) {
                return false;
            }
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(String(value));
        }

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('firstLoginForm');
            const errorBox = document.getElementById('errorBox');
            const successBox = document.getElementById('successBox');
            const newEmailInput = document.getElementById('new_email');
            const newPasswordInput = document.getElementById('new_password');
            const newPasswordConfirmInput = document.getElementById('new_password_confirmation');
            const toggleNewPassword = document.getElementById('toggleNewPassword');
            const newEmailError = document.getElementById('newEmailError');
            const newPasswordError = document.getElementById('newPasswordError');
            const submitBtn = document.getElementById('firstLoginSubmit');
            const spinner = document.getElementById('firstLoginSpinner');
            const submitLabel = document.getElementById('firstLoginSubmitLabel');

            if (toggleNewPassword) {
                toggleNewPassword.addEventListener('click', function () {
                    togglePasswordVisibility('new_password', 'toggleNewPasswordEye', 'toggleNewPasswordEyeOff');
                });
            }

            if (!form) {
                return;
            }

            function clearInlineErrors() {
                if (newEmailError) {
                    newEmailError.classList.add('hidden');
                    newEmailError.textContent = '';
                }
                if (newPasswordError) {
                    newPasswordError.classList.add('hidden');
                    newPasswordError.textContent = '';
                }
            }

            function setInlineError(el, message) {
                if (!el) {
                    return;
                }
                el.textContent = message || '';
                el.classList.toggle('hidden', !message);
            }

            function setLoading(isLoading) {
                if (submitBtn) {
                    submitBtn.disabled = !!isLoading;
                }
                if (spinner) {
                    spinner.classList.toggle('hidden', !isLoading);
                }
                if (submitLabel) {
                    submitLabel.classList.toggle('opacity-0', !!isLoading);
                }
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                if (submitBtn && submitBtn.disabled) {
                    return;
                }

                if (errorBox) {
                    errorBox.classList.add('hidden');
                    errorBox.textContent = '';
                }
                if (successBox) {
                    successBox.classList.add('hidden');
                    successBox.textContent = '';
                }
                clearInlineErrors();

                const body = {};

                if (newEmailInput && newEmailInput.value) {
                    if (!isExampleDotComEmail(newEmailInput.value)) {
                        setInlineError(newEmailError, 'Please enter a valid email ending with @example.com.');
                        newEmailInput.focus();
                        return;
                    }
                    body.email = newEmailInput.value;
                }

                if (!newPasswordInput || !newPasswordInput.value) {
                    setInlineError(newPasswordError, 'Password change is required.');
                    if (newPasswordInput) {
                        newPasswordInput.focus();
                    }
                    return;
                }

                if (!newPasswordConfirmInput || newPasswordInput.value !== newPasswordConfirmInput.value) {
                    setInlineError(newPasswordError, 'Passwords do not match.');
                    newPasswordInput.focus();
                    return;
                }
                if (!isStrongPassword(newPasswordInput.value)) {
                    setInlineError(newPasswordError, 'Password must be at least 8 characters and include uppercase, lowercase, a number, and a symbol.');
                    newPasswordInput.focus();
                    return;
                }
                body.password = newPasswordInput.value;

                body.must_change_credentials = false;

                let userRef = null;
                try {
                    userRef = window.localStorage ? window.localStorage.getItem('current_user_uuid') : null;
                } catch (_) {
                    userRef = null;
                }

                if (!userRef) {
                    try {
                        userRef = window.localStorage ? window.localStorage.getItem('current_user_id') : null;
                    } catch (_) {
                        userRef = null;
                    }
                }

                if (!userRef) {
                    if (errorBox) {
                        errorBox.textContent = 'User information is missing. Please sign in again.';
                        errorBox.classList.remove('hidden');
                    }
                    return;
                }

                setLoading(true);
                let keepLoading = false;

                try {
                    const abortController = typeof AbortController !== 'undefined' ? new AbortController() : null;
                    const timeoutId = setTimeout(function () {
                        if (abortController) {
                            abortController.abort();
                        }
                    }, 15000);

                    const response = await apiFetch("{{ url('/api/users') }}/" + userRef, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        signal: abortController ? abortController.signal : undefined,
                        body: JSON.stringify(body),
                    });
                    clearTimeout(timeoutId);

                    let data = {};

                    try {
                        data = await response.json();
                    } catch (_) {
                        data = {};
                    }

                    if (!response.ok) {
                        if (response.status === 422 && data && data.errors) {
                            if (data.errors.email && data.errors.email.length) {
                                setInlineError(newEmailError, 'Please enter a valid email ending with @example.com.');
                            }
                            if (data.errors.password && data.errors.password.length) {
                                setInlineError(newPasswordError, 'Password change is required.');
                            }
                        } else {
                            const message = data.message || 'Failed to update credentials.';
                            if (errorBox) {
                                errorBox.textContent = message;
                                errorBox.classList.remove('hidden');
                            }
                        }
                        return;
                    }

                    if (successBox) {
                        successBox.textContent = 'Credentials updated. Redirecting to dashboard.';
                        successBox.classList.remove('hidden');
                    }
                    keepLoading = true;
                    setLoading(true);

                    let role = 'admin';

                    if (data.current_role && data.current_role.role_name) {
                        role = String(data.current_role.role_name).toLowerCase();
                    }

                    setTimeout(function () {
                        let target = "{{ url('/dashboard') }}/" + role;
                        if (data && data.uuid) {
                            target += '?user_uuid=' + encodeURIComponent(String(data.uuid));
                        }
                        window.location.href = target;
                    }, 1000);
                } catch (_) {
                    if (errorBox) {
                        errorBox.textContent = 'Network error. Please try again.';
                        errorBox.classList.remove('hidden');
                    }
                } finally {
                    if (!keepLoading) {
                        setLoading(false);
                    }
                }
            });
        });
    </script>
</body>
</html>
