<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;

class PrescriptionItemController extends Controller
{
    public function index()
    {
        return PrescriptionItem::with(['prescription', 'medicine'])->paginate();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'prescription_id' => ['required', 'exists:prescriptions,prescription_id'],
            'medicine_id' => ['nullable', 'exists:medicines,medicine_id'],
            'medicine_name' => ['nullable', 'string'],
            'dosage' => ['nullable', 'string'],
            'frequency' => ['nullable', 'string'],
            'duration' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
        ]);

        $medicineId = array_key_exists('medicine_id', $data) ? $data['medicine_id'] : null;
        $medicineName = array_key_exists('medicine_name', $data) ? $data['medicine_name'] : null;

        if (! $medicineId && ! $medicineName) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => [
                    'medicine_name' => ['Either medicine_id or medicine_name is required.'],
                ],
            ], 422);
        }

        if ($medicineId && ! $medicineName) {
            $medicine = Medicine::query()->find($medicineId);
            if ($medicine) {
                $data['medicine_name'] = $medicine->generic_name;
            }
        }

        $item = PrescriptionItem::create($data);

        return response()->json($item->load(['prescription', 'medicine']), 201);
    }

    public function show(PrescriptionItem $prescriptionItem)
    {
        return $prescriptionItem->load(['prescription', 'medicine']);
    }

    public function update(Request $request, PrescriptionItem $prescriptionItem)
    {
        $data = $request->validate([
            'medicine_id' => ['sometimes', 'nullable', 'exists:medicines,medicine_id'],
            'medicine_name' => ['sometimes', 'nullable', 'string'],
            'dosage' => ['sometimes', 'nullable', 'string'],
            'frequency' => ['sometimes', 'nullable', 'string'],
            'duration' => ['sometimes', 'nullable', 'string'],
            'instructions' => ['sometimes', 'nullable', 'string'],
        ]);

        if (array_key_exists('medicine_id', $data) || array_key_exists('medicine_name', $data)) {
            $nextMedicineId = array_key_exists('medicine_id', $data) ? $data['medicine_id'] : $prescriptionItem->medicine_id;
            $nextMedicineName = array_key_exists('medicine_name', $data) ? $data['medicine_name'] : $prescriptionItem->medicine_name;

            if (! $nextMedicineId && ! $nextMedicineName) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => [
                        'medicine_name' => ['Either medicine_id or medicine_name is required.'],
                    ],
                ], 422);
            }

            if ($nextMedicineId && ! $nextMedicineName) {
                $medicine = Medicine::query()->find($nextMedicineId);
                if ($medicine) {
                    $data['medicine_name'] = $medicine->generic_name;
                }
            }
        }

        $prescriptionItem->update($data);

        return $prescriptionItem->refresh()->load(['prescription', 'medicine']);
    }

    public function destroy(PrescriptionItem $prescriptionItem)
    {
        $prescriptionItem->delete();

        return response()->json([
            'message' => 'Prescription item deleted',
        ]);
    }
}
