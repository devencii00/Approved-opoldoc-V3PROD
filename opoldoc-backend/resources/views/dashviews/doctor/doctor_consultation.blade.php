@php
    $appointments = $doctorTodayAppointments ?? $doctorRecentAppointments ?? [];
    $initialAppointmentId = request()->query('appointment_id');

    $formatUserName = function ($user) {
        if (! $user) {
            return '';
        }
        $parts = array_filter([
            $user->firstname ?? null,
            $user->middlename ?? null,
            $user->lastname ?? null,
        ], function ($v) {
            return (string) $v !== '';
        });
        $name = trim(implode(' ', $parts));
        return $name !== '' ? $name : ('User #' . ($user->user_id ?? ''));
    };
@endphp

<div class="space-y-4">
    <div>
        <h2 class="text-sm font-semibold text-slate-900">Consultation Workspace</h2>
        <p class="text-xs text-slate-500">Select today’s appointment, review the patient snapshot, and record visit notes + prescriptions.</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-12">
        <div class="lg:col-span-3 bg-white border border-slate-200 rounded-[18px] p-4 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
            <div class="mb-3">
                <label for="consult_appointment" class="block text-[0.7rem] text-slate-600 mb-1">Appointment</label>
                <select id="consult_appointment" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="">Select today’s appointment</option>
                    @foreach ($appointments as $appointment)
                        @php
                            $patientName = $formatUserName($appointment->patient);
                            $labelDate = optional($appointment->appointment_datetime)->format('Y-m-d') ?? '—';
                            $labelTime = optional($appointment->appointment_datetime)->format('H:i') ?? '—';
                            $statusName = strtolower((string) ($appointment->status ?? ''));
                        @endphp
                        <option
                            value="{{ $appointment->appointment_id }}"
                            data-status="{{ $statusName }}"
                            data-label="{{ $patientName }} — {{ $labelDate }} {{ $labelTime }}"
                            {{ (string) $appointment->appointment_id === (string) $initialAppointmentId ? 'selected' : '' }}
                        >
                            {{ $statusName === 'consulted' ? '[ Consulted ] ' : '' }}{{ $patientName }} — {{ $labelDate }} {{ $labelTime }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="consultSnapshotLoading" class="hidden mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.75rem] text-slate-600">Loading patient snapshot…</div>
            <div id="consultSnapshotError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

            <div class="border border-slate-100 rounded-xl bg-slate-50 p-3 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="text-[0.7rem] text-slate-400">Patient</div>
                        <div id="consultPatientName" class="text-[0.95rem] font-semibold text-slate-900">—</div>
                        <div id="consultPatientMeta" class="text-[0.72rem] text-slate-500">—</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[0.7rem] text-slate-400">Appointment</div>
                        <div id="consultApptDateTime" class="text-[0.75rem] font-semibold text-slate-700">—</div>
                        <div id="consultApptType" class="text-[0.72rem] text-slate-500">—</div>
                    </div>
                </div>

                <div>
                    <div class="text-[0.7rem] font-semibold text-slate-700 mb-1">Contacts</div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-[0.72rem] text-slate-600">
                        <div>
                            <dt class="text-slate-400">Phone</dt>
                            <dd id="consultPatientPhone" class="font-medium text-slate-800">—</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Email</dt>
                            <dd id="consultPatientEmail" class="font-medium text-slate-800">—</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-slate-400">Address</dt>
                            <dd id="consultPatientAddress" class="font-medium text-slate-800">—</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <div class="text-[0.7rem] font-semibold text-slate-700 mb-1">Dependent</div>
                    <div id="consultDependentBox" class="rounded-lg border border-slate-100 bg-white px-2.5 py-2 text-[0.72rem] text-slate-600">
                        <div id="consultDependentStatus" class="text-slate-500">—</div>
                        <div id="consultParentName" class="font-semibold text-slate-800"></div>
                        <div id="consultParentMeta" class="text-slate-500"></div>
                    </div>
                </div>

                <div>
                    <div class="text-[0.7rem] font-semibold text-slate-700 mb-1">Medical Background</div>
                    <div class="space-y-2">
                        <div>
                            <div class="text-[0.68rem] text-slate-400 mb-1">Drug allergies</div>
                            <div id="consultAllergyDrug" class="flex flex-wrap gap-1"></div>
                        </div>
                        <div>
                            <div class="text-[0.68rem] text-slate-400 mb-1">Food allergies</div>
                            <div id="consultAllergyFood" class="flex flex-wrap gap-1"></div>
                        </div>
                        <div>
                            <div class="text-[0.68rem] text-slate-400 mb-1">Chronic conditions</div>
                            <div id="consultConditions" class="flex flex-wrap gap-1"></div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-lg border border-slate-100 bg-white px-2.5 py-2">
                        <div class="text-[0.68rem] text-slate-400">Last visit</div>
                        <div id="consultLastVisit" class="text-[0.75rem] font-semibold text-slate-800">—</div>
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-white px-2.5 py-2">
                        <div class="text-[0.68rem] text-slate-400">Total visits</div>
                        <div id="consultTotalVisits" class="text-[0.75rem] font-semibold text-slate-800">—</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-6 bg-white border border-slate-200 rounded-[18px] p-4 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900"></h3>
                    <p class="text-xs text-slate-500">Consultation & Prescription.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="consultVitalsBtn" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 text-[0.78rem] font-semibold text-amber-700 hover:bg-amber-100">
                       Take vitals
                    </button>
                    <!-- <button type="button" id="consultClear" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">
                        Clear
                    </button> -->
                    <button type="button" id="consultSave" class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-3 py-1.5 text-[0.78rem] font-semibold text-white hover:bg-cyan-700 disabled:opacity-70 disabled:hover:bg-cyan-600">
                        <span id="consultSaveSpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        <span id="consultSaveLabel">Submit</span>
                    </button>
                </div>
            </div>

            <div id="consultSaveError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
            <div id="consultSaveSuccess" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[0.75rem] text-emerald-700">
                <div class="flex items-center justify-between gap-3">
                    <span id="consultSaveSuccessText"></span>
                    <button type="button" id="consultPrintReceipt" class="hidden inline-flex items-center justify-center rounded-xl border border-emerald-300 bg-white px-3 py-1.5 text-[0.78rem] font-semibold text-emerald-700 hover:bg-emerald-100">
                        Print receipt
                    </button>
                </div>
            </div>
            <div id="consultSafetyBox" class="hidden mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[0.75rem] text-amber-800 whitespace-pre-line"></div>
            <div id="consultVitalsSummary" class="mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.75rem] text-slate-700">
                No vitals recorded yet. This step is optional.
            </div>
            <div id="consultVitalsFeedback" class="hidden mb-3 rounded-lg border px-3 py-2 text-[0.75rem]"></div>

            <div class="grid gap-3 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="consult_diagnosis" class="block text-[0.7rem] text-slate-600 mb-1">Diagnosis</label>
                    <textarea id="consult_diagnosis" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none min-h-[90px]" placeholder="Enter clinical diagnosis"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="consult_treatment" class="block text-[0.7rem] text-slate-600 mb-1">Treatment notes</label>
                    <textarea id="consult_treatment" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none min-h-[120px]" placeholder="Enter treatment plan, follow-up instructions, and other notes"></textarea>
                </div>
                <!-- <div class="md:col-span-2 flex items-center justify-between gap-2 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                    <div class="text-[0.78rem] text-slate-700">
                        Saving consultation notes keeps the appointment active until payment is recorded
                        and marks the appointment as consulted for reception.
                    </div>
                    <label class="inline-flex items-center gap-2 text-[0.78rem] text-slate-700">
                        <input type="checkbox" id="consultAcknowledgeConflicts" class="rounded border-slate-300 text-amber-600 focus:ring-amber-200">
                        Override safety warnings
                    </label>
                </div> -->
            </div>

            <div class="mt-4 border-t border-slate-100 pt-4">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div>
                        <h4 class="text-xs font-semibold text-slate-900">Prescription items</h4>
                        <p class="text-[0.72rem] text-slate-500">Add medicines, then save to issue the prescription.</p>
                    </div>
                    <button type="button" id="consultAddMedicine" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">
                        + Add medicine
                    </button>
                </div>

                <div id="consultPrescriptionScroller" class="w-full max-w-full overflow-x-auto overflow-y-hidden border border-slate-100 rounded-xl pb-2" style="scrollbar-gutter: stable both-edges;">
                    <table class="w-full min-w-[72rem] table-fixed text-left text-xs text-slate-600">
                        <colgroup>
                            <col style="width: 22rem;">
                            <col style="width: 9rem;">
                            <col style="width: 9rem;">
                            <col style="width: 9rem;">
                            <col style="width: 13rem;">
                            <col style="width: 7rem;">
                        </colgroup>
                        <thead class="bg-slate-50">
                            <tr class="border-b border-slate-100 text-[0.68rem] uppercase tracking-widest text-slate-400">
                                <th class="py-2 px-3 font-semibold whitespace-nowrap">Medicine</th>
                                <th class="py-2 px-3 font-semibold whitespace-nowrap">Dosage</th>
                                <th class="py-2 px-3 font-semibold whitespace-nowrap">Frequency</th>
                                <th class="py-2 px-3 font-semibold whitespace-nowrap">Duration</th>
                                <th class="py-2 px-3 font-semibold whitespace-nowrap">Instructions</th>
                                <th class="py-2 px-3 font-semibold whitespace-nowrap">Remove</th>
                            </tr>
                        </thead>
                        <tbody id="consultPrescriptionBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3 bg-white border border-slate-200 rounded-[18px] p-4 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Patient History</h3>
                    <!-- <p class="text-xs text-slate-500">Recent visits for quick context.</p> -->
                </div>
                <select id="consultHistoryFilter" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-[0.75rem] text-slate-700 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                    <option value="all">All</option>
                    <option value="with_rx">With prescriptions</option>
                </select>
            </div>

            <div id="consultHistoryLoading" class="hidden mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[0.75rem] text-slate-600">Loading history…</div>
            <div id="consultHistoryError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

            <div id="consultHistoryTimeline" class="space-y-2 max-h-[38rem] overflow-y-auto pr-1 scrollbar-hidden"></div>
        </div>
    </div>
</div>

<div id="consultSafetyModal" class="hidden fixed inset-0 z-50 bg-slate-900/70">
    <div class="absolute inset-0 flex items-center justify-center px-4 py-6">
        <div class="w-full max-w-lg rounded-3xl bg-white border border-slate-200 shadow-[0_20px_60px_rgba(15,23,42,0.35)] overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3">
                <div>
                    <div class="text-[0.7rem] uppercase tracking-widest text-slate-400">Safety Warning</div>
                    <div class="text-sm font-semibold text-slate-900">Possible allergy conflict detected</div>
                    <div class="text-xs text-slate-500 mt-1">Review before continuing. Override is required to save if conflicts remain.</div>
                </div>
                <button type="button" id="consultSafetyModalClose" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">
                    Close
                </button>
            </div>
            <div class="px-5 py-4">
                <div id="consultSafetyModalBody" class="text-[0.8rem] text-slate-700 whitespace-pre-line"></div>
                <div class="mt-4 flex items-center justify-between gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                    <div class="text-[0.78rem] text-amber-900">
                        Check <span class="font-semibold">Override safety warnings</span> to proceed with saving.
                    </div>
                    <button type="button" id="consultSafetyModalAcknowledge" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-3 py-1.5 text-[0.78rem] font-semibold text-white hover:bg-amber-700">
                        Override
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="consultVitalsModal" class="hidden fixed inset-0 z-50 bg-slate-900/70">
    <div class="absolute inset-0 flex items-center justify-center px-4 py-6">
        <div class="w-full max-w-xl rounded-3xl bg-white border border-slate-200 shadow-[0_20px_60px_rgba(15,23,42,0.35)] overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3">
                <div>
                    <div class="text-[0.7rem] uppercase tracking-widest text-slate-400">Optional Step</div>
                    <div class="text-sm font-semibold text-slate-900">Record patient vitals</div>
                    <div class="text-xs text-slate-500 mt-1">You can save any of these fields now or close this modal to skip.</div>
                </div>
                <button type="button" id="consultVitalsClose" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">
                    Close
                </button>
            </div>
            <div class="px-5 py-4 space-y-4">
                <div id="consultVitalsModalError" class="hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label for="consult_height_cm" class="block text-[0.7rem] text-slate-600 mb-1">Height (cm)</label>
                        <input id="consult_height_cm" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Optional">
                    </div>
                    <div>
                        <label for="consult_weight_kg" class="block text-[0.7rem] text-slate-600 mb-1">Weight (kg)</label>
                        <input id="consult_weight_kg" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Optional">
                    </div>
                    <div>
                        <label for="consult_blood_pressure" class="block text-[0.7rem] text-slate-600 mb-1">Blood pressure</label>
                        <input id="consult_blood_pressure" type="text" maxlength="20" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Optional">
                    </div>
                    <div>
                        <label for="consult_temperature" class="block text-[0.7rem] text-slate-600 mb-1">Temperature</label>
                        <input id="consult_temperature" type="number" step="0.1" min="0" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Optional">
                    </div>
                    <div class="md:col-span-2">
                        <label for="consult_pulse_rate" class="block text-[0.7rem] text-slate-600 mb-1">Pulse rate</label>
                        <input id="consult_pulse_rate" type="number" step="1" min="0" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Optional">
                    </div>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" id="consultVitalsSkip" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">
                    Skip
                </button>
                <button type="button" id="consultVitalsSave" class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-3 py-2 text-[0.78rem] font-semibold text-white hover:bg-cyan-700">
                    <span id="consultVitalsSaveSpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                    <span id="consultVitalsSaveLabel">Save vitals</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="consultConfirmModal" class="hidden fixed inset-0 z-50 bg-slate-900/70">
    <div class="absolute inset-0 flex items-center justify-center px-4 py-6">
        <div class="w-full max-w-2xl rounded-3xl bg-white border border-slate-200 shadow-[0_20px_60px_rgba(15,23,42,0.35)] overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3">
                <div>
                    <div class="text-[0.7rem] uppercase tracking-widest text-slate-400">Confirm Submission</div>
                    <div class="text-sm font-semibold text-slate-900">Review consultation before saving</div>
                    <div class="text-xs text-slate-500 mt-1">This will save the consultation notes and any prescription items listed below.</div>
                </div>
                <button type="button" id="consultConfirmClose" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">
                    Close
                </button>
            </div>
            <div class="px-5 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <div class="text-[0.7rem] uppercase tracking-widest text-slate-400 mb-2">Consultation Notes</div>
                    <div class="space-y-3">
                        <div>
                            <div class="text-[0.72rem] font-semibold text-slate-700">Diagnosis</div>
                            <div id="consultConfirmDiagnosis" class="mt-1 text-[0.8rem] text-slate-700 whitespace-pre-line">—</div>
                        </div>
                        <div>
                            <div class="text-[0.72rem] font-semibold text-slate-700">Treatment Notes</div>
                            <div id="consultConfirmTreatment" class="mt-1 text-[0.8rem] text-slate-700 whitespace-pre-line">—</div>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <div class="text-[0.7rem] uppercase tracking-widest text-slate-400 mb-2">Prescription Items</div>
                    <div id="consultConfirmPrescription" class="space-y-2 text-[0.8rem] text-slate-700"></div>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" id="consultConfirmCancel" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="button" id="consultConfirmSubmit" class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-3 py-2 text-[0.78rem] font-semibold text-white hover:bg-cyan-700">
                    Confirm Submit
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #consultPrescriptionScroller {
        scrollbar-width: auto;
        scrollbar-color: #94a3b8 #e2e8f0;
    }

    #consultPrescriptionScroller::-webkit-scrollbar {
        height: 12px;
    }

    #consultPrescriptionScroller::-webkit-scrollbar-track {
        background: #e2e8f0;
        border-radius: 9999px;
    }

    #consultPrescriptionScroller::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 9999px;
        border: 2px solid #e2e8f0;
    }

    #consultPrescriptionScroller::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var appointmentSelect = document.getElementById('consult_appointment')
        var snapshotLoading = document.getElementById('consultSnapshotLoading')
        var snapshotError = document.getElementById('consultSnapshotError')
        var saveError = document.getElementById('consultSaveError')
        var saveSuccess = document.getElementById('consultSaveSuccess')
        var saveSuccessText = document.getElementById('consultSaveSuccessText')
        var safetyBox = document.getElementById('consultSafetyBox')
        var vitalsSummary = document.getElementById('consultVitalsSummary')
        var vitalsFeedback = document.getElementById('consultVitalsFeedback')
        var diagnosisEl = document.getElementById('consult_diagnosis')
        var treatmentEl = document.getElementById('consult_treatment')
        var vitalsBtn = document.getElementById('consultVitalsBtn')
        var clearBtn = document.getElementById('consultClear')
        var saveBtn = document.getElementById('consultSave')
        var saveSpinner = document.getElementById('consultSaveSpinner')
        var saveLabel = document.getElementById('consultSaveLabel')
        var printBtn = document.getElementById('consultPrintReceipt')
        var addMedBtn = document.getElementById('consultAddMedicine')
        var prescriptionBody = document.getElementById('consultPrescriptionBody')
        var acknowledgeEl = document.getElementById('consultAcknowledgeConflicts')
        var safetyModal = document.getElementById('consultSafetyModal')
        var safetyModalBody = document.getElementById('consultSafetyModalBody')
        var safetyModalClose = document.getElementById('consultSafetyModalClose')
        var safetyModalAck = document.getElementById('consultSafetyModalAcknowledge')
        var vitalsModal = document.getElementById('consultVitalsModal')
        var vitalsClose = document.getElementById('consultVitalsClose')
        var vitalsSkip = document.getElementById('consultVitalsSkip')
        var vitalsSave = document.getElementById('consultVitalsSave')
        var vitalsSaveSpinner = document.getElementById('consultVitalsSaveSpinner')
        var vitalsSaveLabel = document.getElementById('consultVitalsSaveLabel')
        var vitalsModalError = document.getElementById('consultVitalsModalError')
        var heightEl = document.getElementById('consult_height_cm')
        var weightEl = document.getElementById('consult_weight_kg')
        var bloodPressureEl = document.getElementById('consult_blood_pressure')
        var temperatureEl = document.getElementById('consult_temperature')
        var pulseRateEl = document.getElementById('consult_pulse_rate')
        var confirmModal = document.getElementById('consultConfirmModal')
        var confirmClose = document.getElementById('consultConfirmClose')
        var confirmCancel = document.getElementById('consultConfirmCancel')
        var confirmSubmit = document.getElementById('consultConfirmSubmit')
        var confirmDiagnosis = document.getElementById('consultConfirmDiagnosis')
        var confirmTreatment = document.getElementById('consultConfirmTreatment')
        var confirmPrescription = document.getElementById('consultConfirmPrescription')
        var historyFilter = document.getElementById('consultHistoryFilter')
        var historyLoading = document.getElementById('consultHistoryLoading')
        var historyError = document.getElementById('consultHistoryError')
        var historyTimeline = document.getElementById('consultHistoryTimeline')

        var elPatientName = document.getElementById('consultPatientName')
        var elPatientMeta = document.getElementById('consultPatientMeta')
        var elApptDateTime = document.getElementById('consultApptDateTime')
        var elApptType = document.getElementById('consultApptType')
        var elPhone = document.getElementById('consultPatientPhone')
        var elEmail = document.getElementById('consultPatientEmail')
        var elAddress = document.getElementById('consultPatientAddress')
        var elDepStatus = document.getElementById('consultDependentStatus')
        var elParentName = document.getElementById('consultParentName')
        var elParentMeta = document.getElementById('consultParentMeta')
        var elAllergyDrug = document.getElementById('consultAllergyDrug')
        var elAllergyFood = document.getElementById('consultAllergyFood')
        var elConditions = document.getElementById('consultConditions')
        var elLastVisit = document.getElementById('consultLastVisit')
        var elTotalVisits = document.getElementById('consultTotalVisits')

        var state = {
            doctorUserId: null,
            appointmentId: null,
            patientId: null,
            parentUserId: null,
            transactionId: null,
            prescriptionId: null,
            existingItemIds: [],
            medicalBackground: [],
            medicines: [],
            medicinesById: {},
            history: [],
            historyVitalsByAppointment: {},
            lastSavedTransactionId: null,
            vitals: null,
        }
        var successHideTimer = null

        function setVisible(el, visible) {
            if (!el) return
            if (visible) el.classList.remove('hidden')
            else el.classList.add('hidden')
        }

        function setText(el, text) {
            if (!el) return
            el.textContent = text || '—'
        }

        function setHtml(el, html) {
            if (!el) return
            el.innerHTML = html || ''
        }

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
        }

        function clearSuccessTimer() {
            if (!successHideTimer) return
            clearTimeout(successHideTimer)
            successHideTimer = null
        }

        function setPrintVisible(visible) {
            if (!printBtn) return
            printBtn.classList.toggle('hidden', !visible)
        }

        function showSaveSuccess(message, allowPrint) {
            clearSuccessTimer()
            if (saveSuccessText) saveSuccessText.textContent = message || ''
            else if (saveSuccess) saveSuccess.textContent = message || ''
            setPrintVisible(!!allowPrint)
            setVisible(saveSuccess, true)
            successHideTimer = setTimeout(function () {
                setVisible(saveSuccess, false)
            }, 5000)
        }

        function setVitalsFeedback(message, type) {
            if (!vitalsFeedback) return
            if (!message) {
                vitalsFeedback.textContent = ''
                vitalsFeedback.className = 'hidden mb-3 rounded-lg border px-3 py-2 text-[0.75rem]'
                return
            }

            var cls = 'mb-3 rounded-lg border px-3 py-2 text-[0.75rem] '
            if (type === 'error') cls += 'border-red-200 bg-red-50 text-red-700'
            else cls += 'border-emerald-200 bg-emerald-50 text-emerald-700'
            vitalsFeedback.className = cls
            vitalsFeedback.textContent = message
        }

        function setVitalsModalError(message) {
            if (!vitalsModalError) return
            vitalsModalError.textContent = message || ''
            setVisible(vitalsModalError, !!message)
        }

        function setVitalsLoading(isLoading) {
            if (vitalsSave) vitalsSave.disabled = !!isLoading
            if (vitalsSaveSpinner) vitalsSaveSpinner.classList.toggle('hidden', !isLoading)
            if (vitalsSaveLabel) vitalsSaveLabel.textContent = isLoading ? 'Saving...' : 'Save vitals'
        }

        function modalValue(el) {
            return el ? String(el.value || '').trim() : ''
        }

        function normalizeVitalPayload() {
            var payload = {
                height_cm: modalValue(heightEl),
                weight_kg: modalValue(weightEl),
                blood_pressure: modalValue(bloodPressureEl),
                temperature: modalValue(temperatureEl),
                pulse_rate: modalValue(pulseRateEl),
            }

            Object.keys(payload).forEach(function (key) {
                if (payload[key] === '') payload[key] = null
            })

            return payload
        }

        function applyVitalsToForm(vitals) {
            if (heightEl) heightEl.value = vitals && vitals.height_cm != null ? vitals.height_cm : ''
            if (weightEl) weightEl.value = vitals && vitals.weight_kg != null ? vitals.weight_kg : ''
            if (bloodPressureEl) bloodPressureEl.value = vitals && vitals.blood_pressure != null ? vitals.blood_pressure : ''
            if (temperatureEl) temperatureEl.value = vitals && vitals.temperature != null ? vitals.temperature : ''
            if (pulseRateEl) pulseRateEl.value = vitals && vitals.pulse_rate != null ? vitals.pulse_rate : ''
        }

        function formatVitalNumber(value) {
            if (value == null || value === '') return ''
            var num = Number(value)
            return isNaN(num) ? String(value) : String(num)
        }

        function renderVitalsSummary() {
            if (!vitalsSummary) return
            var vitals = state.vitals
            if (!vitals) {
                vitalsSummary.textContent = 'No vitals recorded yet.'
                return
            }

            var parts = []
            if (vitals.height_cm != null && vitals.height_cm !== '') parts.push('Height: ' + formatVitalNumber(vitals.height_cm) + ' cm')
            if (vitals.weight_kg != null && vitals.weight_kg !== '') parts.push('Weight: ' + formatVitalNumber(vitals.weight_kg) + ' kg')
            if (vitals.blood_pressure) parts.push('BP: ' + vitals.blood_pressure)
            if (vitals.temperature != null && vitals.temperature !== '') parts.push('Temp: ' + formatVitalNumber(vitals.temperature))
            if (vitals.pulse_rate != null && vitals.pulse_rate !== '') parts.push('Pulse: ' + formatVitalNumber(vitals.pulse_rate) + ' bpm')

            vitalsSummary.textContent = parts.length ? parts.join(' • ') : 'No vitals recorded yet. This step is optional.'
        }

        function setSubmitLoading(isLoading) {
            if (saveBtn) saveBtn.disabled = !!isLoading
            if (saveSpinner) saveSpinner.classList.toggle('hidden', !isLoading)
            if (saveLabel) saveLabel.textContent = isLoading ? 'Processing...' : 'Submit'
            if (confirmSubmit) confirmSubmit.disabled = !!isLoading
            if (saveBtn) saveBtn.classList.toggle('cursor-wait', !!isLoading)
        }

        function syncAppointmentOptionLabel(option) {
            if (!option) return
            var baseLabel = option.getAttribute('data-label') || ''
            var status = normalizeString(option.getAttribute('data-status'))
            if (!baseLabel) {
                baseLabel = String(option.textContent || '')
                    .replace(/^\s*\[\s*Consulted\s*\]\s*/i, '')
                    .trim()
                option.setAttribute('data-label', baseLabel)
            }
            option.textContent = (status === 'consulted' ? '[ Consulted ] ' : '') + baseLabel
        }

        function markSelectedAppointmentConsulted() {
            if (!appointmentSelect) return
            var option = appointmentSelect.options[appointmentSelect.selectedIndex]
            if (!option) return
            option.setAttribute('data-status', 'consulted')
            syncAppointmentOptionLabel(option)
        }

        function badge(label, variant) {
            var cls = 'inline-flex items-center rounded-full border px-2 py-0.5 text-[0.68rem] font-medium '
            if (variant === 'danger') cls += 'bg-red-50 border-red-200 text-red-700'
            else if (variant === 'warn') cls += 'bg-amber-50 border-amber-200 text-amber-800'
            else cls += 'bg-white border-slate-200 text-slate-700'
            return '<span class="' + cls + '">' + (label || '—') + '</span>'
        }

        function showSafetyModal(text) {
            if (safetyModalBody) safetyModalBody.textContent = text || ''
            setVisible(safetyModal, true)
        }

        function hideSafetyModal() {
            setVisible(safetyModal, false)
        }

        function openConfirmModal() {
            setVisible(confirmModal, true)
        }

        function closeConfirmModal() {
            setVisible(confirmModal, false)
        }

        function openVitalsModal() {
            if (!state.appointmentId) {
                saveError.textContent = 'Select an appointment first.'
                setVisible(saveError, true)
                return
            }
            setVisible(saveError, false)
            setVitalsModalError('')
            applyVitalsToForm(state.vitals)
            setVisible(vitalsModal, true)
        }

        function closeVitalsModal() {
            setVisible(vitalsModal, false)
            setVitalsModalError('')
        }

        function api(url, options) {
            if (!window.apiFetch) {
                return Promise.reject(new Error('API client is not available.'))
            }
            return window.apiFetch(url, options || {})
                .then(function (res) {
                    if (!res.ok) {
                        return res.text().then(function (txt) {
                            var err = new Error('Request failed')
                            err.status = res.status
                            err.body = txt
                            throw err
                        })
                    }
                    return res.json()
                })
        }

        function formatName(user) {
            if (!user) return '—'
            var parts = []
            if (user.firstname) parts.push(user.firstname)
            if (user.middlename) parts.push(user.middlename)
            if (user.lastname) parts.push(user.lastname)
            var name = parts.join(' ').trim()
            return name || ('User #' + (user.user_id || ''))
        }

        function medicineDisplayName(med) {
            if (!med) return '—'
            var generic = med.generic_name || ''
            var brand = med.brand_name || ''
            if (generic && brand) return generic + ' (' + brand + ')'
            return generic || brand || ('Medicine #' + (med.medicine_id || ''))
        }

        function computeAgeFromBirthdate(birthdate) {
            if (!birthdate) return ''
            var d = new Date(birthdate)
            if (isNaN(d.getTime())) return ''
            var now = new Date()
            var years = now.getFullYear() - d.getFullYear()
            var m = now.getMonth() - d.getMonth()
            if (m < 0 || (m === 0 && now.getDate() < d.getDate())) years--
            return years >= 0 ? String(years) : ''
        }

        function normalizeString(v) {
            return (v || '').toString().toLowerCase()
        }

        function getPaginatedData(resp) {
            if (!resp) return []
            if (Array.isArray(resp)) return resp
            if (Array.isArray(resp.data)) return resp.data
            return []
        }

        function resetWorkspace() {
            state.patientId = null
            state.parentUserId = null
            state.transactionId = null
            state.prescriptionId = null
            state.existingItemIds = []
            state.lastSavedTransactionId = null
            state.medicalBackground = []
            state.history = []
            state.vitals = null
            setText(elPatientName, '—')
            setText(elPatientMeta, '—')
            setText(elApptDateTime, '—')
            setText(elApptType, '—')
            setText(elPhone, '—')
            setText(elEmail, '—')
            setText(elAddress, '—')
            setText(elDepStatus, '—')
            setText(elParentName, '')
            setText(elParentMeta, '')
            setHtml(elAllergyDrug, '')
            setHtml(elAllergyFood, '')
            setHtml(elConditions, '')
            setText(elLastVisit, '—')
            setText(elTotalVisits, '—')
            if (diagnosisEl) diagnosisEl.value = ''
            if (treatmentEl) treatmentEl.value = ''
            if (prescriptionBody) prescriptionBody.innerHTML = ''
            if (historyTimeline) historyTimeline.innerHTML = ''
            setVisible(snapshotError, false)
            setVisible(saveError, false)
            setVisible(saveSuccess, false)
            setVisible(safetyBox, false)
            setVitalsFeedback('')
            renderVitalsSummary()
            applyVitalsToForm(null)
            if (acknowledgeEl) acknowledgeEl.checked = false
            setPrintVisible(false)
            clearSuccessTimer()
        }

        function ensureRow(item) {
            if (!prescriptionBody) return
            var tr = document.createElement('tr')
            tr.className = 'border-b border-slate-50 last:border-0 align-top'
            tr.innerHTML = '' +
                '<td class="py-2 px-3 align-top">' +
                    '<select class="consult-med h-9 w-full min-w-0 rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none"></select>' +
                    '<div class="mt-1 space-y-1">' +
                        '<div class="text-[0.68rem] text-slate-400">Indications: <span class="consult-ind text-slate-600"></span></div>' +
                        '<div class="text-[0.68rem] text-slate-400">Contra: <span class="consult-contra text-slate-600"></span></div>' +
                    '</div>' +
                '</td>' +
                '<td class="py-2 px-3 align-top"><input class="consult-dose h-9 w-full min-w-0 rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="e.g. 500mg"></td>' +
                '<td class="py-2 px-3 align-top"><input class="consult-freq h-9 w-full min-w-0 rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="e.g. BID"></td>' +
                '<td class="py-2 px-3 align-top"><input class="consult-dur h-9 w-full min-w-0 rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="e.g. 7 days"></td>' +
                '<td class="py-2 px-3 align-top"><input class="consult-inst h-9 w-full min-w-0 rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="e.g. after meals"></td>' +
                '<td class="py-2 px-3 align-top"><button type="button" class="consult-remove mt-0.5 inline-flex h-9 w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-2 py-1 text-[0.72rem] font-semibold text-slate-700 hover:bg-slate-50">Remove</button></td>'

            var sel = tr.querySelector('.consult-med')
            var ind = tr.querySelector('.consult-ind')
            var contra = tr.querySelector('.consult-contra')
            var removeBtn = tr.querySelector('.consult-remove')

            var opts = ['<option value="">Select</option>']
            state.medicines.forEach(function (m) {
                opts.push('<option value="' + m.medicine_id + '">' + medicineDisplayName(m) + '</option>')
            })
            sel.innerHTML = opts.join('')

            function updateMeta() {
                var id = sel.value
                var med = state.medicinesById[id]
                ind.textContent = med && med.indications ? med.indications : '—'
                contra.textContent = med && med.contraindications ? med.contraindications : '—'
                renderSafety()

                var conflicts = computeConflicts()
                var hasConflict = conflicts.some(function (c) {
                    return normalizeString(c.medicine) === normalizeString(medicineDisplayName(med))
                })

                if (hasConflict) {
                    sel.classList.add('border-red-300')
                    sel.classList.add('bg-red-50')
                    sel.classList.add('focus:border-red-400')
                    sel.classList.add('focus:ring-red-200')

                    var lines = conflicts
                        .filter(function (c) { return normalizeString(c.medicine) === normalizeString(medicineDisplayName(med)) })
                        .slice(0, 8)
                        .map(function (c) { return '• ' + c.medicine + ' vs allergy "' + c.allergy + '"' })
                        .join('\n')
                    showSafetyModal('Possible allergy conflicts:\n' + lines)
                } else {
                    sel.classList.remove('border-red-300')
                    sel.classList.remove('bg-red-50')
                    sel.classList.remove('focus:border-red-400')
                    sel.classList.remove('focus:ring-red-200')
                }
            }

            sel.addEventListener('change', updateMeta)
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    tr.remove()
                    renderSafety()
                })
            }

            if (item) {
                if (item.medicine_id) sel.value = String(item.medicine_id)
                var dose = tr.querySelector('.consult-dose')
                var freq = tr.querySelector('.consult-freq')
                var dur = tr.querySelector('.consult-dur')
                var inst = tr.querySelector('.consult-inst')
                if (dose && item.dosage) dose.value = item.dosage
                if (freq && item.frequency) freq.value = item.frequency
                if (dur && item.duration) dur.value = item.duration
                if (inst && item.instructions) inst.value = item.instructions
            }

            updateMeta()
            prescriptionBody.appendChild(tr)
        }

        function getPrescriptionRows() {
            if (!prescriptionBody) return []
            var trs = Array.prototype.slice.call(prescriptionBody.querySelectorAll('tr'))
            return trs.map(function (tr) {
                return {
                    tr: tr,
                    medicine_id: tr.querySelector('.consult-med') ? tr.querySelector('.consult-med').value : '',
                    dosage: tr.querySelector('.consult-dose') ? tr.querySelector('.consult-dose').value : '',
                    frequency: tr.querySelector('.consult-freq') ? tr.querySelector('.consult-freq').value : '',
                    duration: tr.querySelector('.consult-dur') ? tr.querySelector('.consult-dur').value : '',
                    instructions: tr.querySelector('.consult-inst') ? tr.querySelector('.consult-inst').value : '',
                }
            }).filter(function (r) {
                return r.medicine_id
            })
        }

        function renderConfirmSummary() {
            var rows = getPrescriptionRows()
            if (confirmDiagnosis) {
                confirmDiagnosis.textContent = diagnosisEl && String(diagnosisEl.value || '').trim()
                    ? String(diagnosisEl.value || '').trim()
                    : 'No diagnosis entered.'
            }
            if (confirmTreatment) {
                confirmTreatment.textContent = treatmentEl && String(treatmentEl.value || '').trim()
                    ? String(treatmentEl.value || '').trim()
                    : 'No treatment notes entered.'
            }
            if (confirmPrescription) {
                if (!rows.length) {
                    confirmPrescription.innerHTML = '<div class="text-slate-500">No prescription items added.</div>'
                } else {
                    confirmPrescription.innerHTML = rows.map(function (row, index) {
                        var med = state.medicinesById[String(row.medicine_id)]
                        var parts = [row.dosage, row.frequency, row.duration, row.instructions]
                            .filter(function (part) { return String(part || '').trim() !== '' })
                            .map(function (part) { return escapeHtml(String(part)) })
                        return '' +
                            '<div class="rounded-xl border border-slate-100 bg-white px-3 py-2">' +
                                '<div class="font-semibold text-slate-900">' + escapeHtml((index + 1) + '. ' + medicineDisplayName(med)) + '</div>' +
                                '<div class="mt-1 text-[0.75rem] text-slate-600">' + (parts.length ? parts.join(' • ') : 'No dosage details provided.') + '</div>' +
                            '</div>'
                    }).join('')
                }
            }
        }

        function renderBackground(backgrounds) {
            var drug = []
            var food = []
            var cond = []

            backgrounds.forEach(function (b) {
                var cat = normalizeString(b.category)
                if (cat === 'allergy_drug') drug.push(b)
                else if (cat === 'allergy_food') food.push(b)
                else if (cat === 'condition') cond.push(b)
            })

            setHtml(elAllergyDrug, drug.length ? drug.map(function (b) { return badge(b.name || '—', 'danger') }).join(' ') : '<span class="text-[0.72rem] text-slate-400">None recorded</span>')
            setHtml(elAllergyFood, food.length ? food.map(function (b) { return badge(b.name || '—', 'warn') }).join(' ') : '<span class="text-[0.72rem] text-slate-400">None recorded</span>')
            setHtml(elConditions, cond.length ? cond.map(function (b) { return badge(b.name || '—', 'default') }).join(' ') : '<span class="text-[0.72rem] text-slate-400">None recorded</span>')
        }

        function renderHistory() {
            if (!historyTimeline) return
            var filter = historyFilter ? historyFilter.value : 'all'
            var items = state.history.slice()
            if (filter === 'with_rx') {
                items = items.filter(function (tx) {
                    return tx.prescriptions && tx.prescriptions.length
                })
            }

            if (!items.length) {
                historyTimeline.innerHTML = '<div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3 text-[0.78rem] text-slate-500">No visit history found.</div>'
                return
            }

            historyTimeline.innerHTML = items.map(function (tx) {
                var dt = tx.visit_datetime || tx.transaction_datetime || ''
                var dateStr = dt ? dt.toString().slice(0, 10) : '—'
                var timeStr = dt ? dt.toString().slice(11, 16) : ''
                var dx = tx.diagnosis ? tx.diagnosis : 'No diagnosis'
                var notes = tx.treatment_notes ? tx.treatment_notes : ''
                var appointmentId = tx && tx.appointment_id != null
                    ? String(tx.appointment_id)
                    : (tx && tx.appointment && tx.appointment.appointment_id != null ? String(tx.appointment.appointment_id) : '')
                var visitVitals = appointmentId ? state.historyVitalsByAppointment[appointmentId] : null
                var rx = tx.prescriptions || []
                var rxLines = []
                rx.forEach(function (p) {
                    var items = p.items || []
                    items.forEach(function (it) {
                        var medName = it.medicine ? medicineDisplayName(it.medicine) : ('Medicine #' + it.medicine_id)
                        var line = medName
                        if (it.dosage) line += ' • ' + it.dosage
                        if (it.frequency) line += ' • ' + it.frequency
                        if (it.duration) line += ' • ' + it.duration
                        rxLines.push(line)
                    })
                })
                var rxHtml = rxLines.length
                    ? '<ul class="mt-2 space-y-1 text-[0.72rem] text-slate-600">' + rxLines.slice(0, 6).map(function (l) { return '<li class="flex gap-2"><span class="text-slate-400">•</span><span>' + l + '</span></li>' }).join('') + '</ul>'
                    : '<div class="mt-2 text-[0.72rem] text-slate-400">No prescriptions</div>'

                var notesHtml = notes ? '<div class="mt-2 text-[0.72rem] text-slate-600">' + notes + '</div>' : ''
                var vitalsRows = []
                if (visitVitals && visitVitals.height_cm != null && visitVitals.height_cm !== '') vitalsRows.push('<div><span class="font-semibold text-slate-700">Height:</span> ' + escapeHtml(formatVitalNumber(visitVitals.height_cm)) + ' cm</div>')
                if (visitVitals && visitVitals.weight_kg != null && visitVitals.weight_kg !== '') vitalsRows.push('<div><span class="font-semibold text-slate-700">Weight:</span> ' + escapeHtml(formatVitalNumber(visitVitals.weight_kg)) + ' kg</div>')
                if (visitVitals && visitVitals.blood_pressure) vitalsRows.push('<div><span class="font-semibold text-slate-700">Blood pressure:</span> ' + escapeHtml(visitVitals.blood_pressure) + '</div>')
                if (visitVitals && visitVitals.temperature != null && visitVitals.temperature !== '') vitalsRows.push('<div><span class="font-semibold text-slate-700">Temperature:</span> ' + escapeHtml(formatVitalNumber(visitVitals.temperature)) + '</div>')
                if (visitVitals && visitVitals.pulse_rate != null && visitVitals.pulse_rate !== '') vitalsRows.push('<div><span class="font-semibold text-slate-700">Pulse rate:</span> ' + escapeHtml(formatVitalNumber(visitVitals.pulse_rate)) + ' bpm</div>')
                var vitalsButtonLabel = vitalsRows.length ? 'Show vitals' : 'No vitals recorded'
                var vitalsButtonDisabled = vitalsRows.length ? '' : ' disabled'
                var vitalsButtonClass = vitalsRows.length
                    ? 'mt-2 inline-flex items-center justify-center rounded-lg border border-cyan-200 bg-cyan-50 px-2.5 py-1 text-[0.72rem] font-semibold text-cyan-700 hover:bg-cyan-100'
                    : 'mt-2 inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[0.72rem] font-semibold text-slate-400 cursor-not-allowed'
                var vitalsHtml = vitalsRows.length
                    ? '<div class="mt-2">' +
                        '<button type="button" class="' + vitalsButtonClass + '" data-toggle-history-vitals="' + escapeHtml(String(tx.transaction_id || '')) + '" data-expand-label="Show vitals" data-collapse-label="Retract vitals">' + vitalsButtonLabel + '</button>' +
                        '<div class="hidden mt-2 rounded-lg border border-cyan-100 bg-white px-3 py-2 text-[0.72rem] text-slate-600 space-y-1" data-history-vitals-body="' + escapeHtml(String(tx.transaction_id || '')) + '">' + vitalsRows.join('') + '</div>' +
                    '</div>'
                    : '<div class="mt-2"><button type="button" class="' + vitalsButtonClass + '"' + vitalsButtonDisabled + '>' + vitalsButtonLabel + '</button></div>'

                return '' +
                    '<div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3">' +
                        '<div class="flex items-start justify-between gap-2">' +
                            '<div>' +
                                '<div class="text-[0.68rem] uppercase tracking-widest text-slate-400">Visit</div>' +
                                '<div class="text-[0.85rem] font-semibold text-slate-900">' + dateStr + (timeStr ? (' ' + timeStr) : '') + '</div>' +
                            '</div>' +
                            '<div class="text-right text-[0.72rem] text-slate-400">#' + (tx.transaction_id || '') + '</div>' +
                        '</div>' +
                        '<div class="mt-2 text-[0.78rem] text-slate-700"><span class="font-semibold">Dx:</span> ' + dx + '</div>' +
                        notesHtml +
                        vitalsHtml +
                        rxHtml +
                    '</div>'
            }).join('')
        }

        function computeConflicts() {
            var drugAllergies = state.medicalBackground
                .filter(function (b) { return normalizeString(b.category) === 'allergy_drug' })
                .map(function (b) { return normalizeString(b.name) })
                .filter(Boolean)

            if (!drugAllergies.length) return []

            var rows = getPrescriptionRows()
            var conflicts = []
            rows.forEach(function (r) {
                var med = state.medicinesById[r.medicine_id]
                if (!med) return
                var contra = normalizeString(med.contraindications || '')
                var medName = normalizeString(medicineDisplayName(med))
                drugAllergies.forEach(function (a) {
                    if (a && (contra.indexOf(a) !== -1 || medName.indexOf(a) !== -1)) {
                        conflicts.push({ allergy: a, medicine: medicineDisplayName(med) })
                    }
                })
            })
            return conflicts
        }

        function renderSafety() {
            var conflicts = computeConflicts()
            if (!conflicts.length) {
                setVisible(safetyBox, false)
                return
            }
            var lines = conflicts.slice(0, 8).map(function (c) {
                return '• Possible conflict: ' + c.medicine + ' vs allergy "' + c.allergy + '"'
            })
            safetyBox.textContent = 'Safety warnings:\n' + lines.join('\n')
            setVisible(safetyBox, true)
        }

        function loadMedicines() {
            return api('{{ url('/api/medicines') }}?per_page=200').then(function (resp) {
                state.medicines = getPaginatedData(resp)
                state.medicinesById = {}
                state.medicines.forEach(function (m) {
                    state.medicinesById[String(m.medicine_id)] = m
                })
            })
        }

        function loadDoctorUser() {
            return api('{{ url('/api/user') }}').then(function (u) {
                state.doctorUserId = u && u.user_id ? u.user_id : null
            })
        }

        function loadAppointment(appointmentId) {
            setVisible(snapshotError, false)
            setVisible(snapshotLoading, true)
            return api('{{ url('/api/appointments') }}/' + appointmentId).then(function (appt) {
                state.appointmentId = appt.appointment_id
                state.patientId = appt.patient_id
                state.parentUserId = appt.patient && appt.patient.parent_user_id ? appt.patient.parent_user_id : null
                state.transactionId = appt.transaction ? appt.transaction.transaction_id : null
                state.prescriptionId = null
                state.existingItemIds = []

                setText(elPatientName, formatName(appt.patient))
                var sex = appt.patient && appt.patient.sex ? appt.patient.sex : ''
                var age = appt.patient && appt.patient.age ? String(appt.patient.age) : computeAgeFromBirthdate(appt.patient ? appt.patient.birthdate : '')
                var metaParts = []
                if (sex) metaParts.push(sex)
                if (age) metaParts.push(age + ' yrs')
                metaParts.push(appt.patient && appt.patient.is_dependent ? 'Dependent' : 'Regular')
                setText(elPatientMeta, metaParts.filter(Boolean).join(' • ') || '—')

                var dt = appt.appointment_datetime || ''
                var dateStr = dt ? dt.toString().slice(0, 10) : '—'
                var timeStr = dt ? dt.toString().slice(11, 16) : '—'
                setText(elApptDateTime, dateStr + ' ' + timeStr)
                setText(elApptType, appt.appointment_type ? appt.appointment_type.toString().replace('_', '-') : '—')
                setText(elPhone, appt.patient && appt.patient.contact_number ? appt.patient.contact_number : '—')
                setText(elEmail, appt.patient && appt.patient.email ? appt.patient.email : '—')
                setText(elAddress, appt.patient && appt.patient.address ? appt.patient.address : '—')

                if (appt.patient && appt.patient.is_dependent && state.parentUserId) {
                    setText(elDepStatus, 'Dependent of:')
                    return api('{{ url('/api/users') }}/' + state.parentUserId).then(function (parent) {
                        setText(elParentName, formatName(parent))
                        var pPhone = parent && parent.contact_number ? parent.contact_number : ''
                        var pEmail = parent && parent.email ? parent.email : ''
                        setText(elParentMeta, [pPhone, pEmail].filter(Boolean).join(' • '))
                    }).catch(function () {
                        setText(elParentName, 'Parent record unavailable')
                        setText(elParentMeta, '')
                    })
                }

                setText(elDepStatus, appt.patient && appt.patient.is_dependent ? 'Dependent' : 'Not a dependent')
                setText(elParentName, '')
                setText(elParentMeta, '')
            }).catch(function (err) {
                snapshotError.textContent = err && err.body ? err.body : 'Unable to load appointment details.'
                setVisible(snapshotError, true)
                throw err
            }).finally(function () {
                setVisible(snapshotLoading, false)
            })
        }

        function loadMedicalBackground(patientId) {
            return api('{{ url('/api/medical-backgrounds') }}?patient_id=' + patientId + '&per_page=200').then(function (resp) {
                state.medicalBackground = getPaginatedData(resp)
                renderBackground(state.medicalBackground)
                renderSafety()
            })
        }

        function loadVitals(patientId, appointmentId) {
            state.vitals = null
            renderVitalsSummary()
            applyVitalsToForm(null)
            if (!patientId || !appointmentId) {
                return Promise.resolve()
            }

            return api('{{ url('/api/vitals') }}?patient_id=' + encodeURIComponent(String(patientId)) + '&appointment_id=' + encodeURIComponent(String(appointmentId)) + '&per_page=1')
                .then(function (resp) {
                    var rows = getPaginatedData(resp)
                    state.vitals = rows.length ? rows[0] : null
                    applyVitalsToForm(state.vitals)
                    renderVitalsSummary()
                }).catch(function () {
                    state.vitals = null
                    renderVitalsSummary()
                })
        }

        function loadHistoryVitals(patientId) {
            state.historyVitalsByAppointment = {}
            if (!patientId) {
                return Promise.resolve()
            }

            return api('{{ url('/api/vitals') }}?patient_id=' + encodeURIComponent(String(patientId)) + '&per_page=200')
                .then(function (resp) {
                    var rows = getPaginatedData(resp)
                    var byAppointment = {}
                    rows.forEach(function (row) {
                        if (!row || row.appointment_id == null) return
                        var key = String(row.appointment_id)
                        if (!byAppointment[key]) byAppointment[key] = row
                    })
                    state.historyVitalsByAppointment = byAppointment
                }).catch(function () {
                    state.historyVitalsByAppointment = {}
                })
        }

        function loadHistory(patientId) {
            setVisible(historyError, false)
            setVisible(historyLoading, true)
            return Promise.all([
                api('{{ url('/api/visits') }}?patient_id=' + patientId + '&per_page=50'),
                loadHistoryVitals(patientId),
            ]).then(function (results) {
                var resp = results[0]
                state.history = getPaginatedData(resp)
                setText(elTotalVisits, String(state.history.length))
                var last = state.history.length ? state.history[0] : null
                var dt = last ? (last.visit_datetime || last.transaction_datetime || '') : ''
                setText(elLastVisit, dt ? dt.toString().slice(0, 10) : '—')
                renderHistory()
            }).catch(function (err) {
                historyError.textContent = err && err.body ? err.body : 'Unable to load patient history.'
                setVisible(historyError, true)
            }).finally(function () {
                setVisible(historyLoading, false)
            })
        }

        function loadExistingDraft() {
            if (!state.transactionId) {
                return Promise.resolve()
            }
            return api('{{ url('/api/transactions') }}/' + state.transactionId).then(function (tx) {
                if (diagnosisEl) diagnosisEl.value = tx.diagnosis || ''
                if (treatmentEl) treatmentEl.value = tx.treatment_notes || ''
                var rx = tx.prescriptions && tx.prescriptions.length ? tx.prescriptions[0] : null
                if (rx) {
                    state.prescriptionId = rx.prescription_id
                    state.existingItemIds = (rx.items || []).map(function (it) { return it.item_id })
                    if (prescriptionBody) prescriptionBody.innerHTML = ''
                    if (rx.items && rx.items.length) {
                        rx.items.forEach(function (it) {
                            ensureRow({
                                medicine_id: it.medicine_id,
                                dosage: it.dosage,
                                frequency: it.frequency,
                                duration: it.duration,
                                instructions: it.instructions,
                            })
                        })
                    }
                }
            })
        }

        function saveVitals() {
            setVitalsModalError('')
            setVitalsFeedback('')

            if (!state.appointmentId) {
                setVitalsModalError('Select an appointment first.')
                return Promise.resolve(false)
            }

            var payload = normalizeVitalPayload()
            var hasAnyValue = Object.keys(payload).some(function (key) {
                return payload[key] != null && String(payload[key]).trim() !== ''
            })

            if (!hasAnyValue) {
                setVitalsModalError('Provide at least one vital sign or close the modal to skip.')
                return Promise.resolve(false)
            }

            payload.appointment_id = state.appointmentId
            setVitalsLoading(true)

            return api('{{ url('/api/vitals') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            }).then(function (vital) {
                state.vitals = vital || null
                applyVitalsToForm(state.vitals)
                renderVitalsSummary()
                closeVitalsModal()
                setVitalsFeedback('Vitals saved successfully.', 'success')
                return true
            }).catch(function (err) {
                var message = err && err.body ? err.body : 'Unable to save vitals.'
                try {
                    var parsed = JSON.parse(message)
                    if (parsed && parsed.message) message = parsed.message
                } catch (e) {}
                setVitalsModalError(message)
                setVitalsFeedback(message, 'error')
                return false
            }).finally(function () {
                setVitalsLoading(false)
            })
        }

        function saveAll() {
            setVisible(saveError, false)
            setVisible(saveSuccess, false)
            clearSuccessTimer()

            if (!state.appointmentId) {
                saveError.textContent = 'Select an appointment first.'
                setVisible(saveError, true)
                return Promise.resolve(false)
            }

            var conflicts = computeConflicts()
            if (conflicts.length && (!acknowledgeEl || !acknowledgeEl.checked)) {
                saveError.textContent = 'Safety warnings detected. Check "Override safety warnings" to proceed.'
                setVisible(saveError, true)
                return Promise.resolve(false)
            }

            var prescriptionRows = getPrescriptionRows()
            var shouldSavePrescription = prescriptionRows.length > 0 || !!state.prescriptionId

            var payload = {
                appointment_id: state.appointmentId,
                diagnosis: diagnosisEl ? diagnosisEl.value : '',
                treatment_notes: treatmentEl ? treatmentEl.value : '',
                visit_datetime: new Date().toISOString().slice(0, 19).replace('T', ' '),
            }

            var txPromise = state.transactionId
                ? api('{{ url('/api/transactions') }}/' + state.transactionId, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                })
                : api('{{ url('/api/transactions') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                })

            return txPromise.then(function (tx) {
                state.transactionId = tx.transaction_id
                state.lastSavedTransactionId = tx.transaction_id
                var rxPayload = {
                    transaction_id: state.transactionId,
                    doctor_id: state.doctorUserId,
                    prescribed_datetime: new Date().toISOString().slice(0, 19).replace('T', ' '),
                    notes: null,
                }

                if (!shouldSavePrescription) {
                    state.prescriptionId = null
                    return null
                }

                var rxPromise = state.prescriptionId
                    ? api('{{ url('/api/prescriptions') }}/' + state.prescriptionId, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(rxPayload),
                    })
                    : api('{{ url('/api/prescriptions') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(rxPayload),
                    })

                return rxPromise.then(function (rx) {
                    state.prescriptionId = rx.prescription_id

                    var deletes = state.existingItemIds.reduce(function (p, id) {
                        return p.then(function () {
                            return api('{{ url('/api/prescription-items') }}/' + id, { method: 'DELETE' }).catch(function () {})
                        })
                    }, Promise.resolve())

                    return deletes.then(function () {
                        return prescriptionRows.reduce(function (p, row) {
                            return p.then(function () {
                                return api('{{ url('/api/prescription-items') }}', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        prescription_id: state.prescriptionId,
                                        medicine_id: Number(row.medicine_id),
                                        dosage: row.dosage || null,
                                        frequency: row.frequency || null,
                                        duration: row.duration || null,
                                        instructions: row.instructions || null,
                                    }),
                                })
                            })
                        }, Promise.resolve())
                    })
                })
            }).then(function () {
                return loadExistingDraft()
            }).then(function () {
                var successMessage = shouldSavePrescription
                    ? 'Saved consultation and prescription successfully. Appointment is now consulted.'
                    : 'Saved consultation notes successfully. Appointment is now consulted and remains active until payment is recorded.'
                markSelectedAppointmentConsulted()
                showSaveSuccess(successMessage, !!state.lastSavedTransactionId)
                return loadHistory(state.patientId)
            }).then(function () {
                return true
            }).catch(function (err) {
                saveError.textContent = err && err.body ? err.body : 'Unable to save consultation.'
                setVisible(saveError, true)
                return false
            })
        }

        function handleAppointmentChange() {
            var id = appointmentSelect ? appointmentSelect.value : ''
            resetWorkspace()
            if (!id) return
            state.appointmentId = id
            loadAppointment(id).then(function () {
                return Promise.all([
                    loadMedicalBackground(state.patientId),
                    loadHistory(state.patientId),
                    loadVitals(state.patientId, state.appointmentId),
                    loadExistingDraft(),
                ])
            }).then(function () {
                if (prescriptionBody && !prescriptionBody.querySelector('tr')) {
                    ensureRow()
                }
                renderSafety()
            }).catch(function () {})
        }

        if (historyFilter) {
            historyFilter.addEventListener('change', renderHistory)
        }
        if (historyTimeline) {
            historyTimeline.addEventListener('click', function (e) {
                var btn = e.target && e.target.closest ? e.target.closest('[data-toggle-history-vitals]') : null
                if (!btn || btn.disabled) return
                var id = btn.getAttribute('data-toggle-history-vitals')
                var body = historyTimeline.querySelector('[data-history-vitals-body="' + id + '"]')
                if (!body) return
                var isHidden = body.classList.contains('hidden')
                body.classList.toggle('hidden', !isHidden)
                btn.textContent = isHidden
                    ? (btn.getAttribute('data-collapse-label') || 'Retract vitals')
                    : (btn.getAttribute('data-expand-label') || 'Show vitals')
            })
        }

        if (appointmentSelect) {
            appointmentSelect.addEventListener('change', handleAppointmentChange)
        }

        if (vitalsBtn) {
            vitalsBtn.addEventListener('click', openVitalsModal)
        }
        if (vitalsClose) {
            vitalsClose.addEventListener('click', closeVitalsModal)
        }
        if (vitalsSkip) {
            vitalsSkip.addEventListener('click', closeVitalsModal)
        }
        if (vitalsModal) {
            vitalsModal.addEventListener('click', function (e) {
                if (e.target === vitalsModal) closeVitalsModal()
            })
        }
        if (vitalsSave) {
            vitalsSave.addEventListener('click', function () {
                if (vitalsSave.disabled) return
                saveVitals()
            })
        }

        if (safetyModalClose) {
            safetyModalClose.addEventListener('click', hideSafetyModal)
        }
        if (safetyModal) {
            safetyModal.addEventListener('click', function (e) {
                if (e.target === safetyModal) hideSafetyModal()
            })
        }
        if (safetyModalAck) {
            safetyModalAck.addEventListener('click', function () {
                if (acknowledgeEl) acknowledgeEl.checked = true
                hideSafetyModal()
            })
        }
        if (confirmClose) {
            confirmClose.addEventListener('click', closeConfirmModal)
        }
        if (confirmCancel) {
            confirmCancel.addEventListener('click', closeConfirmModal)
        }
        if (confirmModal) {
            confirmModal.addEventListener('click', function (e) {
                if (e.target === confirmModal) closeConfirmModal()
            })
        }
        if (confirmSubmit) {
            confirmSubmit.addEventListener('click', function () {
                if (confirmSubmit.disabled) return
                closeConfirmModal()
                setSubmitLoading(true)
                saveAll().then(function (ok) {
                }).finally(function () {
                    setSubmitLoading(false)
                })
            })
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (diagnosisEl) diagnosisEl.value = ''
                if (treatmentEl) treatmentEl.value = ''
                if (prescriptionBody) prescriptionBody.innerHTML = ''
                ensureRow()
                setVisible(saveSuccess, false)
                setVisible(saveError, false)
                setVitalsFeedback('')
                renderSafety()
                setPrintVisible(false)
                clearSuccessTimer()
            })
        }

        if (addMedBtn) {
            addMedBtn.addEventListener('click', function () {
                ensureRow()
            })
        }

        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                if (saveBtn.disabled) return
                setVisible(saveError, false)
                setVisible(saveSuccess, false)
                clearSuccessTimer()

                if (!state.appointmentId) {
                    saveError.textContent = 'Select an appointment first.'
                    setVisible(saveError, true)
                    return
                }

                var conflicts = computeConflicts()
                if (conflicts.length && (!acknowledgeEl || !acknowledgeEl.checked)) {
                    saveError.textContent = 'Safety warnings detected. Check "Override safety warnings" to proceed.'
                    setVisible(saveError, true)
                    return
                }

                renderConfirmSummary()
                openConfirmModal()
            })
        }

        if (printBtn) {
            printBtn.addEventListener('click', function () {
                if (!state.lastSavedTransactionId) return
                var url = "{{ url('/print/consultations') }}/" + encodeURIComponent(String(state.lastSavedTransactionId))
                window.open(url, '_blank', 'noopener')
            })
        }

        Promise.all([loadDoctorUser(), loadMedicines()]).then(function () {
            if (appointmentSelect) {
                Array.prototype.slice.call(appointmentSelect.options).forEach(syncAppointmentOptionLabel)
            }
            if (appointmentSelect && appointmentSelect.value) {
                handleAppointmentChange()
            } else {
                ensureRow()
            }
        }).catch(function () {
            snapshotError.textContent = 'Unable to load medicines or user profile.'
            setVisible(snapshotError, true)
        })
    })
</script>
