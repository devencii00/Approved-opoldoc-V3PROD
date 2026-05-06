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

    $currentSection = request()->query('section');
    $currentSection = $currentSection ?: 'overview';
    if ($currentSection === 'medical-background-viewer') {
        $currentSection = 'patient-records';
    }

    $navBase = 'flex items-center gap-2.5 p-2 rounded-xl text-[0.87rem] font-medium mb-1';
    $navInactive = 'text-slate-600 hover:bg-slate-50 hover:text-slate-900';
    $navActive = 'bg-gradient-to-br from-cyan-50/20 to-cyan-100/10 text-cyan-700 relative';
@endphp

    <aside class="w-[248px] flex-shrink-0 bg-white flex flex-col fixed top-0 left-0 bottom-0 z-40 shadow-[4px_0_24px_rgba(15,23,42,0.05)] border-r border-slate-200">
<div class="flex items-start gap-3 p-6 border-b border-slate-100"> 
    
    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 bg-white border border-slate-200 overflow-hidden"> 
        <img src="{{ asset('images/opoldoc3.png') }}" alt="Opol Doctors Medical Clinic" class="w-full h-full object-cover"> 
    </div> 

     
    <div class="pt-0.78"> 
        <div class="font-serif font-bold text-slate-900 text-sm leading-[1.2]">Opol Doctors Medical Clinic</div> 
        <div class="text-slate-400 font-medium text-[0.68rem] uppercase tracking-widest">{{ $roleLabel }}</div> 
    </div> 
