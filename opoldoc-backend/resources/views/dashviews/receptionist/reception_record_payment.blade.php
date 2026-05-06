<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">Record payment</h2>
            <p class="text-xs text-slate-500">Create a payment transaction for a completed visit.</p>
        </div>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Billing</span>
    </div>

    <div id="receptionPaymentError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
    <div id="receptionPaymentSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>

    <form id="receptionPaymentForm" class="grid gap-3 grid-cols-1 md:grid-cols-4 items-end mb-4">
        <div>
            <label for="reception_payment_appointment_id" class="block text-[0.7rem] text-slate-600 mb-1">Appointment ID</label>
            <input id="reception_payment_appointment_id" type="number" min="1" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Appointment ID" required>
        </div>
        <div>
            <label for="reception_payment_amount" class="block text-[0.7rem] text-slate-600 mb-1">Amount</label>
            <input id="reception_payment_amount" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="0.00" required>
        </div>
        <div>
            <label for="reception_payment_discount_amount" class="block text-[0.7rem] text-slate-600 mb-1">Discount amount (optional)</label>
            <input id="reception_payment_discount_amount" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="0.00">
        </div>
        <div>
            <label for="reception_payment_discount_type" class="block text-[0.7rem] text-slate-600 mb-1">Discount type</label>
            <select id="reception_payment_discount_type" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="none">None</option>
                <option value="senior">Senior</option>
                <option value="pwd">PWD</option>
            </select>
        </div>
        <div>
            <label for="reception_payment_mode" class="block text-[0.7rem] text-slate-600 mb-1">Payment mode</label>
            <select id="reception_payment_mode" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="cash" selected>Cash</option>
                <option value="gcash">GCash</option>
            </select>
        </div>
        <div>
            <label for="reception_payment_status" class="block text-[0.7rem] text-slate-600 mb-1">Payment status</label>
            <select id="reception_payment_status" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="">Default (pending)</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="failed">Failed</option>
            </select>
        </div>
        <div>
            <label for="reception_payment_reference" class="block text-[0.7rem] text-slate-600 mb-1">Reference number (optional)</label>
            <input id="reception_payment_reference" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Reference">
        </div>
        <div>
            <label for="reception_payment_datetime" class="block text-[0.7rem] text-slate-600 mb-1">Transaction date &amp; time</label>
            <input id="reception_payment_datetime" type="datetime-local" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
        </div>
        <div class="md:col-span-2">
            <label for="reception_payment_receipt" class="block text-[0.7rem] text-slate-600 mb-1">Receipt (optional)</label>
            <input id="reception_payment_receipt" type="file" accept=".jpg,.jpeg,.png,.pdf" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
        </div>
        <div class="md:col-span-4 flex justify-end">
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors">
                Record payment
            </button>
        </div>
    </form>

    <p class="text-[0.7rem] text-slate-400">
        Link each transaction to an appointment. Use discount type, payment mode, and payment status according to clinic policy.
    </p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('receptionPaymentForm')
        var errorBox = document.getElementById('receptionPaymentError')
        var successBox = document.getElementById('receptionPaymentSuccess')

        function showPaymentError(message) {
            if (!errorBox) return
            errorBox.textContent = message || ''
            if (message) {
                errorBox.classList.remove('hidden')
            } else {
                errorBox.classList.add('hidden')
            }
        }

        function showPaymentSuccess(message) {
            if (!successBox) return
            successBox.textContent = message || ''
            if (message) {
                successBox.classList.remove('hidden')
            } else {
                successBox.classList.add('hidden')
            }
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault()

                showPaymentError('')
                showPaymentSuccess('')

                var appointmentInput = document.getElementById('reception_payment_appointment_id')
                var amountInput = document.getElementById('reception_payment_amount')
                var discountInput = document.getElementById('reception_payment_discount_amount')
                var discountTypeInput = document.getElementById('reception_payment_discount_type')
                var modeInput = document.getElementById('reception_payment_mode')
                var statusInput = document.getElementById('reception_payment_status')
                var referenceInput = document.getElementById('reception_payment_reference')
                var datetimeInput = document.getElementById('reception_payment_datetime')
                var receiptInput = document.getElementById('reception_payment_receipt')

                var appointmentId = appointmentInput ? parseInt(appointmentInput.value, 10) : 0
                var amount = amountInput ? parseFloat(amountInput.value || '0') : 0
                var discountAmount = discountInput && discountInput.value ? parseFloat(discountInput.value) : null
                var discountType = discountTypeInput && discountTypeInput.value ? discountTypeInput.value : 'none'
                var paymentMode = modeInput && modeInput.value ? modeInput.value : null
                var paymentStatus = statusInput && statusInput.value ? statusInput.value : null
                var reference = referenceInput ? referenceInput.value : ''
                var transactionDatetime = datetimeInput ? datetimeInput.value : ''
                var receiptFile = receiptInput && receiptInput.files && receiptInput.files.length ? receiptInput.files[0] : null

                if (!appointmentId) {
                    showPaymentError('Appointment ID is required.')
                    return
                }

                if (!amountInput || isNaN(amount) || amount < 0) {
                    showPaymentError('Amount must be a valid non-negative number.')
                    return
                }

                if (typeof apiFetch !== 'function') {
                    showPaymentError('API client is not available.')
                    return
                }

                var formData = new FormData()
                formData.append('appointment_id', String(appointmentId))
                formData.append('amount', String(amount))
                if (discountAmount !== null && !isNaN(discountAmount)) formData.append('discount_amount', String(discountAmount))
                if (discountType) formData.append('discount_type', String(discountType))
                if (paymentMode) formData.append('payment_mode', String(paymentMode))
                if (paymentStatus) formData.append('payment_status', String(paymentStatus))
                if (reference) formData.append('reference_number', String(reference))
                if (transactionDatetime) formData.append('transaction_datetime', String(transactionDatetime))
                if (receiptFile) formData.append('receipt', receiptFile)

                apiFetch("{{ url('/api/transactions') }}", {
                    method: 'POST',
                    body: formData
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
                            var message = 'Failed to record payment.'
                            if (result.data && result.data.message) {
                                message = result.data.message
                            }
                            showPaymentError(message)
                            return
                        }

                        var txId = result.data && result.data.transaction_id ? result.data.transaction_id : null
                        showPaymentSuccess('Payment has been recorded successfully.' + (txId ? ' Transaction #' + txId + '.' : ''))
                        if (appointmentInput) appointmentInput.value = ''
                        if (amountInput) amountInput.value = ''
                        if (discountInput) discountInput.value = ''
                        if (discountTypeInput) discountTypeInput.value = 'none'
                        if (modeInput) modeInput.value = ''
                        if (statusInput) statusInput.value = ''
                        if (referenceInput) referenceInput.value = ''
                        if (datetimeInput) datetimeInput.value = ''
                        if (receiptInput) receiptInput.value = ''
                    })
                    .catch(function () {
                        showPaymentError('Network error while recording payment.')
                    })
            })
        }
    })
</script>
