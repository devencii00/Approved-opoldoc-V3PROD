<div class="bg-white border border-slate-200 rounded-[18px] shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="grid grid-cols-2 border-b border-slate-200">
      <button id="receptionWalkInTabAccount" type="button" class="px-4 py-3 text-xs font-semibold text-white bg-cyan-500 border-b-2 border-cyan-600 rounded-tl-[18px]">
    Walk-in
</button>
<button id="receptionWalkInTabGuest" type="button" class="px-4 py-3 text-xs font-semibold text-slate-900 bg-white hover:bg-slate-50 border-l border-slate-200 rounded-tr-[18px]">
    Guest walk-in
</button>
    </div>

    <div class="p-5 pb-0">
        <div class="flex items-center justify-between">
            <div>
                <h2 id="receptionWalkInHeaderTitle" class="text-sm font-semibold text-slate-900">Create walk-in</h2>
                <p id="receptionWalkInHeaderDesc" class="text-xs text-slate-500">Register a walk-in based on personal information or an existing patient.</p>
            </div>
            <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Walk-ins</span>
        </div>
    </div>

    <div id="receptionWalkInPanelGuest" class="hidden p-5 pt-4">
    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h3 class="text-xs font-semibold text-slate-900">Walk-in without account</h3>
                <p class="text-[0.72rem] text-slate-500">Creates a patient account + walk-in appointment + queue entry.</p>
            </div>
            <div class="flex items-center gap-2">
                <button id="receptionGuestLinkBtn" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-[0.72rem] font-semibold text-slate-700 hover:bg-slate-50">
                    <x-lucide-qr-code class="w-[18px] h-[18px]" />
                    Public Link / QR
                </button>
                <span class="text-[0.68rem] text-slate-400 uppercase tracking-widest">Guest</span>
            </div>
        </div>

        <div id="receptionGuestWalkInError" class="hidden mb-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
        <div id="receptionGuestWalkInSuccess" class="hidden mb-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>
        <div id="receptionGuestWalkInCreds" class="hidden mb-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[0.75rem] text-slate-700"></div>

       <form id="receptionGuestWalkInForm" class="grid gap-3 grid-cols-1 md:grid-cols-4 items-start">
            <div>
                <label for="reception_guest_firstname" class="block text-[0.7rem] text-slate-600 mb-1">First name</label>
                <input id="reception_guest_firstname" type="text" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="First name">
            </div>
            <div>
                <label for="reception_guest_middlename" class="block text-[0.7rem] text-slate-600 mb-1">Middle name (optional)</label>
                <input id="reception_guest_middlename" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Middle name">
            </div>
            <div>
                <label for="reception_guest_lastname" class="block text-[0.7rem] text-slate-600 mb-1">Last name</label>
                <input id="reception_guest_lastname" type="text" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Last name">
            </div>
            <div>
                <label for="reception_guest_contact" class="block text-[0.7rem] text-slate-600 mb-1">Contact number (optional)</label>
                <input id="reception_guest_contact" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Mobile number">
            </div>
            <div class="min-w-0 md:col-span-2">
                <label for="reception_guest_service_ids" class="block text-[0.7rem] text-slate-600 mb-1">Services</label>
                <div class="mb-1 text-[0.7rem] text-slate-500">&nbsp;</div>
                <div class="relative">
                    <input id="reception_guest_service_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type to search service">
                    <input id="reception_guest_service_ids" type="hidden">
                    <div id="receptionGuestServiceResults" class="hidden absolute left-0 right-0 top-full mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain z-50"></div>
                </div>
                <div id="receptionGuestSelectedServices" class="mt-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[0.78rem] text-slate-700 max-h-24 overflow-y-auto overscroll-contain"></div>
            </div>
            <div class="min-w-0 md:col-span-2">
                <label for="reception_guest_doctor_id" class="block text-[0.7rem] text-slate-600 mb-1">Doctor</label>
                <div class="mb-1 text-[0.7rem] text-slate-500">&nbsp;</div>
                <div class="relative">
                    <input id="reception_guest_doctor_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type to search doctor" disabled>
                    <input id="reception_guest_doctor_id" type="hidden" required>
                    <div id="receptionGuestDoctorResults" class="hidden absolute left-0 right-0 top-full mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain z-50"></div>
                </div>
                
                <div id="receptionGuestDoctorPreview" class="hidden mt-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[0.78rem] text-slate-700 break-words"></div>
            </div>
            <div class="md:col-span-2">
                <label for="reception_guest_reason" class="block text-[0.7rem] text-slate-600 mb-1">Reason (optional)</label>
                <input id="reception_guest_reason" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Reason for visit">
            </div>
            <div>
                <label for="reception_guest_priority_level" class="block text-[0.7rem] text-slate-600 mb-1">Priority level (optional)</label>
                <select id="reception_guest_priority_level" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="">Select priority</option>
                    <option value="1">1 : Emergency</option>
                    <option value="2">2 : PWD</option>
                    <option value="3">3 : Pregnant</option>
                    <option value="4">4 : Senior</option>
                    <option value="5">5 : General</option>
                </select>
            </div>
       <div class="flex items-end self-end">
    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-slate-800 transition-colors">
        Create guest walk-in
    </button>
</div>
        </form>
    </div>
    </div>

    <div id="receptionWalkInPanelAccount" class="p-5 pt-4">
    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h3 class="text-xs font-semibold text-slate-900">Walk-in with account</h3>
                <p class="text-[0.72rem] text-slate-500">Create a walk-in (or scheduled) visit for an existing patient.</p>
            </div>
            <span class="text-[0.68rem] text-slate-400 uppercase tracking-widest">Account</span>
        </div>

        <div id="receptionWalkInAccountError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
        <div id="receptionWalkInAccountSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>

        <form id="receptionWalkInAccountForm" class="grid gap-3 grid-cols-1 md:grid-cols-3 items-start">
            <div class="min-w-0">
                <label for="reception_walkin_patient_id" class="block text-[0.7rem] text-slate-600 mb-1">Patient</label>
                <div class="relative">
                    <input id="reception_walkin_patient_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type to search patient">
                    <input id="reception_walkin_patient_id" type="hidden" required>
                    <div id="receptionWalkInPatientResults" class="hidden absolute left-0 right-0 top-full mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain z-50"></div>
                </div>
                <div id="receptionWalkInPatientPreview" class="hidden mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.78rem] text-slate-700 break-words"></div>
            </div>
            <div class="min-w-0">
                <label for="reception_walkin_service_ids" class="block text-[0.7rem] text-slate-600 mb-1">Services</label>
                <div class="relative">
                    <input id="reception_walkin_service_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type to search service">
                    <input id="reception_walkin_service_ids" type="hidden">
                    <div id="receptionWalkInServiceResults" class="hidden absolute left-0 right-0 top-full mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain z-50"></div>
                </div>
                <div id="receptionWalkInSelectedServices" class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.78rem] text-slate-700 max-h-24 overflow-y-auto overscroll-contain"></div>
            </div>
            <div class="min-w-0">
                <label for="reception_walkin_doctor_id" class="block text-[0.7rem] text-slate-600 mb-1">Doctor</label>
                <div class="relative">
                    <input id="reception_walkin_doctor_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type to search doctor" disabled>
                    <input id="reception_walkin_doctor_id" type="hidden" required>
                    <div id="receptionWalkInDoctorResults" class="hidden absolute left-0 right-0 top-full mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain z-50"></div>
                </div>
                <div id="receptionWalkInDoctorPreview" class="hidden mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.78rem] text-slate-700 break-words"></div>
            </div>
            <div>
                <label for="reception_walkin_priority" class="block text-[0.7rem] text-slate-600 mb-1">Priority level (optional)</label>
                <select id="reception_walkin_priority" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="">Select priority</option>
                    <option value="1">1 : Emergency</option>
                    <option value="2">2 : PWD</option>
                    <option value="3">3 : Pregnant</option>
                    <option value="4">4 : Senior</option>
                    <option value="5">5 : General</option>
                </select>
            </div>
            <div class="md:col-span-3">
                <label for="reception_walkin_reason" class="block text-[0.7rem] text-slate-600 mb-1">Reason (optional)</label>
                <input id="reception_walkin_reason" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Reason for visit">
            </div>

            <input id="reception_walkin_type" type="hidden" value="walk_in">

            <div class="md:col-span-3 flex justify-end">
                <button id="receptionWalkInAccountSubmit" type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors disabled:opacity-60 disabled:hover:bg-cyan-600">
                    <span id="receptionWalkInAccountSpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                    <span id="receptionWalkInAccountSubmitLabel">Create walk-in</span>
                </button>
            </div>
        </form>
    </div>
    </div>
</div>

<div id="receptionGuestLinkModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 px-4 py-6 overflow-y-auto">
    <div class="max-w-2xl mx-auto bg-white rounded-[18px] border border-slate-200 shadow-[0_10px_40px_rgba(15,23,42,0.20)] overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-900">Guest walk-in public link</div>
                <div class="text-xs text-slate-500">Use the QR to let patients register themselves as guest walk-ins.</div>
            </div>
            <button id="receptionGuestLinkClose" type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                <x-lucide-x class="w-[20px] h-[20px]" />
            </button>
        </div>

        <div class="p-5 grid gap-4 grid-cols-1 md:grid-cols-2 items-start">
            <div>
                <div id="receptionGuestLinkModalError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
                <div class="text-[0.7rem] text-slate-400 uppercase tracking-widest mb-1">Current active link</div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 break-words">
                    <a id="receptionGuestActiveLink" href="#" target="_blank" class="text-cyan-700 font-semibold hover:underline"></a>
                </div>

                <div class="mt-3 text-[0.7rem] text-slate-400 uppercase tracking-widest mb-1">Static link</div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 break-words">
                    <a id="receptionGuestStaticLink" href="#" target="_blank" class="text-cyan-700 font-semibold hover:underline"></a>
                </div>

                <div class="mt-3 text-[0.7rem] text-slate-400 uppercase tracking-widest mb-1">QR display page</div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 break-words">
                    <a id="receptionGuestQrPageLink" href="#" target="_blank" class="text-cyan-700 font-semibold hover:underline"></a>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button id="receptionGuestLinkGenerate" type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700">
                        <x-lucide-refresh-cw class="w-[18px] h-[18px]" />
                        Generate new link
                    </button>
                    <button id="receptionGuestLinkDeprecate" type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-[0.78rem] font-semibold hover:bg-slate-50">
                        <x-lucide-ban class="w-[18px] h-[18px]" />
                        Deprecate current
                    </button>
                </div>
            </div>

            <div class="flex flex-col items-center">
                <div class="text-[0.7rem] text-slate-400 uppercase tracking-widest mb-2">QR Code</div>
                <div class="w-[280px] h-[280px] rounded-2xl border border-slate-200 bg-white overflow-hidden flex items-center justify-center">
                    <img id="receptionGuestQrImg" src="" alt="Guest walk-in QR" class="w-[280px] h-[280px] object-contain">
                </div>
                <div class="mt-2 text-[0.72rem] text-slate-500 text-center">
                    Patients can scan this to open the guest registration page.
                </div>
            </div>
        </div>
    </div>
</div>

<div id="receptionWalkInConfirmOverlay" class="hidden fixed inset-0 z-50 bg-slate-900/40 items-center justify-center p-4">
    <div class="w-full max-w-sm rounded-2xl bg-white border border-slate-200 shadow-[0_12px_30px_rgba(15,23,42,0.24)] p-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-700">
                <x-lucide-info class="w-[18px] h-[18px]" />
            </div>
            <div class="flex-1">
                <div class="text-sm font-semibold text-slate-900">Confirm</div>
                <div id="receptionWalkInConfirmMessage" class="text-[0.78rem] text-slate-600 mt-0.5">Are you sure?</div>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button" id="receptionWalkInConfirmCancel" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
            <button type="button" id="receptionWalkInConfirmOk" class="px-3 py-2 rounded-xl bg-slate-900 text-white text-[0.78rem] font-semibold hover:bg-slate-800">Yes</button>
        </div>
    </div>
</div>

<div id="receptionWalkInReviewOverlay" class="hidden fixed inset-0 z-[55] bg-slate-900/40 items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-[0_12px_30px_rgba(15,23,42,0.24)] p-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-700">
                <x-lucide-info class="w-[18px] h-[18px]" />
            </div>
            <div class="flex-1 min-w-0">
                <div id="receptionWalkInReviewTitle" class="text-sm font-semibold text-slate-900">Review Details</div>
                <div id="receptionWalkInReviewContent" class="mt-1 text-[0.78rem] text-slate-600"></div>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button" id="receptionWalkInReviewCancel" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
            <button type="button" id="receptionWalkInReviewOk" class="px-3 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700">Confirm</button>
        </div>
    </div>
</div>

<template id="receptionWalkInsIconX">
    <x-lucide-x class="w-[18px] h-[18px]" />
</template>

