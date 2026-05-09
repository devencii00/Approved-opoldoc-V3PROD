@extends('layouts.app')

@section('title', 'Guest Walk-in')

@section('body')
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-4xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl overflow-hidden border border-slate-200 bg-white flex items-center justify-center">
                        <img src="{{ asset('images/opoldoc3.png') }}" alt="Opol Doctors Medical Clinic" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <div class="font-serif font-bold text-slate-900 leading-tight">Opol Doctors Medical Clinic</div>
                        <div class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Guest Walk-in Registration</div>
                    </div>
                </div>
                <a href="{{ $qrUrl }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white border border-slate-200 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">
                    <x-lucide-qr-code class="w-[18px] h-[18px]" />
                    View QR
                </a>
            </div>

            <div class="bg-white border border-slate-200 rounded-[18px] shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
                <div class="p-6 border-b border-slate-100 bg-slate-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">Create Guest walk-in</h2>
                            <p class="text-xs text-slate-500">Register a guest walk-in based on personal information.</p>
                        </div>
                        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Guest</span>
                    </div>
                </div>

                <div class="p-6">
                    <div id="publicGuestWalkInError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
                    <div id="publicGuestWalkInSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>
                    <div id="publicGuestWalkInCreds" class="hidden mb-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[0.75rem] text-slate-700"></div>

                    <form id="publicGuestWalkInForm" method="POST" action="javascript:void(0)" class="grid gap-3 grid-cols-1 md:grid-cols-4 items-start">
                        <div>
                            <label for="public_guest_firstname" class="block text-[0.7rem] text-slate-600 mb-1">First name</label>
                            <input id="public_guest_firstname" type="text" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="First name">
                        </div>
                        <div>
                            <label for="public_guest_middlename" class="block text-[0.7rem] text-slate-600 mb-1">Middle name (optional)</label>
                            <input id="public_guest_middlename" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Middle name">
                        </div>
                        <div>
                            <label for="public_guest_lastname" class="block text-[0.7rem] text-slate-600 mb-1">Last name</label>
                            <input id="public_guest_lastname" type="text" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Last name">
                        </div>
                        <div>
                            <label for="public_guest_contact" class="block text-[0.7rem] text-slate-600 mb-1">Contact number (optional)</label>
                            <input id="public_guest_contact" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Mobile number">
                        </div>

                        <div class="min-w-0 md:col-span-2">
                            <label for="public_guest_service_search" class="block text-[0.7rem] text-slate-600 mb-1">Services</label>
                            <div class="relative">
                                <input id="public_guest_service_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type to search service">
                                <input id="public_guest_service_ids" type="hidden">
                                <div id="publicGuestServiceResults" class="hidden absolute left-0 right-0 top-full mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain z-50"></div>
                            </div>
                            <div id="publicGuestSelectedServices" class="mt-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[0.78rem] text-slate-700 max-h-28 overflow-y-auto overscroll-contain"></div>
                        </div>

                        <div class="min-w-0 md:col-span-2">
                            <label for="public_guest_doctor_search" class="block text-[0.7rem] text-slate-600 mb-1">Doctor</label>
                            <div class="relative">
                                <input id="public_guest_doctor_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type to search doctor" disabled>
                                <input id="public_guest_doctor_id" type="hidden" required>
                                <div id="publicGuestDoctorResults" class="hidden absolute left-0 right-0 top-full mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain z-50"></div>
                            </div>
                            <div id="publicGuestDoctorPreview" class="hidden mt-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[0.78rem] text-slate-700 break-words"></div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="public_guest_reason" class="block text-[0.7rem] text-slate-600 mb-1">Reason (optional)</label>
                            <input id="public_guest_reason" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Reason for visit">
                        </div>

                        <div>
                            <div class="block text-[0.7rem] text-slate-600 mb-1">Priority</div>
                            <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                                <label class="flex items-center gap-2 text-xs text-slate-700">
                                    <input id="public_priority_pwd" type="checkbox" class="accent-cyan-600">
                                    <span>Are you a PWD?</span>
                                </label>
                                <label class="mt-1 flex items-center gap-2 text-xs text-slate-700">
                                    <input id="public_priority_pregnant" type="checkbox" class="accent-cyan-600">
                                    <span>Are you pregnant?</span>
                                </label>
                                <label class="mt-1 flex items-center gap-2 text-xs text-slate-700">
                                    <input id="public_priority_senior" type="checkbox" class="accent-cyan-600">
                                    <span>Are you a senior citizen?</span>
                                </label>
                                <div class="mt-1 text-[0.68rem] text-slate-400">Check none if N/A</div>
                                <input id="public_guest_priority_level" type="hidden" value="">
                            </div>
                        </div>

                        <div class="flex items-end self-end">
                            <button id="publicGuestWalkInSubmit" type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors disabled:opacity-60 disabled:hover:bg-cyan-600">
                                <span id="publicGuestWalkInSpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                                <span id="publicGuestWalkInSubmitLabel">Submit</span>
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 text-[0.72rem] text-slate-500">
                        Clinic hours: <span class="font-semibold">8:00 AM – 5:00 PM</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="publicWalkInConfirmOverlay" class="hidden fixed inset-0 z-[60] bg-slate-900/40 items-center justify-center p-4">
        <div class="w-full max-w-sm rounded-2xl bg-white border border-slate-200 shadow-[0_12px_30px_rgba(15,23,42,0.24)] p-4">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-700">
                    <x-lucide-info class="w-[18px] h-[18px]" />
                </div>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-slate-900">Confirm Registration</div>
                    <div id="publicWalkInConfirmMessage" class="text-[0.78rem] text-slate-600 mt-1 whitespace-pre-wrap"></div>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-end gap-2">
                <button type="button" id="publicWalkInConfirmCancel" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="button" id="publicWalkInConfirmOk" class="px-3 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var overlay = document.getElementById('publicWalkInConfirmOverlay')
            var messageEl = document.getElementById('publicWalkInConfirmMessage')
            var okBtn = document.getElementById('publicWalkInConfirmOk')
            var cancelBtn = document.getElementById('publicWalkInConfirmCancel')
            var resolver = null

            function close(result) {
                if (overlay) overlay.classList.add('hidden'), overlay.classList.remove('flex')
                var r = resolver; resolver = null
                if (typeof r === 'function') r(!!result)
            }

            window.publicConfirm = function(message) {
                return new Promise(function(resolve) {
                    if (!overlay || !messageEl) return resolve(window.confirm(message))
                    resolver = resolve
                    messageEl.textContent = message
                    overlay.classList.remove('hidden')
                    overlay.classList.add('flex')
                })
            }

            if (okBtn) okBtn.addEventListener('click', function() { close(true) })
            if (cancelBtn) cancelBtn.addEventListener('click', function() { close(false) })
            if (overlay) overlay.addEventListener('click', function(e) { if (e.target === overlay) close(false) })
        })
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var token = @json($token);
            var services = @json($services);
            var doctors = @json($doctors);

            var form = document.getElementById('publicGuestWalkInForm')
            var errorBox = document.getElementById('publicGuestWalkInError')
            var successBox = document.getElementById('publicGuestWalkInSuccess')
            var credsBox = document.getElementById('publicGuestWalkInCreds')
            var submitBtn = document.getElementById('publicGuestWalkInSubmit')
            var submitSpinner = document.getElementById('publicGuestWalkInSpinner')
            var submitLabel = document.getElementById('publicGuestWalkInSubmitLabel')

            var firstNameInput = document.getElementById('public_guest_firstname')
            var middleNameInput = document.getElementById('public_guest_middlename')
            var lastNameInput = document.getElementById('public_guest_lastname')
            var contactInput = document.getElementById('public_guest_contact')
            var reasonInput = document.getElementById('public_guest_reason')

            var serviceSearch = document.getElementById('public_guest_service_search')
            var serviceIdsInput = document.getElementById('public_guest_service_ids')
            var serviceResults = document.getElementById('publicGuestServiceResults')
            var selectedServicesEl = document.getElementById('publicGuestSelectedServices')

            var doctorSearch = document.getElementById('public_guest_doctor_search')
            var doctorIdInput = document.getElementById('public_guest_doctor_id')
            var doctorResults = document.getElementById('publicGuestDoctorResults')
            var doctorPreview = document.getElementById('publicGuestDoctorPreview')

            var pwdCb = document.getElementById('public_priority_pwd')
            var pregCb = document.getElementById('public_priority_pregnant')
            var seniorCb = document.getElementById('public_priority_senior')
            var priorityInput = document.getElementById('public_guest_priority_level')

            var selectedServices = []
            var selectedDoctor = null

            function setSubmitting(isSubmitting) {
                if (submitBtn) submitBtn.disabled = !!isSubmitting
                if (submitSpinner) submitSpinner.classList.toggle('hidden', !isSubmitting)
                if (submitLabel) submitLabel.textContent = isSubmitting ? 'Submitting…' : 'Submit'
            }

            function showError(message) {
                if (!errorBox) return
                errorBox.textContent = message || ''
                errorBox.classList.toggle('hidden', !message)
            }

            var successTimer = null
            function showSuccess(message) {
                if (!successBox) return
                successBox.textContent = message || ''
                if (message) {
                    successBox.classList.remove('hidden')
                    if (successTimer) clearTimeout(successTimer)
                    successTimer = setTimeout(function() {
                        successBox.classList.add('hidden')
                    }, 5000)
                } else {
                    successBox.classList.add('hidden')
                }
            }

            function showCreds(message) {
                if (!credsBox) return
                credsBox.textContent = message || ''
                credsBox.classList.toggle('hidden', !message)
            }

            function normalizeText(value) {
                return String(value || '').trim().toLowerCase()
            }

            function wordPrefixMatch(value, query) {
                var v = normalizeText(value || '')
                var q = normalizeText(query || '')
                if (!q) return true
                if (!v) return false
                if (v.indexOf(q) === 0) return true
                return v.split(/\s+/).some(function (part) { return part.indexOf(q) === 0 })
            }

            function extractServiceCategory(serviceName) {
                var s = String(serviceName || '').trim()
                if (!s) return ''
                var parts = s.split(':')
                return normalizeText(parts[0] || s)
            }

            function serviceGroup(service) {
                return extractServiceCategory(service && service.service_name ? service.service_name : '')
            }

            function isWalkInExcludedService(service) {
                var g = serviceGroup(service)
                return g === 'obsterician - gynecologist' || g === 'obstetrician - gynecologist' || g === 'general surgeon'
            }

            function specializationMatches(serviceCategory, doctorSpecialization) {
                var a = normalizeText(serviceCategory)
                var b = normalizeText(doctorSpecialization)
                if (!a || !b) return false
                return b.indexOf(a) !== -1 || a.indexOf(b) !== -1
            }

            function escapeHtml(input) {
                var s = String(input == null ? '' : input)
                return s
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;')
            }

            function dayKeyFromDate(dateStr) {
                if (!dateStr) return ''
                var d = new Date(dateStr + 'T00:00:00')
                if (isNaN(d.getTime())) return ''
                var keys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']
                return keys[d.getDay()] || ''
            }

            function minutesFromHHMM(timeStr) {
                var t = String(timeStr || '').slice(0, 5)
                if (!/^\d{2}:\d{2}$/.test(t)) return NaN
                var parts = t.split(':')
                return (parseInt(parts[0], 10) * 60) + parseInt(parts[1], 10)
            }

            function doctorSchedulesForDay(doctor, dayKey, dateStr) {
                var list = doctor && doctor.doctor_schedules && Array.isArray(doctor.doctor_schedules) ? doctor.doctor_schedules : []
                var isToday = false
                if (dateStr) {
                    var today = new Date().toISOString().slice(0, 10)
                    isToday = String(dateStr) === today
                }
                return list.filter(function (s) {
                    if (!s) return false
                    if (String(s.day_of_week || '').toLowerCase() !== String(dayKey || '').toLowerCase()) return false
                    if (isToday && s.is_available === false) return false
                    return true
                })
            }

            function hasScheduleAtTime(doctor, dayKey, dateStr, hhmm) {
                var slots = doctorSchedulesForDay(doctor, dayKey, dateStr)
                if (!hhmm) return slots.length > 0
                var t = minutesFromHHMM(String(hhmm || '').slice(0, 5))
                if (isNaN(t)) return slots.length > 0
                return slots.some(function (s) {
                    var st = minutesFromHHMM(String(s.start_time || '').slice(0, 5))
                    var en = minutesFromHHMM(String(s.end_time || '').slice(0, 5))
                    if (isNaN(st) || isNaN(en)) return false
                    return t >= st && t < en
                })
            }

            function syncPriorityHiddenInput() {
                if (!priorityInput) return
                var picks = []
                if (pwdCb && pwdCb.checked) picks.push(2)
                if (pregCb && pregCb.checked) picks.push(3)
                if (seniorCb && seniorCb.checked) picks.push(4)
                if (!picks.length) {
                    priorityInput.value = ''
                    return
                }
                priorityInput.value = String(Math.min.apply(Math, picks))
            }

            function syncServiceHiddenInput() {
                if (!serviceIdsInput) return
                var ids = selectedServices.map(function (s) { return s && s.service_id != null ? parseInt(s.service_id, 10) : 0 }).filter(function (v) { return !!v && !isNaN(v) })
                serviceIdsInput.value = ids.join(',')
            }

            function renderSelectedServices() {
                if (!selectedServicesEl) return
                if (!selectedServices.length) {
                    selectedServicesEl.innerHTML = '<div class="text-slate-400 text-[0.75rem]">No services selected.</div>'
                    return
                }
                var html = ''
                selectedServices.forEach(function (s, idx) {
                    var name = String(s && s.service_name ? s.service_name : '').trim() || 'Service'
                    html += '<div class="flex items-center justify-between gap-2 py-1 border-b border-slate-100 last:border-0">' +
                        '<div class="min-w-0 truncate">' + escapeHtml(name) + '</div>' +
                        '<button type="button" class="text-[0.72rem] font-semibold text-rose-600 hover:underline" data-remove="' + idx + '">Remove</button>' +
                    '</div>'
                })
                selectedServicesEl.innerHTML = html
                var buttons = selectedServicesEl.querySelectorAll('button[data-remove]')
                Array.prototype.forEach.call(buttons, function (btn) {
                    btn.addEventListener('click', function () {
                        var idx = parseInt(btn.getAttribute('data-remove') || '', 10)
                        if (isNaN(idx) || idx < 0) return
                        selectedServices.splice(idx, 1)
                        syncServiceHiddenInput()
                        renderSelectedServices()
                        syncDoctorEnabled()
                        setDoctorSelection(null)
                        if (doctorSearch) doctorSearch.value = ''
                        if (doctorSearch && doctorResults && !doctorResults.classList.contains('hidden')) {
                            searchDoctors(String(doctorSearch.value || ''))
                        }
                    })
                })
            }

            function renderServiceResults(items) {
                if (!serviceResults) return
                var list = Array.isArray(items) ? items : []
                list = list.filter(function (s) { return !isWalkInExcludedService(s) })

                if (selectedServices && selectedServices.length) {
                    var base = serviceGroup(selectedServices[0])
                    if (base) {
                        list = list.filter(function (s) { return serviceGroup(s) === base })
                    }
                }
                if (!list.length) {
                    serviceResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No services found.</div>'
                    serviceResults.classList.remove('hidden')
                    return
                }
                var html = ''
                list.forEach(function (s) {
                    var name = String(s.service_name || '').trim() || 'Service'
                    var meta = []
                    if (s.duration_minutes != null) meta.push(String(s.duration_minutes) + ' min')
                    if (s.price != null) meta.push('₱' + String(s.price))
                    var desc = s.description != null ? String(s.description).trim() : ''
                    html += '<button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 border-b border-slate-100 last:border-0">' +
                        '<div class="text-[0.78rem] text-slate-800 font-semibold">' + escapeHtml(name) + '</div>' +
                        '<div class="text-[0.72rem] text-slate-500">' + escapeHtml(meta.join(' • ') || '—') + '</div>' +
                        (desc ? '<div class="mt-0.5 text-[0.72rem] text-slate-500">' + escapeHtml(desc) + '</div>' : '') +
                    '</button>'
                })
                serviceResults.innerHTML = html
                serviceResults.classList.remove('hidden')
                var buttons = serviceResults.querySelectorAll('button')
                Array.prototype.forEach.call(buttons, function (btn, idx) {
                    btn.addEventListener('click', function () {
                        var chosen = list[idx]
                        if (!chosen) return
                        var sid = chosen.service_id != null ? parseInt(chosen.service_id, 10) : 0
                        if (!sid || isNaN(sid)) return
                        if (selectedServices.some(function (x) { return String(x && x.service_id) === String(sid) })) return
                        selectedServices.push(chosen)
                        syncServiceHiddenInput()
                        renderSelectedServices()
                        syncDoctorEnabled()
                        setDoctorSelection(null)
                        if (doctorSearch) doctorSearch.value = ''
                        if (serviceSearch) serviceSearch.value = ''
                        if (serviceResults) serviceResults.classList.add('hidden')
                    })
                })
            }

            function searchServices(query) {
                var q = normalizeText(query)
                var list = Array.isArray(services) ? services : []
                if (!q) {
                    renderServiceResults(list.slice(0, 12))
                    return
                }
                var filtered = list.filter(function (s) {
                    return wordPrefixMatch(s && s.service_name ? s.service_name : '', q)
                })
                renderServiceResults(filtered.slice(0, 20))
            }

            function doctorDisplayName(doctor) {
                if (!doctor) return ''
                var parts = [doctor.firstname, doctor.middlename, doctor.lastname].filter(function (v) { return String(v || '').trim() !== '' })
                var name = parts.join(' ').trim()
                if (!name) name = 'Doctor #' + (doctor.user_id != null ? doctor.user_id : '')
                return name
            }

            function setDoctorSelection(doctor) {
                selectedDoctor = doctor || null
                if (doctorIdInput) doctorIdInput.value = doctor && doctor.user_id != null ? String(doctor.user_id) : ''
                if (doctorPreview) {
                    if (!doctor) {
                        doctorPreview.textContent = ''
                        doctorPreview.classList.add('hidden')
                    } else {
                        var label = 'Doctor: ' + doctorDisplayName(doctor)
                        if (doctor.specialization) label += ' • ' + String(doctor.specialization)
                        doctorPreview.textContent = label
                        doctorPreview.classList.remove('hidden')
                    }
                }
                if (doctorResults) doctorResults.classList.add('hidden')
            }

            function renderDoctorResults(items) {
                if (!doctorResults) return
                var list = Array.isArray(items) ? items : []
                if (!selectedServices || !selectedServices.length) {
                    doctorResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">Select a service first.</div>'
                    doctorResults.classList.remove('hidden')
                    return
                }
                if (!list.length) {
                    doctorResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No doctors found.</div>'
                    doctorResults.classList.remove('hidden')
                    return
                }

                var dateStr = new Date().toISOString().slice(0, 10)
                var dayKey = dayKeyFromDate(dateStr)
                var checkTime = new Date().toTimeString().slice(0, 5)

                var enriched = list.map(function (d) {
                    var name = doctorDisplayName(d)
                    var spec = d && d.specialization ? String(d.specialization) : ''
                    var isDoctorAvailable = d && d.is_available !== false
                    var hasSchedule = !!dayKey && hasScheduleAtTime(d, dayKey, dateStr, checkTime)
                    var isSelectable = isDoctorAvailable && hasSchedule
                    var tag = ''
                    if (!isDoctorAvailable) tag = 'Unavailable'
                    else if (!hasSchedule) tag = 'No schedule on this time'
                    return { d: d, name: name, spec: spec, isSelectable: isSelectable, tag: tag }
                })

                enriched.sort(function (a, b) {
                    if (a.isSelectable !== b.isSelectable) return a.isSelectable ? -1 : 1
                    return normalizeText(a.name).localeCompare(normalizeText(b.name))
                })

                var html = ''
                enriched.forEach(function (x) {
                    var d = x.d
                    var meta = [x.spec].filter(Boolean).join(' • ')
                    html += '<button type="button" class="w-full text-left px-3 py-2 border-b border-slate-100 last:border-0 flex items-start justify-between gap-3 ' + (x.isSelectable ? 'hover:bg-slate-50' : 'bg-slate-50/60 cursor-not-allowed') + '" ' + (x.isSelectable ? '' : 'disabled') + ' data-doctor-id="' + escapeHtml(d.user_id) + '">' +
                        '<div class="min-w-0">' +
                            '<div class="text-[0.78rem] text-slate-800 font-semibold">' + escapeHtml('Dr. ' + x.name) + '</div>' +
                            '<div class="text-[0.72rem] text-slate-500">#' + escapeHtml(d.user_id) + (meta ? ' • ' + escapeHtml(meta) : '') + '</div>' +
                        '</div>' +
                        (x.tag
                            ? '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.65rem] font-semibold bg-slate-100 text-slate-500 border border-slate-200">' + escapeHtml(x.tag) + '</span>'
                            : '') +
                    '</button>'
                })
                doctorResults.innerHTML = html
                doctorResults.classList.remove('hidden')

                var buttons = doctorResults.querySelectorAll('button[data-doctor-id]')
                Array.prototype.forEach.call(buttons, function (btn) {
                    btn.addEventListener('click', function () {
                        var id = btn.getAttribute('data-doctor-id') || ''
                        var chosen = (doctors || []).find(function (d) { return String(d && d.user_id) === String(id) }) || null
                        if (!chosen) return
                        setDoctorSelection(chosen)
                        if (doctorSearch) doctorSearch.value = doctorDisplayName(chosen)
                    })
                })
            }

            function searchDoctors(query) {
                var q = normalizeText(query)
                var list = (doctors || []).slice()
                var baseService = selectedServices && selectedServices.length ? selectedServices[0] : null
                var category = extractServiceCategory(baseService && baseService.service_name ? baseService.service_name : '')
                if (category) {
                    list = list.filter(function (d) {
                        return specializationMatches(category, d && d.specialization ? d.specialization : '')
                    })
                }
                if (q) {
                    list = list.filter(function (d) {
                        return wordPrefixMatch(doctorDisplayName(d) + ' ' + (d && d.specialization ? d.specialization : ''), q)
                    })
                }
                renderDoctorResults(list.slice(0, 20))
            }

            function syncDoctorEnabled() {
                var enabled = !!(selectedServices && selectedServices.length)
                if (doctorSearch) doctorSearch.disabled = !enabled
                if (!enabled) {
                    setDoctorSelection(null)
                    if (doctorSearch) doctorSearch.value = ''
                }
            }

            if (serviceSearch) {
                serviceSearch.addEventListener('focus', function () {
                    searchServices(String(serviceSearch.value || ''))
                })
                serviceSearch.addEventListener('input', function () {
                    searchServices(String(serviceSearch.value || ''))
                })
            }

            if (doctorSearch) {
                doctorSearch.addEventListener('focus', function () {
                    searchDoctors(String(doctorSearch.value || ''))
                })
                doctorSearch.addEventListener('input', function () {
                    searchDoctors(String(doctorSearch.value || ''))
                })
            }

            function hideResultsOnBlur(inputEl, resultsEl) {
                if (!inputEl || !resultsEl) return
                inputEl.addEventListener('blur', function () {
                    setTimeout(function () {
                        if (resultsEl.classList.contains('hidden')) return
                        var active = document.activeElement
                        if (!(resultsEl.contains(active) || inputEl.contains(active))) {
                            resultsEl.classList.add('hidden')
                        }
                    }, 200) // Increased delay
                })
                // Prevent blur from hiding when clicking results
                resultsEl.addEventListener('mousedown', function (e) {
                    e.preventDefault()
                })
            }

            if (pwdCb) pwdCb.addEventListener('change', syncPriorityHiddenInput)
            if (pregCb) pregCb.addEventListener('change', syncPriorityHiddenInput)
            if (seniorCb) seniorCb.addEventListener('change', syncPriorityHiddenInput)
            syncPriorityHiddenInput()

            hideResultsOnBlur(serviceSearch, serviceResults)
            hideResultsOnBlur(doctorSearch, doctorResults)

            document.addEventListener('click', function (e) {
                var t = e && e.target ? e.target : null
                if (serviceResults && !serviceResults.classList.contains('hidden')) {
                    if (!(serviceResults.contains(t) || (serviceSearch && serviceSearch.contains(t)))) {
                        serviceResults.classList.add('hidden')
                    }
                }
                if (doctorResults && !doctorResults.classList.contains('hidden')) {
                    if (!(doctorResults.contains(t) || (doctorSearch && doctorSearch.contains(t)))) {
                        doctorResults.classList.add('hidden')
                    }
                }
            }, true)

            syncServiceHiddenInput()
            renderSelectedServices()
            syncDoctorEnabled()

            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault()

                    var firstName = firstNameInput ? String(firstNameInput.value || '').trim() : ''
                    var middleName = middleNameInput ? String(middleNameInput.value || '').trim() : ''
                    var lastName = lastNameInput ? String(lastNameInput.value || '').trim() : ''
                    var contact = contactInput ? String(contactInput.value || '').trim() : ''
                    var reason = reasonInput ? String(reasonInput.value || '').trim() : ''

                    var doctorId = doctorIdInput ? parseInt(doctorIdInput.value, 10) : 0
                    var serviceIds = serviceIdsInput && serviceIdsInput.value ? String(serviceIdsInput.value).split(',').map(function (v) { return parseInt(v, 10) }).filter(function (v) { return !!v && !isNaN(v) }) : []
                    var priorityLevel = priorityInput && priorityInput.value ? parseInt(priorityInput.value, 10) : null

                    if (!firstName || !lastName) {
                        showError('First name and last name are required.')
                        return
                    }
                    if (!serviceIds.length) {
                        showError('Services are required.')
                        return
                    }
                    if (!doctorId) {
                        showError('Doctor is required.')
                        return
                    }

                    var doctorName = selectedDoctor ? doctorDisplayName(selectedDoctor) : 'Unknown'
                    var serviceNames = selectedServices.map(function(s) { return s.service_name }).join(', ')
                    
                    var confirmMsg = 'Please review your details:\n\n' +
                        'Name: ' + [firstName, middleName, lastName].filter(function (part) { return String(part || '').trim() !== '' }).join(' ') + '\n' +
                        'Doctor: Dr. ' + doctorName + '\n' +
                        'Services: ' + serviceNames + '\n';
                    
                    if (contact) confirmMsg += 'Contact: ' + contact + '\n';
                    if (reason) confirmMsg += 'Reason: ' + reason + '\n';
                    
                    confirmMsg += '\nAre you sure you want to register?';

                    window.publicConfirm(confirmMsg).then(function(confirmed) {
                        if (!confirmed) return;

                        showError('')
                        showSuccess('')
                        showCreds('')
                        setSubmitting(true)

                        if (typeof apiFetch !== 'function') {
                            setSubmitting(false)
                            showError('API client is not available.')
                            return
                        }

                        var body = {
                            firstname: firstName,
                            middlename: middleName,
                            lastname: lastName,
                            contact_number: contact || undefined,
                            reason_for_visit: reason || undefined,
                            doctor_id: doctorId,
                            service_ids: serviceIds
                        }
                        if (priorityLevel !== null && !isNaN(priorityLevel)) {
                            body.priority_level = priorityLevel
                        }

                        apiFetch("{{ url('/api/public/guest-walk-in') }}/" + encodeURIComponent(token), {
                            method: 'POST',
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
                                setSubmitting(false)
                                if (!result.ok) {
                                    var message = 'Failed to create guest walk-in.'
                                    if (result.data && result.data.message) message = result.data.message
                                    showError(message)
                                    return
                                }

                                showSuccess('Walk-in successfuly created and currently on the queue.')
                                showCreds('')

                                if (firstNameInput) firstNameInput.value = ''
                                if (middleNameInput) middleNameInput.value = ''
                                if (lastNameInput) lastNameInput.value = ''
                                if (contactInput) contactInput.value = ''
                                if (reasonInput) reasonInput.value = ''
                                if (serviceSearch) serviceSearch.value = ''
                                if (doctorSearch) doctorSearch.value = ''
                                if (pwdCb) pwdCb.checked = false
                                if (pregCb) pregCb.checked = false
                                if (seniorCb) seniorCb.checked = false
                                syncPriorityHiddenInput()

                                selectedServices = []
                                syncServiceHiddenInput()
                                renderSelectedServices()
                                syncDoctorEnabled()
                                setDoctorSelection(null)
                            })
                            .catch(function () {
                                setSubmitting(false)
                                showError('Network error while creating guest walk-in.')
                            })
                    })
                })
            }
        })
    </script>
@endsection
