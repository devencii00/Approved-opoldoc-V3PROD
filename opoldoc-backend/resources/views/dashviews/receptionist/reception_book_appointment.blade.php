<div class="bg-white border border-slate-200 rounded-[18px] shadow-[0_2px_10px_rgba(15,23,42,0.04)] overflow-hidden">
    <div class="grid grid-cols-2 border-b border-slate-200">
      <button id="receptionAppointmentTabBook" type="button" class="px-4 py-3 text-xs font-semibold text-white bg-cyan-500 border-b-2 border-cyan-600">
    Book appointment
</button>
<button id="receptionAppointmentTabManage" type="button" class="px-4 py-3 text-xs font-semibold text-slate-900 bg-white hover:bg-slate-50 border-l border-slate-200">
    Manage appointment
</button>
    </div>

    <div id="receptionAppointmentPanelBook" class="p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Book appointment</h2>
                <p class="text-xs text-slate-500">Create a new appointment for a patient and doctor.</p>
            </div>
            <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Appointments</span>
        </div>

        <div id="receptionBookAppointmentError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
        <div id="receptionBookAppointmentSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>

        <form id="receptionBookAppointmentForm" class="grid gap-3 grid-cols-1 md:grid-cols-3 items-start mb-4">
        <div class="min-w-0">
            <label for="reception_appointment_patient_id" class="block text-[0.7rem] text-slate-600 mb-1">Patient</label>
            <div class="mb-1 text-[0.7rem] text-slate-500">&nbsp;</div>
            <div class="relative">
                <input id="reception_patient_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type to search patient">
                <input id="reception_appointment_patient_id" type="hidden" required>
                <div id="receptionPatientResults" class="hidden mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain"></div>
            </div>
            <div id="receptionPatientPreview" class="hidden mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.78rem] text-slate-700 break-words"></div>
        </div>
        <div class="min-w-0">
            <label for="reception_appointment_service_id" class="block text-[0.7rem] text-slate-600 mb-1">Service</label>
            <div class="mb-1 text-[0.7rem] text-slate-500">&nbsp;</div> 
            <div class="relative">
                <input id="reception_service_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type to search service">
                <input id="reception_appointment_service_id" type="hidden">
                <div id="receptionServiceResults" class="hidden mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain"></div>
            </div>
            <div id="receptionSelectedServices" class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.78rem] text-slate-700 max-h-24 overflow-y-auto overscroll-contain"></div>
        </div>
        <div class="min-w-0">
            <label for="reception_appointment_doctor_id" class="block text-[0.7rem] text-slate-600 mb-1">Doctor</label>
            <div class="mb-1 text-[0.7rem] text-slate-500">&nbsp;</div>
            <div class="relative">
                <input id="reception_doctor_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Type to search doctor" disabled>
                <input id="reception_appointment_doctor_id" type="hidden" required>
                <div id="receptionDoctorResults" class="hidden mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain"></div>
            </div>
            <div id="receptionDoctorPreview" class="hidden mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.78rem] text-slate-700 break-words"></div>
        </div>
        <div id="receptionAppointmentDateWrap" class="self-start relative">
            <label for="reception_appointment_date" class="block text-[0.7rem] text-slate-600 mb-1">Date</label>
            <div class="mb-1 text-[0.7rem] text-slate-500">&nbsp;</div>
            <button id="receptionAppointmentDateTrigger" type="button" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 text-left focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none disabled:opacity-60" disabled>
                Select a doctor first
            </button>
            <div id="receptionAppointmentDateOverlay" class="hidden fixed z-50 rounded-xl border border-slate-200 bg-white shadow-[0_12px_30px_rgba(15,23,42,0.12)]">
                <div class="flex items-center justify-between px-3 py-2 border-b border-slate-100">
                    <button id="receptionDatePrev" type="button" class="px-2 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-xs font-semibold">‹</button>
                    <div id="receptionDateMonthLabel" class="text-[0.78rem] font-semibold text-slate-800"></div>
                    <button id="receptionDateNext" type="button" class="px-2 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-xs font-semibold">›</button>
                </div>
                <div class="p-3">
                    <div class="grid grid-cols-7 gap-1 text-[0.68rem] text-slate-400 mb-2">
                        <div class="text-center">Sun</div><div class="text-center">Mon</div><div class="text-center">Tue</div><div class="text-center">Wed</div><div class="text-center">Thu</div><div class="text-center">Fri</div><div class="text-center">Sat</div>
                    </div>
                    <div id="receptionAppointmentDateGrid" class="grid grid-cols-7 gap-1"></div>
                </div>
            </div>
            <select id="reception_appointment_date_select" class="hidden" required disabled>
                <option value="">Select a doctor first</option>
            </select>
            <input id="reception_appointment_date" type="date" class="hidden" tabindex="-1">
        </div>
        <div id="receptionAppointmentTimeWrap" class="self-start relative">
            <label class="block text-[0.7rem] text-slate-600 mb-1">Time slot</label>
            <input id="reception_appointment_time" type="hidden" required>
            <div id="reception_available_days" class="mb-1 text-[0.7rem] text-slate-500"></div>
            <button id="receptionTimeSlotTrigger" type="button" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 text-left focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none disabled:opacity-60" disabled>
                Select a date first
            </button>
            <div id="receptionTimeSlotOverlay" class="hidden absolute left-0 right-0 bottom-full mb-1 z-50 rounded-xl border border-slate-200 bg-white shadow-[0_12px_30px_rgba(15,23,42,0.12)]">
                <div id="reception_time_slots" class="h-44 overflow-y-auto overscroll-contain flex flex-col gap-2 p-2"></div>
            </div>
        </div>
        <input id="reception_appointment_type" type="hidden" value="scheduled">
        <div class="md:col-span-3">
            <label for="reception_appointment_reason" class="block text-[0.7rem] text-slate-600 mb-1">Reason (optional)</label>
            <input id="reception_appointment_reason" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Reason for visit">
        </div>
        <div class="md:col-span-3 flex justify-end">
            <button id="receptionBookAppointmentSubmit" type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors disabled:opacity-60 disabled:hover:bg-cyan-600">
                <span id="receptionBookAppointmentSpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                <span id="receptionBookAppointmentSubmitLabel">Book appointment</span>
            </button>
        </div>
    </form>

    <p class="text-[0.7rem] text-slate-400">
        Appointments booked by reception are confirmed by default.
    </p>
    </div>

    <div id="receptionAppointmentPanelManage" class="hidden p-5">
        <div class="flex items-center justify-between mb-3 gap-3">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Manage appointment</h3>
                <p class="text-xs text-slate-500">Search, update status, or mark check-in for an existing appointment.</p>
            </div>
           <button id="receptionManageTodayOnlyBtn" type="button" class="shrink-0 inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.75rem] font-semibold text-slate-700">
    Show today only
</button>
        </div>

        <div id="receptionManageAppointmentError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
        <div id="receptionManageAppointmentSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700"></div>

        <div class="grid gap-3 grid-cols-1 md:grid-cols-5 items-start mb-4">
            <div class="md:col-span-2 min-w-0">
                <label for="receptionManageApptSearch" class="block text-[0.7rem] text-slate-600 mb-1">Search</label>
                <input id="receptionManageApptSearch" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Search by patient or doctor">
            </div>
            <div class="min-w-0">
                <label for="receptionManageServiceSearch" class="block text-[0.7rem] text-slate-600 mb-1">Service</label>
                <div class="relative">
                    <input id="receptionManageServiceSearch" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="All services" autocomplete="off">
                    <input id="receptionManageServiceId" type="hidden">
                    <div id="receptionManageServiceResults" class="hidden absolute left-0 right-0 top-full mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-sm max-h-64 overflow-y-auto overscroll-contain z-50"></div>
                </div>
            </div>
            <div class="min-w-0">
                <label for="receptionManageSort" class="block text-[0.7rem] text-slate-600 mb-1">Sort by date</label>
                <select id="receptionManageSort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="latest">Latest first</option>
                    <option value="oldest">Oldest first</option>
                </select>
            </div>
            <div class="min-w-0">
                <label for="receptionManageStatus" class="block text-[0.7rem] text-slate-600 mb-1">Status</label>
                <select id="receptionManageStatus" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="no_show">No-show</option>
                </select>
            </div>
        </div>
<div class="w-full" style="display:grid;">
<div class="rounded-2xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto overflow-y-auto max-h-[28rem] show-scrollbar">
        <table class="text-xs" style="min-width:700px;width:100%;table-layout:auto;">
            <thead class="bg-slate-50 text-slate-600 sticky top-0">
                <tr>
                    <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Date</th>
                    <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Time</th>
                    <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Patient</th>
                    <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Age</th>
                    <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Contact</th>
                    <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Service</th>
                    <th class="text-left px-3 py-2 font-semibold whitespace-nowrap">Doctor</th>
                    <th class="text-right px-3 py-2 font-semibold whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody id="receptionManageAppointmentTableBody" class="divide-y divide-slate-100 bg-white"></tbody>
        </table>
    </div>
    <div id="receptionManageAppointmentTableFooter" class="px-3 py-2 text-[0.72rem] text-slate-500 bg-white border-t border-slate-100 flex items-center justify-between">
        <div id="receptionManageAppointmentMeta">Showing latest 10 booked appointments.</div>
        <button id="receptionManageAppointmentRefresh" type="button" class="text-cyan-700 font-semibold hover:text-cyan-800">Refresh</button>
    </div>
</div>

        <pre id="receptionManageAppointmentResult" class="hidden mt-3 text-[0.68rem] text-slate-600 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 overflow-x-auto"></pre>
    </div>
</div>

<div id="receptionBookConfirmOverlay" class="hidden fixed inset-0 z-[70] bg-slate-900/40 items-center justify-center p-4">
    <div class="w-full max-w-sm rounded-2xl bg-white border border-slate-200 shadow-[0_12px_30px_rgba(15,23,42,0.24)] p-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-700">
                <x-lucide-info class="w-[18px] h-[18px]" />
            </div>
            <div class="flex-1">
                <div class="text-sm font-semibold text-slate-900">Confirm</div>
                <div id="receptionBookConfirmMessage" class="text-[0.78rem] text-slate-600 mt-0.5">Are you sure?</div>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button" id="receptionBookConfirmCancel" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
            <button type="button" id="receptionBookConfirmOk" class="px-3 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700">Confirm</button>
        </div>
    </div>
