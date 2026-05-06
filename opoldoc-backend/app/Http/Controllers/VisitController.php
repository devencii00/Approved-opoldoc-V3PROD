<?php

namespace App\Http\Controllers;

use App\Models\LogEntry;
use App\Models\Transaction;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();

        $perPage = (int) $request->query('per_page', 15);
        if ($perPage < 1) {
            $perPage = 15;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $query = Transaction::with([
            'appointment.patient',
            'appointment.doctor',
            'prescriptions.doctor',
            'prescriptions.items.medicine',
        ]);

        if ($currentUser && $currentUser->role === 'patient') {
            $query->whereHas('appointment', function ($q) use ($currentUser) {
                $q->whereIn('patient_id', $currentUser->accessiblePatientIds());
            });
        } elseif ($request->filled('patient_id')) {
            $patientId = (int) $request->input('patient_id');
            $query->whereHas('appointment', function ($q) use ($patientId) {
                $q->where('patient_id', $patientId);
            });

            LogEntry::write(
                $currentUser ? (int) $currentUser->user_id : null,
                'access_patient_visits',
                'patients',
                $patientId,
                [],
                120
            );
        }

        return $query->orderByDesc('visit_datetime')->orderByDesc('transaction_id')->paginate($perPage);
    }


    public function show(Request $request, Transaction $visit)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            $visit->loadMissing('appointment');
            if (! $visit->appointment || ! $currentUser->canAccessPatientId((int) $visit->appointment->patient_id)) {
                abort(403);
            }
        }

        return $visit->load([
            'appointment.patient',
            'appointment.doctor',
            'prescriptions.doctor',
            'prescriptions.items.medicine',
        ]);
    }
}