</div>

    <nav class="flex-1 px-3 py-2 overflow-y-auto scrollbar-hidden">
        @php
            $isDashboardActive = $currentSection === 'overview';
        @endphp

        @if ($roleKey !== 'admin')
            @php
                $groupHeaderBase = 'flex items-center justify-between gap-2 pt-4 pb-1 text-slate-400 text-[0.67rem] font-semibold uppercase tracking-widest';
                $groupToggleBtn = 'inline-flex items-center justify-center w-7 h-7 rounded-lg hover:bg-slate-50 text-slate-400 hover:text-slate-700';
                $mainGroupKey = $roleKey . '-main';
            @endphp

            <div class="{{ $groupHeaderBase }}">
                <div>Main Menu</div>
                <button type="button" class="{{ $groupToggleBtn }} sidebar-group-toggle" data-group="{{ $mainGroupKey }}">
                    <x-lucide-chevron-down class="sidebar-group-icon-expanded w-[18px] h-[18px]" />
                    <x-lucide-chevron-right class="sidebar-group-icon-collapsed hidden w-[18px] h-[18px]" />
                </button>
            </div>
            <div data-group-body="{{ $mainGroupKey }}">
                <a href="{{ route('dashboard', ['role' => $roleKey]) }}" class="{{ $navBase }} {{ $isDashboardActive ? $navActive : $navInactive }}">
                    <x-lucide-layout-dashboard class="w-[18px] h-[18px] flex-shrink-0 {{ $isDashboardActive ? 'text-cyan-600' : '' }}" />
                    Dashboard
                    @if ($isDashboardActive)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>
            </div>
        @endif

        @if ($roleKey === 'admin')
            @php
                $isUserManagement = $currentSection === 'user-management';
                $isDoctorManagement = $currentSection === 'doctor-management';
                $isServicesManagement = $currentSection === 'services-management';
                $isMedicinesManagement = $currentSection === 'medicines-management';
                $isPatientRecords = $currentSection === 'patient-records';
                $isAppointments = $currentSection === 'appointments';
                $isVerificationOversight = $currentSection === 'verification-oversight';
                $isReports = $currentSection === 'reports';
                $isChatbotManagement = $currentSection === 'chatbot-management';
                $isLogs = $currentSection === 'logs';
                $isSettings = $currentSection === 'settings';

                $groupHeaderBase = 'flex items-center justify-between gap-2 pt-4 pb-1 text-slate-400 text-[0.67rem] font-semibold uppercase tracking-widest';
                $groupToggleBtn = 'inline-flex items-center justify-center w-7 h-7 rounded-lg hover:bg-slate-50 text-slate-400 hover:text-slate-700';
            @endphp

            <div class="{{ $groupHeaderBase }}">
                <div>Main Menu</div>
                <button type="button" class="{{ $groupToggleBtn }} sidebar-group-toggle" data-group="admin-main">
                    <x-lucide-chevron-down class="sidebar-group-icon-expanded w-[18px] h-[18px]" />
                    <x-lucide-chevron-right class="sidebar-group-icon-collapsed hidden w-[18px] h-[18px]" />
                </button>
            </div>
            <div data-group-body="admin-main">
                <a href="{{ route('dashboard', ['role' => $roleKey]) }}" class="{{ $navBase }} {{ $isDashboardActive ? $navActive : $navInactive }}">
                    <x-lucide-layout-dashboard class="w-[18px] h-[18px] flex-shrink-0 {{ $isDashboardActive ? 'text-cyan-600' : '' }}" />
                    Dashboard
                    @if ($isDashboardActive)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>
            </div>

            <div class="{{ $groupHeaderBase }}">
                <div>Clinical Management</div>
                <button type="button" class="{{ $groupToggleBtn }} sidebar-group-toggle" data-group="admin-clinical">
                    <x-lucide-chevron-down class="sidebar-group-icon-expanded w-[18px] h-[18px]" />
                    <x-lucide-chevron-right class="sidebar-group-icon-collapsed hidden w-[18px] h-[18px]" />
                </button>
            </div>
            <div data-group-body="admin-clinical">
                <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'doctor-management']) }}" class="{{ $navBase }} {{ $isDoctorManagement ? $navActive : $navInactive }}">
                    <x-lucide-stethoscope class="w-[18px] h-[18px] {{ $isDoctorManagement ? 'text-cyan-600' : '' }}" />
                    Doctors
                    @if ($isDoctorManagement)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>

                <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'appointments']) }}" class="{{ $navBase }} {{ $isAppointments ? $navActive : $navInactive }}">
                    <x-lucide-calendar class="w-[18px] h-[18px] {{ $isAppointments ? 'text-cyan-600' : '' }}" />
                    Appointments
                    @if ($isAppointments)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>

                <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'patient-records']) }}" class="{{ $navBase }} {{ $isPatientRecords ? $navActive : $navInactive }}">
                    <x-lucide-clipboard class="w-[18px] h-[18px] {{ $isPatientRecords ? 'text-cyan-600' : '' }}" />
                    Patient Records
                    @if ($isPatientRecords)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>
            </div>

            <div class="{{ $groupHeaderBase }}">
                <div>Inventory & Services</div>
                <button type="button" class="{{ $groupToggleBtn }} sidebar-group-toggle" data-group="admin-inventory">
                    <x-lucide-chevron-down class="sidebar-group-icon-expanded w-[18px] h-[18px]" />
                    <x-lucide-chevron-right class="sidebar-group-icon-collapsed hidden w-[18px] h-[18px]" />
                </button>
            </div>
            <div data-group-body="admin-inventory">
                <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'services-management']) }}" class="{{ $navBase }} {{ $isServicesManagement ? $navActive : $navInactive }}">
                    <x-lucide-briefcase-medical class="w-[18px] h-[18px] {{ $isServicesManagement ? 'text-cyan-600' : '' }}" />
                    Services
                    @if ($isServicesManagement)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>

                <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'medicines-management']) }}" class="{{ $navBase }} {{ $isMedicinesManagement ? $navActive : $navInactive }}">
                    <x-lucide-syringe class="w-[18px] h-[18px] {{ $isMedicinesManagement ? 'text-cyan-600' : '' }}" />
                    Medicines
                    @if ($isMedicinesManagement)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>
            </div>

            <div class="{{ $groupHeaderBase }}">
                <div>Administrative Tools</div>
                <button type="button" class="{{ $groupToggleBtn }} sidebar-group-toggle" data-group="admin-tools">
                    <x-lucide-chevron-down class="sidebar-group-icon-expanded w-[18px] h-[18px]" />
                    <x-lucide-chevron-right class="sidebar-group-icon-collapsed hidden w-[18px] h-[18px]" />
                </button>
            </div>
            <div data-group-body="admin-tools">
                <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'user-management']) }}" class="{{ $navBase }} {{ $isUserManagement ? $navActive : $navInactive }}">
                    <x-lucide-users class="w-[18px] h-[18px] {{ $isUserManagement ? 'text-cyan-600' : '' }}" />
                    Users
                    @if ($isUserManagement)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>

                <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'verification-oversight']) }}" class="{{ $navBase }} {{ $isVerificationOversight ? $navActive : $navInactive }}">
                    <x-lucide-user-check class="w-[18px] h-[18px] {{ $isVerificationOversight ? 'text-cyan-600' : '' }}" />
                    Verification Oversight
                    @if ($isVerificationOversight)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>
            </div>

            <div class="{{ $groupHeaderBase }}">
                <div>System & Analytics</div>
                <button type="button" class="{{ $groupToggleBtn }} sidebar-group-toggle" data-group="admin-system">
                    <x-lucide-chevron-down class="sidebar-group-icon-expanded w-[18px] h-[18px]" />
                    <x-lucide-chevron-right class="sidebar-group-icon-collapsed hidden w-[18px] h-[18px]" />
                </button>
            </div>
            <div data-group-body="admin-system">
                <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'reports']) }}" class="{{ $navBase }} {{ $isReports ? $navActive : $navInactive }}">
                    <x-lucide-chart-no-axes-combined class="w-[18px] h-[18px] {{ $isReports ? 'text-cyan-600' : '' }}" />
                    Reports
                    @if ($isReports)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>

                <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'logs']) }}" class="{{ $navBase }} {{ $isLogs ? $navActive : $navInactive }}">
                    <x-lucide-folder class="w-[18px] h-[18px] {{ $isLogs ? 'text-cyan-600' : '' }}" />
                    Logs
                    @if ($isLogs)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>

                <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'chatbot-management']) }}" class="{{ $navBase }} {{ $isChatbotManagement ? $navActive : $navInactive }}">
                    <x-lucide-bot class="w-[18px] h-[18px] {{ $isChatbotManagement ? 'text-cyan-600' : '' }}" />
                    Chatbot
                    @if ($isChatbotManagement)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>

                <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'settings']) }}" class="{{ $navBase }} {{ $isSettings ? $navActive : $navInactive }}">
                    <x-lucide-settings class="w-[18px] h-[18px] {{ $isSettings ? 'text-cyan-600' : '' }}" />
                    Settings
                    @if ($isSettings)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>
            </div>
        @elseif ($roleKey === 'receptionist')
            @php
                $isReceptionRegister = $currentSection === 'register-patient';
                $isReceptionAppointments = $currentSection === 'book-appointment';
                $isReceptionWalkIns = $currentSection === 'walk-ins';
                $isReceptionQueue = $currentSection === 'queue-management';
                $isReceptionRecordPayments = $currentSection === 'record-payment';
                $isReceptionVerificationOversight = $currentSection === 'verification-oversight';
                $isReceptionMessages = $currentSection === 'messages';

                $groupHeaderBase = 'flex items-center justify-between gap-2 pt-4 pb-1 text-slate-400 text-[0.67rem] font-semibold uppercase tracking-widest';
                $groupToggleBtn = 'inline-flex items-center justify-center w-7 h-7 rounded-lg hover:bg-slate-50 text-slate-400 hover:text-slate-700';
            @endphp

            <div class="{{ $groupHeaderBase }}">
                <div>Patients & Appointments</div>
                <button type="button" class="{{ $groupToggleBtn }} sidebar-group-toggle" data-group="reception-patients">
                    <x-lucide-chevron-down class="sidebar-group-icon-expanded w-[18px] h-[18px]" />
                    <x-lucide-chevron-right class="sidebar-group-icon-collapsed hidden w-[18px] h-[18px]" />
                </button>
            </div>
            <div data-group-body="reception-patients">

            <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'register-patient']) }}" class="{{ $navBase }} {{ $isReceptionRegister ? $navActive : $navInactive }}">
                <x-lucide-user-plus class="w-[18px] h-[18px] {{ $isReceptionRegister ? 'text-cyan-600' : '' }}" />
                Register patient
                @if ($isReceptionRegister)
                    <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                @endif
            </a>

            <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'book-appointment']) }}" class="{{ $navBase }} {{ $isReceptionAppointments ? $navActive : $navInactive }}">
                <x-lucide-calendar class="w-[18px] h-[18px] {{ $isReceptionAppointments ? 'text-cyan-600' : '' }}" />
                Appointments
                @if ($isReceptionAppointments)
                    <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                @endif
            </a>

            <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'walk-ins']) }}" class="{{ $navBase }} {{ $isReceptionWalkIns ? $navActive : $navInactive }}">
                <x-lucide-user-check class="w-[18px] h-[18px] {{ $isReceptionWalkIns ? 'text-cyan-600' : '' }}" />
                Walk-ins
                @if ($isReceptionWalkIns)
                    <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                @endif
            </a>

            <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'queue-management']) }}" class="{{ $navBase }} {{ $isReceptionQueue ? $navActive : $navInactive }} mb-3">
                <x-lucide-list class="w-[18px] h-[18px] {{ $isReceptionQueue ? 'text-cyan-600' : '' }}" />
                Queue management
                @if ($isReceptionQueue)
                    <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                @endif
            </a>
            </div>

            <div class="{{ $groupHeaderBase }}">
                <div>Verification</div>
                <button type="button" class="{{ $groupToggleBtn }} sidebar-group-toggle" data-group="reception-verification">
                    <x-lucide-chevron-down class="sidebar-group-icon-expanded w-[18px] h-[18px]" />
                    <x-lucide-chevron-right class="sidebar-group-icon-collapsed hidden w-[18px] h-[18px]" />
                </button>
            </div>
            <div data-group-body="reception-verification">

            <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'verification-oversight']) }}" class="{{ $navBase }} {{ $isReceptionVerificationOversight ? $navActive : $navInactive }} mb-3">
                <x-lucide-user-check class="w-[18px] h-[18px] {{ $isReceptionVerificationOversight ? 'text-cyan-600' : '' }}" />
                Verification requests
                @if ($isReceptionVerificationOversight)
                    <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                @endif
            </a>
            </div>

            <div class="{{ $groupHeaderBase }}">
                <div>Billing</div>
                <button type="button" class="{{ $groupToggleBtn }} sidebar-group-toggle" data-group="reception-billing">
                    <x-lucide-chevron-down class="sidebar-group-icon-expanded w-[18px] h-[18px]" />
                    <x-lucide-chevron-right class="sidebar-group-icon-collapsed hidden w-[18px] h-[18px]" />
                </button>
            </div>
            <div data-group-body="reception-billing">

            <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'record-payment']) }}" class="{{ $navBase }} {{ $isReceptionRecordPayments ? $navActive : $navInactive }}">
                <x-lucide-credit-card class="w-[18px] h-[18px] {{ $isReceptionRecordPayments ? 'text-cyan-600' : '' }}" />
                Record payments
                @if ($isReceptionRecordPayments)
                    <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                @endif
            </a>
            </div>

            <div class="{{ $groupHeaderBase }}">
                <div>Communication</div>
                <button type="button" class="{{ $groupToggleBtn }} sidebar-group-toggle" data-group="reception-communication">
                    <x-lucide-chevron-down class="sidebar-group-icon-expanded w-[18px] h-[18px]" />
                    <x-lucide-chevron-right class="sidebar-group-icon-collapsed hidden w-[18px] h-[18px]" />
                </button>
            </div>
            <div data-group-body="reception-communication">
                <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'messages']) }}" class="{{ $navBase }} {{ $isReceptionMessages ? $navActive : $navInactive }}">
                    <x-lucide-messages-square class="w-[18px] h-[18px] {{ $isReceptionMessages ? 'text-cyan-600' : '' }}" />
                    Messages
                    @if ($isReceptionMessages)
                        <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                    @endif
                </a>
            </div>
        @elseif ($roleKey === 'doctor')
            @php
                $isDoctorSchedule = $currentSection === 'my-schedule';
                $isDoctorQueue = $currentSection === 'queue';
                $isDoctorConsultation = $currentSection === 'consultation';
                $isDoctorPrescription = $currentSection === 'prescriptions';
                $isDoctorHistory = $currentSection === 'history';
                $isDoctorSettings = $currentSection === 'settings-doctor';
            @endphp

            <div class="text-slate-400 text-[0.67rem] font-semibold uppercase tracking-widest mt-4 mb-1">Work</div>

            <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'my-schedule']) }}" class="{{ $navBase }} {{ $isDoctorSchedule ? $navActive : $navInactive }}">
                <x-lucide-file-text class="w-[18px] h-[18px] {{ $isDoctorSchedule ? 'text-cyan-600' : '' }}" />
                My Schedule
                @if ($isDoctorSchedule)
                    <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                @endif
            </a>

            <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'queue']) }}" class="{{ $navBase }} {{ $isDoctorQueue ? $navActive : $navInactive }}">
                <x-lucide-list class="w-[18px] h-[18px] {{ $isDoctorQueue ? 'text-cyan-600' : '' }}" />
                Queue
                @if ($isDoctorQueue)
                    <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                @endif
            </a>

            <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'consultation']) }}" class="{{ $navBase }} {{ $isDoctorConsultation ? $navActive : $navInactive }}">
                <x-lucide-clipboard-list class="w-[18px] h-[18px] {{ $isDoctorConsultation ? 'text-cyan-600' : '' }}" />
                Consultation
                @if ($isDoctorConsultation)
                    <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                @endif
            </a>

            <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'prescriptions']) }}" class="{{ $navBase }} {{ $isDoctorPrescription ? $navActive : $navInactive }}">
                <x-lucide-pill class="w-[18px] h-[18px] {{ $isDoctorPrescription ? 'text-cyan-600' : '' }}" />
                Prescription
                @if ($isDoctorPrescription)
                    <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                @endif
            </a>

            <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'history']) }}" class="{{ $navBase }} {{ $isDoctorHistory ? $navActive : $navInactive }} mb-3">
                <x-lucide-history class="w-[18px] h-[18px] {{ $isDoctorHistory ? 'text-cyan-600' : '' }}" />
                History
                @if ($isDoctorHistory)
                    <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                @endif
            </a>

            <div class="text-slate-400 text-[0.67rem] font-semibold uppercase tracking-widest mt-2 mb-1">Settings</div>

            <a href="{{ route('dashboard', ['role' => $roleKey, 'section' => 'settings-doctor']) }}" class="{{ $navBase }} {{ $isDoctorSettings ? $navActive : $navInactive }}">
                <x-lucide-settings class="w-[18px] h-[18px] {{ $isDoctorSettings ? 'text-cyan-600' : '' }}" />
                Doctor Settings
                @if ($isDoctorSettings)
                    <span class="absolute left-0 top-[25%] bottom-[25%] w-1.5 rounded-r bg-cyan-500"></span>
                @endif
            </a>
        @endif
    </nav>

    <div class="px-3 py-4 border-t border-slate-100">
        <div class="flex items-center gap-2.5 p-2 rounded-xl bg-slate-50 mb-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-gradient-to-br from-cyan-400 to-cyan-700 text-white">
                <x-lucide-user class="w-[18px] h-[18px]" />
            </div>
            <div>
                <div id="sidebarUserName" class="text-slate-800 font-semibold text-[0.83rem] leading-tight">{{ $roleLabel }}</div>
                <div id="sidebarUserEmail" class="text-slate-400 text-[0.7rem]"></div>
            </div>
        </div>
        <button type="button" onclick="if(confirm('Are you sure you want to log out?')) { try { if (window.localStorage) { window.localStorage.removeItem('api_token'); window.localStorage.removeItem('current_user_id'); } } catch (_) {} window.location.href='{{ route('webadmin.login') }}'; }" class="w-full flex items-center justify-center gap-2.5 p-2 rounded-xl border border-red-400/25 bg-red-50 text-red-600 text-[0.83rem] font-semibold hover:bg-red-100 hover:border-red-400/40">
            <x-lucide-log-out class="w-[16px] h-[16px]" />
            Sign Out
        </button>
    </div>
