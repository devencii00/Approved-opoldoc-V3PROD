<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\LogEntry;
use App\Models\Notification;
use App\Models\PatientVerification;
use App\Models\Prescription;
use App\Models\Queue;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function show(Request $request, string $role = 'admin')
    {
        $role = strtolower($role);

        $allowed = ['admin', 'doctor', 'receptionist', 'patient'];

        if (! in_array($role, $allowed, true)) {
            abort(404);
        }

        $section = $request->query('section');
        $userId = $request->query('user_id');

        $currentUser = null;
        $doctorId = null;

        if ($userId && $role !== 'admin') {
            $currentUser = User::find($userId);
        }

        if ($role === 'doctor' && $currentUser) {
            $doctorId = $currentUser->user_id;
        }

        $data = [
            'role' => $role,
            'section' => $section,
        ];

        if ($role === 'admin') {
            $today = now()->toDateString();
            $startOfMonth = now()->startOfMonth()->toDateString();
            $appointmentsChartStart = now()->subDays(13)->startOfDay();
            $revenueChartStart = now()->subMonths(11)->startOfMonth();

            $patientCount = User::where('role', 'patient')->count();
            $doctorCount = User::where('role', 'doctor')->count();
            $pendingVerificationCount = PatientVerification::where('status', 'pending')->count();
            $recentLogsCount = LogEntry::count();

            $appointmentsToday = Appointment::whereDate('appointment_datetime', $today)->count();

            $revenueToday = Transaction::whereDate('transaction_datetime', $today)
                ->where('payment_status', 'paid')
                ->sum('amount');

            $revenueThisMonth = Transaction::whereBetween('transaction_datetime', [$startOfMonth, now()])
                ->where('payment_status', 'paid')
                ->sum('amount');

            $userRoleCounts = User::selectRaw('role, COUNT(*) as users_count')
                ->groupBy('role')
                ->get()
                ->map(function ($row) {
                    return (object) [
                        'role_name' => $row->role,
                        'users_count' => $row->users_count,
                    ];
                });

            $recentUsers = User::withCount('children')->latest('user_id')->limit(10)->get();

            $recentPatients = User::where('role', 'patient')
                ->latest('user_id')
                ->limit(10)
                ->get();

            $recentVerifications = PatientVerification::with('patient')
                ->latest('verification_id')
                ->limit(10)
                ->get();

            $recentTransactions = Transaction::latest('transaction_datetime')
                ->limit(10)
                ->get();

            $recentAuditLogs = LogEntry::with('user')
                ->where('action', 'not like', 'access_%')
                ->latest('created_at')
                ->limit(60)
                ->get();

            $recentAccessLogs = LogEntry::with('user')
                ->where('action', 'like', 'access_%')
                ->latest('created_at')
                ->limit(60)
                ->get();

            $data['adminMetrics'] = [
                'patientCount' => $patientCount,
                'doctorCount' => $doctorCount,
                'pendingVerificationsCount' => $pendingVerificationCount,
                'recentLogsCount' => $recentLogsCount,
                'appointmentsToday' => $appointmentsToday,
                'revenueToday' => $revenueToday,
                'revenueThisMonth' => $revenueThisMonth,
            ];

            $verificationStats = PatientVerification::query()
                ->selectRaw('status, COUNT(*) as total_count')
                ->groupBy('status')
                ->pluck('total_count', 'status');

            $data['adminVerificationStats'] = [
                'pending' => (int) ($verificationStats['pending'] ?? 0),
                'approved' => (int) ($verificationStats['approved'] ?? 0),
                'rejected' => (int) ($verificationStats['rejected'] ?? 0),
            ];

            $data['adminUserRoleCounts'] = $userRoleCounts;
            $data['adminRecentUsers'] = $recentUsers;
            $data['adminRecentPatients'] = $recentPatients;
            $data['adminRecentVerifications'] = $recentVerifications;
            $data['adminRecentTransactions'] = $recentTransactions;
            $data['adminRecentAuditLogs'] = $recentAuditLogs;
            $data['adminRecentAccessLogs'] = $recentAccessLogs;

            $appointmentsCounts = Appointment::query()
                ->selectRaw('DATE(appointment_datetime) as day, COUNT(*) as total_count')
                ->whereNotNull('appointment_datetime')
                ->where('appointment_datetime', '>=', $appointmentsChartStart)
                ->groupBy(DB::raw('DATE(appointment_datetime)'))
                ->orderBy('day')
                ->get()
                ->keyBy('day');

            $appointmentLabels = [];
            $appointmentValues = [];
            for ($cursor = $appointmentsChartStart->copy(); $cursor->lte(now()); $cursor->addDay()) {
                $key = $cursor->toDateString();
                $appointmentLabels[] = $key;
                $appointmentValues[] = (int) (($appointmentsCounts[$key]->total_count ?? 0));
            }

            $revenueRows = Transaction::query()
                ->selectRaw("DATE_FORMAT(transaction_datetime, '%Y-%m') as month_key, SUM(amount) as total_amount")
                ->whereNotNull('transaction_datetime')
                ->where('transaction_datetime', '>=', $revenueChartStart)
                ->where('payment_status', 'paid')
                ->groupBy(DB::raw("DATE_FORMAT(transaction_datetime, '%Y-%m')"))
                ->orderBy('month_key')
                ->get()
                ->keyBy('month_key');

            $revenueLabels = [];
            $revenueValues = [];
            for ($cursor = $revenueChartStart->copy(); $cursor->lte(now()); $cursor->addMonth()) {
                $key = $cursor->format('Y-m');
                $revenueLabels[] = $key;
                $revenueValues[] = (float) (($revenueRows[$key]->total_amount ?? 0));
            }

            $data['adminCharts'] = [
                'appointmentsPerDay' => [
                    'labels' => $appointmentLabels,
                    'values' => $appointmentValues,
                ],
                'revenuePerMonth' => [
                    'labels' => $revenueLabels,
                    'values' => $revenueValues,
                ],
            ];

            $appointmentsByStatusToday = Appointment::selectRaw('status, appointment_type, COUNT(*) as total_count')
                ->whereDate('appointment_datetime', $today)
                ->groupBy('status', 'appointment_type')
                ->get();

            $noShowApptIds = Appointment::whereDate('appointment_datetime', $today)
                ->where('status', 'no_show')
                ->pluck('appointment_id');

            $noShowQueueApptIds = Queue::whereDate('queue_datetime', $today)
                ->where('status', 'no_show')
                ->pluck('appointment_id');

            $noShowCount = $noShowApptIds->concat($noShowQueueApptIds)->unique()->count();

            $data['adminReports'] = [
                'appointmentsByStatusToday' => $appointmentsByStatusToday,
                'noShowToday' => $noShowCount,
            ];
        } elseif ($role === 'doctor') {
            $today = now()->toDateString();

            $appointmentsToday = Appointment::whereDate('appointment_datetime', $today)
                ->when($doctorId, function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                })
                ->count();

            $queueToday = Queue::whereDate('queue_datetime', $today)
                ->when($doctorId, function ($q) use ($doctorId) {
                    $q->whereHas('appointment', function ($sub) use ($doctorId) {
                        $sub->where('doctor_id', $doctorId);
                    });
                })
                ->count();

            $completedToday = Transaction::whereDate('visit_datetime', $today)
                ->when($doctorId, function ($q) use ($doctorId) {
                    $q->whereHas('prescriptions', function ($sub) use ($doctorId) {
                        $sub->where('doctor_id', $doctorId);
                    });
                })
                ->count();

            $pendingPrescriptionsToday = Transaction::query()
                ->whereDate('visit_datetime', $today)
                ->when($doctorId, function ($q) use ($doctorId) {
                    $q->whereHas('appointment', function ($sub) use ($doctorId) {
                        $sub->where('doctor_id', $doctorId);
                    });
                })
                ->whereDoesntHave('prescriptions')
                ->count();

            $unreadNotificationsCount = 0;
            $recentNotifications = collect();
            if ($currentUser) {
                $unreadNotificationsCount = Notification::where('user_id', $currentUser->user_id)
                    ->where('is_read', false)
                    ->count();
                $recentNotifications = Notification::where('user_id', $currentUser->user_id)
                    ->latest('created_at')
                    ->limit(10)
                    ->get();
            }

            $recentAppointments = Appointment::with(['patient', 'doctor'])
                ->when($doctorId, function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                })
                ->latest('appointment_datetime')
                ->limit(50)
                ->get();

            $recentVisits = Transaction::with(['appointment.patient'])
                ->when($doctorId, function ($q) use ($doctorId) {
                    $q->whereHas('prescriptions', function ($sub) use ($doctorId) {
                        $sub->where('doctor_id', $doctorId);
                    });
                })
                ->latest('visit_datetime')
                ->limit(50)
                ->get();

            $recentQueue = Queue::with(['appointment.patient', 'appointment.doctor'])
                ->when($doctorId, function ($q) use ($doctorId) {
                    $q->whereHas('appointment', function ($sub) use ($doctorId) {
                        $sub->where('doctor_id', $doctorId);
                    });
                })
                ->latest('queue_datetime')
                ->limit(50)
                ->get();

            $todayAppointments = Appointment::with(['patient', 'doctor', 'queue', 'transaction'])
                ->whereDate('appointment_datetime', $today)
                ->when($doctorId, function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                })
                ->orderBy('appointment_datetime')
                ->get();

            $todayQueue = Queue::with(['appointment.patient', 'appointment.doctor'])
                ->whereDate('queue_datetime', $today)
                ->when($doctorId, function ($q) use ($doctorId) {
                    $q->whereHas('appointment', function ($sub) use ($doctorId) {
                        $sub->where('doctor_id', $doctorId);
                    });
                })
                ->orderBy('queue_number')
                ->orderBy('queue_datetime')
                ->get();

            $activeQueueCount = $todayQueue->filter(function ($row) {
                return in_array($row->status, ['waiting', 'serving'], true);
            })->count();

            $doctorPatients = collect();

            if ($doctorId) {
                $patientIds = Appointment::where('doctor_id', $doctorId)
                    ->distinct()
                    ->pluck('patient_id');

                $doctorPatients = User::where('role', 'patient')
                    ->whereIn('user_id', $patientIds)
                    ->latest('user_id')
                    ->limit(50)
                    ->get();
            }

            $recentPrescriptions = Prescription::with(['transaction.appointment.patient', 'doctor', 'items'])
                ->when($doctorId, function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                })
                ->latest('prescribed_datetime')
                ->limit(50)
                ->get();

            $appointmentsCountQuery = Appointment::query();
            $visitsCountQuery = Transaction::query();
            $prescriptionsCountQuery = Prescription::query();
            $queueCountQuery = Queue::query();

            if ($doctorId) {
                $appointmentsCountQuery->where('doctor_id', $doctorId);
                $visitsCountQuery->whereHas('prescriptions', function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                });
                $prescriptionsCountQuery->where('doctor_id', $doctorId);
                $queueCountQuery->whereHas('appointment', function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                });
            }

            $activitySummary = [
                'totalAppointments' => $appointmentsCountQuery->count(),
                'totalVisits' => $visitsCountQuery->count(),
                'totalPrescriptions' => $prescriptionsCountQuery->count(),
                'totalQueueEntries' => $queueCountQuery->count(),
            ];

            $data['doctorMetrics'] = [
                'appointmentsToday' => $appointmentsToday,
                'queueToday' => $activeQueueCount,
                'completedToday' => $completedToday,
                'pendingPrescriptionsToday' => $pendingPrescriptionsToday,
                'unreadNotificationsCount' => $unreadNotificationsCount,
            ];

            $data['doctorRecentAppointments'] = $recentAppointments;
            $data['doctorRecentVisits'] = $recentVisits;
            $data['doctorRecentQueue'] = $recentQueue;
            $data['doctorTodayAppointments'] = $todayAppointments;
            $data['doctorTodayQueue'] = $todayQueue;
            $data['doctorRecentNotifications'] = $recentNotifications;
            $data['doctorPatients'] = $doctorPatients;
            $data['doctorRecentPrescriptions'] = $recentPrescriptions;
            $data['doctorActivitySummary'] = $activitySummary;
            $data['currentUser'] = $currentUser;
        } elseif ($role === 'receptionist') {
            $today = now()->toDateString();

            $newRegistrationsToday = User::where('role', 'patient')
                ->whereDate('created_at', $today)
                ->count();

            $appointmentsToday = Appointment::whereDate('appointment_datetime', $today)->count();

            $walkInsToday = Appointment::whereDate('appointment_datetime', $today)
                ->where('appointment_type', 'walk_in')
                ->count();

            $pendingQueueRequests = Appointment::where('appointment_type', 'scheduled')
                ->where('status', 'pending')
                ->whereDate('created_at', $today)
                ->count();

            $waitingCount = Queue::whereDate('queue_datetime', $today)
                ->where('status', 'waiting')
                ->count();

            $currentQueueCount = Queue::whereDate('queue_datetime', $today)
                ->whereIn('status', ['waiting', 'serving'])
                ->count();

            $transactionsToday = Transaction::whereDate('transaction_datetime', $today)
                ->where('payment_status', 'paid')
                ->sum('amount');

            $receptionQueue = Queue::with(['appointment.patient', 'appointment.doctor'])
                ->whereDate('queue_datetime', $today)
                ->orderBy('priority_level')
                ->orderBy('queue_number')
                ->get();

            $receptionAppointments = Appointment::with(['patient', 'doctor'])
                ->whereDate('appointment_datetime', $today)
                ->orderBy('appointment_datetime')
                ->get();

            $data['receptionMetrics'] = [
                'newRegistrationsToday' => $newRegistrationsToday,
                'appointmentsToday' => $appointmentsToday,
                'walkInsToday' => $walkInsToday,
                'pendingQueueRequests' => $pendingQueueRequests,
                'waitingCount' => $waitingCount,
                'currentQueueCount' => $currentQueueCount,
                'transactionsToday' => $transactionsToday,
            ];

            $data['receptionQueue'] = $receptionQueue;
            $data['receptionAppointments'] = $receptionAppointments;
        } elseif ($role === 'patient') {
            $data['patientDashboard'] = true;
        }

        return view('dashviews.main', $data);
    }
}