</div>
<div id="receptionBookReviewOverlay" class="hidden fixed inset-0 z-[75] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4 transition-all duration-200">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden">
        <!-- Header section with icon and title - refined spacing -->
        <div class="px-5 pt-5 pb-3 border-b border-slate-100 bg-gradient-to-r from-white to-slate-50/50">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-50 border border-cyan-200 flex items-center justify-center text-cyan-600 shadow-sm flex-shrink-0">
                    <x-lucide-calendar-check class="w-5 h-5" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-base font-semibold text-slate-800 tracking-tight">Review Appointment Details</div>
                    <p class="text-xs text-slate-500 mt-0.5">Please verify all information before confirming</p>
                </div>
            </div>
        </div>

        <!-- Content area - improved typography and visual hierarchy -->
        <div class="px-5 py-4 bg-white">
            <div id="receptionBookReviewContent" class="text-sm text-slate-700 leading-relaxed space-y-3">
                <!-- Dynamic content will be injected here with better structure -->
                <div class="bg-slate-50/80 rounded-xl border border-slate-100 p-4 space-y-3">
                    <div class="flex items-start gap-2.5">
                        <x-lucide-user class="w-4 h-4 mt-0.5 text-slate-400 flex-shrink-0" />
                        <div class="flex flex-wrap items-baseline gap-1">
                            <span class="font-medium text-slate-800">Patient:</span>
                            <span class="text-slate-600">—</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <x-lucide-calendar-days class="w-4 h-4 mt-0.5 text-slate-400 flex-shrink-0" />
                        <div class="flex flex-wrap items-baseline gap-1">
                            <span class="font-medium text-slate-800">Date:</span>
                            <span class="text-slate-600">—</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <x-lucide-clock class="w-4 h-4 mt-0.5 text-slate-400 flex-shrink-0" />
                        <div class="flex flex-wrap items-baseline gap-1">
                            <span class="font-medium text-slate-800">Time:</span>
                            <span class="text-slate-600">—</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <x-lucide-stethoscope class="w-4 h-4 mt-0.5 text-slate-400 flex-shrink-0" />
                        <div class="flex flex-wrap items-baseline gap-1">
                            <span class="font-medium text-slate-800">Doctor / Department:</span>
                            <span class="text-slate-600">—</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <x-lucide-file-text class="w-4 h-4 mt-0.5 text-slate-400 flex-shrink-0" />
                        <div class="flex flex-wrap items-baseline gap-1">
                            <span class="font-medium text-slate-800">Reason / Notes:</span>
                            <span class="text-slate-600">—</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-start gap-2 text-xs text-amber-700 bg-amber-50/60 rounded-lg px-3 py-2">
                    <x-lucide-alert-circle class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" />
                    <span>Please ensure all details are correct before confirming the appointment.</span>
                </div>
            </div>
        </div>

        <!-- Footer buttons - improved hierarchy -->
        <div class="px-5 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-2.5">
            <button type="button" id="receptionBookReviewCancel" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-slate-200 focus:ring-offset-1">
                Cancel
            </button>
            <button type="button" id="receptionBookReviewOk" class="px-5 py-2 rounded-lg bg-cyan-600 text-white text-sm font-semibold hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 shadow-sm transition-all duration-150">
                Confirm Appointment
            </button>
        </div>
    </div>
</div>
<template id="receptionBookAppointmentIconX">
    <x-lucide-x class="w-[18px] h-[18px]" />
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var iconX = (function () {
            var tpl = document.getElementById('receptionBookAppointmentIconX')
            return tpl ? String(tpl.innerHTML || '').trim() : ''
        })()
        var tabBookBtn = document.getElementById('receptionAppointmentTabBook')
        var tabManageBtn = document.getElementById('receptionAppointmentTabManage')
        var panelBook = document.getElementById('receptionAppointmentPanelBook')
        var panelManage = document.getElementById('receptionAppointmentPanelManage')
