<?php

namespace App\Http\Controllers;

use App\Models\LogEntry;
use App\Models\MedicalBackground;
use Illuminate\Http\Request;

class MedicalBackgroundController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        if ($perPage < 1) {
            $perPage = 15;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $query = MedicalBackground::query()->with('patient');

        $currentUser = $request->user();
        $isPatient = $currentUser && $currentUser->role === 'patient';

        if ($isPatient) {
            $query->whereIn('patient_id', $currentUser->accessiblePatientIds());
        } elseif ($request->filled('patient_id')) {
            $patientId = (int) $request->query('patient_id');
            $query->where('patient_id', $patientId);

            LogEntry::write(
                $currentUser ? (int) $currentUser->user_id : null,
                'access_patient_medical_background',
                'patients',
                $patientId,
                [],
                120
            );
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        return $query->orderBy('category')->orderBy('name')->paginate($perPage);
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();
        $isPatient = $currentUser && $currentUser->role === 'patient';

        $rules = [
            'category' => ['required', 'in:allergy_food,allergy_drug,condition'],
            'name' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ];

        $rules['patient_id'] = [$isPatient ? 'sometimes' : 'required', 'exists:users,user_id'];

        if ($isPatient && $currentUser) {
            $rules['patient_id'][] = function ($attribute, $value, $fail) use ($currentUser) {
                if ($value === null || $value === '') {
                    return;
                }
                if (! $currentUser->canAccessPatientId((int) $value)) {
                    $fail('Invalid patient selection.');
                }
            };
        }

        $data = $request->validate($rules);

        if ($isPatient) {
            $targetPatientId = (int) ($data['patient_id'] ?? $currentUser->user_id);
            if (! $currentUser->canAccessPatientId($targetPatientId)) {
                abort(403);
            }
            $data['patient_id'] = $targetPatientId;
        }

        $record = MedicalBackground::create($data);

        return response()->json($record->load('patient'), 201);
    }

    public function show(MedicalBackground $medicalBackground)
    {
        $currentUser = request()->user();
        if ($currentUser && $currentUser->role === 'patient') {
            if (! $currentUser->canAccessPatientId((int) $medicalBackground->patient_id)) {
                abort(403);
            }
        }

        return $medicalBackground->load('patient');
    }

    public function update(Request $request, MedicalBackground $medicalBackground)
    {
        $currentUser = $request->user();
        $isPatient = $currentUser && $currentUser->role === 'patient';
        if ($isPatient) {
            if (! $currentUser->canAccessPatientId((int) $medicalBackground->patient_id)) {
                abort(403);
            }
        }

        $data = $request->validate([
            'patient_id' => ['sometimes', 'exists:users,user_id'],
            'category' => ['sometimes', 'in:allergy_food,allergy_drug,condition'],
            'name' => ['sometimes', 'string'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        if ($isPatient) {
            unset($data['patient_id']);
        }

        $medicalBackground->update($data);

        return $medicalBackground->refresh()->load('patient');
    }

    public function destroy(MedicalBackground $medicalBackground)
    {
        $currentUser = request()->user();
        if ($currentUser && $currentUser->role === 'patient') {
            if (! $currentUser->canAccessPatientId((int) $medicalBackground->patient_id)) {
                abort(403);
            }
        }

        $medicalBackground->delete();

        return response()->json([
            'message' => 'Medical background deleted',
        ]);
    }
}
