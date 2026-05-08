@extends('layouts.app')

@section('title', 'Dashboard')

@section('body')
<div class="flex min-h-screen">
    <x-sidebar :role="$role" />

    <div class="ml-[248px] flex-1 flex flex-col min-h-screen">
        <x-header :role="$role" />

        <div class="flex-1 p-8 md:p-5">
            @php
                $mapping = [
                    'admin' => 'admindb',
                    'doctor' => 'doctordb',
                    'receptionist' => 'receptdb',
                    'patient' => 'patientdb',
                ];

                $key = $mapping[$role] ?? null;
                $viewName = $key ? 'dashviews.' . $role . '.' . $key : null;
            @endphp

            @if ($viewName)
                @includeIf($viewName)
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var expectedRole = "{{ strtolower($role ?? 'admin') }}"
        if (expectedRole && expectedRole !== 'patient') {
            var applyNoAutocomplete = function (root) {
                var scope = root && root.querySelectorAll ? root : document

                var forms = scope.querySelectorAll('form')
                Array.prototype.forEach.call(forms, function (form) {
                    form.setAttribute('autocomplete', 'off')

                    if (!form.querySelector('[data-autofill-trap="1"]')) {
                        var trapUser = document.createElement('input')
                        trapUser.setAttribute('type', 'text')
                        trapUser.setAttribute('tabindex', '-1')
                        trapUser.setAttribute('autocomplete', 'username')
                        trapUser.setAttribute('name', 'fake_username_' + Math.random().toString(16).slice(2))
                        trapUser.setAttribute('data-autofill-trap', '1')
                        trapUser.style.position = 'absolute'
                        trapUser.style.left = '-9999px'
                        trapUser.style.width = '1px'
                        trapUser.style.height = '1px'
                        trapUser.style.opacity = '0'

                        var trapPass = document.createElement('input')
                        trapPass.setAttribute('type', 'password')
                        trapPass.setAttribute('tabindex', '-1')
                        trapPass.setAttribute('autocomplete', 'current-password')
                        trapPass.setAttribute('name', 'fake_password_' + Math.random().toString(16).slice(2))
                        trapPass.setAttribute('data-autofill-trap', '1')
                        trapPass.style.position = 'absolute'
                        trapPass.style.left = '-9999px'
                        trapPass.style.width = '1px'
                        trapPass.style.height = '1px'
                        trapPass.style.opacity = '0'

                        form.prepend(trapPass)
                        form.prepend(trapUser)
                    }
                })

                var fields = scope.querySelectorAll('input, textarea')
                Array.prototype.forEach.call(fields, function (el) {
                    var tag = (el.tagName || '').toLowerCase()
                    if (tag !== 'input' && tag !== 'textarea') return
                    var type = (el.getAttribute('type') || '').toLowerCase()
                    if (type === 'hidden') return
                    if (type === 'checkbox' || type === 'radio' || type === 'submit' || type === 'button' || type === 'file') {
                        return
                    }
                    el.setAttribute('autocomplete', 'off')
                    el.setAttribute('autocapitalize', 'off')
                    el.setAttribute('autocorrect', 'off')
                    el.setAttribute('spellcheck', 'false')
                    el.setAttribute('data-lpignore', 'true')
                    el.setAttribute('data-form-type', 'other')

                    if (el.disabled) {
                        return
                    }
                    if (el.hasAttribute('readonly')) {
                        return
                    }
                    if (el.getAttribute('data-no-autofill-readonly') === '1') {
                        return
                    }

                    el.setAttribute('data-no-autofill-readonly', '1')
                    el.setAttribute('readonly', 'readonly')
                    el.addEventListener('focus', function () {
                        if (el.getAttribute('data-no-autofill-readonly') === '1') {
                            el.removeAttribute('readonly')
                            el.removeAttribute('data-no-autofill-readonly')
                        }
                    }, { once: true })
                })
            }

            applyNoAutocomplete(document)

            try {
                var observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (m) {
                        Array.prototype.forEach.call(m.addedNodes || [], function (node) {
                            if (!node || node.nodeType !== 1) return
                            applyNoAutocomplete(node)
                        })
                    })
                })
                observer.observe(document.documentElement, { childList: true, subtree: true })
            } catch (e) {
            }
        }
        if (typeof apiFetch !== 'function') return

        apiFetch("{{ request()->getBasePath() }}/api/user", { method: 'GET' })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, status: r.status, data: d } }).catch(function () { return { ok: r.ok, status: r.status, data: null } }) })
            .then(function (result) {
                if (!result.ok || !result.data) {
                    return
                }

                var actualRole = result.data && result.data.role ? String(result.data.role).toLowerCase() : ''
                var userUuid = result.data && result.data.uuid ? String(result.data.uuid) : ''
                if (!actualRole) return
                if (actualRole === expectedRole) return

                var target = "{{ request()->getBaseUrl() }}/dashboard/" + encodeURIComponent(actualRole)
                if (actualRole !== 'admin' && userUuid) {
                    target += '?user_uuid=' + encodeURIComponent(userUuid)
                }
                window.location.href = target
            })
            .catch(function () {})
    })
</script>
@endsection