function setAppointmentTab(tab) {
    var isBook = tab === 'book'
    if (panelBook) panelBook.classList.toggle('hidden', !isBook)
    if (panelManage) panelManage.classList.toggle('hidden', isBook)

    if (tabBookBtn) {
        // Active tab (Book)
        tabBookBtn.classList.toggle('bg-cyan-500', isBook)      // Cyan background
        tabBookBtn.classList.toggle('text-white', isBook)       // White text
        tabBookBtn.classList.toggle('border-b-2', isBook)       // Bottom border indicator
        tabBookBtn.classList.toggle('border-cyan-600', isBook)  // Darker cyan border
        // Inactive tab
        tabBookBtn.classList.toggle('bg-white', !isBook)        // White background
        tabBookBtn.classList.toggle('text-slate-900', !isBook)  // Black/dark text
        tabBookBtn.classList.toggle('hover:bg-slate-50', !isBook) // Hover effect
        tabBookBtn.classList.toggle('border-b-0', !isBook)      // No border when inactive
        tabBookBtn.classList.toggle('border-l', !isBook)        // Left border separator
        tabBookBtn.classList.toggle('border-slate-200', !isBook) // Border color
    }
    if (tabManageBtn) {
        // Active tab (Manage)
        tabManageBtn.classList.toggle('bg-cyan-500', !isBook)    // Cyan background
        tabManageBtn.classList.toggle('text-white', !isBook)     // White text
        tabManageBtn.classList.toggle('border-b-2', !isBook)     // Bottom border indicator
        tabManageBtn.classList.toggle('border-cyan-600', !isBook)// Darker cyan border
        // Inactive tab
        tabManageBtn.classList.toggle('bg-white', isBook)        // White background
        tabManageBtn.classList.toggle('text-slate-900', isBook)  // Black/dark text
        tabManageBtn.classList.toggle('hover:bg-slate-50', isBook) // Hover effect
        tabManageBtn.classList.toggle('border-b-0', isBook)      // No border when inactive
        tabManageBtn.classList.toggle('border-l', isBook)        // Left border separator
        tabManageBtn.classList.toggle('border-slate-200', isBook) // Border color
    }
}

        if (tabBookBtn) {
            tabBookBtn.addEventListener('click', function () { setAppointmentTab('book') })
        }
        if (tabManageBtn) {
            tabManageBtn.addEventListener('click', function () { setAppointmentTab('manage') })
        }
        setAppointmentTab('book')

        var form = document.getElementById('receptionBookAppointmentForm')
        var errorBox = document.getElementById('receptionBookAppointmentError')
        var successBox = document.getElementById('receptionBookAppointmentSuccess')
        var submitBtn = document.getElementById('receptionBookAppointmentSubmit')
        var submitSpinner = document.getElementById('receptionBookAppointmentSpinner')
        var submitLabel = document.getElementById('receptionBookAppointmentSubmitLabel')
        var patientSearch = document.getElementById('reception_patient_search')
        var patientSelect = document.getElementById('reception_appointment_patient_id')
        var patientResults = document.getElementById('receptionPatientResults')
        var patientPreview = document.getElementById('receptionPatientPreview')
        var serviceSearch = document.getElementById('reception_service_search')
        var serviceSelect = document.getElementById('reception_appointment_service_id')
        var serviceResults = document.getElementById('receptionServiceResults')
        var selectedServicesEl = document.getElementById('receptionSelectedServices')
        var doctorSearch = document.getElementById('reception_doctor_search')
        var doctorSelect = document.getElementById('reception_appointment_doctor_id')
        var doctorResults = document.getElementById('receptionDoctorResults')
        var doctorPreview = document.getElementById('receptionDoctorPreview')
        var dateSelect = document.getElementById('reception_appointment_date_select')
        var dateInput = document.getElementById('reception_appointment_date')
        var dateLoadMore = document.getElementById('reception_appointment_date_load_more')
        var dateRangeHint = document.getElementById('reception_appointment_date_range_hint')
        var dateWrap = document.getElementById('receptionAppointmentDateWrap')
        var dateTrigger = document.getElementById('receptionAppointmentDateTrigger')
        var dateOverlay = document.getElementById('receptionAppointmentDateOverlay')
        var dateGrid = document.getElementById('receptionAppointmentDateGrid')
        var datePrevBtn = document.getElementById('receptionDatePrev')
        var dateNextBtn = document.getElementById('receptionDateNext')
        var dateMonthLabel = document.getElementById('receptionDateMonthLabel')
        var timeInput = document.getElementById('reception_appointment_time')
        var timeWrap = document.getElementById('receptionAppointmentTimeWrap')
        var timeTrigger = document.getElementById('receptionTimeSlotTrigger')
        var timeOverlay = document.getElementById('receptionTimeSlotOverlay')
        var availableDaysEl = document.getElementById('reception_available_days')
        var timeSlotsEl = document.getElementById('reception_time_slots')
        var services = []
        var doctors = []
        var servicesLoaded = false
        var servicesLoading = false
        var doctorsLoaded = false
        var doctorsLoading = false
        var doctorSchedules = []
        var doctorAvailableDaySet = {}
        var doctorAppointments = []
        var selectedSlotStart = null
        var slotMinutes = 60
        var patientSearchTimer = null
        var selectedPatient = null
        var selectedServices = []
        var selectedDoctor = null
        var previousDoctorId = 0
        var previousServiceIds = []
        var previousServiceIdSet = {}

        function setBookSubmitting(isSubmitting) {
            if (submitBtn) submitBtn.disabled = !!isSubmitting
            if (submitSpinner) submitSpinner.classList.toggle('hidden', !isSubmitting)
            if (submitLabel) submitLabel.textContent = isSubmitting ? 'Booking…' : 'Book appointment'
        }

        function showBookAppointmentError(message) {
            if (!errorBox) return
            errorBox.textContent = message || ''
            if (message) {
                errorBox.classList.remove('hidden')
            } else {
                errorBox.classList.add('hidden')
            }
        }

        function showBookAppointmentSuccess(message) {
            if (!successBox) return
            successBox.textContent = message || ''
            if (message) {
                successBox.classList.remove('hidden')
            } else {
                successBox.classList.add('hidden')
            }
        }

        function normalizeText(value) {
            return String(value || '').trim().toLowerCase()
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

        function patientLabel(p) {
            var id = p && (p.user_id != null ? p.user_id : p.id)
            var parts = [p && p.firstname, p && p.middlename, p && p.lastname].filter(function (v) { return String(v || '').trim() !== '' })
            var name = parts.join(' ').trim()
            if (!name) name = 'Patient'
            return '#' + id + ' — ' + name
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

                    renderDoctorResults()
                    if (serviceSearch && (document.activeElement === serviceSearch || (serviceResults && !serviceResults.classList.contains('hidden')))) {
                        renderServiceResults()
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
                    if (patientSearch) {
                        patientSearch.value = patientDisplayName(chosen)
                    }
                })
            })
        }

        var patientInitialList = []
        var patientInitialLoaded = false
        var patientInitialLoading = false

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

        function loadInitialPatients() {
            if (patientInitialLoaded || patientInitialLoading || typeof apiFetch !== 'function') return
            patientInitialLoading = true
            apiFetch("{{ url('/api/patients') }}?per_page=10&sort=desc", { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    }).catch(function () {
                        return { ok: response.ok, data: null }
                    })
                })
                .then(function (result) {
                    if (!result.ok) return
                    var list = []
                    if (result.data && Array.isArray(result.data.data)) {
                        list = result.data.data
                    } else if (Array.isArray(result.data)) {
                        list = result.data
                    }
                    patientInitialList = Array.isArray(list) ? list : []
                    patientInitialLoaded = true
                })
                .catch(function () {})
                .finally(function () {
                    patientInitialLoading = false
                })
        }

        function dayKeyFromDate(dateStr) {
            if (!dateStr) return ''
            var d = new Date(dateStr + 'T00:00:00')
            if (isNaN(d.getTime())) return ''
            var keys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']
            return keys[d.getDay()] || ''
        }

        function normalizeDayKey(raw) {
            var v = String(raw == null ? '' : raw).trim().toLowerCase()
            if (!v) return ''
            if (/^\d+$/.test(v)) {
                var n = parseInt(v, 10)
                var keys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']
                return keys[n] || ''
            }
            var map = {
                sun: 'sun', sunday: 'sun',
                mon: 'mon', monday: 'mon',
                tue: 'tue', tues: 'tue', tuesday: 'tue',
                wed: 'wed', wednesday: 'wed',
                thu: 'thu', thur: 'thu', thurs: 'thu', thursday: 'thu',
                fri: 'fri', friday: 'fri',
                sat: 'sat', saturday: 'sat'
            }
            if (map[v]) return map[v]
            var s3 = v.slice(0, 3)
            return map[s3] || ''
        }

        function dayLabelFromKey(key) {
            var map = { sun: 'Sun', mon: 'Mon', tue: 'Tue', wed: 'Wed', thu: 'Thu', fri: 'Fri', sat: 'Sat' }
            return map[String(key || '').toLowerCase()] || String(key || '')
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

        function formatLocalDateIso(dateObj) {
            var d = dateObj instanceof Date ? dateObj : new Date()
            var y = d.getFullYear()
            var m = String(d.getMonth() + 1).padStart(2, '0')
            var day = String(d.getDate()).padStart(2, '0')
            return String(y) + '-' + m + '-' + day
        }

        function localDateIso() {
            return formatLocalDateIso(new Date())
        }

        function clearAvailability() {
            doctorSchedules = []
            doctorAvailableDaySet = {}
            doctorAppointments = []
            selectedSlotStart = null
            if (timeInput) timeInput.value = ''
            if (dateSelect) {
                dateSelect.innerHTML = '<option value="">Select a date</option>'
                dateSelect.disabled = true
            }
            if (dateLoadMore) dateLoadMore.classList.add('hidden')
            if (dateRangeHint) {
                dateRangeHint.textContent = ''
                dateRangeHint.classList.add('hidden')
            }
            if (dateInput) dateInput.value = ''
            if (availableDaysEl) availableDaysEl.textContent = '\u00A0'
            if (timeSlotsEl) timeSlotsEl.innerHTML = ''
            datePickerMonth = (function () {
                var now = new Date()
                return new Date(now.getFullYear(), now.getMonth(), 1)
            })()
            renderDatePicker()
            closeDateOverlay()

            if (timeTrigger) {
                timeTrigger.disabled = true
                timeTrigger.textContent = 'Select a date first'
            }
            closeTimeOverlay()
        }

        function serviceKey(service) {
            var id = service && service.service_id != null ? parseInt(service.service_id, 10) : 0
            return String(id || '')
        }

        function serviceGroup(service) {
            if (!service) return ''
            var name = String(service.service_name || '').trim()
            if (!name) return ''
            var parts = name.split(':')
            var group = String(parts[0] || name).trim().toLowerCase()
            return group
        }

        function selectedServiceIds() {
            return (selectedServices || [])
                .map(function (s) { return s && s.service_id != null ? parseInt(s.service_id, 10) : 0 })
                .filter(function (v) { return !!v && !isNaN(v) })
        }

        function syncServiceHiddenInput() {
            if (!serviceSelect) return
            var ids = selectedServiceIds()
            serviceSelect.value = ids.length ? String(ids[0]) : ''
        }

        function renderSelectedServices() {
            if (!selectedServicesEl) return
            var list = Array.isArray(selectedServices) ? selectedServices : []
            if (!list.length) {
                selectedServicesEl.innerHTML = '<div class="text-[0.75rem] text-slate-500">No services selected.</div>'
                return
            }

            selectedServicesEl.innerHTML = list.map(function (s) {
                var id = s && s.service_id != null ? parseInt(s.service_id, 10) : 0
                var name = s && s.service_name ? String(s.service_name) : ('Service #' + id)
                return (
                    '<div class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-200/60 last:border-0">' +
                        '<div class="min-w-0 text-slate-700 text-[0.78rem] font-semibold truncate">' + escapeHtml(name) + '</div>' +
                        '<button type="button" class="shrink-0 inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 reception-remove-service" data-service-id="' + escapeHtml(id) + '">' +
                            iconX +
                        '</button>' +
                    '</div>'
                )
            }).join('')

            var buttons = selectedServicesEl.querySelectorAll('.reception-remove-service')
            Array.prototype.forEach.call(buttons, function (btn) {
                btn.addEventListener('click', function () {
                    var id = parseInt(btn.getAttribute('data-service-id') || '0', 10)
                    if (!id) return
                    selectedServices = (selectedServices || []).filter(function (s) {
                        return parseInt(s && s.service_id ? s.service_id : 0, 10) !== id
                    })
                    syncServiceHiddenInput()
                    renderSelectedServices()

                    if (!selectedServices.length) {
                        if (doctorSearch) doctorSearch.disabled = true
                        if (doctorSearch) doctorSearch.value = ''
                        setDoctorSelection(null)
                        renderServiceResults()
                    } else {
                        if (doctorSearch) doctorSearch.disabled = false
                        setDoctorSelection(null)
                        renderDoctorResults()
                        renderServiceResults()
                    }
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

            if (doctorSearch) doctorSearch.disabled = selectedServices.length === 0
            setDoctorSelection(null)
        }

        function renderServiceResults() {
            if (!serviceResults) return
            var q = serviceSearch ? normalizeText(serviceSearch.value) : ''
            var list = (services || []).slice()

            if (selectedServices && selectedServices.length) {
                var base = serviceGroup(selectedServices[0])
                if (base) {
                    list = list.filter(function (s) {
                        return serviceGroup(s) === base
                    })
                }
            }

            if (q) {
                list = list.filter(function (s) {
                    var name = normalizeText(s && s.service_name ? s.service_name : '')
                    return wordPrefixMatch(name, q)
                })
            }
            list.sort(function (a, b) {
                var ai = a && a.service_id != null ? parseInt(a.service_id, 10) : 0
                var bi = b && b.service_id != null ? parseInt(b.service_id, 10) : 0
                return (isNaN(bi) ? 0 : bi) - (isNaN(ai) ? 0 : ai)
            })

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

            list = list.slice(0, 10)
            if (!list.length) {
                serviceResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No services found.</div>'
                serviceResults.classList.remove('hidden')
                return
            }
            var html = ''
            list.forEach(function (s) {
                var title = s.service_name || ('Service #' + s.service_id)
                var meta = []
                if (s && s.duration_minutes != null) meta.push(String(s.duration_minutes) + ' min')
                if (s && s.price != null) meta.push('₱' + String(s.price))
                var sub = s.description ? String(s.description).trim() : ''
                var isLast = !!(previousServiceIdSet && previousServiceIdSet[String(s.service_id)])
                var tag = isLast
                    ? '<span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[0.65rem] font-semibold bg-amber-50 text-amber-800 border border-amber-200">Last inquired</span>'
                    : ''
                html += '<button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 border-b border-slate-100 last:border-0">' +
                    '<div class="text-[0.78rem] text-slate-800 font-semibold flex items-center justify-between gap-2">' +
                        '<span class="min-w-0 truncate">' + escapeHtml(title) + '</span>' +
                        tag +
                    '</div>' +
                    '<div class="text-[0.72rem] text-slate-500">' + escapeHtml(meta.join(' • ') || '—') + '</div>' +
                    (sub ? '<div class="mt-0.5 text-[0.72rem] text-slate-500">' + escapeHtml(sub) + '</div>' : '') +
                '</button>'
            })
            serviceResults.innerHTML = html
            serviceResults.classList.remove('hidden')

            var buttons = serviceResults.querySelectorAll('button')
            Array.prototype.forEach.call(buttons, function (btn, idx) {
                btn.addEventListener('click', function () {
                    var chosen = list[idx]
                    addService(chosen)
                })
            })
        }

        function doctorLabel(d) {
            if (!d) return ''
            var name = [d.firstname, d.middlename, d.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
            if (!name) name = 'Doctor #' + (d.user_id || '')
            return name + (d.specialization ? ' — ' + d.specialization : '')
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
                if (normalizeDayKey(s.day_of_week) !== normalizeDayKey(dayKey)) return false
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

        function setDoctorSelection(doctor) {
            selectedDoctor = doctor || null
            if (doctorSelect) doctorSelect.value = doctor && doctor.user_id ? String(doctor.user_id) : ''

            if (doctorPreview) {
                if (!doctor) {
                    doctorPreview.textContent = ''
                    doctorPreview.classList.add('hidden')
                } else {
                    var parts = []
                    parts.push('Doctor: ' + doctorLabel(doctor))
                    if (previousDoctorId && parseInt(doctor.user_id, 10) === previousDoctorId) {
                        parts.push('Last provider')
                    }
                    var dateStr = dateInput && dateInput.value ? String(dateInput.value).slice(0, 10) : localDateIso()
                    var dayKey = dayKeyFromDate(dateStr)
                    var checkTime = selectedSlotStart ? String(selectedSlotStart).slice(0, 5) : ''
                    var hasSchedule = !!dayKey && hasScheduleAtTime(doctor, dayKey, dateStr, checkTime)
                    parts.push('Availability: ' + ((doctor.is_available !== false && hasSchedule) ? 'Available' : 'Unavailable'))
                    var primary = selectedServices && selectedServices.length ? selectedServices[0] : null
                    var category = extractServiceCategory(primary ? primary.service_name : '')
                    if (category) parts.push('Service match: ' + (specializationMatches(category, doctor.specialization) ? 'Yes' : 'No'))
                    doctorPreview.textContent = parts.join(' • ')
                    doctorPreview.classList.remove('hidden')
                }
            }

            if (doctorResults) {
                doctorResults.innerHTML = ''
                doctorResults.classList.add('hidden')
            }

            clearAvailability()

            if (doctor && doctor.user_id) {
                var embedded = doctor.doctor_schedules
                if (Array.isArray(embedded) && embedded.length) {
                    doctorSchedules = embedded.slice()
                    renderAvailableDays()
                    if (dateSelect) dateSelect.disabled = true
                    renderDatePicker()
                }
                loadDoctorSchedulesAndAvailability(doctor.user_id, dateInput ? dateInput.value : '')
                applyAppointmentTypeUI()
            }
        }

        function renderDoctorResults() {
            if (!doctorResults) return
            var q = doctorSearch ? normalizeText(doctorSearch.value) : ''

            var primary = selectedServices && selectedServices.length ? selectedServices[0] : null
            var category = extractServiceCategory(primary ? primary.service_name : '')
            var list = []
            if (category) {
                list = (doctors || []).filter(function (d) {
                    return specializationMatches(category, d.specialization)
                })
            }

            if (q) {
                list = list.filter(function (d) {
                    return wordPrefixMatch(doctorLabel(d), q)
                })
            }

            list.sort(function (a, b) {
                var ai = a && a.user_id != null ? parseInt(a.user_id, 10) : 0
                var bi = b && b.user_id != null ? parseInt(b.user_id, 10) : 0
                return (isNaN(bi) ? 0 : bi) - (isNaN(ai) ? 0 : ai)
            })
            list = list.slice(0, 8)

            if (!category) {
                doctorResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">Select a service first.</div>'
                doctorResults.classList.remove('hidden')
                return
            }

            if (!list.length) {
                doctorResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No doctors found.</div>'
                doctorResults.classList.remove('hidden')
                return
            }

            var dateStr = (dateSelect && dateSelect.value) ? String(dateSelect.value).slice(0, 10) : (dateInput && dateInput.value ? String(dateInput.value).slice(0, 10) : localDateIso())
            var dayKey = dayKeyFromDate(dateStr)
            var checkTime = selectedSlotStart ? String(selectedSlotStart).slice(0, 5) : ''

            var enriched = list.map(function (d) {
                var name = [d.firstname, d.middlename, d.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim()
                if (!name) name = 'Doctor #' + d.user_id
                var isDoctorAvailable = d && d.is_available !== false
                var hasSchedule = !!dayKey && hasScheduleAtTime(d, dayKey, dateStr, checkTime)
                var isSelectable = isDoctorAvailable && hasSchedule
                var tag = ''
                if (!isDoctorAvailable) tag = 'Unavailable'
                else if (!hasSchedule) tag = 'No schedule on this time'
                else if (previousDoctorId && parseInt(d.user_id, 10) === previousDoctorId) tag = 'Last provider'
                return { d: d, name: name, isSelectable: isSelectable, tag: tag }
            })

            enriched.sort(function (a, b) {
                if (a.isSelectable !== b.isSelectable) return a.isSelectable ? -1 : 1
                if ((a.tag === 'Last provider') !== (b.tag === 'Last provider')) return a.tag === 'Last provider' ? -1 : 1
                var ai = a.d && a.d.user_id != null ? parseInt(a.d.user_id, 10) : 0
                var bi = b.d && b.d.user_id != null ? parseInt(b.d.user_id, 10) : 0
                return (isNaN(bi) ? 0 : bi) - (isNaN(ai) ? 0 : ai)
            })

            enriched = enriched.slice(0, 8)

            var html = ''
            enriched.forEach(function (x) {
                var d = x.d
                var name = x.name
                html += '<button type="button" class="w-full text-left px-3 py-2 border-b border-slate-100 last:border-0 flex items-start justify-between gap-3 ' + (x.isSelectable ? 'hover:bg-slate-50' : 'bg-slate-50/60 cursor-not-allowed') + '" ' + (x.isSelectable ? '' : 'disabled') + '>' +
                    '<div class="min-w-0">' +
                        '<div class="text-[0.78rem] text-slate-800 font-semibold">' + escapeHtml('Dr. ' + name) + '</div>' +
                        '<div class="text-[0.72rem] text-slate-500">' + escapeHtml(d.specialization || '—') + '</div>' +
                    '</div>' +
                    (x.tag
                        ? '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.65rem] font-semibold ' + ((x.tag === 'Last provider') ? 'bg-cyan-500/10 text-cyan-700 border border-cyan-200' : 'bg-slate-100 text-slate-500 border border-slate-200') + '">' + escapeHtml(x.tag) + '</span>'
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
                    if (doctorSearch) doctorSearch.value = [chosen.firstname, chosen.middlename, chosen.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim() || ('Doctor #' + chosen.user_id)
                })
            })
        }

        function renderAvailableDays() {
            if (!availableDaysEl) return
            if (!doctorSchedules.length) {
                availableDaysEl.textContent = '\u00A0'
                return
            }
            doctorAvailableDaySet = {}
            doctorSchedules.forEach(function (s) {
                var dayKey = normalizeDayKey(s && s.day_of_week != null ? s.day_of_week : '')
                if (dayKey) doctorAvailableDaySet[dayKey] = true
            })
            var order = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']
            var keys = Object.keys(doctorAvailableDaySet).sort(function (a, b) { return order.indexOf(a) - order.indexOf(b) })
            availableDaysEl.textContent = keys.length ? ('Available days: ' + keys.map(dayLabelFromKey).join(', ')) : '\u00A0'
        }

        function formatDateIso(d) {
            var yyyy = String(d.getFullYear())
            var mm = String(d.getMonth() + 1).padStart(2, '0')
            var dd = String(d.getDate()).padStart(2, '0')
            return yyyy + '-' + mm + '-' + dd
        }

        function formatDateLabel(d) {
            var keys = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
            return formatDateIso(d) + ' (' + (keys[d.getDay()] || '') + ')'
        }

        var dateCursor = null
        var dateCursorFirstIso = null
        var dateCursorLastIso = null

        function resetDateCursor() {
            var today = new Date()
            today.setHours(0, 0, 0, 0)
            dateCursor = today
            dateCursorFirstIso = null
            dateCursorLastIso = null
        }

        function appendAllowedDates(batchSize) {
            if (!dateSelect) return
            var allowedKeys = doctorAvailableDaySet && Object.keys(doctorAvailableDaySet).length ? doctorAvailableDaySet : null
            if (!allowedKeys) return
            if (!dateCursor) resetDateCursor()

            var added = 0
            var scanned = 0
            var maxScan = 365
            while (added < batchSize && scanned < maxScan) {
                var iso = formatDateIso(dateCursor)
                var dayKey = dayKeyFromDate(iso)
                if (dayKey && allowedKeys[dayKey]) {
                    var option = document.createElement('option')
                    option.value = iso
                    option.textContent = formatDateLabel(dateCursor)
                    dateSelect.appendChild(option)
                    added++
                    if (!dateCursorFirstIso) dateCursorFirstIso = iso
                    dateCursorLastIso = iso
                }
                dateCursor = new Date(dateCursor.getTime())
                dateCursor.setDate(dateCursor.getDate() + 1)
                scanned++
            }

            if (dateRangeHint) {
                if (dateCursorFirstIso && dateCursorLastIso) {
                    dateRangeHint.textContent = 'Loaded: ' + dateCursorFirstIso + ' → ' + dateCursorLastIso
                    dateRangeHint.classList.remove('hidden')
                } else {
                    dateRangeHint.textContent = ''
                    dateRangeHint.classList.add('hidden')
                }
            }

            if (dateLoadMore) {
                dateLoadMore.classList.toggle('hidden', !allowedKeys)
                dateLoadMore.disabled = scanned >= maxScan
                dateLoadMore.classList.toggle('opacity-60', dateLoadMore.disabled)
                dateLoadMore.classList.toggle('cursor-not-allowed', dateLoadMore.disabled)
            }

            renderDatePicker()
        }

        function closeDateOverlay() {
            if (dateOverlay) {
                dateOverlay.classList.add('hidden')
                dateOverlay.style.left = ''
                dateOverlay.style.top = ''
                dateOverlay.style.width = ''
            }
        }

        function closeTimeOverlay() {
            if (timeOverlay) timeOverlay.classList.add('hidden')
        }

        var datePickerMonth = (function () {
            var now = new Date()
            return new Date(now.getFullYear(), now.getMonth(), 1)
        })()

        function isoFromDate(d) {
            var yr = d.getFullYear()
            var mo = String(d.getMonth() + 1).padStart(2, '0')
            var da = String(d.getDate()).padStart(2, '0')
            return yr + '-' + mo + '-' + da
        }

        function friendlyDateLabelFromIso(iso) {
            var datePart = String(iso || '').slice(0, 10)
            if (!/^\d{4}-\d{2}-\d{2}$/.test(datePart)) return datePart || 'Select a date'
            var d = new Date(datePart + 'T00:00:00')
            if (isNaN(d.getTime())) return datePart
            return d.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: '2-digit', year: 'numeric' })
        }

        function syncDatePickerUI() {
            if (!dateTrigger) return
            var allowedKeys = doctorAvailableDaySet && Object.keys(doctorAvailableDaySet).length ? doctorAvailableDaySet : null
            var doctorId = doctorSelect && doctorSelect.value ? String(doctorSelect.value) : ''
            var enabled = !!doctorId && !!allowedKeys
            dateTrigger.disabled = !enabled

            var selected = dateInput && dateInput.value ? String(dateInput.value).slice(0, 10) : ''
            if (!enabled) {
                dateTrigger.textContent = 'Select a doctor first'
            } else if (selected) {
                dateTrigger.textContent = friendlyDateLabelFromIso(selected)
            } else {
                dateTrigger.textContent = 'Select a date'
            }
        }

        function renderDatePicker() {
            syncDatePickerUI()
            if (!dateGrid || !dateMonthLabel) return

            var allowedKeys = doctorAvailableDaySet && Object.keys(doctorAvailableDaySet).length ? doctorAvailableDaySet : null
            var doctorId = doctorSelect && doctorSelect.value ? String(doctorSelect.value) : ''
            if (!doctorId) {
                dateMonthLabel.textContent = ''
                dateGrid.innerHTML = '<div class="col-span-7 text-[0.75rem] text-slate-500 py-2">Select a doctor first.</div>'
                return
            }
            if (!allowedKeys) {
                dateMonthLabel.textContent = ''
                dateGrid.innerHTML = '<div class="col-span-7 text-[0.75rem] text-slate-500 py-2">No available schedule days.</div>'
                return
            }

            var year = datePickerMonth.getFullYear()
            var month = datePickerMonth.getMonth()
            var first = new Date(year, month, 1)
            var firstDow = first.getDay()
            var daysIn = new Date(year, month + 1, 0).getDate()

            dateMonthLabel.textContent = first.toLocaleDateString(undefined, { month: 'long', year: 'numeric' })

            var today = new Date()
            today.setHours(0, 0, 0, 0)

            var selectedIso = dateInput && dateInput.value ? String(dateInput.value).slice(0, 10) : ''
            var keys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']

            var cells = []
            for (var i = 0; i < firstDow; i++) cells.push('')
            for (var day = 1; day <= daysIn; day++) {
                var d = new Date(year, month, day)
                var iso = isoFromDate(d)
                var dayKey = keys[d.getDay()] || ''
                var allowed = !!allowedKeys[dayKey]
                var notPast = d.getTime() >= today.getTime()
                var enabled = allowed && notPast
                var selected = selectedIso && selectedIso === iso
                var base =
                    'w-full aspect-square rounded-lg text-[0.75rem] font-semibold border transition-colors flex items-center justify-center'
                var cls = base + ' ' + (enabled
                    ? (selected ? 'bg-cyan-600 text-white border-cyan-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50')
                    : 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed')
                cells.push('<button type="button" class="' + cls + '" data-date="' + iso + '"' + (enabled ? '' : ' disabled') + '>' + day + '</button>')
            }

            var total = Math.ceil(cells.length / 7) * 7
            while (cells.length < total) cells.push('')

            dateGrid.innerHTML = cells.map(function (html) {
                return html ? html : '<div></div>'
            }).join('')
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
                if (normalizeDayKey(s.day_of_week) !== normalizeDayKey(dayKey)) return false
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

        if (timeTrigger) {
            timeTrigger.addEventListener('click', function () {
                if (timeTrigger.disabled) return
                if (!timeOverlay) return
                renderTimeSlots()
                timeOverlay.classList.toggle('hidden')
            })
        }

        document.addEventListener('click', function (e) {
            if (dateOverlay && !dateOverlay.classList.contains('hidden')) {
                if (!dateWrap || (!dateWrap.contains(e.target) && !dateOverlay.contains(e.target))) {
                    closeDateOverlay()
                }
            }
            if (timeOverlay && !timeOverlay.classList.contains('hidden')) {
                if (!timeWrap || (!timeWrap.contains(e.target) && !timeOverlay.contains(e.target))) {
                    closeTimeOverlay()
                }
            }
        })

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDateOverlay()
                closeTimeOverlay()
            }
        })

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
                        showBookAppointmentError(msg)
                        if ((!doctorSchedules || !doctorSchedules.length) && dateSelect) {
                            dateSelect.innerHTML = '<option value=\"\">Failed to load schedules</option>'
                            dateSelect.disabled = true
                        }
                        renderAvailableDays()
                        renderDatePicker()
                        renderTimeSlots()
                        return
                    }

                    var raw = result.data && Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    doctorSchedules = raw || []
                    renderAvailableDays()
                    if (dateSelect) dateSelect.disabled = true
                    renderDatePicker()
                    if (dateStr) {
                        loadDoctorAppointments(doctorId, dateStr)
                    } else {
                        renderTimeSlots()
                    }
                })
                .catch(function () {
                    showBookAppointmentError('Network error while loading doctor schedules.')
                    if ((!doctorSchedules || !doctorSchedules.length) && dateSelect) {
                        dateSelect.innerHTML = '<option value=\"\">Network error loading schedules</option>'
                        dateSelect.disabled = true
                    }
                    renderAvailableDays()
                    renderDatePicker()
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
                .catch(function () {
                    doctorAppointments = []
                    renderTimeSlots()
                })
        }

        function loadServicesAndDoctors() {
            if (typeof apiFetch !== 'function') return

            servicesLoading = true
            apiFetch("{{ url('/api/services') }}?per_page=100", { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    }).catch(function () {
                        return { ok: response.ok, data: null }
                    })
                })
                .then(function (result) {
                    var raw = result.data && Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    var allowedServiceNames = [
                        'obsterician - gynecologist',
                        'obstetrician - gynecologist',
                        'general surgeon'
                    ]
                    services = (raw || []).filter(function (s) {
                        var name = normalizeText(s && s.service_name ? s.service_name : '')
                        return allowedServiceNames.indexOf(name) !== -1
                    })
                    servicesLoaded = true
                    if (serviceSearch && serviceSearch.value) {
                        renderServiceResults()
                    }
                })
                .catch(function () {})
                .finally(function () {
                    servicesLoading = false
                })

            doctorsLoading = true
            apiFetch("{{ url('/api/doctors') }}?per_page=200", { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    }).catch(function () {
                        return { ok: response.ok, data: null }
                    })
                })
                .then(function (result) {
                    var raw = result.data && Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    doctors = raw || []
                    doctorsLoaded = true
                    if (doctorSearch && doctorSearch.value) {
                        renderDoctorResults()
                    }
                })
                .catch(function () {})
                .finally(function () {
                    doctorsLoading = false
                })
        }

        if (patientSearch) {
            loadInitialPatients()

            patientSearch.addEventListener('focus', function () {
                showBookAppointmentError('')
                showBookAppointmentSuccess('')
                var q = String(patientSearch.value || '').trim()
                if (!q) {
                    if (!patientInitialLoaded && !patientInitialLoading) {
                        loadInitialPatients()
                    }
                    if (!patientInitialLoaded && patientInitialLoading) {
                        renderPatientResults([])
                        if (patientResults) {
                            patientResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">Loading patients…</div>'
                            patientResults.classList.remove('hidden')
                        }
                        return
                    }
                    renderPatientResults(patientInitialList)
                    return
                }
                searchPatients(q)
            })

            patientSearch.addEventListener('input', function () {
                var q = String(patientSearch.value || '').trim()

                if (selectedPatient) {
                    var currentName = patientDisplayName(selectedPatient)
                    if (normalizeText(q) !== normalizeText(currentName)) {
                        setPatientSelection(null)
                    }
                }

                if (patientSearchTimer) clearTimeout(patientSearchTimer)
                if (!q) {
                    renderPatientResults(patientInitialList)
                    return
                }
                patientSearchTimer = setTimeout(function () {
                    searchPatients(q)
                }, 250)
            })
        }

        if (serviceSearch) {
            serviceSearch.addEventListener('focus', function () {
                showBookAppointmentError('')
                showBookAppointmentSuccess('')

                if (!servicesLoaded && servicesLoading) {
                    if (serviceResults) {
                        serviceResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">Loading services…</div>'
                        serviceResults.classList.remove('hidden')
                    }
                    return
                }

                renderServiceResults()
            })

            serviceSearch.addEventListener('input', function () {
                showBookAppointmentError('')
                showBookAppointmentSuccess('')
                renderServiceResults()
            })
        }
        if (doctorSearch) {
            doctorSearch.addEventListener('focus', function () {
                showBookAppointmentError('')
                showBookAppointmentSuccess('')

                if (doctorSearch.disabled) {
                    if (doctorResults) doctorResults.classList.add('hidden')
                    return
                }

                if (!doctorsLoaded && doctorsLoading) {
                    if (doctorResults) {
                        doctorResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">Loading doctors…</div>'
                        doctorResults.classList.remove('hidden')
                    }
                    return
                }

                renderDoctorResults()
            })

            doctorSearch.addEventListener('input', function () {
                showBookAppointmentError('')
                showBookAppointmentSuccess('')

                var q = String(doctorSearch.value || '').trim()
                if (selectedDoctor) {
                    var currentName = [selectedDoctor.firstname, selectedDoctor.middlename, selectedDoctor.lastname].filter(function (v) { return String(v || '').trim() !== '' }).join(' ').trim() || ('Doctor #' + selectedDoctor.user_id)
                    if (normalizeText(q) !== normalizeText(currentName)) {
                        setDoctorSelection(null)
                    }
                }
                renderDoctorResults()
            })
        }

        function onDateChanged() {
            showBookAppointmentError('')
            showBookAppointmentSuccess('')
            selectedSlotStart = null
            if (timeInput) timeInput.value = ''
            var doctorId = doctorSelect ? doctorSelect.value : ''
            var dateStr = dateInput && dateInput.value ? String(dateInput.value).slice(0, 10) : ''
            renderDatePicker()
            closeDateOverlay()
            closeTimeOverlay()
            if (!doctorId || !dateStr) {
                renderTimeSlots()
                return
            }
            loadDoctorAppointments(doctorId, dateStr)
        }

        renderDatePicker()

        function positionDateOverlay() {
            if (!dateOverlay || !dateTrigger) return
            if (dateOverlay.classList.contains('hidden')) return

            var triggerRect = dateTrigger.getBoundingClientRect()
            var margin = 8

            dateOverlay.style.width = Math.max(220, Math.floor(triggerRect.width)) + 'px'
            dateOverlay.style.left = '0px'
            dateOverlay.style.top = '0px'

            window.requestAnimationFrame(function () {
                if (!dateOverlay || dateOverlay.classList.contains('hidden')) return

                var overlayRect = dateOverlay.getBoundingClientRect()
                var maxLeft = Math.max(margin, window.innerWidth - overlayRect.width - margin)
                var left = Math.min(Math.max(triggerRect.left, margin), maxLeft)

                var top = triggerRect.top - overlayRect.height - margin
                if (top < margin) {
                    top = triggerRect.bottom + margin
                }
                if (top + overlayRect.height > window.innerHeight - margin) {
                    top = Math.max(margin, window.innerHeight - overlayRect.height - margin)
                }

                dateOverlay.style.left = Math.floor(left) + 'px'
                dateOverlay.style.top = Math.floor(top) + 'px'
            })
        }

        if (dateTrigger) {
            dateTrigger.addEventListener('click', function () {
                if (dateTrigger.disabled) return
                if (!dateOverlay) return
                renderDatePicker()
                dateOverlay.classList.toggle('hidden')
                positionDateOverlay()
            })
        }

        window.addEventListener('resize', function () {
            positionDateOverlay()
        })
        window.addEventListener('scroll', function () {
            positionDateOverlay()
        }, true)

        if (datePrevBtn) {
            datePrevBtn.addEventListener('click', function () {
                datePickerMonth = new Date(datePickerMonth.getFullYear(), datePickerMonth.getMonth() - 1, 1)
                renderDatePicker()
                positionDateOverlay()
            })
        }

        if (dateNextBtn) {
            dateNextBtn.addEventListener('click', function () {
                datePickerMonth = new Date(datePickerMonth.getFullYear(), datePickerMonth.getMonth() + 1, 1)
                renderDatePicker()
                positionDateOverlay()
            })
        }

        if (dateGrid) {
            dateGrid.addEventListener('click', function (e) {
                var btn = e.target && e.target.closest ? e.target.closest('button[data-date]') : null
                if (!btn || !dateInput) return
                var iso = btn.getAttribute('data-date') || ''
                if (!iso) return
                dateInput.value = iso
                onDateChanged()
            })
        }

        var typeInput = document.getElementById('reception_appointment_type')
        var typeScheduledBtn = document.getElementById('receptionApptTypeScheduledBtn')
        var typeWalkInBtn = document.getElementById('receptionApptTypeWalkInBtn')

        function setTypeButtonState(btn, isActive) {
            if (!btn) return
            btn.classList.toggle('bg-cyan-500', isActive)
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
            var type = typeInput && typeInput.value ? typeInput.value : 'scheduled'
            setTypeButtonState(typeScheduledBtn, type === 'scheduled')
            setTypeButtonState(typeWalkInBtn, type === 'walk_in')
        }

        function setAppointmentType(nextType) {
            var type = nextType === 'walk_in' ? 'walk_in' : 'scheduled'
            if (typeInput) typeInput.value = type
            showBookAppointmentError('')
            showBookAppointmentSuccess('')
            applyAppointmentTypeUI()
            syncTypeToggleUI()
        }

        function applyAppointmentTypeUI() {
            if (typeInput) typeInput.value = 'scheduled'
            var isWalkIn = false
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
                renderTimeSlots()
            }
        }
        if (typeScheduledBtn) {
            typeScheduledBtn.addEventListener('click', function () { setAppointmentType('scheduled') })
        }
        if (typeWalkInBtn) {
            typeWalkInBtn.addEventListener('click', function () { setAppointmentType('walk_in') })
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

        loadServicesAndDoctors()
        if (dateInput) {
            var today = new Date()
            var yyyy = String(today.getFullYear())
            var mm = String(today.getMonth() + 1).padStart(2, '0')
            var dd = String(today.getDate()).padStart(2, '0')
            dateInput.min = yyyy + '-' + mm + '-' + dd
        }
        if (typeInput && !typeInput.value) typeInput.value = 'scheduled'
        applyAppointmentTypeUI()
        syncTypeToggleUI()
        renderTimeSlots()

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault()

                showBookAppointmentError('')
                showBookAppointmentSuccess('')
                setBookSubmitting(true)

                var patientInput = document.getElementById('reception_appointment_patient_id')
                var doctorInput = document.getElementById('reception_appointment_doctor_id')
                var serviceInput = document.getElementById('reception_appointment_service_id')
                var dateSelect = document.getElementById('reception_appointment_date_select')
                var dateInput = document.getElementById('reception_appointment_date')
                var timeInput = document.getElementById('reception_appointment_time')
                var typeInput = document.getElementById('reception_appointment_type')
                var reasonInput = document.getElementById('reception_appointment_reason')

                var patientId = patientInput ? parseInt(patientInput.value, 10) : 0
                var doctorId = doctorInput ? parseInt(doctorInput.value, 10) : 0
                var serviceIds = selectedServiceIds()
                var date = (dateInput && dateInput.value ? dateInput.value : (dateSelect && dateSelect.value ? dateSelect.value : ''))
                var time = timeInput ? timeInput.value : ''
                var type = 'scheduled'
                var reason = reasonInput ? reasonInput.value : ''

                if (!patientId || !doctorId || !serviceIds.length) {
                    showBookAppointmentError('Patient, service, and doctor are required.')
                    setBookSubmitting(false)
                    return
                }

                if (type !== 'walk_in') {
                    if (!date || !time) {
                        showBookAppointmentError('Date and time are required for scheduled appointments.')
                        setBookSubmitting(false)
                        return
                    }
                }

                if (typeof apiFetch !== 'function') {
                    showBookAppointmentError('API client is not available.')
                    setBookSubmitting(false)
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
                if (reason) {
                    body.reason_for_visit = reason
                }

                var reviewDetails = {
                    'Patient': selectedPatient ? patientDisplayName(selectedPatient) : ('#' + String(patientId)),
                    'Doctor': selectedDoctor ? doctorLabel(selectedDoctor) : ('#' + String(doctorId)),
                    'Services': (selectedServices || []).map(function (s) { return s && s.service_name ? s.service_name : '' }).filter(Boolean).join(', ') || 'N/A',
                    'Date': date || 'N/A',
                    'Time': time ? formatTime12h(time) : 'N/A',
                    'Reason': reason || 'N/A'
                }

                function submitAppointment() {
                    apiFetch("{{ url('/api/appointments') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(body)
                    })
                        .then(function (response) { return readResponse(response) })
                        .then(function (result) {
                            if (!result.ok) {
                                var message = 'Failed to book appointment.'
                                if (result.data && result.data.message) {
                                    message = result.data.message
                                }
                                showBookAppointmentError(message)
                                return
                            }

                            var createdId = result.data && result.data.appointment_id ? parseInt(result.data.appointment_id, 10) : 0
                            if (!createdId || isNaN(createdId)) {
                                showBookAppointmentError('Appointment was not saved. Please refresh and try again.')
                                return
                            }

                            showBookAppointmentSuccess('Appointment has been created successfully.')
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
                            if (typeInput) typeInput.value = 'scheduled'
                            if (reasonInput) reasonInput.value = ''
                            applyAppointmentTypeUI()
                            syncTypeToggleUI()
                            loadManageAppointments()
                        })
                        .catch(function () {
                            showBookAppointmentError('Network error while booking appointment.')
                        })
                        .finally(function () {
                            setBookSubmitting(false)
                        })
                }

                openReview(reviewDetails)
                    .then(function (reviewOk) {
                        if (!reviewOk) {
                            setBookSubmitting(false)
                            return
                        }
                        return apiFetch("{{ url('/api/appointments/active-exists') }}?patient_id=" + encodeURIComponent(String(patientId)), { method: 'GET' })
                            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d } }).catch(function () { return { ok: r.ok, data: null } }) })
                            .then(function (res) {
                                var exists = !!(res && res.ok && res.data && res.data.exists)
                                if (!exists) {
                                    submitAppointment()
                                    return
                                }
                                confirmAction('This patient has an active appointment already, would you still like to book this appointment?', { okText: 'Yes', delayMs: 3000 })
                                    .then(function (confirmed) {
                                        if (!confirmed) {
                                            setBookSubmitting(false)
                                            return
                                        }
                                        submitAppointment()
                                    })
                            })
                            .catch(function () {
                                submitAppointment()
                            })
                    })
            })
        }

        var manageError = document.getElementById('receptionManageAppointmentError')
        var manageSuccess = document.getElementById('receptionManageAppointmentSuccess')
        var manageResult = document.getElementById('receptionManageAppointmentResult')
        var manageSearchInput = document.getElementById('receptionManageApptSearch')
        var manageServiceSearch = document.getElementById('receptionManageServiceSearch')
        var manageServiceId = document.getElementById('receptionManageServiceId')
        var manageServiceResults = document.getElementById('receptionManageServiceResults')
        var manageSortSelect = document.getElementById('receptionManageSort')
        var manageStatusSelect = document.getElementById('receptionManageStatus')
        var manageTableBody = document.getElementById('receptionManageAppointmentTableBody')
        var manageMeta = document.getElementById('receptionManageAppointmentMeta')
        var manageRefreshBtn = document.getElementById('receptionManageAppointmentRefresh')
        var manageTodayOnlyBtn = document.getElementById('receptionManageTodayOnlyBtn')
        var manageShowTodayOnly = false
        var manageSearchTimer = null
        var manageServices = []
        var manageServicesLoaded = false
        var manageServicesLoading = false

        var confirmOverlay = document.getElementById('receptionBookConfirmOverlay')
        var confirmMessage = document.getElementById('receptionBookConfirmMessage')
        var confirmOk = document.getElementById('receptionBookConfirmOk')
        var confirmCancel = document.getElementById('receptionBookConfirmCancel')
        var reviewOverlay = document.getElementById('receptionBookReviewOverlay')
        var reviewContent = document.getElementById('receptionBookReviewContent')
        var reviewOk = document.getElementById('receptionBookReviewOk')
        var reviewCancel = document.getElementById('receptionBookReviewCancel')
        var confirmResolver = null
        var reviewResolver = null
        var confirmDelayTimer = null
        var confirmOkDefaultHtml = confirmOk ? confirmOk.innerHTML : ''

        function setManageSubmitting(isSubmitting) {
            var disabled = !!isSubmitting
            if (manageSortSelect) manageSortSelect.disabled = disabled
            if (manageStatusSelect) manageStatusSelect.disabled = disabled
            if (manageRefreshBtn) manageRefreshBtn.disabled = disabled
            if (manageTodayOnlyBtn) manageTodayOnlyBtn.disabled = disabled
        }
