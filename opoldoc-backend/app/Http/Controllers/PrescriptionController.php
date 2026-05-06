<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Prescription::with(['doctor', 'transaction.appointment.patient', 'items.medicine']);

        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            $query->whereHas('transaction.appointment', function ($q) use ($currentUser) {
                $q->whereIn('patient_id', $currentUser->accessiblePatientIds());
            });
        } elseif ($request->filled('patient_id')) {
            $patientId = $request->query('patient_id');
            $query->whereHas('transaction.appointment', function ($q) use ($patientId) {
                $q->where('patient_id', $patientId);
            });
        }

        return $query->latest('prescribed_datetime')->paginate();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'transaction_id' => ['required', 'exists:transactions,transaction_id'],
            'doctor_id' => ['required', 'exists:users,user_id'],
            'notes' => ['nullable', 'string'],
            'prescribed_datetime' => ['nullable', 'date'],
        ]);

        $prescription = Prescription::create($data);

        return response()->json($prescription->load(['doctor', 'transaction']), 201);
    }

    public function show(Request $request, Prescription $prescription)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            $prescription->loadMissing('transaction.appointment');
            $appointment = $prescription->transaction ? $prescription->transaction->appointment : null;
            if (! $appointment || ! $currentUser->canAccessPatientId((int) $appointment->patient_id)) {
                abort(403);
            }
        }

        return $prescription->load(['doctor', 'transaction.appointment.patient', 'items.medicine']);
    }

    public function update(Request $request, Prescription $prescription)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $data = $request->validate([
            'notes' => ['sometimes', 'nullable', 'string'],
            'prescribed_datetime' => ['sometimes', 'nullable', 'date'],
        ]);

        $prescription->update($data);

        return $prescription->refresh()->load(['doctor', 'transaction', 'items']);
    }

    public function destroy(Request $request, Prescription $prescription)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $prescription->delete();

        return response()->json([
            'message' => 'Prescription deleted',
        ]);
    }
}
