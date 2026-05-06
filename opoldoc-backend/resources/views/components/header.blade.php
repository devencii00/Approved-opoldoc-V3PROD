@props(['role' => 'admin'])

@php
    $roleKey = strtolower($role ?? 'admin');
    $roleNames = [
        'admin' => 'Admin',
        'doctor' => 'Doctor',
        'receptionist' => 'Receptionist',
        'patient' => 'Patient',
    ];
    $roleLabel = $roleNames[$roleKey] ?? ucfirst($roleKey);
@endphp

<header class="sticky top-0 z-30 bg-white/85 backdrop-blur-md border-b border-slate-200 px-8 h-15 flex items-center justify-between">
    <div class="flex items-center gap-1 text-slate-400 text-[0.82rem]">
        <span>Opol Clinic</span>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6" />
        </svg>
        <span class="text-slate-500">{{ $roleLabel }}</span>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6" />
        </svg>
        <span class="text-slate-700 font-semibold">Dashboard</span>
    </div>
    <div class="relative flex items-center gap-3">
        <button id="headerNotificationButton" class="w-8.5 h-8.5 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:border-cyan-400 hover:text-cyan-600 relative">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>
            <span id="headerNotificationDot" class="absolute top-1.5 right-1.5 w-1.75 h-1.75 rounded-full bg-cyan-500 border-2 border-white"></span>
        </button>

        <div id="headerNotificationPanel" class="hidden absolute right-0 top-10 w-80 max-h-80 bg-white border border-slate-200 rounded-2xl shadow-[0_10px_30px_rgba(15,23,42,0.18)] overflow-hidden">
            <div class="px-3 py-2 border-b border-slate-100 flex items-center justify-between">
                <p class="text-[0.75rem] font-semibold text-slate-800">Notifications</p>
                <span class="text-[0.65rem] text-slate-400 uppercase tracking-widest">Activity</span>
            </div>
            <div id="headerNotificationBody" class="max-h-64 overflow-y-auto scrollbar-hidden">
                <div class="px-3 py-3 text-[0.75rem] text-slate-400">
                    Loading notifications...
                </div>
            </div>
        </div>
    </div>
</header>

<template id="headerIconNotificationAppointment">
    <x-lucide-calendar class="w-4 h-4 text-cyan-600" />
</template>
<template id="headerIconNotificationPayment">
    <x-lucide-credit-card class="w-4 h-4 text-cyan-600" />
</template>
<template id="headerIconNotificationSystem">
    <x-lucide-info class="w-4 h-4 text-cyan-600" />
</template>

<script>
    (function () {
        function headerApiFetch(path, options) {
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

        function renderNotifications(container, items) {
            if (!container) {
                return
            }

            var iconAppointment = (function () {
                var tpl = document.getElementById('headerIconNotificationAppointment')
                return tpl ? String(tpl.innerHTML || '').trim() : ''
            })()
            var iconPayment = (function () {
                var tpl = document.getElementById('headerIconNotificationPayment')
                return tpl ? String(tpl.innerHTML || '').trim() : ''
            })()
            var iconSystem = (function () {
                var tpl = document.getElementById('headerIconNotificationSystem')
                return tpl ? String(tpl.innerHTML || '').trim() : ''
            })()

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;')
            }

            if (!items || !items.length) {
                container.innerHTML = '<div class="px-3 py-3 text-[0.75rem] text-slate-400">No notifications at the moment.</div>'
                return
            }

            var html = ''
            items.forEach(function (item) {
                var type = item && item.type ? String(item.type) : 'system'
                var title = type === 'appointment' ? 'Appointment update' : (type === 'payment' ? 'Payment update' : 'System')
                var body = item && item.message ? String(item.message) : ''
                var createdAt = item && item.created_at ? String(item.created_at) : ''
                var timeLabel = ''
                if (createdAt) {
                    var dt = new Date(createdAt)
                    if (!isNaN(dt.getTime())) {
                        timeLabel = dt.toLocaleString()
                    }
                }

                var iconHtml = type === 'appointment' ? iconAppointment : (type === 'payment' ? iconPayment : iconSystem)

                html += '<div class="px-3 py-2 border-b border-slate-50 last:border-0 flex gap-2">' +
                    '<div class="mt-0.5">' +
                    iconHtml +
                    '</div>' +
                    '<div class="flex-1">' +
                    '<div class="text-[0.75rem] font-semibold text-slate-800">' + escapeHtml(title) + '</div>' +
                    '<div class="text-[0.7rem] text-slate-500">' + escapeHtml(body) + '</div>' +
                    '<div class="text-[0.65rem] text-slate-400 mt-0.5">' + escapeHtml(timeLabel) + '</div>' +
                    '</div>' +
                    '</div>'
            })

            container.innerHTML = html
        }

        document.addEventListener('DOMContentLoaded', function () {
            var button = document.getElementById('headerNotificationButton')
            var panel = document.getElementById('headerNotificationPanel')
            var body = document.getElementById('headerNotificationBody')
            var dot = document.getElementById('headerNotificationDot')

            if (!button || !panel) {
                return
            }

            button.addEventListener('click', function (e) {
                e.stopPropagation()
                var isHidden = panel.classList.contains('hidden')
                if (isHidden) {
                    if (body) {
                        body.innerHTML = '<div class="px-3 py-3 text-[0.75rem] text-slate-400">Loading notifications...</div>'
                    }
                    headerApiFetch("{{ url('/api/notifications') }}", { method: 'GET' })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                return { ok: response.ok, data: data }
                            }).catch(function () {
                                return { ok: response.ok, data: null }
                            })
                        })
                        .then(function (result) {
                            if (!result.ok || !result.data) {
                                if (body) {
                                    body.innerHTML = '<div class="px-3 py-3 text-[0.75rem] text-slate-400">Unable to load notifications.</div>'
                                }
                                return
                            }

                            var payload = result.data
                            var items = []
                            if (Array.isArray(payload)) {
                                items = payload
                            } else if (Array.isArray(payload.data)) {
                                items = payload.data
                            } else if (Array.isArray(payload.notifications)) {
                                items = payload.notifications
                            }

                            renderNotifications(body, items)

                            var hasUnread = items.some(function (n) {
                                return n && n.is_read === false
                            })
                            if (dot) {
                                dot.classList.toggle('hidden', !hasUnread)
                            }
                        })
                        .catch(function () {
                            if (body) {
                                body.innerHTML = '<div class="px-3 py-3 text-[0.75rem] text-slate-400">Unable to load notifications.</div>'
                            }
                        })
                }
                panel.classList.toggle('hidden')
            })

            document.addEventListener('click', function () {
                if (panel && !panel.classList.contains('hidden')) {
                    panel.classList.add('hidden')
                }
            })
        })
    })()
</script>