function updateManageTodayButton() {
    if (!manageTodayOnlyBtn) return
    if (manageShowTodayOnly) {
        manageTodayOnlyBtn.textContent = 'Showing today only'
        manageTodayOnlyBtn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200', 'hover:bg-slate-50')
        manageTodayOnlyBtn.classList.add('bg-cyan-600', 'text-white', 'border-cyan-600', 'hover:bg-cyan-700', 'hover:border-cyan-700')
    } else {
        manageTodayOnlyBtn.textContent = 'Show today only'
        manageTodayOnlyBtn.classList.add('bg-white', 'text-slate-700', 'border-slate-200', 'hover:bg-slate-50')
        manageTodayOnlyBtn.classList.remove('bg-cyan-600', 'text-white', 'border-cyan-600', 'hover:bg-cyan-700', 'hover:border-cyan-700')
    }
}

        function confirmAction(message, options) {
            return new Promise(function (resolve) {
                if (!confirmOverlay || !confirmMessage || !confirmOk || !confirmCancel) {
                    resolve(window.confirm(message || 'Are you sure?'))
                    return
                }
                if (confirmDelayTimer) {
                    clearTimeout(confirmDelayTimer)
                    confirmDelayTimer = null
                }
                var opts = (options && typeof options === 'object') ? options : {}
                var okText = String(opts.okText || 'Confirm')
                var delayMs = opts.delayMs != null ? parseInt(opts.delayMs, 10) : 0
                if (isNaN(delayMs) || delayMs < 0) delayMs = 0

                confirmMessage.textContent = message || 'Are you sure?'
                confirmOk.disabled = delayMs > 0
                if (delayMs > 0) {
                    confirmOk.innerHTML = '<span class="inline-flex items-center gap-2"><span class="w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span><span>' + okText + '</span></span>'
                    confirmDelayTimer = setTimeout(function () {
                        confirmOk.disabled = false
                        confirmOk.textContent = okText
                        confirmDelayTimer = null
                    }, delayMs)
                } else {
                    confirmOk.textContent = okText
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
            if (confirmDelayTimer) {
                clearTimeout(confirmDelayTimer)
                confirmDelayTimer = null
            }
            if (confirmOk) {
                confirmOk.disabled = false
                confirmOk.innerHTML = confirmOkDefaultHtml || 'Confirm'
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

        function closeReview(result) {
            if (reviewOverlay) {
                reviewOverlay.classList.add('hidden')
                reviewOverlay.classList.remove('flex')
            }
            var resolver = reviewResolver
            reviewResolver = null
            if (typeof resolver === 'function') resolver(!!result)
        }

        function openReview(details) {
            return new Promise(function (resolve) {
                if (!reviewOverlay || !reviewContent || !reviewOk || !reviewCancel) {
                    resolve(window.confirm('Please review appointment details before submitting.'))
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
                var source = details && typeof details === 'object' ? details : {}
                var rows = Object.keys(source).map(function (key) {
                    return '<li><strong class="font-semibold text-slate-800">' + esc(key) + ':</strong> ' + esc(source[key]) + '</li>'
                })
                reviewContent.innerHTML = '<ul class="space-y-1">' + rows.join('') + '</ul>'
                reviewResolver = resolve
                reviewOverlay.classList.remove('hidden')
                reviewOverlay.classList.add('flex')
            })
        }

        if (reviewOk) reviewOk.addEventListener('click', function () { closeReview(true) })
        if (reviewCancel) reviewCancel.addEventListener('click', function () { closeReview(false) })
        if (reviewOverlay) {
            reviewOverlay.addEventListener('click', function (e) {
                if (e.target === reviewOverlay) closeReview(false)
            })
        }

        function showManageError(message) {
            if (!manageError) return
            manageError.textContent = message || ''
            if (message) {
                manageError.classList.remove('hidden')
            } else {
                manageError.classList.add('hidden')
            }
        }

        function showManageSuccess(message) {
            if (!manageSuccess) return
            manageSuccess.textContent = message || ''
            if (message) {
                manageSuccess.classList.remove('hidden')
            } else {
                manageSuccess.classList.add('hidden')
            }
        }

        function showManageResult(data) {
            if (!manageResult) return
            if (!data) {
                manageResult.classList.add('hidden')
                manageResult.textContent = ''
                return
            }
            try {
                manageResult.textContent = JSON.stringify(data, null, 2)
            } catch (e) {
                manageResult.textContent = String(data)
            }
            manageResult.classList.remove('hidden')
        }

        function wordPrefixMatch(value, query) {
            var v = normalizeText(value || '')
            var q = normalizeText(query || '')
            if (!q) return true
            if (!v) return false
            if (v.indexOf(q) === 0) return true
            return v.split(/\s+/).some(function (part) { return part.indexOf(q) === 0 })
        }

        function patientFullName(patient) {
            if (!patient) return ''
            return [patient.firstname, patient.middlename, patient.lastname]
                .filter(function (v) { return String(v || '').trim() !== '' })
                .join(' ')
                .trim()
        }

        function ageFromBirthdate(dateStr) {
            var raw = String(dateStr || '').slice(0, 10)
            if (!/^\d{4}-\d{2}-\d{2}$/.test(raw)) return ''
            var now = new Date()
            var y = parseInt(raw.slice(0, 4), 10)
            var m = parseInt(raw.slice(5, 7), 10) - 1
            var d = parseInt(raw.slice(8, 10), 10)
            var dob = new Date(y, m, d)
            if (isNaN(dob.getTime())) return ''
            var age = now.getFullYear() - dob.getFullYear()
            var monthDiff = now.getMonth() - dob.getMonth()
            if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < dob.getDate())) {
                age -= 1
            }
            return age < 0 ? '' : String(age)
        }

        function safeIsoParts(iso) {
            var raw = String(iso || '').replace('T', ' ')
            if (raw.length >= 16) raw = raw.slice(0, 16)
            var datePart = raw.slice(0, 10)
            var timePart = raw.slice(11, 16)
            return { date: datePart, time: timePart }
        }

        function serviceSummary(appt) {
            var services = appt && Array.isArray(appt.services) ? appt.services : []
            var names = services
                .map(function (s) { return String((s && s.service_name) ? s.service_name : '').trim() })
                .filter(function (v) { return v !== '' })
            if (!names.length) return '—'
            return names.join(', ')
        }

        function manageStatusLabel(appt) {
            var status = appt && appt.status ? String(appt.status) : ''
            if (!status) return ''
            if (status === 'confirmed') {
                if (appt && appt.check_in_time) return 'checked-in'
            }
            return status.replace(/_/g, ' ')
        }

        function manageRowHtml(appt) {
            var id = appt && appt.appointment_id != null ? appt.appointment_id : ''
            var when = safeIsoParts(appt && appt.appointment_datetime ? appt.appointment_datetime : '')
            var p = appt ? appt.patient : null
            var d = appt ? appt.doctor : null
            var patientName = patientFullName(p) || ('Patient #' + (p && p.user_id != null ? p.user_id : ''))
            var doctorName = patientFullName(d) || ('Doctor #' + (d && d.user_id != null ? d.user_id : ''))
            var age = ageFromBirthdate(p && p.birthdate ? p.birthdate : '')
            var contact = p && p.contact_number ? String(p.contact_number) : '—'
            var serviceText = serviceSummary(appt)
            var statusLabel = manageStatusLabel(appt)
            var statusBadge = statusLabel
                ? ('<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[0.68rem] border border-slate-200 bg-slate-50 text-slate-700">' + escapeHtml(statusLabel) + '</span>')
                : ''

            var statusKey = String(appt && appt.status ? appt.status : '').toLowerCase()
            var canAct = statusKey === 'pending' || statusKey === 'confirmed'
            var canCheckIn = statusKey === 'confirmed' && !(appt && appt.check_in_time)
            var canComplete = statusKey === 'confirmed'
            var canCancel = statusKey === 'pending' || statusKey === 'confirmed'

            var actions = ''
            if (canAct) {
                if (canCheckIn) {
                    actions += '<button type="button" data-action="check_in" data-id="' + escapeHtml(id) + '" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 font-semibold">Check-in</button>'
                }
                if (canComplete) {
                    actions += '<button type="button" data-action="complete" data-id="' + escapeHtml(id) + '" class="px-2.5 py-1 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 font-semibold">Complete</button>'
                }
                if (canCancel) {
                    actions += '<button type="button" data-action="cancel" data-id="' + escapeHtml(id) + '" class="px-2.5 py-1 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 font-semibold">Cancel</button>'
                }
            }

            if (!actions) {
                actions = '<span class="text-slate-400">—</span>'
            } else {
                actions = '<div class="inline-flex items-center gap-1">' + actions + '</div>'
            }

            return (
                '<tr data-appointment-id="' + escapeHtml(id) + '">' +
                    '<td class="px-3 py-2 text-slate-700 whitespace-nowrap">' + escapeHtml(when.date || '—') + statusBadge + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 whitespace-nowrap">' + escapeHtml(when.time ? formatTime12h(when.time) : '—') + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 min-w-[12rem] whitespace-nowrap">' + escapeHtml(patientName) + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 whitespace-nowrap">' + escapeHtml(age || '—') + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 whitespace-nowrap">' + escapeHtml(contact) + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 min-w-[14rem] whitespace-nowrap">' + escapeHtml(serviceText) + '</td>' +
                    '<td class="px-3 py-2 text-slate-700 min-w-[12rem] whitespace-nowrap">' + escapeHtml(doctorName) + '</td>' +
                    '<td class="px-3 py-2 text-right whitespace-nowrap">' +
                        actions +
                    '</td>' +
                '</tr>'
            )
        }

        function applyManageRowUpdate(appt) {
            if (!manageTableBody) return
            if (!appt || appt.appointment_id == null) return
            var id = String(appt.appointment_id)
            var row = manageTableBody.querySelector('tr[data-appointment-id="' + id + '"]')
            if (!row) return
            row.outerHTML = manageRowHtml(appt)
        }

        function renderManageServiceResults() {
            if (!manageServiceResults || !manageServiceSearch) return
            var q = String(manageServiceSearch.value || '').trim()
            var list = Array.isArray(manageServices) ? manageServices : []
            var filtered = list.filter(function (s) {
                var name = s && s.service_name ? String(s.service_name) : ''
                return wordPrefixMatch(name, q)
            })
            filtered = filtered.slice(0, 25)
            if (!filtered.length) {
                manageServiceResults.innerHTML = '<div class="px-3 py-2 text-[0.75rem] text-slate-500">No services found.</div>'
            } else {
                manageServiceResults.innerHTML = filtered.map(function (s) {
                    var id = s.service_id != null ? s.service_id : ''
                    var name = s.service_name != null ? s.service_name : ('Service #' + id)
                    return '<button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 text-[0.78rem] text-slate-700" data-service-id="' + escapeHtml(id) + '">' + escapeHtml(name) + '</button>'
                }).join('')
            }
            manageServiceResults.classList.remove('hidden')
        }

        function setManageServiceSelection(service) {
            if (manageServiceId) manageServiceId.value = service && service.service_id != null ? String(service.service_id) : ''
            if (manageServiceSearch) {
                manageServiceSearch.value = service && service.service_name ? String(service.service_name) : ''
                if (!service) manageServiceSearch.placeholder = 'All services'
            }
            if (manageServiceResults) manageServiceResults.classList.add('hidden')
        }

        function loadManageServices() {
            if (manageServicesLoaded || manageServicesLoading || typeof apiFetch !== 'function') return
            manageServicesLoading = true
            apiFetch("{{ url('/api/services') }}?per_page=100", { method: 'GET' })
                .then(function (response) { return readResponse(response) })
                .then(function (result) {
                    if (!result.ok) return
                    var raw = result.data && Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    manageServices = raw || []
                    manageServicesLoaded = true
                })
                .catch(function () {})
                .finally(function () {
                    manageServicesLoading = false
                })
        }

        function renderManageAppointments(list) {
            if (!manageTableBody) return
            var rows = Array.isArray(list) ? list : []
            if (!rows.length) {
                manageTableBody.innerHTML = '<tr><td colspan="8" class="px-3 py-6 text-center text-[0.78rem] text-slate-500">No appointments found.</td></tr>'
                return
            }
            manageTableBody.innerHTML = rows.map(manageRowHtml).join('')
        }

        function loadManageAppointments() {
            if (typeof apiFetch !== 'function') return
            showManageError('')
            showManageSuccess('')
            showManageResult(null)
            setManageSubmitting(true)

            var url = "{{ url('/api/appointments') }}" + '?per_page=100&appointment_type=scheduled'
            var order = manageSortSelect && manageSortSelect.value ? String(manageSortSelect.value) : 'latest'
            url += '&order=' + encodeURIComponent(order === 'oldest' ? 'oldest' : 'latest')

            var now = new Date()
            var startIso = ''
            var endIso = ''
            if (manageShowTodayOnly) {
                var todayIso = formatLocalDateIso(now)
                startIso = todayIso
                endIso = todayIso
            } else {
                var start = new Date(now.getFullYear(), now.getMonth(), 1)
                var end = new Date(now.getFullYear(), now.getMonth() + 1, 0)
                startIso = formatLocalDateIso(start)
                endIso = formatLocalDateIso(end)
            }
            url += '&start_date=' + encodeURIComponent(startIso)
            url += '&end_date=' + encodeURIComponent(endIso)

            var search = manageSearchInput ? normalizeText(manageSearchInput.value) : ''
            if (search) url += '&search=' + encodeURIComponent(search)

            var serviceId = manageServiceId && manageServiceId.value ? parseInt(manageServiceId.value, 10) : 0
            if (serviceId) url += '&service_id=' + encodeURIComponent(serviceId)

            var statusFilter = manageStatusSelect && manageStatusSelect.value ? String(manageStatusSelect.value) : ''
            if (statusFilter) url += '&status=' + encodeURIComponent(statusFilter)

            apiFetch(url, { method: 'GET' })
                .then(function (response) { return readResponse(response) })
                .then(function (result) {
                    if (!result.ok) {
                        var msg = (result.data && result.data.message) ? String(result.data.message) : 'Failed to load appointments.'
                        showManageError(msg)
                        renderManageAppointments([])
                        return
                    }
                    var raw = result.data && Array.isArray(result.data.data) ? result.data.data : (Array.isArray(result.data) ? result.data : [])
                    var rows = Array.isArray(raw) ? raw.slice() : []

                    function statusRank(appt) {
                        var s = String(appt && appt.status ? appt.status : '').toLowerCase()
                        if (s === 'cancelled') return 3
                        if (s === 'completed') return 2
                        if (s === 'no_show') return 1
                        return 0
                    }

                    rows.sort(function (a, b) {
                        var ra = statusRank(a)
                        var rb = statusRank(b)
                        if (ra < rb) return -1
                        if (ra > rb) return 1

                        var da = a && a.appointment_datetime ? String(a.appointment_datetime) : ''
                        var db = b && b.appointment_datetime ? String(b.appointment_datetime) : ''
                        if (order === 'oldest') {
                            if (da < db) return -1
                            if (da > db) return 1
                            return 0
                        }
                        if (da < db) return 1
                        if (da > db) return -1
                        return 0
                    })

                    renderManageAppointments(rows)

                    if (manageMeta) {
                        if (manageShowTodayOnly) {
                            manageMeta.textContent = 'Showing ' + String(rows.length) + ' appointments for ' + startIso + '.'
                        } else {
                            var monthLabel = startIso.slice(0, 7)
                            manageMeta.textContent = 'Showing ' + String(rows.length) + ' appointments for ' + monthLabel + '.'
                        }
                    }
                })
                .catch(function () {
                    showManageError('Network error while loading appointments.')
                    renderManageAppointments([])
                })
                .finally(function () {
                    setManageSubmitting(false)
                })
        }

        if (manageServiceSearch) {
            manageServiceSearch.addEventListener('focus', function () {
                loadManageServices()
                renderManageServiceResults()
            })
            manageServiceSearch.addEventListener('input', function () {
                if (manageServiceId && manageServiceId.value) {
                    var picked = manageServices.find(function (s) { return String(s.service_id) === String(manageServiceId.value) }) || null
                    var pickedName = picked && picked.service_name ? String(picked.service_name) : ''
                    if (normalizeText(manageServiceSearch.value) !== normalizeText(pickedName)) {
                        setManageServiceSelection(null)
                        loadManageAppointments()
                    }
                }
                loadManageServices()
                renderManageServiceResults()
            })
        }

        if (manageServiceResults) {
            manageServiceResults.addEventListener('click', function (e) {
                var btn = e.target && e.target.closest ? e.target.closest('button[data-service-id]') : null
                if (!btn) return
                var id = btn.getAttribute('data-service-id')
                var picked = manageServices.find(function (s) { return String(s.service_id) === String(id) }) || null
                setManageServiceSelection(picked)
                loadManageAppointments()
            })
        }

        if (manageSearchInput) {
            manageSearchInput.addEventListener('input', function () {
                if (manageSearchTimer) clearTimeout(manageSearchTimer)
                manageSearchTimer = setTimeout(function () {
                    loadManageAppointments()
                }, 250)
            })
        }

        if (manageSortSelect) {
            manageSortSelect.addEventListener('change', function () {
                loadManageAppointments()
            })
        }
        if (manageStatusSelect) {
            manageStatusSelect.addEventListener('change', function () {
                loadManageAppointments()
            })
        }

        if (manageRefreshBtn) {
            manageRefreshBtn.addEventListener('click', function () {
                loadManageAppointments()
            })
        }
        if (manageTodayOnlyBtn) {
            updateManageTodayButton()
            manageTodayOnlyBtn.addEventListener('click', function () {
                manageShowTodayOnly = !manageShowTodayOnly
                updateManageTodayButton()
                loadManageAppointments()
            })
        }

        if (manageTableBody) {
            manageTableBody.addEventListener('click', function (e) {
                var btn = e.target && e.target.closest ? e.target.closest('button[data-action][data-id]') : null
                if (!btn) return
                var action = btn.getAttribute('data-action') || ''
                var id = parseInt(btn.getAttribute('data-id') || '0', 10)
                if (!id) return

                showManageError('')
                showManageSuccess('')
                setManageSubmitting(true)

                var url = "{{ url('/api/appointments') }}/" + encodeURIComponent(id)

                if (action === 'view') {
                    apiFetch(url, { method: 'GET' })
                        .then(function (response) { return readResponse(response) })
                        .then(function (result) {
                            if (!result.ok) {
                                var msg = (result.data && result.data.message) ? String(result.data.message) : 'Failed to fetch appointment.'
                                showManageError(msg)
                                return
                            }
                            showManageSuccess('Appointment details loaded.')
                            showManageResult(result.data)
                        })
                        .catch(function () {
                            showManageError('Network error while fetching appointment.')
                        })
                        .finally(function () {
                            setManageSubmitting(false)
                        })
                    return
                }

                var body = {}
                var confirmText = 'Apply this action?'
                if (action === 'check_in') {
                    var nowCheckIn = new Date()
                    body.check_in_time = formatLocalDateIso(nowCheckIn) + ' ' + nowCheckIn.toTimeString().slice(0, 8)
                    confirmText = 'Mark this appointment as checked-in now?'
                } else if (action === 'cancel') {
                    body.status = 'cancelled'
                    confirmText = 'Cancel this appointment?'
                } else if (action === 'complete') {
                    body.status = 'completed'
                    confirmText = 'Mark this appointment as completed?'
                } else {
                    setManageSubmitting(false)
                    return
                }

                confirmAction(confirmText)
                    .then(function (confirmed) {
                        if (!confirmed) return
                        return apiFetch(url, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(body)
                        })
                            .then(function (response) { return readResponse(response) })
                            .then(function (result) {
                                if (!result.ok) {
                                    var msg = (result.data && result.data.message) ? String(result.data.message) : 'Failed to update appointment.'
                                    showManageError(msg)
                                    return
                                }
                                showManageSuccess('Appointment has been updated.')
                                showManageResult(result.data)
                                applyManageRowUpdate(result.data)
                                loadManageAppointments()
                            })
                    })
                    .catch(function () {
                        showManageError('Network error while updating appointment.')
                    })
                    .finally(function () {
                        setManageSubmitting(false)
                    })
            })
        }

        document.addEventListener('click', function (e) {
            if (!manageServiceResults || !manageServiceSearch) return
            if (manageServiceSearch.contains(e.target) || manageServiceResults.contains(e.target)) return
            manageServiceResults.classList.add('hidden')
        })

        loadManageAppointments()
    })
</script>
