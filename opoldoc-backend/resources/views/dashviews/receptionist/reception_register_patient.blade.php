<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">Register patient</h2>
            <p class="text-xs text-slate-500">Create a patient account and capture basic details for front desk use.</p>
        </div>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Patients</span>
    </div>

    <div id="receptionRegisterPatientError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
    <div id="receptionRegisterPatientSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>
    <pre id="receptionRegisterPatientCredentials" class="hidden mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.7rem] text-slate-700 overflow-x-auto"></pre>

    <form id="receptionRegisterPatientForm" class="grid gap-3 grid-cols-1 md:grid-cols-3 items-end mb-4">
        <div class="md:col-span-3">
            <label class="inline-flex items-center gap-2 text-[0.75rem] text-slate-700 font-semibold">
                <input id="reception_patient_is_dependent" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                Dependent account
            </label>
            <div class="text-[0.7rem] text-slate-400 mt-1">
                Enable to link this patient as a dependent under an existing parent patient.
            </div>
        </div>

        <div id="receptionDependentParentSection" class="hidden md:col-span-3">
            <label for="reception_parent_search" class="block text-[0.7rem] text-slate-600 mb-1">Parent (search by name, email, ID)</label>
            <div class="relative">
                <input id="reception_parent_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type to search parent">
                <input id="reception_parent_user_id" type="hidden">
                <div id="receptionParentResults" class="hidden absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden"></div>
            </div>
            <div id="receptionParentPreview" class="hidden mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.78rem] text-slate-700"></div>
            <div id="receptionDependentRelationshipSection" class="hidden mt-3">
                <label for="reception_dependent_relationship" class="block text-[0.7rem] text-slate-600 mb-1">Relationship</label>
                <select id="reception_dependent_relationship" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="">Select</option>
                    <option value="mother">Mother</option>
                    <option value="father">Father</option>
                    <option value="guardian">Guardian</option>
                </select>
            </div>
        </div>

        <div>
            <label for="reception_patient_firstname" class="block text-[0.7rem] text-slate-600 mb-1">Firstname</label>
            <input id="reception_patient_firstname" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Firstname">
        </div>
        <div>
            <label for="reception_patient_middlename" class="block text-[0.7rem] text-slate-600 mb-1">Middlename (optional)</label>
            <input id="reception_patient_middlename" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Middlename">
        </div>
        <div>
            <label for="reception_patient_lastname" class="block text-[0.7rem] text-slate-600 mb-1">Lastname</label>
            <input id="reception_patient_lastname" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Lastname">
        </div>
        <div>
            <label for="reception_patient_birthdate" class="block text-[0.7rem] text-slate-600 mb-1">Birthdate</label>
            <input id="reception_patient_birthdate" type="date" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
        </div>
        <div>
            <label for="reception_patient_sex" class="block text-[0.7rem] text-slate-600 mb-1">Sex</label>
            <select id="reception_patient_sex" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="">Select</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>
        <div>
            <label id="reception_patient_contact_label" for="reception_patient_contact" class="block text-[0.7rem] text-slate-600 mb-1">Contact number</label>
            <input id="reception_patient_contact" type="text" inputmode="tel" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="+63 9xx xxx xxxx">
        </div>
        <div class="md:col-span-3">
            <label for="reception_patient_address" class="block text-[0.7rem] text-slate-600 mb-1">Address</label>
            <input id="reception_patient_address" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Complete address">
        </div>
        <div class="md:col-span-2">
            <label id="reception_patient_email_label" for="reception_patient_email" class="block text-[0.7rem] text-slate-600 mb-1">Email</label>
            <input id="reception_patient_email" type="email" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Email address">
        </div>
        <div>
            <button id="receptionRegisterPatientSubmit" type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors">
                Register
            </button>
        </div>
    </form>

    <p id="receptionRegisterPatientHint" class="text-[0.7rem] text-slate-400">
        Email is required for patient accounts. Dependent accounts may be registered without an email.
    </p>
</div>