<script>
    window.receptionWalkInsIconX = '';
    document.addEventListener('DOMContentLoaded', function () {
        window.receptionWalkInsIconX = (function () {
            var tpl = document.getElementById('receptionWalkInsIconX')
            return tpl ? String(tpl.innerHTML || '').trim() : ''
        })()
        var tabAccountBtn = document.getElementById('receptionWalkInTabAccount')
        var tabGuestBtn = document.getElementById('receptionWalkInTabGuest')
        var panelAccount = document.getElementById('receptionWalkInPanelAccount')
        var panelGuest = document.getElementById('receptionWalkInPanelGuest')
        var headerTitle = document.getElementById('receptionWalkInHeaderTitle')
        var headerDesc = document.getElementById('receptionWalkInHeaderDesc')
function setWalkInTab(tab) {
    var isAccount = tab === 'account'
    if (panelAccount) panelAccount.classList.toggle('hidden', !isAccount)
    if (panelGuest) panelGuest.classList.toggle('hidden', isAccount)

    if (headerTitle) {
        headerTitle.textContent = isAccount ? 'Create walk-in' : 'Create Guest walk-in'
    }
    if (headerDesc) {
        headerDesc.textContent = isAccount
            ? 'Register a walk-in based on personal information or an existing patient.'
            : 'Register a guest walk-in based on personal information.'
    }

    if (tabAccountBtn) {
        // Active tab (Account)
        tabAccountBtn.classList.toggle('bg-cyan-500', isAccount)      // Cyan background
        tabAccountBtn.classList.toggle('text-white', isAccount)       // White text
        tabAccountBtn.classList.toggle('border-b-2', isAccount)       // Bottom border indicator
        tabAccountBtn.classList.toggle('border-cyan-600', isAccount)  // Darker cyan border
        // Inactive tab
        tabAccountBtn.classList.toggle('bg-white', !isAccount)        // White background
        tabAccountBtn.classList.toggle('text-slate-900', !isAccount)  // Dark text
        tabAccountBtn.classList.toggle('hover:bg-slate-50', !isAccount) // Hover effect
        tabAccountBtn.classList.toggle('border-b-0', !isAccount)      // No border when inactive
        tabAccountBtn.classList.toggle('border-l', !isAccount)        // Left border separator
        tabAccountBtn.classList.toggle('border-slate-200', !isAccount) // Border color
    }
    if (tabGuestBtn) {
        // Active tab (Guest)
        tabGuestBtn.classList.toggle('bg-cyan-500', !isAccount)       // Cyan background
        tabGuestBtn.classList.toggle('text-white', !isAccount)        // White text
        tabGuestBtn.classList.toggle('border-b-2', !isAccount)        // Bottom border indicator
        tabGuestBtn.classList.toggle('border-cyan-600', !isAccount)   // Darker cyan border
        // Inactive tab
        tabGuestBtn.classList.toggle('bg-white', isAccount)           // White background
        tabGuestBtn.classList.toggle('text-slate-900', isAccount)     // Dark text
        tabGuestBtn.classList.toggle('hover:bg-slate-50', isAccount)  // Hover effect
        tabGuestBtn.classList.toggle('border-b-0', isAccount)         // No border when inactive
        tabGuestBtn.classList.toggle('border-l', isAccount)           // Left border separator
        tabGuestBtn.classList.toggle('border-slate-200', isAccount)   // Border color
    }
}
        if (tabAccountBtn) tabAccountBtn.addEventListener('click', function () { setWalkInTab('account') })
        if (tabGuestBtn) tabGuestBtn.addEventListener('click', function () { setWalkInTab('guest') })
        setWalkInTab('account')
    })
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var overlay = document.getElementById('receptionWalkInConfirmOverlay')
        var messageEl = document.getElementById('receptionWalkInConfirmMessage')
        var okBtn = document.getElementById('receptionWalkInConfirmOk')
        var cancelBtn = document.getElementById('receptionWalkInConfirmCancel')
        var reviewOverlay = document.getElementById('receptionWalkInReviewOverlay')
        var reviewTitleEl = document.getElementById('receptionWalkInReviewTitle')
        var reviewContentEl = document.getElementById('receptionWalkInReviewContent')
        var reviewOkBtn = document.getElementById('receptionWalkInReviewOk')
        var reviewCancelBtn = document.getElementById('receptionWalkInReviewCancel')
        var resolver = null
        var reviewResolver = null
        var timer = null
        var okDefaultHtml = okBtn ? okBtn.innerHTML : ''

        function close(result) {
            if (overlay) {
                overlay.classList.add('hidden')
                overlay.classList.remove('flex')
            }
            if (timer) {
                clearTimeout(timer)
                timer = null
            }
            if (okBtn) {
                okBtn.disabled = false
                okBtn.innerHTML = okDefaultHtml || 'Yes'
            }
            var r = resolver
            resolver = null
            if (typeof r === 'function') r(!!result)
        }

        function open(message, delayMs) {
            return new Promise(function (resolve) {
                if (!overlay || !messageEl || !okBtn || !cancelBtn) {
                    resolve(window.confirm(message || 'Are you sure?'))
                    return
                }
                if (timer) {
                    clearTimeout(timer)
                    timer = null
                }
                resolver = resolve
                messageEl.textContent = message || 'Are you sure?'
                var ms = delayMs != null ? parseInt(delayMs, 10) : 0
                if (isNaN(ms) || ms < 0) ms = 0
                okBtn.disabled = ms > 0
                if (ms > 0) {
                    okBtn.innerHTML = '<span class="inline-flex items-center gap-2"><span class="w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span><span>Yes</span></span>'
                    timer = setTimeout(function () {
                        okBtn.disabled = false
                        okBtn.textContent = 'Yes'
                        timer = null
                    }, ms)
                } else {
                    okBtn.textContent = 'Yes'
                }
                overlay.classList.remove('hidden')
                overlay.classList.add('flex')
            })
        }

        function closeReview(result) {
            if (reviewOverlay) {
                reviewOverlay.classList.add('hidden')
                reviewOverlay.classList.remove('flex')
            }
            var r = reviewResolver
            reviewResolver = null
            if (typeof r === 'function') r(!!result)
        }

        function openReview(title, details) {
            return new Promise(function (resolve) {
                if (!reviewOverlay || !reviewContentEl || !reviewOkBtn || !reviewCancelBtn) {
                    resolve(window.confirm('Please review details before continuing.'))
                    return
                }
                function esc(input) {
                    return String(input == null ? '' : input)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;')
                }
                reviewResolver = resolve
                if (reviewTitleEl) reviewTitleEl.textContent = title || 'Review Details'
                var rows = []
                var source = details && typeof details === 'object' ? details : {}
                Object.keys(source).forEach(function (key) {
                    rows.push('<li><strong class="font-semibold text-slate-800">' + esc(key) + ':</strong> ' + esc(source[key]) + '</li>')
                })
                reviewContentEl.innerHTML = '<ul class="space-y-1">' + rows.join('') + '</ul>'
                reviewOverlay.classList.remove('hidden')
                reviewOverlay.classList.add('flex')
            })
        }

        if (okBtn) okBtn.addEventListener('click', function () { close(true) })
        if (cancelBtn) cancelBtn.addEventListener('click', function () { close(false) })
        if (overlay) overlay.addEventListener('click', function (e) { if (e.target === overlay) close(false) })
        if (reviewOkBtn) reviewOkBtn.addEventListener('click', function () { closeReview(true) })
        if (reviewCancelBtn) reviewCancelBtn.addEventListener('click', function () { closeReview(false) })
        if (reviewOverlay) reviewOverlay.addEventListener('click', function (e) { if (e.target === reviewOverlay) closeReview(false) })

        window.receptionWalkInConfirm = open
        window.receptionWalkInReview = openReview
    })
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var guestForm = document.getElementById('receptionGuestWalkInForm')
        var guestErrorBox = document.getElementById('receptionGuestWalkInError')
        var guestSuccessBox = document.getElementById('receptionGuestWalkInSuccess')
        var guestCredsBox = document.getElementById('receptionGuestWalkInCreds')
        var guestServiceSearch = document.getElementById('reception_guest_service_search')
        var guestServiceIdsInput = document.getElementById('reception_guest_service_ids')
        var guestServiceResults = document.getElementById('receptionGuestServiceResults')
        var guestSelectedServicesEl = document.getElementById('receptionGuestSelectedServices')
        var guestDoctorSearch = document.getElementById('reception_guest_doctor_search')
        var guestDoctorIdInput = document.getElementById('reception_guest_doctor_id')
        var guestDoctorResults = document.getElementById('receptionGuestDoctorResults')
        var guestDoctorPreview = document.getElementById('receptionGuestDoctorPreview')

        var guestServices = []
        var guestPopularServices = []
        var guestDoctors = []
        var guestSelectedServices = []
        var guestSelectedDoctor = null
        var guestLoadingServices = false
        var guestLoadingDoctors = false
        var guestPreviousDoctorId = 0
        var guestPreviousServiceIds = []
        var guestPreviousServiceIdSet = {}
        var guestNameTimer = null
        var guestFirstNameInput = document.getElementById('reception_guest_firstname')
        var guestMiddleNameInput = document.getElementById('reception_guest_middlename')
        var guestLastNameInput = document.getElementById('reception_guest_lastname')

        function showGuestError(message) {
            if (!guestErrorBox) return
            guestErrorBox.textContent = message || ''
            if (message) {
                guestErrorBox.classList.remove('hidden')
            } else {
                guestErrorBox.classList.add('hidden')
            }
        }

        function showGuestSuccess(message) {
            if (!guestSuccessBox) return
            guestSuccessBox.textContent = message || ''
            if (message) {
                guestSuccessBox.classList.remove('hidden')
            } else {
                guestSuccessBox.classList.add('hidden')
            }
        }

        function showGuestCreds(message) {
            if (!guestCredsBox) return
            guestCredsBox.textContent = message || ''
            if (message) {
                guestCredsBox.classList.remove('hidden')
            } else {
                guestCredsBox.classList.add('hidden')
            }
        }

        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
        }

        function normalizeText(text) {
            return String(text || '')
                .toLowerCase()
                .replace(/\s+/g, ' ')
                .trim()
        }

        function wordPrefixMatch(text, query) {
            var t = normalizeText(text)
            var q = normalizeText(query)
            if (!q) return true
            var words = t.split(' ')
            return words.some(function (w) { return w.indexOf(q) === 0 })
        }

        function serviceGroup(service) {
            if (!service) return ''
            var name = String(service.service_name || '').trim()
            if (!name) return ''
            var parts = name.split(':')
            var group = String(parts[0] || name).trim().toLowerCase()
            return group
        }

        function isWalkInExcludedService(service) {
            var g = serviceGroup(service)
            return g === 'obsterician - gynecologist' || g === 'obstetrician - gynecologist' || g === 'general surgeon'
        }

        function extractServiceCategory(serviceName) {
            var raw = String(serviceName || '').trim()
            if (!raw) return ''
            var parts = raw.split(':')
            var category = String(parts[0] || raw).trim().toLowerCase()
            return category
        }

        function specializationMatches(serviceCategory, doctorSpecialization) {
            var a = normalizeText(serviceCategory)
            var b = normalizeText(doctorSpecialization)
            if (!a || !b) return false
            return b.indexOf(a) !== -1 || a.indexOf(b) !== -1
        }

        function dayKeyFromDate(dateStr) {
            if (!dateStr) return ''
            var d = new Date(dateStr + 'T00:00:00')
            if (isNaN(d.getTime())) return ''
            var keys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']
            return keys[d.getDay()] || ''
        }

        function localDateIso() {
            var now = new Date()
            var y = now.getFullYear()
            var m = String(now.getMonth() + 1).padStart(2, '0')
            var d = String(now.getDate()).padStart(2, '0')
            return y + '-' + m + '-' + d
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
                var today = localDateIso()
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

        function selectedGuestServiceIds() {
            return (guestSelectedServices || [])
                .map(function (s) { return parseInt(s && s.service_id != null ? s.service_id : 0, 10) })
                .filter(function (id) { return !!id && !isNaN(id) })
        }

        function syncGuestServiceHiddenInput() {
            if (!guestServiceIdsInput) return
            guestServiceIdsInput.value = selectedGuestServiceIds().join(',')
        }

        function syncGuestDoctorEnabled() {
            if (!guestDoctorSearch) return
            guestDoctorSearch.disabled = !(guestSelectedServices && guestSelectedServices.length)
            if (guestDoctorSearch.disabled) {
                guestDoctorSearch.value = ''
                setGuestDoctorSelection(null)
            }
        }

        function applyGuestHistory(payload) {
            guestPreviousDoctorId = payload && payload.previous_doctor_id ? parseInt(payload.previous_doctor_id, 10) : 0
            if (isNaN(guestPreviousDoctorId)) guestPreviousDoctorId = 0
            guestPreviousServiceIds = Array.isArray(payload && payload.previous_service_ids) ? payload.previous_service_ids.map(function (v) { return parseInt(v, 10) }).filter(function (v) { return !!v && !isNaN(v) }) : []
            guestPreviousServiceIds = Array.from(new Set(guestPreviousServiceIds.map(function (v) { return String(v) }))).map(function (v) { return parseInt(v, 10) })
            guestPreviousServiceIdSet = {}
            guestPreviousServiceIds.forEach(function (id) { guestPreviousServiceIdSet[String(id)] = true })
        }

        function loadGuestHistoryForNames() {
            if (typeof apiFetch !== 'function') return Promise.resolve(null)
            var fn = guestFirstNameInput ? String(guestFirstNameInput.value || '').trim() : ''
            var mn = guestMiddleNameInput ? String(guestMiddleNameInput.value || '').trim() : ''
            var ln = guestLastNameInput ? String(guestLastNameInput.value || '').trim() : ''
            if (!fn || !mn || !ln) {
                applyGuestHistory(null)
                return Promise.resolve(null)
            }
            var url = "{{ url('/api/walk-ins/guest/check-duplicates') }}" +
                '?firstname=' + encodeURIComponent(fn) +
                '&middlename=' + encodeURIComponent(mn) +
                '&lastname=' + encodeURIComponent(ln)
            return apiFetch(url, { method: 'GET' })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d } }).catch(function () { return { ok: r.ok, data: null } }) })
                .then(function (res) {
                    if (!res || !res.ok) return null
                    applyGuestHistory(res.data)
                    return res.data
                })
                .catch(function () { return null })
        }

        function scheduleGuestHistoryLoad() {
            if (guestNameTimer) clearTimeout(guestNameTimer)
            guestNameTimer = setTimeout(function () {
                loadGuestHistoryForNames().then(function () {
                    if (guestServiceSearch && guestServiceResults && !guestServiceResults.classList.contains('hidden')) {
                        searchGuestServices(String(guestServiceSearch.value || ''))
                    }
                    if (guestDoctorSearch && guestDoctorResults && !guestDoctorResults.classList.contains('hidden')) {
                        searchGuestDoctors(String(guestDoctorSearch.value || ''))
                    }
                })
            }, 250)
        }

        function renderGuestSelectedServices() {
            if (!guestSelectedServicesEl) return
            var list = Array.isArray(guestSelectedServices) ? guestSelectedServices : []
            if (!list.length) {
                guestSelectedServicesEl.innerHTML = '<div class="text-[0.75rem] text-slate-500">No services selected.</div>'
                return
            }

            guestSelectedServicesEl.innerHTML = list.map(function (s) {
                var id = parseInt(s && s.service_id != null ? s.service_id : 0, 10)
                var name = String(s && s.service_name ? s.service_name : '').trim() || 'Service'
                var meta = []
                if (s && s.duration_minutes != null) meta.push(String(s.duration_minutes) + ' min')
                if (s && s.price != null) meta.push('₱' + String(s.price))
                return '' +
                    '<div class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-200 last:border-0">' +
                        '<div class="min-w-0">' +
                            '<div class="text-[0.78rem] text-slate-800 font-semibold truncate">' + escapeHtml(name) + '</div>' +
                            '<div class="text-[0.72rem] text-slate-500">' + escapeHtml(meta.join(' • ') || '—') + '</div>' +
                        '</div>' +
                        '<button type="button" class="reception-guest-remove-service inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" data-service-id="' + escapeHtml(id) + '">' +
                            window.receptionWalkInsIconX +
                        '</button>' +
                    '</div>'
            }).join('')

            var buttons = guestSelectedServicesEl.querySelectorAll('.reception-guest-remove-service')
            Array.prototype.forEach.call(buttons, function (btn) {
                btn.addEventListener('click', function () {
                    var id = parseInt(btn.getAttribute('data-service-id') || '0', 10)
                    if (!id) return
                    guestSelectedServices = (guestSelectedServices || []).filter(function (s) {
                        return parseInt(s && s.service_id != null ? s.service_id : 0, 10) !== id
                    })
                    syncGuestServiceHiddenInput()
                    renderGuestSelectedServices()
                    syncGuestDoctorEnabled()
                    setGuestDoctorSelection(null)
                    if (guestDoctorSearch) guestDoctorSearch.value = ''
                    renderGuestServiceResults(guestServices.slice(0, 10))
                })
            })
        }

        function addGuestService(service) {
            if (!service || service.service_id == null) return
            var id = String(service.service_id)
            var exists = (guestSelectedServices || []).some(function (s) { return String(s && s.service_id) === id })
            if (exists) return
            guestSelectedServices = (guestSelectedServices || []).concat([service])
            syncGuestServiceHiddenInput()
            renderGuestSelectedServices()
            syncGuestDoctorEnabled()
            setGuestDoctorSelection(null)
            if (guestDoctorSearch) guestDoctorSearch.value = ''
            if (guestServiceSearch) guestServiceSearch.value = ''
            if (guestServiceResults) guestServiceResults.classList.add('hidden')
        }

        function renderGuestServiceResults(items) {
            if (!guestServiceResults) return
            var list = Array.isArray(items) ? items : []

            list = list.filter(function (s) { return !isWalkInExcludedService(s) })

            if (guestSelectedServices && guestSelectedServices.length) {
                var base = serviceGroup(guestSelectedServices[0])
                if (base) {
                    list = list.filter(function (s) { return serviceGroup(s) === base })
                }
            }

            if (guestPreviousServiceIds && guestPreviousServiceIds.length) {
                var order = {}
                guestPreviousServiceIds.forEach(function (id, idx) {
                    order[String(id)] = idx
                })
                var pinned = []
                var rest = []
                list.forEach(function (s) {
                    var sid = s && s.service_id != null ? String(s.service_id) : ''
                    if (sid !== '' && order[sid] != null) pinned.push(s)
                    else rest.push(s)
                })
                pinned.sort(function (a, b) {
                    return (order[String(a.service_id)] || 0) - (order[String(b.service_id)] || 0)
                })
                list = pinned.concat(rest)
            }

            if (!list.length) {
                guestServiceResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No services found.</div>'
                guestServiceResults.classList.remove('hidden')
                return
            }

            guestServiceResults.innerHTML = list.slice(0, 12).map(function (s) {
                var name = String(s.service_name || '').trim() || 'Service'
                var meta = []
                if (s.duration_minutes != null) meta.push(String(s.duration_minutes) + ' min')
                if (s.price != null) meta.push('₱' + String(s.price))
                var desc = s.description != null ? String(s.description).trim() : ''
                var isLast = !!(guestPreviousServiceIdSet && guestPreviousServiceIdSet[String(s.service_id)])
                var tag = isLast
                    ? '<span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[0.65rem] font-semibold bg-amber-50 text-amber-800 border border-amber-200">Last inquired</span>'
                    : ''
                return '<button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 border-b border-slate-100 last:border-0" data-service-id="' + escapeHtml(s.service_id) + '">' +
                    '<div class="text-[0.78rem] text-slate-800 font-semibold flex items-center justify-between gap-2">' +
                        '<span class="min-w-0 truncate">' + escapeHtml(name) + '</span>' +
                        tag +
                    '</div>' +
                    '<div class="text-[0.72rem] text-slate-500">' + escapeHtml(meta.join(' • ') || '—') + '</div>' +
                    (desc ? '<div class="mt-0.5 text-[0.72rem] text-slate-500">' + escapeHtml(desc) + '</div>' : '') +
                '</button>'
            }).join('')
            guestServiceResults.classList.remove('hidden')

            var buttons = guestServiceResults.querySelectorAll('button[data-service-id]')
            Array.prototype.forEach.call(buttons, function (btn) {
                btn.addEventListener('click', function () {
                    var id = btn.getAttribute('data-service-id') || ''
                    var chosen = (guestServices || []).find(function (s) { return String(s && s.service_id) === String(id) }) || null
                    if (!chosen) return
                    addGuestService(chosen)
                })
            })
        }

        function searchGuestServices(query) {
            var q = normalizeText(query)
            if (!q) {
                if (guestSelectedServices && guestSelectedServices.length) {
                    renderGuestServiceResults(guestServices || [])
                } else {
                    renderGuestServiceResults((guestPopularServices && guestPopularServices.length) ? guestPopularServices.slice() : (guestServices || []).slice(0, 12))
                }
                return
            }
            var filtered = (guestServices || []).filter(function (s) {
                return wordPrefixMatch(s && s.service_name ? s.service_name : '', q)
            })
            renderGuestServiceResults(filtered)
        }

        function doctorDisplayName(doctor) {
            if (!doctor) return ''
            var parts = [doctor.firstname, doctor.middlename, doctor.lastname].filter(function (v) { return String(v || '').trim() !== '' })
            var name = parts.join(' ').trim()
            if (!name) name = 'Doctor #' + (doctor.user_id != null ? doctor.user_id : '')
            return name
        }

        function setGuestDoctorSelection(doctor) {
            guestSelectedDoctor = doctor || null
            if (guestDoctorIdInput) guestDoctorIdInput.value = doctor && doctor.user_id != null ? String(doctor.user_id) : ''
            if (guestDoctorPreview) {
                if (!doctor) {
                    guestDoctorPreview.textContent = ''
                    guestDoctorPreview.classList.add('hidden')
                } else {
                    var label = 'Doctor: ' + doctorDisplayName(doctor)
                    if (doctor.specialization) label += ' • ' + String(doctor.specialization)
                    if (guestPreviousDoctorId && parseInt(doctor.user_id, 10) === guestPreviousDoctorId) label += ' • Last provider'
                    guestDoctorPreview.textContent = label
                    guestDoctorPreview.classList.remove('hidden')
                }
            }
            if (guestDoctorResults) guestDoctorResults.classList.add('hidden')
        }

        function renderGuestDoctorResults(items) {
            if (!guestDoctorResults) return
            var list = Array.isArray(items) ? items : []
            if (!guestSelectedServices || !guestSelectedServices.length) {
                guestDoctorResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">Select a service first.</div>'
                guestDoctorResults.classList.remove('hidden')
                return
            }
            if (!list.length) {
                guestDoctorResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No doctors found.</div>'
                guestDoctorResults.classList.remove('hidden')
                return
            }

            var dateStr = localDateIso()
            var dayKey = dayKeyFromDate(dateStr)
            var checkTime = new Date().toTimeString().slice(0, 5)

            var enriched = list.slice(0, 20).map(function (d) {
                var name = doctorDisplayName(d)
                var spec = d && d.specialization ? String(d.specialization) : ''
                var isDoctorAvailable = d && d.is_available !== false
                var hasSchedule = !!dayKey && hasScheduleAtTime(d, dayKey, dateStr, checkTime)
                var isSelectable = isDoctorAvailable && hasSchedule
                var tag = ''
                if (!isDoctorAvailable) tag = 'Unavailable'
                else if (!hasSchedule) tag = 'No schedule on this time'
                else if (guestPreviousDoctorId && parseInt(d.user_id, 10) === guestPreviousDoctorId) tag = 'Last provider'
                return { d: d, name: name, spec: spec, isSelectable: isSelectable, tag: tag }
            })

            enriched.sort(function (a, b) {
                if (a.isSelectable !== b.isSelectable) return a.isSelectable ? -1 : 1
                if ((a.tag === 'Last provider') !== (b.tag === 'Last provider')) return a.tag === 'Last provider' ? -1 : 1
                return normalizeText(a.name).localeCompare(normalizeText(b.name))
            })

            guestDoctorResults.innerHTML = enriched.slice(0, 12).map(function (x) {
                return '<button type="button" class="w-full text-left px-3 py-2 border-b border-slate-100 last:border-0 flex items-start justify-between gap-3 ' + (x.isSelectable ? 'hover:bg-slate-50' : 'bg-slate-50/60 cursor-not-allowed') + '" ' + (x.isSelectable ? '' : 'disabled') + ' data-doctor-id="' + escapeHtml(x.d.user_id) + '">' +
                    '<div class="min-w-0">' +
                        '<div class="text-[0.78rem] text-slate-800 font-semibold">' + escapeHtml('Dr. ' + x.name) + '</div>' +
                        '<div class="text-[0.72rem] text-slate-500">' + escapeHtml(x.spec || '—') + '</div>' +
                    '</div>' +
                    (x.tag ? '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.65rem] font-semibold ' + (x.tag === 'Last provider' ? 'bg-cyan-500/10 text-cyan-700 border border-cyan-200' : 'bg-slate-100 text-slate-500 border border-slate-200') + '">' + escapeHtml(x.tag) + '</span>' : '') +
                '</button>'
            }).join('')
            guestDoctorResults.classList.remove('hidden')

            var buttons = guestDoctorResults.querySelectorAll('button[data-doctor-id]')
            Array.prototype.forEach.call(buttons, function (btn) {
                btn.addEventListener('click', function () {
                    var id = btn.getAttribute('data-doctor-id') || ''
                    var chosen = (guestDoctors || []).find(function (d) { return String(d && d.user_id) === String(id) }) || null
                    if (!chosen) return
                    setGuestDoctorSelection(chosen)
                    if (guestDoctorSearch) guestDoctorSearch.value = doctorDisplayName(chosen)
                })
            })
        }

        function searchGuestDoctors(query) {
            var q = normalizeText(query)
            var baseService = guestSelectedServices && guestSelectedServices.length ? guestSelectedServices[0] : null
            var category = extractServiceCategory(baseService && baseService.service_name ? baseService.service_name : '')

            var list = (guestDoctors || []).slice()
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
            renderGuestDoctorResults(list.slice(0, 20))
        }

        function loadGuestServices() {
            if (guestLoadingServices || typeof apiFetch !== 'function') return
            guestLoadingServices = true
            apiFetch("{{ url('/api/services') }}?per_page=100", { method: 'GET' })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d } }).catch(function () { return { ok: r.ok, data: null } }) })
                .then(function (res) {
                    if (!res.ok) return
                    var raw = res.data && Array.isArray(res.data.data) ? res.data.data : (Array.isArray(res.data) ? res.data : [])
                    guestServices = raw || []
                })
                .catch(function () {})
                .finally(function () { guestLoadingServices = false })

            apiFetch("{{ url('/api/services-popular') }}?limit=10", { method: 'GET' })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d } }).catch(function () { return { ok: r.ok, data: null } }) })
                .then(function (res) {
                    if (!res.ok) return
                    var raw = res.data && Array.isArray(res.data.data) ? res.data.data : (Array.isArray(res.data) ? res.data : [])
                    guestPopularServices = raw || []
                })
                .catch(function () {})
        }

        function loadGuestDoctors() {
            if (guestLoadingDoctors || typeof apiFetch !== 'function') return
            guestLoadingDoctors = true
            apiFetch("{{ url('/api/doctors') }}?per_page=100", { method: 'GET' })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d } }).catch(function () { return { ok: r.ok, data: null } }) })
                .then(function (res) {
                    if (!res.ok) return
                    var raw = res.data && Array.isArray(res.data.data) ? res.data.data : (Array.isArray(res.data) ? res.data : [])
                    guestDoctors = raw || []
                })
                .catch(function () {})
                .finally(function () { guestLoadingDoctors = false })
        }

        loadGuestServices()
        loadGuestDoctors()
        renderGuestSelectedServices()
        syncGuestDoctorEnabled()
        scheduleGuestHistoryLoad()

        if (guestFirstNameInput) guestFirstNameInput.addEventListener('input', scheduleGuestHistoryLoad)
        if (guestMiddleNameInput) guestMiddleNameInput.addEventListener('input', scheduleGuestHistoryLoad)
        if (guestLastNameInput) guestLastNameInput.addEventListener('input', scheduleGuestHistoryLoad)

        if (guestServiceSearch) {
            guestServiceSearch.addEventListener('focus', function () {
                loadGuestServices()
                searchGuestServices(String(guestServiceSearch.value || ''))
            })
            guestServiceSearch.addEventListener('input', function () {
                loadGuestServices()
                searchGuestServices(String(guestServiceSearch.value || ''))
            })
        }

        if (guestDoctorSearch) {
            guestDoctorSearch.addEventListener('focus', function () {
                loadGuestDoctors()
                searchGuestDoctors(String(guestDoctorSearch.value || ''))
            })
            guestDoctorSearch.addEventListener('input', function () {
                loadGuestDoctors()
                searchGuestDoctors(String(guestDoctorSearch.value || ''))
            })
        }

        document.addEventListener('click', function (e) {
            var target = e && e.target ? e.target : null
            if (guestServiceResults && !guestServiceResults.classList.contains('hidden')) {
                if (!(guestServiceResults.contains(target) || (guestServiceSearch && guestServiceSearch.contains(target)))) {
                    guestServiceResults.classList.add('hidden')
                }
            }
            if (guestDoctorResults && !guestDoctorResults.classList.contains('hidden')) {
                if (!(guestDoctorResults.contains(target) || (guestDoctorSearch && guestDoctorSearch.contains(target)))) {
                    guestDoctorResults.classList.add('hidden')
                }
            }
        })

        if (guestForm) {
            guestForm.addEventListener('submit', function (e) {
                e.preventDefault()

                showGuestError('')
                showGuestSuccess('')
                showGuestCreds('')

                var firstNameInput = document.getElementById('reception_guest_firstname')
                var middleNameInput = document.getElementById('reception_guest_middlename')
                var lastNameInput = document.getElementById('reception_guest_lastname')
                var contactInput = document.getElementById('reception_guest_contact')
                var doctorInput = document.getElementById('reception_guest_doctor_id')
                var serviceIdsInput = document.getElementById('reception_guest_service_ids')
                var reasonInput = document.getElementById('reception_guest_reason')
                var priorityInput = document.getElementById('reception_guest_priority_level')

                var doctorId = doctorInput ? parseInt(doctorInput.value, 10) : 0
                var serviceIds = serviceIdsInput && serviceIdsInput.value ? String(serviceIdsInput.value).split(',').map(function (v) { return parseInt(v, 10) }).filter(function (v) { return !!v && !isNaN(v) }) : []
                if (!serviceIds.length) {
                    showGuestError('Services are required.')
                    return
                }
                if (!doctorId) {
                    showGuestError('Doctor is required.')
                    return
                }

                if (typeof apiFetch !== 'function') {
                    showGuestError('API client is not available.')
                    return
                }

                var body = {
                    doctor_id: doctorId,
                    service_ids: serviceIds
                }

                var firstName = firstNameInput ? String(firstNameInput.value || '').trim() : ''
                var middleName = middleNameInput ? String(middleNameInput.value || '').trim() : ''
                var lastName = lastNameInput ? String(lastNameInput.value || '').trim() : ''
                var contact = contactInput ? String(contactInput.value || '').trim() : ''
                var reason = reasonInput ? String(reasonInput.value || '').trim() : ''
                var priorityLevel = priorityInput && priorityInput.value ? parseInt(priorityInput.value, 10) : null

                if (!firstName || !lastName) {
                    showGuestError('First name and last name are required.')
                    return
                }

                body.firstname = firstName
                body.middlename = middleName
                body.lastname = lastName
                if (contact) body.contact_number = contact
                if (reason) body.reason_for_visit = reason
                if (priorityLevel !== null && !isNaN(priorityLevel)) body.priority_level = priorityLevel

                var reviewDetails = {
                    'First Name': firstName,
                    'Middle Name': middleName,
                    'Last Name': lastName,
                    'Contact Number': contact || 'N/A',
                    'Doctor': guestSelectedDoctor ? doctorDisplayName(guestSelectedDoctor) : ('#' + String(doctorId)),
                    'Services': (guestSelectedServices || []).map(function (s) { return s && s.service_name ? s.service_name : '' }).filter(Boolean).join(', '),
                    'Reason': reason || 'N/A',
                    'Priority Level': (priorityLevel !== null && !isNaN(priorityLevel)) ? String(priorityLevel) : 'N/A'
                }

                function submitGuestWalkIn() {
                    apiFetch("{{ url('/api/walk-ins/guest') }}", {
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
                                var message = 'Failed to create guest walk-in.'
                                if (result.data && result.data.message) {
                                    message = result.data.message
                                }
                                showGuestError(message)
                                return
                            }

                            showGuestSuccess('Walk-in successfuly created and currently on the queue.')
                            showGuestCreds('')

                            if (firstNameInput) firstNameInput.value = ''
                            if (middleNameInput) middleNameInput.value = ''
                            if (lastNameInput) lastNameInput.value = ''
                            if (contactInput) contactInput.value = ''
                            guestSelectedServices = []
                            syncGuestServiceHiddenInput()
                            renderGuestSelectedServices()
                            syncGuestDoctorEnabled()

                            if (guestServiceSearch) guestServiceSearch.value = ''
                            if (guestDoctorSearch) guestDoctorSearch.value = ''
                            setGuestDoctorSelection(null)
                            if (doctorInput) doctorInput.value = ''
                            if (reasonInput) reasonInput.value = ''
                            if (priorityInput) priorityInput.value = ''
                        })
                        .catch(function () {
                            showGuestError('Network error while creating guest walk-in.')
                        })
                }
                var askReview = window.receptionWalkInReview
                var reviewPromise = (typeof askReview === 'function')
                    ? askReview('Review Guest Walk-in Details', reviewDetails)
                    : Promise.resolve(window.confirm('Please review details before submitting this guest walk-in.'))

                reviewPromise
                    .then(function (reviewOk) {
                        if (!reviewOk) return
                        return loadGuestHistoryForNames()
                            .then(function (dup) {
                                if (dup && dup.similar_in_queue) {
                                    var ask = window.receptionWalkInConfirm
                                    if (typeof ask === 'function') {
                                        return ask('A patient with similar details is already in current queue, would you still like to register this queue entry?', 3000)
                                            .then(function (confirmed) {
                                                if (!confirmed) return
                                                submitGuestWalkIn()
                                            })
                                    }
                                    if (!window.confirm('A patient with similar details is already in current queue, would you still like to register this queue entry?')) return
                                }
                                submitGuestWalkIn()
                            })
                            .catch(function () {
                                submitGuestWalkIn()
                            })
                    })
            })
        }
    })
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var openBtn = document.getElementById('receptionGuestLinkBtn')
        var modal = document.getElementById('receptionGuestLinkModal')
        var closeBtn = document.getElementById('receptionGuestLinkClose')
        var errBox = document.getElementById('receptionGuestLinkModalError')
        var activeLinkEl = document.getElementById('receptionGuestActiveLink')
        var staticLinkEl = document.getElementById('receptionGuestStaticLink')
        var qrPageLinkEl = document.getElementById('receptionGuestQrPageLink')
        var qrImg = document.getElementById('receptionGuestQrImg')
        var generateBtn = document.getElementById('receptionGuestLinkGenerate')
        var deprecateBtn = document.getElementById('receptionGuestLinkDeprecate')

        var currentLinkId = null
        var currentActiveUrl = null
        var currentStaticUrl = null
        var currentQrUrl = null

        function showError(message) {
            if (!errBox) return
            errBox.textContent = message || ''
            errBox.classList.toggle('hidden', !message)
        }

        function setQrUrl(url) {
            if (!qrImg) return
            var u = String(url || '').trim()
            if (!u) {
                qrImg.src = ''
                return
            }
            qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' + encodeURIComponent(u)
        }

        function setLink(el, url) {
            if (!el) return
            var u = String(url || '').trim()
            el.textContent = u || '—'
            el.href = u || '#'
            el.classList.toggle('pointer-events-none', !u)
            el.classList.toggle('text-slate-400', !u)
            el.classList.toggle('font-semibold', !!u)
        }

        function applyPayload(payload) {
            var link = payload && payload.link ? payload.link : null
            currentLinkId = link && link.link_id != null ? String(link.link_id) : null
            currentActiveUrl = payload && payload.active_url ? String(payload.active_url) : null
            currentStaticUrl = payload && payload.static_url ? String(payload.static_url) : null
            currentQrUrl = payload && payload.qr_url ? String(payload.qr_url) : null

            setLink(activeLinkEl, currentActiveUrl)
            setLink(staticLinkEl, currentStaticUrl)
            setLink(qrPageLinkEl, currentQrUrl)

            var qrTarget = currentActiveUrl || currentStaticUrl
            setQrUrl(qrTarget)

            if (deprecateBtn) deprecateBtn.disabled = !currentLinkId
        }

        function loadCurrent() {
            if (typeof apiFetch !== 'function') return
            showError('')
            apiFetch("{{ url('/api/guest-walk-in-links/current') }}", { method: 'GET' })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d } }).catch(function () { return { ok: r.ok, data: null } }) })
                .then(function (res) {
                    if (!res.ok) {
                        showError((res.data && res.data.message) ? res.data.message : 'Failed to load current link.')
                        return
                    }
                    applyPayload(res.data)
                })
                .catch(function () {
                    showError('Network error while loading current link.')
                })
        }

        function generateNew() {
            if (typeof apiFetch !== 'function') return
            showError('')
            if (generateBtn) generateBtn.disabled = true
            apiFetch("{{ url('/api/guest-walk-in-links') }}", { method: 'POST' })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d } }).catch(function () { return { ok: r.ok, data: null } }) })
                .then(function (res) {
                    if (!res.ok) {
                        showError((res.data && res.data.message) ? res.data.message : 'Failed to generate new link.')
                        return
                    }
                    applyPayload(res.data)
                })
                .catch(function () {
                    showError('Network error while generating new link.')
                })
                .finally(function () {
                    if (generateBtn) generateBtn.disabled = false
                })
        }

        function deprecateCurrent() {
            if (!currentLinkId || typeof apiFetch !== 'function') return
            showError('')
            if (deprecateBtn) deprecateBtn.disabled = true
            apiFetch("{{ url('/api/guest-walk-in-links') }}/" + encodeURIComponent(currentLinkId) + "/deprecate", { method: 'POST' })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d } }).catch(function () { return { ok: r.ok, data: null } }) })
                .then(function (res) {
                    if (!res.ok) {
                        showError((res.data && res.data.message) ? res.data.message : 'Failed to deprecate current link.')
                        return
                    }
                    applyPayload(res.data)
                })
                .catch(function () {
                    showError('Network error while deprecating link.')
                })
                .finally(function () {
                    if (deprecateBtn) deprecateBtn.disabled = !currentLinkId
                })
        }

        function openModal() {
            if (!modal) return
            modal.classList.remove('hidden')
            loadCurrent()
        }

        function closeModal() {
            if (!modal) return
            modal.classList.add('hidden')
        }

        if (openBtn) openBtn.addEventListener('click', openModal)
        if (closeBtn) closeBtn.addEventListener('click', closeModal)
        if (generateBtn) generateBtn.addEventListener('click', generateNew)
        if (deprecateBtn) deprecateBtn.addEventListener('click', deprecateCurrent)

        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e && e.target === modal) closeModal()
            })
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeModal()
        })
    })
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var accountPanel = document.getElementById('receptionWalkInPanelAccount')
        function accountQuery(selector) {
            return accountPanel ? accountPanel.querySelector(selector) : null
        }

        var form = accountQuery('#receptionWalkInAccountForm')
        var errorBox = accountQuery('#receptionWalkInAccountError')
        var successBox = accountQuery('#receptionWalkInAccountSuccess')
        var submitBtn = accountQuery('#receptionWalkInAccountSubmit')
        var submitSpinner = accountQuery('#receptionWalkInAccountSpinner')
        var submitLabel = accountQuery('#receptionWalkInAccountSubmitLabel')
        var patientSearch = accountQuery('#reception_walkin_patient_search')
        var patientSelect = accountQuery('#reception_walkin_patient_id')
        var patientResults = accountQuery('#receptionWalkInPatientResults')
        var patientPreview = accountQuery('#receptionWalkInPatientPreview')
        var serviceSearch = accountQuery('#reception_walkin_service_search')
        var serviceIdsInput = accountQuery('#reception_walkin_service_ids')
        var serviceResults = accountQuery('#receptionWalkInServiceResults')
        var selectedServicesEl = accountQuery('#receptionWalkInSelectedServices')
        var doctorSearch = accountQuery('#reception_walkin_doctor_search')
        var doctorSelect = accountQuery('#reception_walkin_doctor_id')
        var doctorResults = accountQuery('#receptionWalkInDoctorResults')
        var doctorPreview = accountQuery('#receptionWalkInDoctorPreview')
        var dateSelect = accountQuery('#reception_walkin_date_select')
        var dateInput = accountQuery('#reception_walkin_date')
        var dateLoadMore = accountQuery('#reception_walkin_date_load_more')
        var dateRangeHint = accountQuery('#reception_walkin_date_range_hint')
        var dateWrap = accountQuery('#receptionWalkInDateWrap')
        var dateTrigger = accountQuery('#receptionWalkInDateTrigger')
        var dateOverlay = accountQuery('#receptionWalkInDateOverlay')
        var dateGrid = accountQuery('#receptionWalkInDateGrid')
        var dateList = dateGrid
        var datePrevBtn = accountQuery('#receptionWalkInDatePrev')
        var dateNextBtn = accountQuery('#receptionWalkInDateNext')
        var dateMonthLabel = accountQuery('#receptionWalkInDateMonthLabel')
        var timeInput = accountQuery('#reception_walkin_time')
        var timeWrap = accountQuery('#receptionWalkInTimeWrap')
        var timeTrigger = accountQuery('#receptionWalkInTimeTrigger')
        var timeOverlay = accountQuery('#receptionWalkInTimeOverlay')
        var availableDaysEl = accountQuery('#reception_walkin_available_days')
        var timeSlotsEl = accountQuery('#reception_walkin_time_slots')
        var previousDoctorId = 0
        var previousServiceIds = []
        var previousServiceIdSet = {}
        var services = []
        var doctors = []
        var servicesLoaded = false
        var servicesLoading = false
        var servicesLoadError = ''
        var popularServices = []
        var popularServicesLoaded = false
        var popularServicesLoading = false
        var popularServicesLoadError = ''
        var doctorsLoaded = false
        var doctorsLoading = false
        var doctorsLoadError = ''
        var doctorSchedules = []
        var doctorAvailableDaySet = {}
        var doctorAppointments = []
        var selectedSlotStart = null
        var slotMinutes = 60
        var patientSearchTimer = null
        var selectedPatient = null
        var selectedServices = []
        var selectedDoctor = null
        var dateCursorFirstIso = null
        var dateCursorLastIso = null
        var dateCursorIndex = 0
        var typeInput = accountQuery('#reception_walkin_type')

        function getAppointmentType() {
            return typeInput && typeInput.value ? String(typeInput.value) : 'walk_in'
        }

        function setSubmitting(isSubmitting) {
            if (submitBtn) submitBtn.disabled = !!isSubmitting
            if (submitSpinner) submitSpinner.classList.toggle('hidden', !isSubmitting)
            if (submitLabel) submitLabel.textContent = isSubmitting ? 'Creating…' : 'Create walk-in'
        }

        function showError(message) {
            if (!errorBox) return
            errorBox.textContent = message || ''
            errorBox.classList.toggle('hidden', !message)
        }

        function showSuccess(message) {
            if (!successBox) return
            successBox.textContent = message || ''
            successBox.classList.toggle('hidden', !message)
        }

        function normalizeText(value) {
            return String(value || '').trim().toLowerCase()
        }

        function localDateIso() {
            var now = new Date()
            var y = now.getFullYear()
            var m = String(now.getMonth() + 1).padStart(2, '0')
            var d = String(now.getDate()).padStart(2, '0')
            return y + '-' + m + '-' + d
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

        function patientDisplayName(patient) {
            if (!patient) return ''
            var name = [patient.firstname, patient.middlename, patient.lastname]
                .filter(function (v) { return String(v || '').trim() !== '' })
                .join(' ')
                .trim()
            if (!name) name = 'User #' + (patient.user_id != null ? patient.user_id : '')
            return name
        }

        function setPatientSelection(patient) {
            selectedPatient = patient || null
            if (patientSelect) patientSelect.value = patient && patient.user_id ? String(patient.user_id) : ''
            previousDoctorId = 0
            previousServiceIds = []
            previousServiceIdSet = {}

            if (patientPreview) {
                if (!patient) {
                    patientPreview.textContent = ''
                    patientPreview.classList.add('hidden')
                } else {
                    var parts = []
                    var name = [patient.firstname, patient.middlename, patient.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
                    if (!name) name = 'User #' + patient.user_id
                    parts.push('Name: ' + name)
                    if (patient.birthdate) parts.push('Birthdate: ' + String(patient.birthdate).slice(0, 10))
                    if (patient.contact_number) parts.push('Contact: ' + patient.contact_number)
                    if (patient.address) parts.push('Address: ' + patient.address)
                    patientPreview.textContent = parts.join(' • ')
                    patientPreview.classList.remove('hidden')
                }
            }

            if (patientResults) {
                patientResults.innerHTML = ''
                patientResults.classList.add('hidden')
            }

            if (patient && patient.user_id) {
                loadPreviousProvider(String(patient.user_id))
            }
        }

        function loadPreviousProvider(patientId) {
            if (!patientId || typeof apiFetch !== 'function') return
            apiFetch("{{ url('/api/appointments') }}?patient_id=" + encodeURIComponent(patientId) + "&per_page=1&order=latest", { method: 'GET' })
                .then(function (r) { return readResponse(r) })
                .then(function (res) {
                    if (!res.ok) return
                    var list = res.data && Array.isArray(res.data.data) ? res.data.data : (Array.isArray(res.data) ? res.data : [])
                    var last = list && list.length ? list[0] : null
                    var docId = last && last.doctor_id != null ? parseInt(last.doctor_id, 10) : 0
                    previousDoctorId = (!docId || isNaN(docId)) ? 0 : docId

                    previousServiceIds = []
                    previousServiceIdSet = {}
                    var lastServices = last && Array.isArray(last.services) ? last.services : []
                    lastServices.forEach(function (s) {
                        var sid = s && s.service_id != null ? parseInt(s.service_id, 10) : 0
                        if (!sid || isNaN(sid)) return
                        if (previousServiceIdSet[String(sid)]) return
                        previousServiceIdSet[String(sid)] = true
                        previousServiceIds.push(sid)
                    })

                    if (doctorSearch && doctorResults && !doctorResults.classList.contains('hidden')) {
                        searchDoctors(String(doctorSearch.value || '').trim())
                    }
                    if (serviceSearch && serviceResults && !serviceResults.classList.contains('hidden')) {
                        searchServices(String(serviceSearch.value || '').trim())
                    }
                })
                .catch(function () {})
        }

        function renderPatientResults(items) {
            if (!patientResults) return
            var list = Array.isArray(items) ? items : []
            if (!list.length) {
                patientResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No patients found.</div>'
                patientResults.classList.remove('hidden')
                return
            }

            var html = ''
            list.forEach(function (p) {
                var name = [p.firstname, p.middlename, p.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
                if (!name) name = 'User #' + p.user_id
                var meta = [p.email, p.contact_number].filter(Boolean).join(' • ')
                html += '<button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 border-b border-slate-100 last:border-0">' +
                    '<div class="text-[0.78rem] text-slate-800 font-semibold">' + escapeHtml(name) + '</div>' +
                    '<div class="text-[0.72rem] text-slate-500">' + (meta ? escapeHtml(meta) : '—') + '</div>' +
                '</button>'
            })
            patientResults.innerHTML = html
            patientResults.classList.remove('hidden')

            var buttons = patientResults.querySelectorAll('button')
            Array.prototype.forEach.call(buttons, function (btn, idx) {
                btn.addEventListener('click', function () {
                    var chosen = list[idx]
                    setPatientSelection(chosen)
                    if (patientSearch) patientSearch.value = patientDisplayName(chosen)
                })
            })
        }

        function searchPatients(query) {
            if (typeof apiFetch !== 'function') return
            apiFetch("{{ url('/api/patients') }}?per_page=10&sort=desc&search=" + encodeURIComponent(query), { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    }).catch(function () {
                        return { ok: response.ok, data: null }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        renderPatientResults([])
                        return
                    }
                    var list = []
                    if (result.data && Array.isArray(result.data.data)) {
                        list = result.data.data
                    } else if (Array.isArray(result.data)) {
                        list = result.data
                    }
                    renderPatientResults(list)
                })
                .catch(function () {
                    renderPatientResults([])
                })
        }

        function serviceDisplayName(service) {
            if (!service) return ''
            return String(service.service_name || service.name || '').trim()
        }

        function serviceKey(service) {
            if (!service || service.service_id == null) return ''
            return String(service.service_id)
        }

        function serviceGroup(service) {
            if (!service) return ''
            var name = String(service.service_name || '').trim()
            if (!name) return ''
            var parts = name.split(':')
            var group = String(parts[0] || name).trim().toLowerCase()
            return group
        }

        function isWalkInExcludedService(service) {
            var g = serviceGroup(service)
            return g === 'obsterician - gynecologist' || g === 'obstetrician - gynecologist' || g === 'general surgeon'
        }

        function selectedServiceIds() {
            return (selectedServices || [])
                .map(function (s) { return parseInt(s && s.service_id != null ? s.service_id : 0, 10) })
                .filter(function (id) { return !!id && !isNaN(id) })
        }

        function syncServiceHiddenInput() {
            if (!serviceIdsInput) return
            var ids = selectedServiceIds()
            serviceIdsInput.value = ids.join(',')
        }

        function renderSelectedServices() {
            if (!selectedServicesEl) return
            var list = Array.isArray(selectedServices) ? selectedServices : []
            if (!list.length) {
                selectedServicesEl.innerHTML = '<div class="text-[0.75rem] text-slate-500">No services selected.</div>'
                return
            }

            selectedServicesEl.innerHTML = list.map(function (s) {
                var id = parseInt(s && s.service_id != null ? s.service_id : 0, 10)
                var name = String(s && s.service_name ? s.service_name : '').trim() || 'Service'
                var meta = []
                if (s && s.duration_minutes != null) meta.push(String(s.duration_minutes) + ' min')
                if (s && s.price != null) meta.push('₱' + String(s.price))
                return '' +
                    '<div class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-200 last:border-0">' +
                        '<div class="min-w-0">' +
                            '<div class="text-[0.78rem] text-slate-800 font-semibold truncate">' + escapeHtml(name) + '</div>' +
                            '<div class="text-[0.72rem] text-slate-500">' + escapeHtml(meta.join(' • ') || '—') + '</div>' +
                        '</div>' +
                        '<button type="button" class="reception-remove-service inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 text-slate-600 hover:bg-white" data-service-id="' + escapeHtml(id) + '">' +
                            window.receptionWalkInsIconX +
                        '</button>' +
                    '</div>'
            }).join('')

            var buttons = selectedServicesEl.querySelectorAll('.reception-remove-service')
            Array.prototype.forEach.call(buttons, function (btn) {
                btn.addEventListener('click', function () {
                    var id = parseInt(btn.getAttribute('data-service-id') || '0', 10)
                    if (!id) return
                    selectedServices = (selectedServices || []).filter(function (s) {
                        return parseInt(s && s.service_id != null ? s.service_id : 0, 10) !== id
                    })
                    syncServiceHiddenInput()
                    renderSelectedServices()
                    filterDoctorsByService()
                    searchServices(String(serviceSearch && serviceSearch.value ? serviceSearch.value : '').trim())
                })
            })
        }

        function addService(service) {
            if (!service || service.service_id == null) return
            var key = serviceKey(service)
            if (!key) return
            var exists = (selectedServices || []).some(function (s) { return serviceKey(s) === key })
            if (exists) return

            selectedServices = (selectedServices || []).concat([service])
            syncServiceHiddenInput()
            renderSelectedServices()

            if (serviceResults) {
                serviceResults.innerHTML = ''
                serviceResults.classList.add('hidden')
            }
            if (serviceSearch) serviceSearch.value = ''
        }

        function renderServiceResults(items) {
            if (!serviceResults) return
            var list = Array.isArray(items) ? items : []

            var type = getAppointmentType()
            if (type === 'walk_in') {
                list = list.filter(function (s) { return !isWalkInExcludedService(s) })
            }

            if (selectedServices && selectedServices.length) {
                var base = serviceGroup(selectedServices[0])
                if (base) {
                    list = list.filter(function (s) {
                        return serviceGroup(s) === base
                    })
                }
            }

            if (previousServiceIds && previousServiceIds.length) {
                var order = {}
                previousServiceIds.forEach(function (id, idx) {
                    order[String(id)] = idx
                })
                var pinned = []
                var rest = []
                list.forEach(function (s) {
                    var sid = s && s.service_id != null ? String(s.service_id) : ''
                    if (sid !== '' && order[sid] != null) pinned.push(s)
                    else rest.push(s)
                })
                pinned.sort(function (a, b) {
                    return (order[String(a.service_id)] || 0) - (order[String(b.service_id)] || 0)
                })
                list = pinned.concat(rest)
            }

            if (!list.length) {
                serviceResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No services found.</div>'
                serviceResults.classList.remove('hidden')
                return
            }
            var html = ''
            list.slice(0, 12).forEach(function (s) {
                var name = String(s.service_name || '').trim() || 'Service'
                var isLast = !!(previousServiceIdSet && previousServiceIdSet[String(s.service_id)])
                var tag = isLast
                    ? '<span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[0.65rem] font-semibold bg-amber-50 text-amber-800 border border-amber-200">Last inquired</span>'
                    : ''
                var meta = []
                if (s.duration_minutes != null) meta.push(String(s.duration_minutes) + ' min')
                if (s.price != null) meta.push('₱' + String(s.price))
                var desc = s.description != null ? String(s.description).trim() : ''
                html += '<button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 border-b border-slate-100 last:border-0">' +
                    '<div class="text-[0.78rem] text-slate-800 font-semibold flex items-center justify-between gap-2">' +
                        '<span class="min-w-0 truncate">' + escapeHtml(name) + '</span>' +
                        tag +
                    '</div>' +
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
                    addService(chosen)
                    filterDoctorsByService()
                })
            })
        }

        function searchServices(query) {
            var q = normalizeText(query)
            var list = Array.isArray(services) ? services : []

            if (!q) {
                if (selectedServices && selectedServices.length) {
                    renderServiceResults(list)
                    return
                }

                var popularList = Array.isArray(popularServices) ? popularServices : []
                if (popularServicesLoaded && popularList.length) {
                    renderServiceResults(popularList.slice(0, 10))
                    return
                }

                if (servicesLoaded && list.length) {
                    renderServiceResults(list.slice(0, 10))
                    return
                }

                if (serviceResults) {
                    var isLoading = servicesLoading || popularServicesLoading
                    var hasLoadError = !isLoading && !servicesLoaded && !!servicesLoadError
                    var message = hasLoadError ? servicesLoadError : (isLoading ? 'Loading services…' : 'No services available.')
                    serviceResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">' + escapeHtml(message) + '</div>'
                    serviceResults.classList.remove('hidden')
                }
                return
            }
            var filtered = list.filter(function (s) {
                var name = normalizeText(s && s.service_name ? s.service_name : '')
                return wordPrefixMatch(name, q)
            })
            renderServiceResults(filtered)
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
            if (doctorSelect) doctorSelect.value = doctor && doctor.user_id ? String(doctor.user_id) : ''

            if (doctorPreview) {
                if (!doctor) {
                    doctorPreview.textContent = ''
                    doctorPreview.classList.add('hidden')
                } else {
                    var parts = []
                    var name = [doctor.firstname, doctor.middlename, doctor.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
                    if (!name) name = 'Doctor #' + doctor.user_id
                    var type = getAppointmentType()
                    var dateStr = type === 'walk_in'
                        ? localDateIso()
                        : ((dateSelect && dateSelect.value) ? String(dateSelect.value) : localDateIso())
                    var dayKey = dayKeyFromDate(dateStr)
                    var checkTime = type === 'walk_in'
                        ? new Date().toTimeString().slice(0, 5)
                        : (selectedSlotStart ? String(selectedSlotStart).slice(0, 5) : '')
                    var hasSchedule = !!dayKey && hasScheduleAtTime(doctor, dayKey, dateStr, checkTime)
                    var categories = (selectedServices || [])
                        .map(function (s) { return extractServiceCategory(s && s.service_name ? s.service_name : '') })
                        .filter(function (c) { return !!c })
                    var spec = doctor && doctor.specialization ? doctor.specialization : ''
                    var matchesService = !categories.length || categories.every(function (c) { return specializationMatches(c, spec) })

                    parts.push('Name: ' + name)
                    if (doctor.specialization) parts.push('Specialization: ' + doctor.specialization)
                    if (previousDoctorId && parseInt(doctor.user_id, 10) === previousDoctorId) parts.push('Last provider')
                    parts.push('Availability: ' + ((doctor.is_available !== false && hasSchedule) ? 'Available' : 'Unavailable'))
                    if (categories.length) parts.push('Service match: ' + (matchesService ? 'Yes' : 'No'))
                    doctorPreview.textContent = parts.join(' • ')
                    doctorPreview.classList.remove('hidden')
                }
            }

            if (doctorResults) {
                doctorResults.innerHTML = ''
                doctorResults.classList.add('hidden')
            }

            var type = getAppointmentType()
            if (type !== 'walk_in') {
                clearAvailability()
                if (doctor && doctor.user_id) {
                    loadDoctorSchedulesAndAvailability(String(doctor.user_id), null)
                } else {
                    renderTimeSlots()
                }
            } else {
                renderTimeSlots()
            }
        }

        function doctorSchedulesForDay(doctor, dayKey, dateStr) {
            var list = doctor && doctor.doctor_schedules && Array.isArray(doctor.doctor_schedules) ? doctor.doctor_schedules : []
            var isToday = false
            if (dateStr) {
                var today = localDateIso()
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

        function renderDoctorResults(items) {
            if (!doctorResults) return
            var list = Array.isArray(items) ? items : []
            if (!list.length) {
                doctorResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No doctors found.</div>'
                doctorResults.classList.remove('hidden')
                return
            }
            var type = getAppointmentType()
            var dateStr = ''
            if (type === 'walk_in') {
                dateStr = localDateIso()
            } else {
                dateStr = (dateSelect && dateSelect.value) ? String(dateSelect.value) : localDateIso()
            }
            var dayKey = dayKeyFromDate(dateStr)
            var checkTime = ''
            if (type === 'walk_in') {
                checkTime = new Date().toTimeString().slice(0, 5)
            } else if (selectedSlotStart) {
                checkTime = String(selectedSlotStart).slice(0, 5)
            }

            var enriched = list.map(function (d) {
                var name = [d.firstname, d.middlename, d.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
                if (!name) name = 'Doctor #' + d.user_id
                var spec = d && d.specialization ? String(d.specialization) : ''
                var isDoctorAvailable = d && d.is_available !== false
                var hasSchedule = !!dayKey && hasScheduleAtTime(d, dayKey, dateStr, checkTime)
                var isSelectable = isDoctorAvailable && hasSchedule
                var tag = ''
                if (!isDoctorAvailable) tag = 'Unavailable'
                else if (!hasSchedule) tag = 'No schedule on this time'
                else if (previousDoctorId && parseInt(d.user_id, 10) === previousDoctorId) tag = 'Last provider'
                return {
                    d: d,
                    name: name,
                    spec: spec,
                    isSelectable: isSelectable,
                    tag: tag,
                }
            })

            enriched.sort(function (a, b) {
                if (a.isSelectable !== b.isSelectable) return a.isSelectable ? -1 : 1
                if ((a.tag === 'Last provider') !== (b.tag === 'Last provider')) return a.tag === 'Last provider' ? -1 : 1
                return normalizeText(a.name).localeCompare(normalizeText(b.name))
            })

            var html = ''
            enriched.forEach(function (x) {
                var d = x.d
                var meta = [x.spec].filter(Boolean).join(' • ')
                html += '<button type="button" class="w-full text-left px-3 py-2 border-b border-slate-100 last:border-0 flex items-start justify-between gap-3 ' + (x.isSelectable ? 'hover:bg-slate-50' : 'bg-slate-50/60 cursor-not-allowed') + '" ' + (x.isSelectable ? '' : 'disabled') + '>' +
                    '<div class="min-w-0">' +
                        '<div class="text-[0.78rem] text-slate-800 font-semibold">' + escapeHtml('Dr. ' + x.name) + '</div>' +
                        '<div class="text-[0.72rem] text-slate-500">#' + escapeHtml(d.user_id) + (meta ? ' • ' + escapeHtml(meta) : '') + '</div>' +
                    '</div>' +
                    (x.tag
                        ? '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.65rem] font-semibold ' + (x.tag === 'Last provider' ? 'bg-cyan-500/10 text-cyan-700 border border-cyan-200' : 'bg-slate-100 text-slate-500 border border-slate-200') + '">' + escapeHtml(x.tag) + '</span>'
                        : '') +
                '</button>'
            })
            doctorResults.innerHTML = html
            doctorResults.classList.remove('hidden')

            var buttons = doctorResults.querySelectorAll('button')
            Array.prototype.forEach.call(buttons, function (btn, idx) {
                btn.addEventListener('click', function () {
                    var chosen = enriched[idx] ? enriched[idx].d : null
                    if (!chosen) return
                    setDoctorSelection(chosen)
                    if (doctorSearch) doctorSearch.value = doctorDisplayName(chosen)
                })
            })
        }

        function filterDoctorsByService() {
            var list = Array.isArray(doctors) ? doctors : []
            if (!selectedServices || !selectedServices.length) {
                if (doctorSearch) doctorSearch.disabled = true
                setDoctorSelection(null)
                if (doctorSearch) doctorSearch.value = ''
                if (doctorResults) doctorResults.classList.add('hidden')
                return
            }
            var categories = (selectedServices || [])
                .map(function (s) { return extractServiceCategory(s && s.service_name ? s.service_name : '') })
                .filter(function (c) { return !!c })

            var filtered = list.filter(function (d) {
                var spec = d && d.specialization ? d.specialization : ''
                return categories.every(function (c) { return specializationMatches(c, spec) })
            })
            if (doctorSearch) doctorSearch.disabled = false
            if (filtered.length === 1) {
                var candidate = filtered[0]
                var type = getAppointmentType()
                var dateStr = type === 'walk_in'
                    ? localDateIso()
                    : ((dateSelect && dateSelect.value) ? String(dateSelect.value) : localDateIso())
                var dayKey = dayKeyFromDate(dateStr)
                var checkTime = type === 'walk_in' ? new Date().toTimeString().slice(0, 5) : (selectedSlotStart ? String(selectedSlotStart).slice(0, 5) : '')
                var isSelectable = candidate && candidate.is_available !== false && !!dayKey && hasScheduleAtTime(candidate, dayKey, dateStr, checkTime)
                if (isSelectable) {
                    setDoctorSelection(candidate)
                    if (doctorSearch) doctorSearch.value = doctorDisplayName(candidate)
                } else {
                    setDoctorSelection(null)
                    if (doctorSearch) doctorSearch.value = ''
                }
            } else {
                if (selectedDoctor) {
                    var stillOk = filtered.some(function (d) { return String(d.user_id) === String(selectedDoctor.user_id) })
                    if (!stillOk) {
                        setDoctorSelection(null)
                        if (doctorSearch) doctorSearch.value = ''
                    }
                }
            }
        }

        function searchDoctors(query) {
            var q = normalizeText(query)
            var list = Array.isArray(doctors) ? doctors : []
            if (selectedServices && selectedServices.length) {
                var categories = (selectedServices || [])
                    .map(function (s) { return extractServiceCategory(s && s.service_name ? s.service_name : '') })
                    .filter(function (c) { return !!c })
                list = list.filter(function (d) {
                    var spec = d && d.specialization ? d.specialization : ''
                    return categories.every(function (c) { return specializationMatches(c, spec) })
                })
            }
            if (!q) {
                renderDoctorResults(list.slice(0, 30))
                return
            }
            var filtered = list.filter(function (d) {
                var name = normalizeText([d.firstname, d.middlename, d.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' '))
                var spec = normalizeText(d && d.specialization ? d.specialization : '')
                return wordPrefixMatch(name, q) || wordPrefixMatch(spec, q)
            })
            renderDoctorResults(filtered.slice(0, 30))
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

        function formatTime12h(hhmmss) {
            var t = String(hhmmss || '').slice(0, 5)
            if (!/^\d{2}:\d{2}$/.test(t)) return t
            var parts = t.split(':')
            var h24 = parseInt(parts[0], 10)
            var m = parts[1]
            var ap = h24 >= 12 ? 'PM' : 'AM'
            var h12 = h24 % 12
            if (h12 === 0) h12 = 12
            return h12 + ':' + m + ' ' + ap
        }

        function readResponse(response) {
            return response.text().then(function (text) {
                var data = null
                try {
                    data = text ? JSON.parse(text) : null
                } catch (e) {
                    data = null
                }
                return { ok: response.ok, status: response.status, data: data, raw: text }
            })
        }

        function isoDate(d) {
            var yr = d.getFullYear()
            var mo = String(d.getMonth() + 1).padStart(2, '0')
            var da = String(d.getDate()).padStart(2, '0')
            return yr + '-' + mo + '-' + da
        }

        function resetDateCursor() {
            var now = new Date()
            dateCursorFirstIso = isoDate(now)
            var end = new Date(now.getTime() + (1000 * 60 * 60 * 24 * 365))
            dateCursorLastIso = isoDate(end)
            dateCursorIndex = 0
        }

        function appendAllowedDates(daysToAdd) {
            if (!dateSelect) return
            var allowedKeys = doctorAvailableDaySet && Object.keys(doctorAvailableDaySet).length ? doctorAvailableDaySet : null
            if (!allowedKeys) return
            var start = new Date(dateCursorFirstIso + 'T00:00:00')
            if (isNaN(start.getTime())) return
            var added = 0
            var limit = parseInt(daysToAdd || 0, 10)
            if (isNaN(limit) || limit <= 0) limit = 60

            for (var i = 0; i < limit * 3 && added < limit; i++) {
                var d = new Date(start.getTime() + (1000 * 60 * 60 * 24 * (dateCursorIndex + i)))
                if (d.getTime() > new Date(dateCursorLastIso + 'T00:00:00').getTime()) break
                var key = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'][d.getDay()] || ''
                if (!key || !allowedKeys[key]) continue
                var value = isoDate(d)
                if (dateSelect.querySelector('option[value="' + value + '"]')) continue
                var opt = document.createElement('option')
                opt.value = value
                opt.textContent = value
                dateSelect.appendChild(opt)
                added += 1
            }

            dateCursorIndex += limit
            if (dateLoadMore) dateLoadMore.classList.toggle('hidden', dateSelect.options.length <= 1)
            if (dateRangeHint) {
                dateRangeHint.textContent = dateCursorFirstIso + ' → ' + dateCursorLastIso
                dateRangeHint.classList.remove('hidden')
            }

            syncDateRollerFromSelect()
        }

        function friendlyDateLabelFromIso(iso) {
            var datePart = String(iso || '').slice(0, 10)
            if (!/^\d{4}-\d{2}-\d{2}$/.test(datePart)) return datePart || 'Select a date'
            var d = new Date(datePart + 'T00:00:00')
            if (isNaN(d.getTime())) return datePart
            var parts = d.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: '2-digit' })
            return parts + ' · ' + datePart
        }

        function closeDateOverlay() {
            if (dateOverlay) dateOverlay.classList.add('hidden')
        }

        function closeTimeOverlay() {
            if (timeOverlay) timeOverlay.classList.add('hidden')
        }

        function syncDateRollerFromSelect() {
            if (!dateSelect || !dateTrigger) return
            dateTrigger.disabled = !!dateSelect.disabled

            var selected = dateSelect.value ? String(dateSelect.value) : ''
            if (dateSelect.disabled) {
                dateTrigger.textContent = 'Select a doctor first'
            } else if (selected) {
                dateTrigger.textContent = friendlyDateLabelFromIso(selected)
            } else {
                dateTrigger.textContent = 'Select a date'
            }

            if (!dateList) return
            var html = ''
            var opts = Array.prototype.slice.call(dateSelect.options || [])
            var usable = opts.filter(function (o) { return o && o.value })
            if (!usable.length) {
                html = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No dates available.</div>'
            } else {
                html = usable.map(function (o) {
                    var isActive = selected && String(o.value) === selected
                    return (
                        '<button type="button" class="w-full text-left px-3 py-2 text-[0.78rem] snap-start ' +
                        (isActive ? 'bg-cyan-50 text-cyan-800 font-semibold' : 'text-slate-700 hover:bg-slate-50') +
                        '" data-date="' + escapeHtml(o.value) + '">' + escapeHtml(o.textContent || o.value) + '</button>'
                    )
                }).join('')
            }
            dateList.innerHTML = html
        }

        function populateAllowedDates() {
            if (!dateSelect) return
            dateSelect.innerHTML = ''
            var placeholder = document.createElement('option')
            placeholder.value = ''
            placeholder.textContent = 'Select a date'
            dateSelect.appendChild(placeholder)

            resetDateCursor()
            var allowedKeys = doctorAvailableDaySet && Object.keys(doctorAvailableDaySet).length ? doctorAvailableDaySet : null
            if (!allowedKeys) {
                var opt = document.createElement('option')
                opt.value = ''
                opt.textContent = 'No available schedule days'
                dateSelect.appendChild(opt)
                dateSelect.disabled = false
                if (dateLoadMore) dateLoadMore.classList.add('hidden')
                if (dateRangeHint) {
                    dateRangeHint.textContent = ''
                    dateRangeHint.classList.add('hidden')
                }
                syncDateRollerFromSelect()
                return
            }
            appendAllowedDates(60)
            dateSelect.disabled = false
            if (dateSelect.options && dateSelect.options.length <= 1) {
                var none = document.createElement('option')
                none.value = ''
                none.textContent = 'No available dates in range'
                dateSelect.appendChild(none)
            }
            syncDateRollerFromSelect()
        }

        function renderAvailableDays() {
            if (!availableDaysEl) return
            if (!doctorSchedules || !doctorSchedules.length) {
                availableDaysEl.textContent = ''
                return
            }
            var days = {}
            doctorSchedules.forEach(function (s) {
                if (!s) return
                var k = String(s.day_of_week || '').toLowerCase()
                if (!k) return
                days[k] = true
            })
            doctorAvailableDaySet = days
            var order = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']
            var labels = { mon: 'Mon', tue: 'Tue', wed: 'Wed', thu: 'Thu', fri: 'Fri', sat: 'Sat', sun: 'Sun' }
            var list = order.filter(function (k) { return !!days[k] }).map(function (k) { return labels[k] || k })
            availableDaysEl.textContent = list.length ? ('Available: ' + list.join(', ')) : ''
        }

        function clearAvailability() {
            doctorSchedules = []
            doctorAvailableDaySet = {}
            doctorAppointments = []
            selectedSlotStart = null
            if (timeInput) timeInput.value = ''
            if (dateSelect) {
                if (selectedDoctor && selectedDoctor.user_id) {
                    dateSelect.innerHTML = '<option value="">Loading available dates…</option>'
                    dateSelect.disabled = false
                } else {
                    dateSelect.innerHTML = '<option value="">Select a doctor first</option>'
                    dateSelect.disabled = true
                }
            }
            syncDateRollerFromSelect()
            if (dateLoadMore) dateLoadMore.classList.add('hidden')
            if (dateRangeHint) {
                dateRangeHint.textContent = ''
                dateRangeHint.classList.add('hidden')
            }

            if (timeTrigger) {
                timeTrigger.disabled = true
                timeTrigger.textContent = 'Select a date first'
            }
            closeTimeOverlay()
        }

        function hhmmFromMinutes(mins) {
            var m = parseInt(mins, 10)
            if (isNaN(m)) return ''
            var hh = Math.floor(m / 60)
            var mm = m % 60
            var hhStr = String(hh).padStart(2, '0')
            var mmStr = String(mm).padStart(2, '0')
            return hhStr + ':' + mmStr
        }

        function renderTimeSlots() {
            if (!timeSlotsEl) return
            timeSlotsEl.innerHTML = ''

            var apptType = getAppointmentType()
            if (apptType === 'walk_in') {
                if (timeTrigger) {
                    timeTrigger.disabled = true
                    timeTrigger.textContent = 'Walk-in does not require a time slot'
                }
                closeTimeOverlay()
                timeSlotsEl.innerHTML = '<div class="text-[0.7rem] text-slate-400">Walk-in visits do not require a time slot.</div>'
                return
            }

            if (!doctorSelect || !doctorSelect.value) {
                if (timeTrigger) {
                    timeTrigger.disabled = true
                    timeTrigger.textContent = 'Select a doctor first'
                }
                closeTimeOverlay()
                timeSlotsEl.innerHTML = '<div class="text-[0.7rem] text-slate-400">Select a doctor to load time slots.</div>'
                return
            }
            if (!dateInput || !dateInput.value) {
                if (timeTrigger) {
                    timeTrigger.disabled = true
                    timeTrigger.textContent = 'Select a date first'
                }
                closeTimeOverlay()
                timeSlotsEl.innerHTML = '<div class="text-[0.7rem] text-slate-400">Select a date to load time slots.</div>'
                return
            }
            if (!doctorSchedules.length) {
                if (timeTrigger) {
                    timeTrigger.disabled = true
                    timeTrigger.textContent = 'No schedules found'
                }
                closeTimeOverlay()
                timeSlotsEl.innerHTML = '<div class="text-[0.7rem] text-slate-400">No schedules found for this doctor.</div>'
                return
            }

            var dayKey = dayKeyFromDate(dateInput.value)
            if (!dayKey) {
                if (timeTrigger) {
                    timeTrigger.disabled = true
                    timeTrigger.textContent = 'Invalid date'
                }
                closeTimeOverlay()
                timeSlotsEl.innerHTML = '<div class="text-[0.7rem] text-slate-400">Invalid date selected.</div>'
                return
            }
            if (doctorAvailableDaySet && Object.keys(doctorAvailableDaySet).length && !doctorAvailableDaySet[dayKey]) {
                if (timeTrigger) {
                    timeTrigger.disabled = true
                    timeTrigger.textContent = 'Doctor not available'
                }
                closeTimeOverlay()
                timeSlotsEl.innerHTML = '<div class="text-[0.7rem] text-slate-400">Doctor is not available on this day.</div>'
                return
            }

            var todayIso = localDateIso()
            var isToday = String(dateInput.value) === todayIso
            var daySchedules = doctorSchedules.filter(function (s) {
                if (!s) return false
                if (String(s.day_of_week || '').toLowerCase() !== dayKey) return false
                if (isToday && s.is_available === false) return false
                return true
            })

            if (!daySchedules.length) {
                if (timeTrigger) {
                    timeTrigger.disabled = true
                    timeTrigger.textContent = 'No available slots'
                }
                closeTimeOverlay()
                timeSlotsEl.innerHTML = '<div class="text-[0.7rem] text-slate-400">Doctor has no available slots on this day.</div>'
                return
            }

            daySchedules.sort(function (a, b) {
                var sa = minutesFromHHMM(String(a.start_time || ''))
                var sb = minutesFromHHMM(String(b.start_time || ''))
                if (isNaN(sa) || isNaN(sb)) return 0
                return sa - sb
            })

            var intervals = []
            daySchedules.forEach(function (s) {
                var st = minutesFromHHMM(String(s.start_time || ''))
                var en = minutesFromHHMM(String(s.end_time || ''))
                if (isNaN(st) || isNaN(en) || en <= st) return
                intervals.push({ start: st, end: en })
            })
            intervals.sort(function (a, b) { return a.start - b.start })
            var merged = []
            intervals.forEach(function (i) {
                var last = merged.length ? merged[merged.length - 1] : null
                if (!last) {
                    merged.push({ start: i.start, end: i.end })
                    return
                }
                if (i.start <= last.end) {
                    last.end = Math.max(last.end, i.end)
                    return
                }
                merged.push({ start: i.start, end: i.end })
            })

            var appts = Array.isArray(doctorAppointments) ? doctorAppointments : []
            var bookedSet = {}
            appts.forEach(function (a) {
                if (!a || !a.appointment_datetime) return
                if (String(a.status || '').toLowerCase() === 'cancelled') return
                if (a.appointment_type && String(a.appointment_type) !== 'scheduled') return
                var dt = String(a.appointment_datetime).replace('T', ' ').slice(0, 16)
                if (!/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/.test(dt)) return
                var datePart = dt.slice(0, 10)
                var timePart = dt.slice(11, 16)
                if (datePart !== dateInput.value) return
                bookedSet[timePart] = true
            })

            var slots = []
            merged.forEach(function (block) {
                for (var m = block.start; m + slotMinutes <= block.end; m += slotMinutes) {
                    slots.push({ start: m, end: m + slotMinutes })
                }
            })

            if (!slots.length) {
                if (timeTrigger) {
                    timeTrigger.disabled = true
                    timeTrigger.textContent = 'No time slots available'
                }
                closeTimeOverlay()
                timeSlotsEl.innerHTML = '<div class="text-[0.7rem] text-slate-400">No time slots available for this day.</div>'
                return
            }

            if (timeTrigger) {
                timeTrigger.disabled = false
                timeTrigger.textContent = selectedSlotStart ? ('Selected: ' + formatTime12h(selectedSlotStart)) : 'Select a time slot'
            }

            slots.forEach(function (slot) {
                var startHHMM = hhmmFromMinutes(slot.start)
                var endHHMM = hhmmFromMinutes(slot.end)
                var isBooked = !!bookedSet[startHHMM]
                var isSelected = String(selectedSlotStart || '') === startHHMM
                var label = formatTime12h(startHHMM) + '–' + formatTime12h(endHHMM)

                var btn = document.createElement('button')
                btn.type = 'button'
                btn.className =
                    'w-full px-3 py-2 rounded-xl text-[0.75rem] font-semibold border transition-colors flex items-center justify-between ' +
                    (isBooked
                        ? 'border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed'
                        : (isSelected
                            ? 'border-cyan-600 bg-cyan-600 text-white'
                            : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'))
                btn.disabled = !!isBooked
                btn.textContent = label + (isBooked ? ' · Booked' : '')
                btn.addEventListener('click', function () {
                    selectedSlotStart = startHHMM
                    if (timeInput) timeInput.value = startHHMM
                    closeTimeOverlay()
                    renderTimeSlots()
                })
                timeSlotsEl.appendChild(btn)
            })
        }

        function loadDoctorSchedulesAndAvailability(doctorId, dateStr) {
            if (!doctorId || typeof apiFetch !== 'function') return
            clearAvailability()
            apiFetch("{{ url('/api/doctor-schedules') }}?doctor_id=" + encodeURIComponent(doctorId) + "&per_page=100", { method: 'GET' })
                .then(function (response) { return readResponse(response) })
                .then(function (result) {
                    if (!result.ok) {
                        var msg = (result.data && result.data.message) ? String(result.data.message) : 'Failed to load doctor schedules.'
                        if (result.status === 401) msg = 'Session expired. Please log in again.'
                        if (result.status === 403) msg = 'Forbidden (403). Your account does not have permission to view this doctor’s schedules.'
                        showError(msg)
                        if ((!doctorSchedules || !doctorSchedules.length) && dateSelect) {
                            dateSelect.innerHTML = '<option value="">Failed to load schedules</option>'
                            dateSelect.disabled = false
                        }
                        renderAvailableDays()
                        populateAllowedDates()
                        renderTimeSlots()
                        return
                    }

                    var raw = result.data && Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    doctorSchedules = raw || []
                    renderAvailableDays()
                    populateAllowedDates()
                    if (dateSelect) dateSelect.disabled = false
                    if (dateStr) {
                        loadDoctorAppointments(doctorId, dateStr)
                    } else {
                        renderTimeSlots()
                    }
                })
                .catch(function () {
                    showError('Network error while loading doctor schedules.')
                    if ((!doctorSchedules || !doctorSchedules.length) && dateSelect) {
                        dateSelect.innerHTML = '<option value="">Network error loading schedules</option>'
                        dateSelect.disabled = false
                    }
                    renderAvailableDays()
                    populateAllowedDates()
                    renderTimeSlots()
                })
        }

        function loadDoctorAppointments(doctorId, dateStr) {
            if (!doctorId || !dateStr || typeof apiFetch !== 'function') return
            apiFetch("{{ url('/api/appointments') }}?doctor_id=" + encodeURIComponent(doctorId) + "&start_date=" + encodeURIComponent(dateStr) + "&end_date=" + encodeURIComponent(dateStr) + "&per_page=200", { method: 'GET' })
                .then(function (response) { return readResponse(response) })
                .then(function (result) {
                    var raw = result.data && Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    doctorAppointments = raw || []
                    renderTimeSlots()
                })
                .catch(function () { renderTimeSlots() })
        }

        function onDateChanged() {
            showError('')
            showSuccess('')
            selectedSlotStart = null
            if (timeInput) timeInput.value = ''
            syncDateRollerFromSelect()
            closeDateOverlay()
            closeTimeOverlay()
            if (!doctorSelect || !doctorSelect.value) {
                renderTimeSlots()
                return
            }
            var dateStr = dateSelect && dateSelect.value ? dateSelect.value : ''
            if (!dateStr) {
                if (dateInput) dateInput.value = ''
                renderTimeSlots()
                return
            }
            if (dateInput) dateInput.value = dateStr
            loadDoctorAppointments(doctorSelect.value, dateStr)
        }

        function loadServicesAndDoctors() {
            if (typeof apiFetch !== 'function') return
            if (!servicesLoaded && !servicesLoading) {
                servicesLoading = true
                servicesLoadError = ''
                apiFetch("{{ url('/api/services') }}?per_page=100", { method: 'GET' })
                    .then(function (r) { return readResponse(r) })
                    .then(function (res) {
                        if (res.ok) {
                            services = res.data && Array.isArray(res.data.data) ? res.data.data : (Array.isArray(res.data) ? res.data : [])
                            servicesLoaded = true
                            if (serviceSearch && document.activeElement === serviceSearch) {
                                searchServices(String(serviceSearch.value || '').trim())
                            }
                        } else {
                            servicesLoadError = (res.data && res.data.message) ? String(res.data.message) : 'Failed to load services.'
                        }
                    })
                    .catch(function () { servicesLoadError = 'Failed to load services.' })
                    .finally(function () { servicesLoading = false })
            }
            if (!popularServicesLoaded && !popularServicesLoading) {
                popularServicesLoading = true
                popularServicesLoadError = ''
                apiFetch("{{ url('/api/services-popular') }}?limit=10", { method: 'GET' })
                    .then(function (r) { return readResponse(r) })
                    .then(function (res) {
                        if (res.ok) {
                            popularServices = Array.isArray(res.data) ? res.data : (res.data && Array.isArray(res.data.data) ? res.data.data : [])
                            popularServicesLoaded = true
                        } else {
                            popularServicesLoadError = (res.data && res.data.message) ? String(res.data.message) : 'Failed to load popular services.'
                        }
                    })
                    .catch(function () { popularServicesLoadError = 'Failed to load popular services.' })
                    .finally(function () { popularServicesLoading = false })
            }
            if (!doctorsLoaded && !doctorsLoading) {
                doctorsLoading = true
                doctorsLoadError = ''
                apiFetch("{{ url('/api/doctors') }}?per_page=100", { method: 'GET' })
                    .then(function (r) { return readResponse(r) })
                    .then(function (res) {
                        if (res.ok) {
                            doctors = res.data && Array.isArray(res.data.data) ? res.data.data : (Array.isArray(res.data) ? res.data : [])
                            doctorsLoaded = true
                            filterDoctorsByService()
                            if (doctorSearch && document.activeElement === doctorSearch) {
                                searchDoctors(String(doctorSearch.value || '').trim())
                            }
                        } else {
                            doctorsLoadError = (res.data && res.data.message) ? String(res.data.message) : 'Failed to load doctors.'
                        }
                    })
                    .catch(function () { doctorsLoadError = 'Failed to load doctors.' })
                    .finally(function () { doctorsLoading = false })
            }
        }

        var typeScheduledBtn = accountQuery('#receptionWalkInTypeScheduledBtn')
        var typeWalkInBtn = accountQuery('#receptionWalkInTypeWalkInBtn')

        function setTypeButtonState(btn, isActive) {
            if (!btn) return
            btn.classList.toggle('bg-white', isActive)
            btn.classList.toggle('text-slate-900', isActive)
            btn.classList.toggle('shadow-sm', isActive)
            btn.classList.toggle('border', isActive)
            btn.classList.toggle('border-slate-200', isActive)
            btn.classList.toggle('bg-transparent', !isActive)
            btn.classList.toggle('text-slate-600', !isActive)
        }

        function syncTypeToggleUI() {
            if (typeScheduledBtn) typeScheduledBtn.textContent = 'Scheduled'
            if (typeWalkInBtn) typeWalkInBtn.textContent = 'Walk-in'
            var type = typeInput && typeInput.value ? typeInput.value : 'walk_in'
            setTypeButtonState(typeScheduledBtn, type === 'scheduled')
            setTypeButtonState(typeWalkInBtn, type === 'walk_in')
        }

        function applyAppointmentTypeUI() {
            var type = typeInput && typeInput.value ? String(typeInput.value) : 'walk_in'
            var isWalkIn = type === 'walk_in'
            if (dateWrap) dateWrap.classList.toggle('hidden', isWalkIn)
            if (timeWrap) timeWrap.classList.toggle('hidden', isWalkIn)
            if (dateSelect) {
                dateSelect.required = !isWalkIn
                dateSelect.disabled = isWalkIn || !doctorSelect || !doctorSelect.value
            }
            if (dateLoadMore) {
                var canShowMore = !isWalkIn && !!(doctorSelect && doctorSelect.value) && !!(doctorAvailableDaySet && Object.keys(doctorAvailableDaySet).length)
                dateLoadMore.classList.toggle('hidden', !canShowMore)
            }
            if (dateRangeHint) {
                var canShowHint = !isWalkIn && !!(doctorSelect && doctorSelect.value) && !!(dateCursorFirstIso && dateCursorLastIso)
                dateRangeHint.classList.toggle('hidden', !canShowHint)
            }
            if (dateInput) dateInput.required = false
            if (timeInput) timeInput.required = !isWalkIn
            if (isWalkIn) {
                if (dateSelect) dateSelect.value = ''
                if (dateInput) dateInput.value = ''
                if (timeInput) timeInput.value = ''
                selectedSlotStart = null
                var filteredServices = (selectedServices || []).filter(function (s) { return !isWalkInExcludedService(s) })
                if (filteredServices.length !== (selectedServices || []).length) {
                    selectedServices = filteredServices
                    syncServiceHiddenInput()
                    renderSelectedServices()
                    filterDoctorsByService()
                }
            } else {
                if (doctorSelect && doctorSelect.value && (!doctorSchedules || !doctorSchedules.length)) {
                    clearAvailability()
                    loadDoctorSchedulesAndAvailability(String(doctorSelect.value), null)
                }
            }
            renderTimeSlots()
        }

        function setAppointmentType(nextType) {
            if (typeInput) typeInput.value = nextType === 'scheduled' ? 'scheduled' : 'walk_in'
            applyAppointmentTypeUI()
            syncTypeToggleUI()
        }

        if (typeScheduledBtn) typeScheduledBtn.addEventListener('click', function () { setAppointmentType('scheduled') })
        if (typeWalkInBtn) typeWalkInBtn.addEventListener('click', function () { setAppointmentType('walk_in') })

        if (patientSearch) {
            patientSearch.addEventListener('input', function () {
                var q = String(patientSearch.value || '').trim()
                if (selectedPatient) setPatientSelection(null)
                if (patientSearchTimer) clearTimeout(patientSearchTimer)
                patientSearchTimer = setTimeout(function () {
                    searchPatients(q)
                }, 250)
            })
            patientSearch.addEventListener('focus', function () {
                var q = String(patientSearch.value || '').trim()
                if (q) {
                    searchPatients(q)
                } else {
                    searchPatients('')
                }
            })
        }

        if (serviceSearch) {
            serviceSearch.addEventListener('input', function () {
                loadServicesAndDoctors()
                var q = String(serviceSearch.value || '').trim()
                searchServices(q)
            })
            serviceSearch.addEventListener('focus', function () {
                loadServicesAndDoctors()
                var q = String(serviceSearch.value || '').trim()
                searchServices(q)
            })
        }

        if (doctorSearch) {
            doctorSearch.addEventListener('input', function () {
                loadServicesAndDoctors()
                var q = String(doctorSearch.value || '').trim()
                if (selectedDoctor) setDoctorSelection(null)
                searchDoctors(q)
            })
            doctorSearch.addEventListener('focus', function () {
                loadServicesAndDoctors()
                var q = String(doctorSearch.value || '').trim()
                searchDoctors(q)
            })
        }

        document.addEventListener('click', function (e) {
            var target = e && e.target ? e.target : null
            if (patientResults && !patientResults.classList.contains('hidden')) {
                if (!(patientResults.contains(target) || (patientSearch && patientSearch.contains(target)))) {
                    patientResults.classList.add('hidden')
                }
            }
            if (serviceResults && !serviceResults.classList.contains('hidden')) {
                if (!(serviceResults.contains(target) || (serviceSearch && serviceSearch.contains(target)))) {
                    serviceResults.classList.add('hidden')
                }
            }
            if (doctorResults && !doctorResults.classList.contains('hidden')) {
                if (!(doctorResults.contains(target) || (doctorSearch && doctorSearch.contains(target)))) {
                    doctorResults.classList.add('hidden')
                }
            }
        })

        if (dateSelect) {
            dateSelect.addEventListener('change', onDateChanged)
            if (dateLoadMore) {
                dateLoadMore.addEventListener('click', function () {
                    if (dateLoadMore.disabled) return
                    appendAllowedDates(60)
                })
            }
            syncDateRollerFromSelect()
        }

        if (dateTrigger) {
            dateTrigger.addEventListener('click', function () {
                if (!dateSelect || dateSelect.disabled) return
                if (!dateOverlay) return
                syncDateRollerFromSelect()
                dateOverlay.classList.toggle('hidden')
            })
        }

        if (dateList) {
            dateList.addEventListener('click', function (e) {
                var btn = e.target && e.target.closest ? e.target.closest('button[data-date]') : null
                if (!btn || !dateSelect) return
                var iso = btn.getAttribute('data-date') || ''
                if (!iso) return
                dateSelect.value = iso
                dateSelect.dispatchEvent(new Event('change', { bubbles: true }))
            })
        }

        if (timeTrigger) {
            timeTrigger.addEventListener('click', function () {
                if (timeTrigger.disabled) return
                if (!timeOverlay) return
                renderTimeSlots()
                timeOverlay.classList.toggle('hidden')
            })
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDateOverlay()
                closeTimeOverlay()
            }
        })

        loadServicesAndDoctors()
        syncServiceHiddenInput()
        renderSelectedServices()
        if (typeInput && !typeInput.value) typeInput.value = 'walk_in'
        syncTypeToggleUI()
        applyAppointmentTypeUI()

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault()

                showError('')
                showSuccess('')
                setSubmitting(true)

                var patientInput = accountQuery('#reception_walkin_patient_id')
                var doctorInput = accountQuery('#reception_walkin_doctor_id')
                var dateSelect = accountQuery('#reception_walkin_date_select')
                var dateInput = accountQuery('#reception_walkin_date')
                var timeInput = accountQuery('#reception_walkin_time')
                var currentTypeInput = accountQuery('#reception_walkin_type')
                var priorityInput = accountQuery('#reception_walkin_priority')
                var reasonInput = accountQuery('#reception_walkin_reason')

                var patientId = patientInput ? parseInt(patientInput.value, 10) : 0
                var doctorId = doctorInput ? parseInt(doctorInput.value, 10) : 0
                var serviceIds = selectedServiceIds()
                var date = dateSelect && dateSelect.value ? dateSelect.value : (dateInput ? dateInput.value : '')
                var time = timeInput ? timeInput.value : ''
                var type = currentTypeInput && currentTypeInput.value ? String(currentTypeInput.value) : 'walk_in'
                var priority = priorityInput && priorityInput.value ? parseInt(priorityInput.value, 10) : null
                var reason = reasonInput ? reasonInput.value : ''
                var autoQueue = true

                if (!patientId || !serviceIds.length || !doctorId) {
                    showError('Patient, services, and doctor are required.')
                    setSubmitting(false)
                    return
                }

                if (type !== 'walk_in') {
                    if (!date || !time) {
                        showError('Date and time are required for scheduled visits.')
                        setSubmitting(false)
                        return
                    }
                }

                if (typeof apiFetch !== 'function') {
                    showError('API client is not available.')
                    setSubmitting(false)
                    return
                }

                var body = {
                    patient_id: patientId,
                    doctor_id: doctorId,
                    service_ids: serviceIds,
                    appointment_type: type,
                    status: 'confirmed'
                }

                if (type !== 'walk_in') {
                    body.appointment_datetime = date + ' ' + time
                }
                if (reason) body.reason_for_visit = reason
                if (priority !== null && !isNaN(priority)) body.priority_level = priority

                var patientName = selectedPatient ? patientDisplayName(selectedPatient) : ('#' + String(patientId))
                var doctorName = selectedDoctor ? doctorDisplayName(selectedDoctor) : ('#' + String(doctorId))
                var serviceText = (selectedServices || []).map(function (s) { return s && s.service_name ? s.service_name : '' }).filter(Boolean).join(', ')
                var reviewDetails = {
                    'Appointment Type': type === 'walk_in' ? 'Walk-in' : 'Scheduled',
                    'Patient': patientName,
                    'Doctor': doctorName,
                    'Services': serviceText || 'N/A',
                    'Date': type === 'walk_in' ? localDateIso() : date,
                    'Time': type === 'walk_in' ? 'Now' : time,
                    'Reason': reason || 'N/A',
                    'Priority Level': (priority !== null && !isNaN(priority)) ? String(priority) : 'N/A'
                }

                function readJson(response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, status: response.status, data: data }
                    }).catch(function () {
                        return { ok: response.ok, status: response.status, data: null }
                    })
                }

                function resetFormState() {
                    if (patientSearch) patientSearch.value = ''
                    if (serviceSearch) serviceSearch.value = ''
                    if (doctorSearch) doctorSearch.value = ''
                    setPatientSelection(null)
                    selectedServices = []
                    syncServiceHiddenInput()
                    renderSelectedServices()
                    setDoctorSelection(null)
                    if (dateInput) dateInput.value = ''
                    if (timeInput) timeInput.value = ''
                    if (priorityInput) priorityInput.value = ''
                    if (reasonInput) reasonInput.value = ''
                    if (currentTypeInput) currentTypeInput.value = 'walk_in'
                    syncTypeToggleUI()
                    applyAppointmentTypeUI()
                }

                function createAndQueue(options) {
                    var opts = options && typeof options === 'object' ? options : {}
                    var createUrl = type === 'walk_in' ? "{{ url('/api/walk-ins') }}" : "{{ url('/api/appointments') }}"
                    var requestBody = Object.assign({}, body)
                    if (type === 'walk_in' && opts.forceDuplicateQueue) {
                        requestBody.force_duplicate_queue = true
                    }

                    return apiFetch(createUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(requestBody)
                    })
                        .then(readJson)
                        .then(function (result) {
                            if (!result.ok) {
                                var message = 'Failed to create appointment.'
                                if (result.data && result.data.message) message = result.data.message
                                showError(message)
                                return
                            }

                            var created = result.data || {}
                            function afterQueue() {
                                showSuccess(type === 'walk_in'
                                    ? 'Walk-in successfuly created and currently on the queue.'
                                    : 'Appointment has been created successfully. Queue entry created.')
                                resetFormState()
                            }

                            if (autoQueue && created && created.appointment_id) {
                                var queueBody = { appointment_id: created.appointment_id }
                                if (type === 'walk_in' && opts.forceDuplicatePatient) {
                                    queueBody.force_duplicate_patient = true
                                }
                                return apiFetch("{{ url('/api/queues') }}", {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify(queueBody)
                                })
                                    .then(function () { afterQueue() })
                                    .catch(function () { afterQueue() })
                            }
                            afterQueue()
                        })
                        .catch(function () {
                            showError('Network error while creating appointment.')
                        })
                        .finally(function () {
                            setSubmitting(false)
                        })
                }

                function continueAfterReview() {
                    if (type === 'walk_in') {
                        return apiFetch("{{ url('/api/queues/active-exists') }}?patient_id=" + encodeURIComponent(String(patientId)), { method: 'GET' })
                            .then(readJson)
                            .then(function (res) {
                                var exists = !!(res && res.ok && res.data && res.data.exists)
                                if (!exists) return createAndQueue()
                                var ask = window.receptionWalkInConfirm
                                if (typeof ask === 'function') {
                                    return ask('This patient is already in the queue, would you still like to register this queue entry?', 3000)
                                        .then(function (confirmed) {
                                            if (!confirmed) {
                                                setSubmitting(false)
                                                return
                                            }
                                            return createAndQueue({ forceDuplicateQueue: true, forceDuplicatePatient: true })
                                        })
                                }
                                if (!window.confirm('This patient is already in the queue, would you still like to register this queue entry?')) {
                                    setSubmitting(false)
                                    return
                                }
                                return createAndQueue({ forceDuplicateQueue: true, forceDuplicatePatient: true })
                            })
                            .catch(function () {
                                return createAndQueue()
                            })
                    }

                    return apiFetch("{{ url('/api/appointments/active-exists') }}?patient_id=" + encodeURIComponent(String(patientId)), { method: 'GET' })
                        .then(readJson)
                        .then(function (res) {
                            var exists = !!(res && res.ok && res.data && res.data.exists)
                            if (!exists) return createAndQueue()
                            var ask = window.receptionWalkInConfirm
                            if (typeof ask === 'function') {
                                return ask('This patient has an active appointment already, would you still like to book this appointment?', 3000)
                                    .then(function (confirmed) {
                                        if (!confirmed) {
                                            setSubmitting(false)
                                            return
                                        }
                                        return createAndQueue()
                                    })
                            }
                            if (!window.confirm('This patient has an active appointment already, would you still like to book this appointment?')) {
                                setSubmitting(false)
                                return
                            }
                            return createAndQueue()
                        })
                        .catch(function () {
                            return createAndQueue()
                        })
                }

                var askReview = window.receptionWalkInReview
                var reviewPromise = (typeof askReview === 'function')
                    ? askReview('Review Walk-in Details', reviewDetails)
                    : Promise.resolve(window.confirm('Please review details before submitting this walk-in.'))

                reviewPromise.then(function (reviewOk) {
                    if (!reviewOk) {
                        setSubmitting(false)
                        return
                    }
                    return continueAfterReview()
                })
            })
        }
    })
</script>
