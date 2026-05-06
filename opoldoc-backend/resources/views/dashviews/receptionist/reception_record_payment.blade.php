<div class="bg-white border border-slate-200 rounded-[18px] shadow-[0_2px_10px_rgba(15,23,42,0.04)] overflow-hidden">
    <div class="grid grid-cols-2 border-b border-slate-200">
        <button id="receptionBillingTabRecord" type="button" class="px-4 py-3 text-xs font-semibold text-white bg-cyan-500 border-b-2 border-cyan-600">
            Record payment
        </button>
        <button id="receptionBillingTabTransactions" type="button" class="px-4 py-3 text-xs font-semibold text-slate-900 bg-white hover:bg-slate-50 border-l border-slate-200">
            Transactions record
        </button>
    </div>

    <div id="receptionBillingPanelRecord" class="p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Record payment</h2>
                <p class="text-xs text-slate-500">Select an appointment, review totals, and confirm payment.</p>
            </div>
            <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Billing</span>
        </div>

        <div id="receptionPaymentError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
        <div id="receptionPaymentSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>

        <form id="receptionPaymentForm" class="grid gap-3 grid-cols-1 md:grid-cols-4 items-end mb-4">
            <div class="md:col-span-2 min-w-0">
                <label for="reception_payment_appointment_search" class="block text-[0.7rem] text-slate-600 mb-1">Appointment</label>
                <div class="relative">
                    <input id="reception_payment_appointment_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type patient full name or appointment ID">
                    <input id="reception_payment_appointment_id" type="hidden" required>
                    <div id="receptionPaymentAppointmentResults" class="hidden absolute left-0 right-0 top-full mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain z-50"></div>
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-[0.7rem] text-slate-600 mb-1">Reference number</label>
                <div id="receptionPaymentReferenceDisplay" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">Auto-generated on save</div>
            </div>

            <div id="receptionPaymentAppointmentPreview" class="hidden md:col-span-4 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.78rem] text-slate-700"></div>

            <div class="md:col-span-2">
                <label class="block text-[0.7rem] text-slate-600 mb-1">Services in appointment</label>
                <div id="receptionPaymentServicesDisplay" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 min-h-[2.5rem]">Select an appointment first</div>
            </div>
            <div>
                <label class="block text-[0.7rem] text-slate-600 mb-1">Original amount</label>
                <div id="receptionPaymentAmountDisplay" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800">PHP 0.00</div>
            </div>
            <div>
                <label class="block text-[0.7rem] text-slate-600 mb-1">Net amount</label>
                <div id="receptionPaymentNetAmountDisplay" class="w-full rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800">PHP 0.00</div>
            </div>

            <div class="md:col-span-4">
                <button id="receptionPaymentToggleDiscount" type="button" class="inline-flex items-center text-[0.75rem] font-semibold text-cyan-700 hover:text-cyan-800">Add discount</button>
            </div>

            <div id="receptionPaymentDiscountWrap" class="hidden md:col-span-4 grid gap-3 grid-cols-1 md:grid-cols-2">
                <div>
                    <label for="reception_payment_discount_type" class="block text-[0.7rem] text-slate-600 mb-1">Discount type</label>
                    <select id="reception_payment_discount_type" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                        <option value="none" selected>No discount</option>
                        <option value="pwd">PWD (15%)</option>
                        <option value="pregnant">Pregnant (10%)</option>
                        <option value="senior">Senior (5%)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[0.7rem] text-slate-600 mb-1">Discount amount</label>
                    <div id="receptionPaymentDiscountAmountDisplay" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800">PHP 0.00</div>
                </div>
            </div>

            <div>
                <label class="block text-[0.7rem] text-slate-600 mb-1">Payment mode</label>
                <div class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">Cash</div>
            </div>
            <div>
                <label class="block text-[0.7rem] text-slate-600 mb-1">Payment status</label>
                <div class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">Paid (auto)</div>
            </div>

            <div class="md:col-span-4 flex justify-end">
                <button id="receptionPaymentSubmit" type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors disabled:opacity-60 disabled:hover:bg-cyan-600">
                    <span id="receptionPaymentSubmitSpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                    <span id="receptionPaymentSubmitLabel">Record payment</span>
                </button>
            </div>
        </form>
    </div>

    <div id="receptionBillingPanelTransactions" class="hidden p-5">
        <div class="flex items-center justify-between mb-3 gap-3">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Transactions record</h3>
                <p class="text-xs text-slate-500">Search and review billing transactions.</p>
            </div>
            <button id="receptionTransactionsTodayOnlyBtn" type="button" class="shrink-0 inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.75rem] font-semibold text-slate-700 hover:bg-slate-50">Show today only</button>
        </div>

        <div id="receptionTransactionsError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

        <div class="grid gap-3 grid-cols-1 md:grid-cols-5 items-start mb-4">
            <div class="md:col-span-2 min-w-0">
                <label for="receptionTransactionsSearch" class="block text-[0.7rem] text-slate-600 mb-1">Search</label>
                <input id="receptionTransactionsSearch" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Search by patient name or ref #">
            </div>
            <div class="min-w-0">
                <label for="receptionTransactionsServiceSearch" class="block text-[0.7rem] text-slate-600 mb-1">Service</label>
                <div class="relative">
                    <input id="receptionTransactionsServiceSearch" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="All services" autocomplete="off">
                    <input id="receptionTransactionsServiceId" type="hidden">
                    <div id="receptionTransactionsServiceResults" class="hidden absolute left-0 right-0 top-full mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain z-50"></div>
                </div>
            </div>
            <div class="min-w-0">
                <label for="receptionTransactionsSort" class="block text-[0.7rem] text-slate-600 mb-1">Sort by date</label>
                <select id="receptionTransactionsSort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="latest">Latest first</option>
                    <option value="oldest">Oldest first</option>
                </select>
            </div>
            <div class="min-w-0">
                <label class="block text-[0.7rem] text-slate-600 mb-1">&nbsp;</label>
                <button id="receptionTransactionsRefresh" type="button" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50">Refresh</button>
            </div>
        </div>

        <div class="w-full" style="display:grid;">
            <div class="rounded-2xl border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto overflow-y-auto max-h-[28rem] show-scrollbar">
                    <table class="text-xs" style="min-width:980px;width:100%;table-layout:auto;">
                        <thead class="bg-slate-50 text-slate-600 sticky top-0">
                            <tr>
                                <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Date</th>
                                <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Reference</th>
                                <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Patient</th>
                                <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Service</th>
                                <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Type</th>
                                <th class="text-right px-3 py-2 font-semibold whitespace-nowrap">Gross</th>
                                <th class="text-right px-3 py-2 font-semibold whitespace-nowrap">Discount</th>
                                <th class="text-right px-3 py-2 font-semibold whitespace-nowrap">Net</th>
                                <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Mode</th>
                            </tr>
                        </thead>
                        <tbody id="receptionTransactionsTableBody" class="divide-y divide-slate-100 bg-white"></tbody>
                    </table>
                </div>
                <div id="receptionTransactionsTableFooter" class="px-3 py-2 text-[0.72rem] text-slate-500 bg-white border-t border-slate-100 flex items-center justify-between">
                    <div id="receptionTransactionsMeta">Showing transactions.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="receptionPaymentReviewOverlay" class="hidden fixed inset-0 z-[80] bg-slate-900/40 items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-[0_12px_30px_rgba(15,23,42,0.24)] p-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-700">
                <x-lucide-info class="w-[18px] h-[18px]" />
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-slate-900">Review Payment Details</div>
                <div id="receptionPaymentReviewContent" class="mt-1 text-[0.78rem] text-slate-600"></div>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button" id="receptionPaymentReviewCancel" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
            <button type="button" id="receptionPaymentReviewConfirm" class="px-3 py-2 rounded-xl bg-slate-900 text-white text-[0.78rem] font-semibold hover:bg-slate-800">Confirm</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var recordTab = document.getElementById('receptionBillingTabRecord')
        var txTab = document.getElementById('receptionBillingTabTransactions')
        var recordPanel = document.getElementById('receptionBillingPanelRecord')
        var txPanel = document.getElementById('receptionBillingPanelTransactions')

        var paymentError = document.getElementById('receptionPaymentError')
        var paymentSuccess = document.getElementById('receptionPaymentSuccess')
        var paymentForm = document.getElementById('receptionPaymentForm')
        var paymentSubmit = document.getElementById('receptionPaymentSubmit')
        var paymentSubmitSpinner = document.getElementById('receptionPaymentSubmitSpinner')
        var paymentSubmitLabel = document.getElementById('receptionPaymentSubmitLabel')

        var appointmentSearch = document.getElementById('reception_payment_appointment_search')
        var appointmentIdInput = document.getElementById('reception_payment_appointment_id')
        var appointmentResults = document.getElementById('receptionPaymentAppointmentResults')
        var appointmentPreview = document.getElementById('receptionPaymentAppointmentPreview')
        var servicesDisplay = document.getElementById('receptionPaymentServicesDisplay')
        var amountDisplay = document.getElementById('receptionPaymentAmountDisplay')
        var discountDisplay = document.getElementById('receptionPaymentDiscountAmountDisplay')
        var netDisplay = document.getElementById('receptionPaymentNetAmountDisplay')
        var referenceDisplay = document.getElementById('receptionPaymentReferenceDisplay')
        var discountToggle = document.getElementById('receptionPaymentToggleDiscount')
        var discountWrap = document.getElementById('receptionPaymentDiscountWrap')
        var discountTypeSelect = document.getElementById('reception_payment_discount_type')

        var reviewOverlay = document.getElementById('receptionPaymentReviewOverlay')
        var reviewContent = document.getElementById('receptionPaymentReviewContent')
        var reviewCancel = document.getElementById('receptionPaymentReviewCancel')
        var reviewConfirm = document.getElementById('receptionPaymentReviewConfirm')
        var reviewConfirmDefaultHtml = reviewConfirm ? reviewConfirm.innerHTML : ''
        var reviewResolver = null
        var reviewDelayTimer = null

        var txError = document.getElementById('receptionTransactionsError')
        var txSearch = document.getElementById('receptionTransactionsSearch')
        var txServiceSearch = document.getElementById('receptionTransactionsServiceSearch')
        var txServiceId = document.getElementById('receptionTransactionsServiceId')
        var txServiceResults = document.getElementById('receptionTransactionsServiceResults')
        var txSort = document.getElementById('receptionTransactionsSort')
        var txRefresh = document.getElementById('receptionTransactionsRefresh')
        var txTableBody = document.getElementById('receptionTransactionsTableBody')
        var txMeta = document.getElementById('receptionTransactionsMeta')
        var txTodayBtn = document.getElementById('receptionTransactionsTodayOnlyBtn')

        var selectedAppointment = null
        var appointmentSearchTimer = null
        var transactionsSearchTimer = null
        var showDiscount = false
        var txTodayOnly = false
        var txServices = []
        var txServicesLoaded = false
        var txServicesLoading = false

        function setBillingTab(tab) {
            var isRecord = tab === 'record'
            if (recordPanel) recordPanel.classList.toggle('hidden', !isRecord)
            if (txPanel) txPanel.classList.toggle('hidden', isRecord)
            if (recordTab) {
                recordTab.classList.toggle('bg-cyan-500', isRecord)
                recordTab.classList.toggle('text-white', isRecord)
                recordTab.classList.toggle('border-b-2', isRecord)
                recordTab.classList.toggle('border-cyan-600', isRecord)
                recordTab.classList.toggle('bg-white', !isRecord)
                recordTab.classList.toggle('text-slate-900', !isRecord)
                recordTab.classList.toggle('hover:bg-slate-50', !isRecord)
            }
            if (txTab) {
                txTab.classList.toggle('bg-cyan-500', !isRecord)
                txTab.classList.toggle('text-white', !isRecord)
                txTab.classList.toggle('border-b-2', !isRecord)
                txTab.classList.toggle('border-cyan-600', !isRecord)
                txTab.classList.toggle('bg-white', isRecord)
                txTab.classList.toggle('text-slate-900', isRecord)
                txTab.classList.toggle('hover:bg-slate-50', isRecord)
            }
        }

        function normalizeText(v) {
            return String(v || '').toLowerCase().trim().replace(/\s+/g, ' ')
        }

        function escapeHtml(input) {
            return String(input == null ? '' : input)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
        }

        function wordPrefixMatch(value, query) {
            var v = normalizeText(value)
            var q = normalizeText(query)
            if (!q) return true
            if (!v) return false
            if (v.indexOf(q) === 0) return true
            return v.split(/\s+/).some(function (part) { return part.indexOf(q) === 0 })
        }

        function showPaymentError(message) {
            if (!paymentError) return
            paymentError.textContent = message || ''
            paymentError.classList.toggle('hidden', !message)
        }

        function showPaymentSuccess(message) {
            if (!paymentSuccess) return
            paymentSuccess.textContent = message || ''
            paymentSuccess.classList.toggle('hidden', !message)
        }

        function showTransactionsError(message) {
            if (!txError) return
            txError.textContent = message || ''
            txError.classList.toggle('hidden', !message)
        }

        function money(value) {
            var n = parseFloat(value || 0)
            if (isNaN(n)) n = 0
            return 'PHP ' + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        }

        function appointmentPatientName(appt) {
            var p = appt && appt.patient ? appt.patient : null
            if (!p) return 'Patient'
            var name = [p.firstname, p.middlename, p.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
            if (!name) name = 'User #' + (p.user_id || '')
            return name
        }

        function appointmentDoctorName(appt) {
            var d = appt && appt.doctor ? appt.doctor : null
            if (!d) return 'Doctor'
            var name = [d.firstname, d.middlename, d.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
            if (!name) name = 'User #' + (d.user_id || '')
            return name
        }

        function servicesFromAppointment(appt) {
            var list = appt && Array.isArray(appt.services) ? appt.services : []
            return list.map(function (s) {
                var name = s && s.service_name ? String(s.service_name).trim() : ''
                var price = s && s.price != null ? parseFloat(s.price) : 0
                if (isNaN(price)) price = 0
                return { name: name, price: price }
            }).filter(function (x) { return x.name !== '' })
        }

        function originalAmount(appt) {
            return servicesFromAppointment(appt).reduce(function (sum, s) { return sum + (parseFloat(s.price) || 0) }, 0)
        }

        function discountRate() {
            var type = discountTypeSelect && discountTypeSelect.value ? String(discountTypeSelect.value) : 'none'
            if (type === 'pwd') return 0.15
            if (type === 'pregnant') return 0.10
            if (type === 'senior') return 0.05
            return 0
        }

        function discountAmount(appt) {
            return originalAmount(appt) * discountRate()
        }

        function refreshTotalsUI() {
            var gross = originalAmount(selectedAppointment)
            var discount = discountAmount(selectedAppointment)
            var net = Math.max(0, gross - discount)
            if (amountDisplay) amountDisplay.textContent = money(gross)
            if (discountDisplay) discountDisplay.textContent = money(discount)
            if (netDisplay) netDisplay.textContent = money(net)
        }

        function resetAppointmentSelection() {
            selectedAppointment = null
            if (appointmentIdInput) appointmentIdInput.value = ''
            if (appointmentPreview) {
                appointmentPreview.textContent = ''
                appointmentPreview.classList.add('hidden')
            }
            if (servicesDisplay) servicesDisplay.textContent = 'Select an appointment first'
            if (referenceDisplay) referenceDisplay.textContent = 'Auto-generated on save'
            refreshTotalsUI()
        }

        function setAppointmentSelection(appt) {
            selectedAppointment = appt || null
            if (appointmentIdInput) {
                appointmentIdInput.value = appt && appt.appointment_id != null ? String(appt.appointment_id) : ''
            }
            if (!appt) {
                resetAppointmentSelection()
                return
            }

            if (appointmentSearch) {
                appointmentSearch.value = '#' + String(appt.appointment_id) + ' - ' + appointmentPatientName(appt)
            }
            if (appointmentResults) {
                appointmentResults.innerHTML = ''
                appointmentResults.classList.add('hidden')
            }

            var serviceRows = servicesFromAppointment(appt)
            if (servicesDisplay) {
                if (!serviceRows.length) {
                    servicesDisplay.textContent = 'No services linked to this appointment.'
                } else {
                    servicesDisplay.innerHTML = serviceRows.map(function (s) {
                        return '<div class="flex items-center justify-between gap-2 py-1 border-b border-slate-200/60 last:border-0"><span class="truncate">' + escapeHtml(s.name) + '</span><span class="shrink-0 font-semibold">' + escapeHtml(money(s.price)) + '</span></div>'
                    }).join('')
                }
            }

            var when = appt && appt.appointment_datetime ? String(appt.appointment_datetime).replace('T', ' ').slice(0, 16) : '—'
            if (appointmentPreview) {
                appointmentPreview.textContent = 'Patient: ' + appointmentPatientName(appt) + ' • Doctor: ' + appointmentDoctorName(appt) + ' • Date/Time: ' + when + ' • Type: ' + String(appt.appointment_type || 'scheduled')
                appointmentPreview.classList.remove('hidden')
            }

            refreshTotalsUI()
        }

        function renderAppointmentResults(items, q) {
            if (!appointmentResults) return
            var query = normalizeText(q || '')
            var today = (function () {
                var d = new Date()
                var y = d.getFullYear()
                var m = String(d.getMonth() + 1).padStart(2, '0')
                var day = String(d.getDate()).padStart(2, '0')
                return y + '-' + m + '-' + day
            })()

            var list = (Array.isArray(items) ? items : []).filter(function (appt) {
                var type = String(appt && appt.appointment_type ? appt.appointment_type : '').toLowerCase()
                if (type !== 'scheduled' && type !== 'walk_in') return false

                var status = String(appt && appt.status ? appt.status : '').toLowerCase()
                if (status === 'completed') return false

                var datePart = appt && appt.appointment_datetime ? String(appt.appointment_datetime).slice(0, 10) : ''
                if (datePart !== today) return false

                var patient = appointmentPatientName(appt)
                var doctor = appointmentDoctorName(appt)
                var idText = String(appt && appt.appointment_id != null ? appt.appointment_id : '')
                var full = normalizeText(patient + ' ' + doctor + ' #' + idText)
                if (!query) return true
                return full.indexOf(query) !== -1 || wordPrefixMatch(patient, query) || idText.indexOf(query) === 0
            }).slice(0, 15)

            if (!list.length) {
                appointmentResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No appointments found.</div>'
                appointmentResults.classList.remove('hidden')
                return
            }

            appointmentResults.innerHTML = list.map(function (appt) {
                var id = appt && appt.appointment_id != null ? appt.appointment_id : ''
                var patient = appointmentPatientName(appt)
                var when = appt && appt.appointment_datetime ? String(appt.appointment_datetime).replace('T', ' ').slice(0, 16) : '—'
                var type = appt && appt.appointment_type ? String(appt.appointment_type) : 'scheduled'
                return '<button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 border-b border-slate-100 last:border-0">' +
                    '<div class="text-[0.78rem] text-slate-800 font-semibold">#' + escapeHtml(id) + ' - ' + escapeHtml(patient) + '</div>' +
                    '<div class="text-[0.72rem] text-slate-500">' + escapeHtml(when + ' • ' + type) + '</div>' +
                '</button>'
            }).join('')
            appointmentResults.classList.remove('hidden')

            var buttons = appointmentResults.querySelectorAll('button')
            Array.prototype.forEach.call(buttons, function (btn, idx) {
                btn.addEventListener('click', function () {
                    setAppointmentSelection(list[idx])
                })
            })
        }

        function searchAppointments(query) {
            if (typeof apiFetch !== 'function') return
            var q = String(query || '').trim()
            var now = new Date()
            var y = now.getFullYear()
            var m = String(now.getMonth() + 1).padStart(2, '0')
            var d = String(now.getDate()).padStart(2, '0')
            var today = y + '-' + m + '-' + d
            var url = "{{ url('/api/appointments') }}" + '?per_page=100&order=latest&start_date=' + encodeURIComponent(today) + '&end_date=' + encodeURIComponent(today)
            if (q) url += '&search=' + encodeURIComponent(q)

            apiFetch(url, { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) { return { ok: response.ok, data: data } }).catch(function () { return { ok: response.ok, data: null } })
                })
                .then(function (result) {
                    if (!result.ok || !result.data) {
                        renderAppointmentResults([], q)
                        return
                    }
                    var list = Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    renderAppointmentResults(list, q)
                })
                .catch(function () {
                    renderAppointmentResults([], q)
                })
        }

        function setPaymentSubmitting(isSubmitting) {
            if (paymentSubmit) paymentSubmit.disabled = !!isSubmitting
            if (paymentSubmitSpinner) paymentSubmitSpinner.classList.toggle('hidden', !isSubmitting)
            if (paymentSubmitLabel) paymentSubmitLabel.textContent = isSubmitting ? 'Saving...' : 'Record payment'
        }

        function closeReview(result) {
            if (reviewOverlay) {
                reviewOverlay.classList.add('hidden')
                reviewOverlay.classList.remove('flex')
            }
            if (reviewDelayTimer) {
                clearTimeout(reviewDelayTimer)
                reviewDelayTimer = null
            }
            if (reviewConfirm) {
                reviewConfirm.disabled = false
                reviewConfirm.innerHTML = reviewConfirmDefaultHtml || 'Confirm'
            }
            var resolver = reviewResolver
            reviewResolver = null
            if (typeof resolver === 'function') resolver(!!result)
        }

        function openReview(details) {
            return new Promise(function (resolve) {
                if (!reviewOverlay || !reviewContent || !reviewConfirm || !reviewCancel) {
                    resolve(window.confirm('Please review payment details before submitting.'))
                    return
                }
                var rows = Object.keys(details || {}).map(function (key) {
                    return '<li><strong class="font-semibold text-slate-800">' + escapeHtml(key) + ':</strong> ' + escapeHtml(details[key]) + '</li>'
                })
                reviewContent.innerHTML = '<ul class="space-y-1">' + rows.join('') + '</ul>'
                reviewResolver = resolve
                reviewOverlay.classList.remove('hidden')
                reviewOverlay.classList.add('flex')

                reviewConfirm.disabled = true
                reviewConfirm.innerHTML = '<span class="inline-flex items-center gap-2"><span class="w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span><span>Confirm</span></span>'
                reviewDelayTimer = setTimeout(function () {
                    reviewConfirm.disabled = false
                    reviewConfirm.innerHTML = reviewConfirmDefaultHtml || 'Confirm'
                    reviewDelayTimer = null
                }, 3000)
            })
        }

        function updateDiscountUI() {
            if (discountWrap) discountWrap.classList.toggle('hidden', !showDiscount)
            if (discountToggle) discountToggle.textContent = showDiscount ? 'Hide discount' : 'Add discount'
            if (!showDiscount && discountTypeSelect) discountTypeSelect.value = 'none'
            refreshTotalsUI()
        }

        function txSetTodayButton() {
            if (!txTodayBtn) return
            if (txTodayOnly) {
                txTodayBtn.textContent = 'Showing today only'
                txTodayBtn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200')
                txTodayBtn.classList.add('bg-cyan-600', 'text-white', 'border-cyan-600')
            } else {
                txTodayBtn.textContent = 'Show today only'
                txTodayBtn.classList.add('bg-white', 'text-slate-700', 'border-slate-200')
                txTodayBtn.classList.remove('bg-cyan-600', 'text-white', 'border-cyan-600')
            }
        }

        function txServiceSummary(tx) {
            var appt = tx && tx.appointment ? tx.appointment : null
            var services = appt && Array.isArray(appt.services) ? appt.services : []
            var names = services.map(function (s) { return String((s && s.service_name) ? s.service_name : '').trim() }).filter(function (v) { return v !== '' })
            if (!names.length) return '—'
            return names.join(', ')
        }

        function txPatientName(tx) {
            var appt = tx && tx.appointment ? tx.appointment : null
            return appointmentPatientName(appt)
        }

        function txDatePart(tx) {
            var raw = tx && tx.transaction_datetime ? String(tx.transaction_datetime) : ''
            if (!raw) raw = tx && tx.created_at ? String(tx.created_at) : ''
            return raw ? raw.replace('T', ' ').slice(0, 16) : '—'
        }

        function renderTransactions(rows) {
            if (!txTableBody) return
            var list = Array.isArray(rows) ? rows : []
            if (!list.length) {
                txTableBody.innerHTML = '<tr><td colspan="9" class="px-3 py-6 text-center text-[0.78rem] text-slate-500">No transactions found.</td></tr>'
                return
            }
            txTableBody.innerHTML = list.map(function (tx) {
                var appt = tx && tx.appointment ? tx.appointment : null
                var apptStatus = String(appt && appt.status ? appt.status : '').toLowerCase()
                var date = txDatePart(tx)
                var ref = tx && tx.reference_number ? String(tx.reference_number) : '—'
                var patient = txPatientName(tx)
                var services = txServiceSummary(tx)
                var type = appt && appt.appointment_type ? String(appt.appointment_type) : 'scheduled'
                var gross = parseFloat(tx && tx.amount != null ? tx.amount : 0)
                var disc = parseFloat(tx && tx.discount_amount != null ? tx.discount_amount : 0)
                if (isNaN(gross)) gross = 0
                if (isNaN(disc)) disc = 0
                var net = Math.max(0, gross - disc)
                var mode = tx && tx.payment_mode ? String(tx.payment_mode).toUpperCase() : 'CASH'
                var badge = apptStatus ? '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[0.68rem] border border-slate-200 bg-slate-50 text-slate-700">' + escapeHtml(apptStatus.replace(/_/g, ' ')) + '</span>' : ''
                return '<tr>' +
                    '<td class="px-3 py-2 text-slate-700 whitespace-nowrap">' + escapeHtml(date) + badge + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 whitespace-nowrap">' + escapeHtml(ref) + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 min-w-[12rem] whitespace-nowrap">' + escapeHtml(patient) + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 min-w-[14rem] whitespace-nowrap">' + escapeHtml(services) + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 whitespace-nowrap">' + escapeHtml(type) + '</td>' +
                    '<td class="px-3 py-2 text-right text-slate-700 whitespace-nowrap">' + escapeHtml(money(gross)) + '</td>' +
                    '<td class="px-3 py-2 text-right text-slate-700 whitespace-nowrap">' + escapeHtml(money(disc)) + '</td>' +
                    '<td class="px-3 py-2 text-right text-slate-700 whitespace-nowrap">' + escapeHtml(money(net)) + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 whitespace-nowrap">' + escapeHtml(mode) + '</td>' +
                '</tr>'
            }).join('')
        }

        function loadTransactions() {
            if (typeof apiFetch !== 'function') return
            showTransactionsError('')

            var url = "{{ url('/api/transactions') }}" + '?per_page=100'
            var order = txSort && txSort.value ? String(txSort.value) : 'latest'
            url += '&order=' + encodeURIComponent(order === 'oldest' ? 'oldest' : 'latest')

            var now = new Date()
            var yyyy = now.getFullYear()
            var mm = String(now.getMonth() + 1).padStart(2, '0')
            var dd = String(now.getDate()).padStart(2, '0')
            var today = yyyy + '-' + mm + '-' + dd
            if (txTodayOnly) {
                url += '&start_date=' + encodeURIComponent(today) + '&end_date=' + encodeURIComponent(today)
            } else {
                var start = yyyy + '-' + mm + '-01'
                var end = yyyy + '-' + mm + '-' + String(new Date(yyyy, now.getMonth() + 1, 0).getDate()).padStart(2, '0')
                url += '&start_date=' + encodeURIComponent(start) + '&end_date=' + encodeURIComponent(end)
            }

            var search = txSearch ? normalizeText(txSearch.value) : ''
            if (search) url += '&search=' + encodeURIComponent(search)
            var serviceId = txServiceId && txServiceId.value ? parseInt(txServiceId.value, 10) : 0
            if (serviceId) url += '&service_id=' + encodeURIComponent(serviceId)

            apiFetch(url, { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) { return { ok: response.ok, data: data } }).catch(function () { return { ok: response.ok, data: null } })
                })
                .then(function (result) {
                    if (!result.ok || !result.data) {
                        showTransactionsError((result.data && result.data.message) ? String(result.data.message) : 'Failed to load transactions.')
                        renderTransactions([])
                        return
                    }
                    var rows = Array.isArray(result.data.data) ? result.data.data.slice() : (Array.isArray(result.data) ? result.data.slice() : [])

                    rows.sort(function (a, b) {
                        function completeRank(tx) {
                            var appt = tx && tx.appointment ? tx.appointment : null
                            var status = String(appt && appt.status ? appt.status : '').toLowerCase()
                            return status === 'completed' ? 1 : 0
                        }
                        var ra = completeRank(a)
                        var rb = completeRank(b)
                        if (ra !== rb) return ra - rb
                        var da = String(a && a.transaction_datetime ? a.transaction_datetime : (a && a.created_at ? a.created_at : ''))
                        var db = String(b && b.transaction_datetime ? b.transaction_datetime : (b && b.created_at ? b.created_at : ''))
                        if (order === 'oldest') return da < db ? -1 : (da > db ? 1 : 0)
                        return da < db ? 1 : (da > db ? -1 : 0)
                    })

                    renderTransactions(rows)
                    if (txMeta) {
                        txMeta.textContent = 'Showing ' + String(rows.length) + (txTodayOnly ? (' transactions for ' + today + '.') : ' transactions for this month.')
                    }
                })
                .catch(function () {
                    showTransactionsError('Network error while loading transactions.')
                    renderTransactions([])
                })
        }

        function renderTxServiceResults() {
            if (!txServiceResults || !txServiceSearch) return
            var q = String(txServiceSearch.value || '').trim()
            var filtered = (Array.isArray(txServices) ? txServices : []).filter(function (s) {
                return wordPrefixMatch(s && s.service_name ? s.service_name : '', q)
            }).slice(0, 25)

            if (!filtered.length) {
                txServiceResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No services found.</div>'
            } else {
                txServiceResults.innerHTML = filtered.map(function (s) {
                    var id = s && s.service_id != null ? s.service_id : ''
                    var name = s && s.service_name ? s.service_name : ('Service #' + id)
                    return '<button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 text-[0.78rem] text-slate-700" data-service-id="' + escapeHtml(id) + '">' + escapeHtml(name) + '</button>'
                }).join('')
            }
            txServiceResults.classList.remove('hidden')
        }

        function loadTxServices() {
            if (txServicesLoaded || txServicesLoading || typeof apiFetch !== 'function') return
            txServicesLoading = true
            apiFetch("{{ url('/api/services') }}?per_page=100", { method: 'GET' })
                .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data } }).catch(function () { return { ok: response.ok, data: null } }) })
                .then(function (result) {
                    if (!result.ok) return
                    txServices = Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    txServicesLoaded = true
                })
                .catch(function () {})
                .finally(function () {
                    txServicesLoading = false
                })
        }

        function setTxServiceSelection(service) {
            if (txServiceId) txServiceId.value = service && service.service_id != null ? String(service.service_id) : ''
            if (txServiceSearch) {
                txServiceSearch.value = service && service.service_name ? String(service.service_name) : ''
                if (!service) txServiceSearch.placeholder = 'All services'
            }
            if (txServiceResults) txServiceResults.classList.add('hidden')
        }

        if (recordTab) recordTab.addEventListener('click', function () { setBillingTab('record') })
        if (txTab) txTab.addEventListener('click', function () { setBillingTab('transactions') })
        setBillingTab('record')

        if (discountToggle) {
            discountToggle.addEventListener('click', function () {
                showDiscount = !showDiscount
                updateDiscountUI()
            })
        }
        if (discountTypeSelect) discountTypeSelect.addEventListener('change', refreshTotalsUI)
        updateDiscountUI()
        resetAppointmentSelection()

        if (appointmentSearch) {
            appointmentSearch.addEventListener('focus', function () {
                showPaymentError('')
                showPaymentSuccess('')
                searchAppointments(String(appointmentSearch.value || '').trim())
            })
            appointmentSearch.addEventListener('input', function () {
                var q = String(appointmentSearch.value || '').trim()
                if (selectedAppointment) {
                    var current = '#' + String(selectedAppointment.appointment_id || '') + ' - ' + appointmentPatientName(selectedAppointment)
                    if (normalizeText(current) !== normalizeText(q)) {
                        resetAppointmentSelection()
                    }
                }
                if (appointmentSearchTimer) clearTimeout(appointmentSearchTimer)
                appointmentSearchTimer = setTimeout(function () {
                    searchAppointments(q)
                }, 250)
            })
        }

        if (reviewCancel) reviewCancel.addEventListener('click', function () { closeReview(false) })
        if (reviewConfirm) reviewConfirm.addEventListener('click', function () { closeReview(true) })
        if (reviewOverlay) {
            reviewOverlay.addEventListener('click', function (e) {
                if (e.target === reviewOverlay) closeReview(false)
            })
        }

        if (paymentForm) {
            paymentForm.addEventListener('submit', function (e) {
                e.preventDefault()
                showPaymentError('')
                showPaymentSuccess('')
                if (typeof apiFetch !== 'function') {
                    showPaymentError('API client is not available.')
                    return
                }

                var appointmentId = appointmentIdInput ? parseInt(appointmentIdInput.value || '0', 10) : 0
                if (!appointmentId || !selectedAppointment) {
                    showPaymentError('Please select an appointment.')
                    return
                }

                var gross = originalAmount(selectedAppointment)
                if (gross <= 0) {
                    showPaymentError('Selected appointment has no billable services.')
                    return
                }
                var discountType = discountTypeSelect && showDiscount ? String(discountTypeSelect.value || 'none') : 'none'
                var discount = showDiscount ? discountAmount(selectedAppointment) : 0
                var net = Math.max(0, gross - discount)

                var details = {
                    'Appointment ID': String(appointmentId),
                    'Patient': appointmentPatientName(selectedAppointment),
                    'Doctor': appointmentDoctorName(selectedAppointment),
                    'Gross Amount': money(gross),
                    'Discount Type': discountType,
                    'Discount Amount': money(discount),
                    'Net Amount': money(net),
                    'Payment Mode': 'cash',
                    'Payment Status': 'paid',
                }

                setPaymentSubmitting(true)
                openReview(details)
                    .then(function (confirmed) {
                        if (!confirmed) {
                            setPaymentSubmitting(false)
                            return
                        }

                        return apiFetch("{{ url('/api/transactions') }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                appointment_id: appointmentId,
                                amount: gross,
                                discount_type: discountType,
                                payment_mode: 'cash',
                                payment_status: 'paid'
                            })
                        })
                            .then(function (response) {
                                return response.json().then(function (data) { return { ok: response.ok, data: data } }).catch(function () { return { ok: response.ok, data: null } })
                            })
                            .then(function (result) {
                                if (!result.ok) {
                                    showPaymentError((result.data && result.data.message) ? String(result.data.message) : 'Failed to record payment.')
                                    return
                                }
                                var txId = result.data && result.data.transaction_id ? result.data.transaction_id : null
                                var ref = result.data && result.data.reference_number ? result.data.reference_number : ''
                                showPaymentSuccess('Payment recorded successfully.' + (txId ? (' Transaction #' + txId + '.') : ''))
                                if (referenceDisplay) referenceDisplay.textContent = ref ? String(ref) : 'Auto-generated on save'
                                if (appointmentSearch) appointmentSearch.value = ''
                                resetAppointmentSelection()
                                showDiscount = false
                                updateDiscountUI()
                                loadTransactions()
                            })
                    })
                    .catch(function () {
                        showPaymentError('Network error while recording payment.')
                    })
                    .finally(function () {
                        setPaymentSubmitting(false)
                    })
            })
        }

        if (txTodayBtn) {
            txSetTodayButton()
            txTodayBtn.addEventListener('click', function () {
                txTodayOnly = !txTodayOnly
                txSetTodayButton()
                loadTransactions()
            })
        }
        if (txRefresh) txRefresh.addEventListener('click', loadTransactions)
        if (txSort) txSort.addEventListener('change', loadTransactions)
        if (txSearch) {
            txSearch.addEventListener('input', function () {
                if (transactionsSearchTimer) clearTimeout(transactionsSearchTimer)
                transactionsSearchTimer = setTimeout(function () { loadTransactions() }, 250)
            })
        }
        if (txServiceSearch) {
            txServiceSearch.addEventListener('focus', function () {
                loadTxServices()
                renderTxServiceResults()
            })
            txServiceSearch.addEventListener('input', function () {
                if (txServiceId && txServiceId.value) {
                    var picked = txServices.find(function (s) { return String(s.service_id) === String(txServiceId.value) }) || null
                    var pickedName = picked && picked.service_name ? String(picked.service_name) : ''
                    if (normalizeText(txServiceSearch.value) !== normalizeText(pickedName)) {
                        setTxServiceSelection(null)
                        loadTransactions()
                    }
                }
                loadTxServices()
                renderTxServiceResults()
            })
        }
        if (txServiceResults) {
            txServiceResults.addEventListener('click', function (e) {
                var btn = e.target && e.target.closest ? e.target.closest('button[data-service-id]') : null
                if (!btn) return
                var id = btn.getAttribute('data-service-id')
                var picked = txServices.find(function (s) { return String(s.service_id) === String(id) }) || null
                setTxServiceSelection(picked)
                loadTransactions()
            })
        }

        document.addEventListener('click', function (e) {
            var target = e.target
            if (appointmentResults && !appointmentResults.classList.contains('hidden')) {
                if (!(appointmentResults.contains(target) || (appointmentSearch && appointmentSearch.contains(target)))) {
                    appointmentResults.classList.add('hidden')
                }
            }
            if (txServiceResults && !txServiceResults.classList.contains('hidden')) {
                if (!(txServiceResults.contains(target) || (txServiceSearch && txServiceSearch.contains(target)))) {
                    txServiceResults.classList.add('hidden')
                }
            }
        })

        loadTransactions()
    })
</script>