<div id="receptionRegisterPatientConfirmOverlay" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4 transition-all duration-200">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden">
        <!-- Header area with refined spacing and visual hierarchy -->
        <div class="px-5 pt-5 pb-3 border-b border-slate-100 bg-gradient-to-r from-white to-slate-50/50">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 flex-shrink-0">
                    <!-- Lucide info icon from composer/blade -->
                    <x-lucide-info class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <h3 id="receptionRegisterPatientConfirmMessage" class="text-base font-semibold text-slate-800 tracking-tight">Confirm patient registration</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Please review the information below before confirming</p>
                </div>
            </div>
        </div>
        
        <!-- Body with clear, scannable details section - improved for reading -->
        <div class="px-5 py-4 bg-white">
            <!-- dynamic details with card-like presentation for better readability -->
            <div id="receptionRegisterPatientConfirmDetails" class="bg-slate-50/80 rounded-xl border border-slate-100 p-4 text-sm text-slate-700 leading-relaxed space-y-2">
                <!-- Example structured content - will be dynamically replaced -->
                <div class="flex items-start gap-2 text-slate-600">
                    <x-lucide-user class="w-3.5 h-3.5 mt-0.5 text-slate-400 flex-shrink-0" />
                    <span class="font-medium">Name:</span>
                    <span class="text-slate-700">Sarah Johnson</span>
                </div>
                <div class="flex items-start gap-2 text-slate-600">
                    <x-lucide-calendar class="w-3.5 h-3.5 mt-0.5 text-slate-400 flex-shrink-0" />
                    <span class="font-medium">Date of birth:</span>
                    <span class="text-slate-700">May 15, 1984</span>
                </div>
                <div class="flex items-start gap-2 text-slate-600">
                    <x-lucide-phone class="w-3.5 h-3.5 mt-0.5 text-slate-400 flex-shrink-0" />
                    <span class="font-medium">Contact:</span>
                    <span class="text-slate-700">+1 (555) 123-4567</span>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-200 text-xs text-amber-600 bg-amber-50/50 -mx-2 px-2 py-1.5 rounded-md flex items-center gap-2">
                    <x-lucide-alert-circle class="w-3.5 h-3.5" />
                    <span>This action will register the patient in the system</span>
                </div>
            </div>
        </div>
        
        <!-- Footer with improved button hierarchy and spacing -->
        <div class="px-5 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-2.5">
            <button type="button" id="receptionRegisterPatientConfirmCancel" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-slate-200 focus:ring-offset-1">
                Cancel
            </button>
            <button type="button" id="receptionRegisterPatientConfirmOk" class="px-5 py-2 rounded-lg bg-cyan-600 text-white text-sm font-semibold hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 shadow-sm transition-all duration-150">
                Confirm registration
            </button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('receptionRegisterPatientForm')
        var errorBox = document.getElementById('receptionRegisterPatientError')
        var successBox = document.getElementById('receptionRegisterPatientSuccess')
        var credentialsBox = document.getElementById('receptionRegisterPatientCredentials')
        var dependentToggle = document.getElementById('reception_patient_is_dependent')
        var parentSection = document.getElementById('receptionDependentParentSection')
        var parentSearchInput = document.getElementById('reception_parent_search')
        var parentUserIdInput = document.getElementById('reception_parent_user_id')
        var parentResults = document.getElementById('receptionParentResults')
        var parentPreview = document.getElementById('receptionParentPreview')
        var relationshipSection = document.getElementById('receptionDependentRelationshipSection')
        var relationshipSelect = document.getElementById('reception_dependent_relationship')
        var submitButton = document.getElementById('receptionRegisterPatientSubmit')
        var emailLabel = document.getElementById('reception_patient_email_label')
        var contactLabel = document.getElementById('reception_patient_contact_label')
        var hint = document.getElementById('receptionRegisterPatientHint')
        var parentSearchTimer = null
        var selectedParent = null
        var successTimer = null

        var confirmOverlay = document.getElementById('receptionRegisterPatientConfirmOverlay')
        var confirmMessage = document.getElementById('receptionRegisterPatientConfirmMessage')
        var confirmDetails = document.getElementById('receptionRegisterPatientConfirmDetails')
        var confirmOk = document.getElementById('receptionRegisterPatientConfirmOk')
        var confirmCancel = document.getElementById('receptionRegisterPatientConfirmCancel')
        var confirmResolver = null

        function escapeHtml(input) {
            var s = String(input == null ? '' : input)
            return s
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
        }

        function isValidPersonName(value) {
            var v = String(value || '').trim()
            if (v === '') {
                return true
            }
            try {
                return /^[\p{L}\p{M}][\p{L}\p{M}\s.'\-\u00B7]*$/u.test(v)
            } catch (_) {
                return /^[A-Za-z][A-Za-z\s.'-]*$/.test(v)
            }
        }

        function normalizePersonName(value) {
            var s = String(value || '').trim()
            if (!s) return ''
            s = s.replace(/\s+/g, ' ')
            s = s.replace(/\s*([.'\-\u00B7])\s*/g, '$1')
            return s
        }

        function showRegisterPatientError(message) {
            if (!errorBox) return
            errorBox.textContent = message || ''
            if (message) {
                errorBox.classList.remove('hidden')
            } else {
                errorBox.classList.add('hidden')
            }
        }

        function showRegisterPatientSuccess(message) {
            if (!successBox) return
            successBox.textContent = message || ''
            if (message) {
                successBox.classList.remove('hidden')
                if (successTimer) clearTimeout(successTimer)
                successTimer = setTimeout(function () {
                    showRegisterPatientSuccess('')
                }, 3500)
            } else {
                successBox.classList.add('hidden')
            }
        }

        function showCredentials(payload) {
            if (!credentialsBox) return
            if (!payload) {
                credentialsBox.textContent = ''
                credentialsBox.classList.add('hidden')
                return
            }
            try {
                credentialsBox.textContent = JSON.stringify(payload, null, 2)
            } catch (_) {
                credentialsBox.textContent = String(payload)
            }
            credentialsBox.classList.remove('hidden')
        }

        function confirmAction(message, detailsHtml) {
            return new Promise(function (resolve) {
                if (!confirmOverlay || !confirmMessage || !confirmOk || !confirmCancel) {
                    resolve(window.confirm(message || 'Are you sure?'))
                    return
                }
                confirmMessage.textContent = message || 'Are you sure?'
                if (confirmDetails) {
                    confirmDetails.innerHTML = detailsHtml || ''
                }
                confirmResolver = resolve
                confirmOverlay.classList.remove('hidden')
                confirmOverlay.classList.add('flex')
            })
        }

        function closeConfirm(result) {
            if (confirmOverlay) {
                confirmOverlay.classList.add('hidden')
                confirmOverlay.classList.remove('flex')
            }
            var resolver = confirmResolver
            confirmResolver = null
            if (typeof resolver === 'function') {
                resolver(!!result)
            }
        }

        if (confirmOk) confirmOk.addEventListener('click', function () { closeConfirm(true) })
        if (confirmCancel) confirmCancel.addEventListener('click', function () { closeConfirm(false) })
        if (confirmOverlay) {
            confirmOverlay.addEventListener('click', function (e) {
                if (e.target === confirmOverlay) closeConfirm(false)
            })
        }

        function normalizePHContact(value) {
            var raw = String(value || '').trim()
            if (!raw) return ''
            var compact = raw.replace(/[^\d+]/g, '')
            if (compact === '+63') return ''

            var digits = compact.replace(/[^\d]/g, '')
            if (!digits) return ''

            if (digits.length === 11 && digits.indexOf('09') === 0) {
                return '+63' + digits.slice(1)
            }
            if (digits.length === 10 && digits.indexOf('9') === 0) {
                return '+63' + digits
            }
            if (digits.length === 12 && digits.indexOf('639') === 0) {
                return '+' + digits
            }
            if (compact.indexOf('+') === 0 && digits.length === 12 && digits.indexOf('639') === 0) {
                return '+' + digits
            }

            return ''
        }

        function isValidPHContact(value) {
            return /^\+639\d{9}$/.test(String(value || ''))
        }

        function setSubmitting(isSubmitting) {
            if (!submitButton) return
            submitButton.disabled = !!isSubmitting
            submitButton.textContent = isSubmitting ? 'Registering…' : (dependentToggle && dependentToggle.checked ? 'Register dependent' : 'Register patient')
        }

        function setParentSelection(parent) {
            selectedParent = parent || null
            if (parentUserIdInput) parentUserIdInput.value = parent && parent.user_id ? String(parent.user_id) : ''

            if (parentPreview) {
                if (!parent) {
                    parentPreview.textContent = ''
                    parentPreview.classList.add('hidden')
                } else {
                    var parts = []
                    var name = [parent.firstname, parent.middlename, parent.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
                    if (!name) name = 'User #' + parent.user_id
                    parts.push('Name: ' + name)
                    if (parent.email) parts.push('Email: ' + parent.email)
                    if (parent.contact_number) parts.push('Contact: ' + parent.contact_number)
                    if (parent.address) parts.push('Address: ' + parent.address)
                    parentPreview.textContent = parts.join(' • ')
                    parentPreview.classList.remove('hidden')
                }
            }

            if (parentResults) {
                parentResults.innerHTML = ''
                parentResults.classList.add('hidden')
            }

            if (relationshipSection) {
                relationshipSection.classList.toggle('hidden', !(dependentToggle && dependentToggle.checked && !!parent))
            }
            if (relationshipSelect) {
                relationshipSelect.required = !!(dependentToggle && dependentToggle.checked && !!parent)
                if (!parent) relationshipSelect.value = ''
            }

            var addressInput = document.getElementById('reception_patient_address')
            if (addressInput && dependentToggle && dependentToggle.checked && parent && (!String(addressInput.value || '').trim())) {
                if (parent.address) {
                    addressInput.value = String(parent.address)
                }
            }
        }

        function setDependentMode(on) {
            var enabled = !!on
            if (parentSection) parentSection.classList.toggle('hidden', !enabled)
            if (emailLabel) emailLabel.textContent = enabled ? 'Email (optional)' : 'Email'
            if (contactLabel) contactLabel.textContent = enabled ? 'Contact number (optional)' : 'Contact number'
            var emailInput = document.getElementById('reception_patient_email')
            if (emailInput) emailInput.required = !enabled
            var addressLabel = document.querySelector('label[for="reception_patient_address"]')
            if (addressLabel) addressLabel.textContent = enabled ? 'Address (optional)' : 'Address'
            if (hint) {
                hint.textContent = enabled
                    ? 'Email is optional for dependent accounts. If omitted, activation may require adding an email later.'
                    : 'Email is required for patient accounts.'
            }
            if (submitButton) submitButton.textContent = enabled ? 'Register dependent' : 'Register patient'
            if (!enabled) {
                if (parentSearchInput) parentSearchInput.value = ''
                setParentSelection(null)
                if (relationshipSection) relationshipSection.classList.add('hidden')
                if (relationshipSelect) {
                    relationshipSelect.required = false
                    relationshipSelect.value = ''
                }
            }
        }

        if (dependentToggle) {
            dependentToggle.addEventListener('change', function () {
                setDependentMode(!!dependentToggle.checked)
            })
            setDependentMode(!!dependentToggle.checked)
        }

        function renderParentResults(items) {
            if (!parentResults) return
            var list = Array.isArray(items) ? items : []
            if (!list.length) {
                parentResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No parents found.</div>'
                parentResults.classList.remove('hidden')
                return
            }

            var html = ''
            list.forEach(function (p) {
                var name = [p.firstname, p.middlename, p.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
                if (!name) name = 'User #' + p.user_id
                var meta = [p.email, p.contact_number].filter(Boolean).join(' • ')
                html += '<button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 border-b border-slate-100 last:border-0">' +
                    '<div class="text-[0.78rem] text-slate-800 font-semibold">' + escapeHtml(name) + '</div>' +
                    '<div class="text-[0.72rem] text-slate-500">#' + escapeHtml(p.user_id) + (meta ? ' • ' + escapeHtml(meta) : '') + '</div>' +
                '</button>'
            })
            parentResults.innerHTML = html
            parentResults.classList.remove('hidden')

            var buttons = parentResults.querySelectorAll('button')
            Array.prototype.forEach.call(buttons, function (btn, idx) {
                btn.addEventListener('click', function () {
                    setParentSelection(list[idx])
                    if (parentSearchInput) {
                        var chosenName = [list[idx].firstname, list[idx].middlename, list[idx].lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
                        if (!chosenName) chosenName = 'User #' + list[idx].user_id
                        parentSearchInput.value = chosenName
                    }
                })
            })
        }

        function searchParents(query) {
            if (typeof apiFetch !== 'function') return
            apiFetch("{{ url('/api/patients') }}?parents_only=1&per_page=8&search=" + encodeURIComponent(query), { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    }).catch(function () {
                        return { ok: response.ok, data: null }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        renderParentResults([])
                        return
                    }
                    var list = []
                    if (result.data && Array.isArray(result.data.data)) {
                        list = result.data.data
                    } else if (Array.isArray(result.data)) {
                        list = result.data
                    }
                    renderParentResults(list)
                })
                .catch(function () {
                    renderParentResults([])
                })
        }

        if (parentSearchInput) {
            parentSearchInput.addEventListener('input', function () {
                var q = String(parentSearchInput.value || '').trim()
                if (parentSearchTimer) clearTimeout(parentSearchTimer)
                if (q.length < 2) {
                    if (parentResults) parentResults.classList.add('hidden')
                    return
                }
                parentSearchTimer = setTimeout(function () {
                    searchParents(q)
                }, 250)
            })
        }

        document.addEventListener('click', function (e) {
            if (!parentResults || parentResults.classList.contains('hidden')) return
            var target = e.target
            if (parentResults.contains(target)) return
            if (parentSearchInput && parentSearchInput.contains(target)) return
            parentResults.classList.add('hidden')
        })

        var contactInput = document.getElementById('reception_patient_contact')
        if (contactInput) {
            if (!String(contactInput.value || '').trim()) {
                contactInput.value = '+63'
            }
            contactInput.addEventListener('focus', function () {
                var v = String(contactInput.value || '').trim()
                if (!v) contactInput.value = '+63'
            })
            contactInput.addEventListener('blur', function () {
                var normalized = normalizePHContact(contactInput.value)
                if (normalized) contactInput.value = normalized
                if (!normalized && String(contactInput.value || '').trim() === '+63') contactInput.value = '+63'
            })
        }

        function fetchPossibleDuplicates(payload) {
            if (typeof apiFetch !== 'function') return Promise.resolve([])
            var parts = [payload.firstname, payload.middlename, payload.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
            if (!parts) return Promise.resolve([])

            return apiFetch("{{ url('/api/patients') }}?per_page=10&search=" + encodeURIComponent(parts), { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    }).catch(function () {
                        return { ok: response.ok, data: null }
                    })
                })
                .then(function (result) {
                    if (!result.ok || !result.data) return []
                    var list = Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])

                    function normName(v) {
                        return String(v || '').trim().toLowerCase()
                    }

                    var first = normName(payload.firstname)
                    var middle = normName(payload.middlename)
                    var last = normName(payload.lastname)
                    var birth = String(payload.birthdate || '').trim()
                    var email = normName(payload.email || '')
                    var contact = String(payload.contact_number || '').trim()

                    return list.filter(function (p) {
                        if (!p) return false
                        var pf = normName(p.firstname)
                        var pm = normName(p.middlename)
                        var pl = normName(p.lastname)
                        if (!pf || !pl) return false

                        var sameName = pf === first && pl === last && (middle ? pm === middle : true)
                        if (!sameName) return false

                        var matches = 0
                        if (birth && String(p.birthdate || '').trim() === birth) matches += 1
                        if (email && normName(p.email || '') === email) matches += 1
                        if (contact && String(p.contact_number || '').trim() === contact) matches += 1

                        return matches > 0
                    }).slice(0, 5)
                })
                .catch(function () {
                    return []
                })
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault()

                showRegisterPatientError('')
                showRegisterPatientSuccess('')
                showCredentials(null)

                var firstnameInput = document.getElementById('reception_patient_firstname')
                var middlenameInput = document.getElementById('reception_patient_middlename')
                var lastnameInput = document.getElementById('reception_patient_lastname')
                var birthdateInput = document.getElementById('reception_patient_birthdate')
                var sexInput = document.getElementById('reception_patient_sex')
                var contactInput2 = document.getElementById('reception_patient_contact')
                var addressInput = document.getElementById('reception_patient_address')
                var emailInput = document.getElementById('reception_patient_email')
                var isDependent = dependentToggle ? !!dependentToggle.checked : false
                var parentId = parentUserIdInput ? parseInt(parentUserIdInput.value || '0', 10) : 0
                var relationship = relationshipSelect ? String(relationshipSelect.value || '') : ''

                var fName = firstnameInput ? normalizePersonName(firstnameInput.value) : ''
                var mName = middlenameInput ? normalizePersonName(middlenameInput.value) : ''
                var lName = lastnameInput ? normalizePersonName(lastnameInput.value) : ''

                if (!isValidPersonName(fName) || (mName !== '' && !isValidPersonName(mName)) || !isValidPersonName(lName)) {
                    showRegisterPatientError('Name fields must contain letters only (accents allowed), plus hyphens, apostrophes, periods, and middle dots.')
                    return
                }

                if (!fName || !lName) {
                    showRegisterPatientError('Firstname and lastname are required.')
                    return
                }

                if (firstnameInput) firstnameInput.value = fName
                if (middlenameInput) middlenameInput.value = mName
                if (lastnameInput) lastnameInput.value = lName

                if (typeof apiFetch !== 'function') {
                    showRegisterPatientError('API client is not available.')
                    return
                }

                var body = {
                    firstname: fName,
                    middlename: mName,
                    lastname: lName,
                    birthdate: birthdateInput ? birthdateInput.value : '',
                    sex: sexInput ? sexInput.value : '',
                    contact_number: '',
                    address: addressInput ? addressInput.value.trim() : ''
                }

                if (!body.birthdate) {
                    showRegisterPatientError('Birthdate is required.')
                    return
                }

                var email = emailInput ? emailInput.value.trim() : ''
                if (!isDependent) {
                    if (!email) {
                        showRegisterPatientError('Email is required for patient accounts.')
                        return
                    }
                    body.email = email
                } else if (email) {
                    body.email = email
                }

                var rawContact = contactInput2 ? contactInput2.value : ''
                var normalizedContact = normalizePHContact(rawContact)
                if (isDependent) {
                    if (normalizedContact) {
                        if (!isValidPHContact(normalizedContact)) {
                            showRegisterPatientError('Please enter a valid PH contact number (e.g. +639750443410).')
                            return
                        }
                        body.contact_number = normalizedContact
                        if (contactInput2) contactInput2.value = normalizedContact
                    } else {
                        delete body.contact_number
                        if (contactInput2) contactInput2.value = '+63'
                    }
                } else {
                    if (!normalizedContact || !isValidPHContact(normalizedContact)) {
                        showRegisterPatientError('Please enter a valid PH contact number (e.g. +639750443410).')
                        return
                    }
                    body.contact_number = normalizedContact
                    if (contactInput2) contactInput2.value = normalizedContact
                }

                var url = isDependent ? "{{ url('/api/dependents') }}" : "{{ url('/api/patients') }}"
                if (isDependent) {
                    if (!parentId) {
                        showRegisterPatientError('Please select the parent patient first.')
                        return
                    }
                    body.parent_user_id = parentId
                    if (!relationship) {
                        showRegisterPatientError('Please select the relationship.')
                        return
                    }
                    body.relationship = relationship
                    if ((!body.address || !String(body.address).trim()) && selectedParent && selectedParent.address) {
                        body.address = String(selectedParent.address)
                    }
                }

                function maskHalf(value) {
                    var s = String(value == null ? '' : value)
                    if (!s) return '—'
                    var visible = Math.ceil(s.length / 2)
                    return s.slice(0, visible) + new Array(Math.max(0, s.length - visible) + 1).join('*')
                }

                function buildConfirmDetails(dupes) {
                    var name = [body.firstname, body.middlename, body.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
                    var details = '<div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">' +
                        '<div class="text-[0.7rem] text-slate-500">Name</div>' +
                        '<div class="text-[0.8rem] font-semibold text-slate-800">' + escapeHtml(name || '—') + '</div>' +
                        '<div class="mt-2 grid grid-cols-2 gap-2">' +
                            '<div>' +
                                '<div class="text-[0.7rem] text-slate-500">Birthdate</div>' +
                                '<div class="text-[0.78rem] text-slate-700">' + escapeHtml(body.birthdate || '—') + '</div>' +
                            '</div>' +
                            '<div>' +
                                '<div class="text-[0.7rem] text-slate-500">Sex</div>' +
                                '<div class="text-[0.78rem] text-slate-700">' + escapeHtml(body.sex || '—') + '</div>' +
                            '</div>' +
                            '<div>' +
                                '<div class="text-[0.7rem] text-slate-500">Contact</div>' +
                                '<div class="text-[0.78rem] text-slate-700">' + escapeHtml(body.contact_number || '—') + '</div>' +
                            '</div>' +
                            '<div>' +
                                '<div class="text-[0.7rem] text-slate-500">Email</div>' +
                                '<div class="text-[0.78rem] text-slate-700">' + escapeHtml(body.email || '—') + '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="mt-2">' +
                            '<div class="text-[0.7rem] text-slate-500">Address</div>' +
                            '<div class="text-[0.78rem] text-slate-700">' + escapeHtml(body.address || '—') + '</div>' +
                        '</div>' +
                        (isDependent ? (
                            '<div class="mt-2 grid grid-cols-2 gap-2">' +
                                '<div>' +
                                    '<div class="text-[0.7rem] text-slate-500">Parent</div>' +
                                    '<div class="text-[0.78rem] text-slate-700">' + escapeHtml((selectedParent && selectedParent.firstname ? [selectedParent.firstname, selectedParent.middlename, selectedParent.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim() : '') || ('#' + String(parentId || ''))) + '</div>' +
                                '</div>' +
                                '<div>' +
                                    '<div class="text-[0.7rem] text-slate-500">Relationship</div>' +
                                    '<div class="text-[0.78rem] text-slate-700">' + escapeHtml(relationship || '—') + '</div>' +
                                '</div>' +
                            '</div>'
                        ) : '') +
                    '</div>'

                    if (!dupes || !dupes.length) return details

                    var list = dupes.map(function (p) {
                        var nm = [p.firstname, p.middlename, p.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
                        var meta = []
                        if (p.birthdate) meta.push('Birthdate: ' + escapeHtml(p.birthdate))
                        if (p.contact_number) meta.push('Contact: ' + escapeHtml(p.contact_number))
                        if (p.email) meta.push('Email: ' + escapeHtml(p.email))
                        return '<div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2">' +
                            '<div class="text-[0.78rem] font-semibold text-slate-900">' + escapeHtml(nm || ('Patient #' + p.user_id)) + '</div>' +
                            '<div class="text-[0.72rem] text-slate-600 mt-0.5">#' + escapeHtml(p.user_id) + (meta.length ? ' • ' + meta.join(' • ') : '') + '</div>' +
                        '</div>'
                    }).join('')

                    return details + '<div class="mt-3">' +
                        '<div class="text-[0.72rem] font-semibold text-slate-700 mb-1">Similar patients found</div>' +
                        '<div class="space-y-2">' + list + '</div>' +
                    '</div>'
                }

                function buildStrongMatchDetails(matches) {
                    var p = matches && matches.length ? matches[0] : null
                    if (!p) return ''
                    var nm = [p.firstname, p.middlename, p.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
                    return '<div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2">' +
                        '<div class="text-[0.7rem] text-amber-700 font-semibold mb-1">Existing patient (masked)</div>' +
                        '<div class="text-[0.78rem] text-slate-900 font-semibold">' + escapeHtml(maskHalf(nm || ('Patient #' + p.user_id))) + '</div>' +
                        '<div class="mt-2 grid grid-cols-2 gap-2 text-[0.75rem] text-slate-700">' +
                            '<div><span class="text-slate-500">Birthdate:</span> ' + escapeHtml(maskHalf(p.birthdate || '—')) + '</div>' +
                            '<div><span class="text-slate-500">Sex:</span> ' + escapeHtml(maskHalf(p.sex || '—')) + '</div>' +
                            '<div><span class="text-slate-500">Contact:</span> ' + escapeHtml(maskHalf(p.contact_number || '—')) + '</div>' +
                            '<div><span class="text-slate-500">Address:</span> ' + escapeHtml(maskHalf(p.address || '—')) + '</div>' +
                        '</div>' +
                    '</div>'
                }

                setSubmitting(true)

                fetchPossibleDuplicates(body)
                    .then(function (dupes) {
                        function normalizeText(v) {
                            return String(v || '').trim().toLowerCase().replace(/\s+/g, ' ')
                        }
                        var strongMatches = (dupes || []).filter(function (p) {
                            if (!p) return false
                            return normalizeText(p.firstname) === normalizeText(body.firstname) &&
                                normalizeText(p.middlename) === normalizeText(body.middlename) &&
                                normalizeText(p.lastname) === normalizeText(body.lastname) &&
                                String(p.birthdate || '').trim() === String(body.birthdate || '').trim() &&
                                normalizeText(p.sex) === normalizeText(body.sex) &&
                                String(p.contact_number || '').trim() === String(body.contact_number || '').trim() &&
                                normalizeText(p.address) === normalizeText(body.address)
                        })

                        if (strongMatches.length) {
                            return confirmAction(
                                'There’s a patient with similar info. Do you still want to register this patient?',
                                buildStrongMatchDetails(strongMatches)
                            )
                        }

                        return confirmAction('Register this patient?', buildConfirmDetails(dupes))
                    })
                    .then(function (confirmed) {
                        if (!confirmed) {
                            setSubmitting(false)
                            return
                        }

                        return apiFetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
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
                                    var message2 = 'Failed to register patient.'
                                    if (result.data && result.data.message) {
                                        message2 = result.data.message
                                    }
                                    showRegisterPatientError(message2)
                                    return
                                }

                                var payload = result.data || null
                                var credentials = payload && payload.credentials ? payload.credentials : null
                                var activation = payload && payload.activation ? payload.activation : null

                                if (isDependent) {
                                    if (activation && activation.requires_email) {
                                        showRegisterPatientSuccess('Dependent registered. ' + (activation.prompt || 'Add email to activate account.'))
                                    } else {
                                        showRegisterPatientSuccess('Dependent has been registered successfully.')
                                    }
                                    showCredentials(null)
                                } else {
                                    showRegisterPatientSuccess('Patient has been registered successfully. Credentials were sent to the email address.')
                                    showCredentials(null)
                                }

                                if (firstnameInput) firstnameInput.value = ''
                                if (middlenameInput) middlenameInput.value = ''
                                if (lastnameInput) lastnameInput.value = ''
                                if (birthdateInput) birthdateInput.value = ''
                                if (sexInput) sexInput.value = ''
                                if (contactInput2) contactInput2.value = '+63'
                                if (addressInput) addressInput.value = ''
                                if (emailInput) emailInput.value = ''
                                if (dependentToggle) dependentToggle.checked = false
                                setDependentMode(false)
                            })
                            .catch(function () {
                                showRegisterPatientError('Network error while registering patient.')
                            })
                            .finally(function () {
                                setSubmitting(false)
                            })
                    })
                    .catch(function () {
                        setSubmitting(false)
                        showRegisterPatientError('Unable to validate registration right now.')
                    })
            })
        }
    })
</script>