</aside>

<script>
    (function () {
        function sidebarApiFetch(path, options) {
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

        function formatUserName(user) {
            if (!user) {
                return ''
            }
            var parts = []
            if (user.firstname) parts.push(String(user.firstname))
            if (user.middlename) parts.push(String(user.middlename))
            if (user.lastname) parts.push(String(user.lastname))
            var name = parts.join(' ').trim()
            if (name) {
                return name
            }
            return 'User'
        }

        document.addEventListener('DOMContentLoaded', function () {
            var toggles = document.querySelectorAll('.sidebar-group-toggle')
            toggles.forEach(function (btn) {
                var group = btn.getAttribute('data-group')
                if (!group) {
                    return
                }
                var body = document.querySelector('[data-group-body="' + group + '"]')
                var iconExpanded = btn.querySelector('.sidebar-group-icon-expanded')
                var iconCollapsed = btn.querySelector('.sidebar-group-icon-collapsed')
                var storageKey = 'sidebar_group_' + group
                var collapsed = false
                try {
                    collapsed = window.localStorage ? window.localStorage.getItem(storageKey) === '1' : false
                } catch (_) {
                    collapsed = false
                }
                if (body) {
                    body.classList.toggle('hidden', collapsed)
                }
                if (iconExpanded) iconExpanded.classList.toggle('hidden', collapsed)
                if (iconCollapsed) iconCollapsed.classList.toggle('hidden', !collapsed)
                btn.addEventListener('click', function () {
                    collapsed = !collapsed
                    if (body) {
                        body.classList.toggle('hidden', collapsed)
                    }
                    if (iconExpanded) iconExpanded.classList.toggle('hidden', collapsed)
                    if (iconCollapsed) iconCollapsed.classList.toggle('hidden', !collapsed)
                    try {
                        if (window.localStorage) {
                            window.localStorage.setItem(storageKey, collapsed ? '1' : '0')
                        }
                    } catch (_) {}
                })
            })

            var nameEl = document.getElementById('sidebarUserName')
            var emailEl = document.getElementById('sidebarUserEmail')
            if (!nameEl || !emailEl) {
                return
            }

            var userId = null
            try {
                userId = window.localStorage ? window.localStorage.getItem('current_user_id') : null
            } catch (_) {
                userId = null
            }

            if (!userId) {
                return
            }

            sidebarApiFetch("{{ url('/api/users') }}/" + encodeURIComponent(userId), { method: 'GET' })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data }
                    }).catch(function () {
                        return { ok: response.ok, data: null }
                    })
                })
                .then(function (result) {
                    if (!result.ok || !result.data) {
                        return
                    }
                    var user = result.data
                    nameEl.textContent = formatUserName(user)
                    emailEl.textContent = user && user.email ? String(user.email) : ''
                })
                .catch(function () {})
        })
    })()
</script>
