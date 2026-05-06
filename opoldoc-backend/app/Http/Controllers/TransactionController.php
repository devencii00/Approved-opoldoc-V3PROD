<?php

namespace App\Http\Controllers;

use App\Models\LogEntry;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('appointment');

        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            $query->whereHas('appointment', function ($q) use ($currentUser) {
                $q->whereIn('patient_id', $currentUser->accessiblePatientIds());
            });
        } elseif ($request->filled('patient_id')) {
            $patientId = (int) $request->query('patient_id');
            $query->whereHas('appointment', function ($q) use ($patientId) {
                $q->where('patient_id', $patientId);
            });
        }

        return $query->paginate();
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $data = $request->validate([
            'appointment_id' => ['required', 'exists:appointments,appointment_id'],
            'amount' => ['nullable', 'numeric'],
            'discount_amount' => ['nullable', 'numeric'],
            'discount_type' => ['nullable', 'in:none,senior,pwd'],
            'payment_mode' => ['nullable', 'in:cash,gcash'],
            'payment_status' => ['nullable', 'in:pending,paid,failed'],
            'reference_number' => ['nullable', 'string'],
            'receipt' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
            'transaction_datetime' => ['nullable', 'date'],
            'visit_datetime' => ['nullable', 'date'],
            'diagnosis' => ['nullable', 'string'],
            'treatment_notes' => ['nullable', 'string'],
        ]);

        if (! isset($data['discount_amount'])) {
            $data['discount_amount'] = 0;
        }

        if (! isset($data['discount_type'])) {
            $data['discount_type'] = 'none';
        }

        if (! isset($data['payment_status'])) {
            $data['payment_status'] = 'pending';
        }

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }
        unset($data['receipt']);

        $transaction = Transaction::where('appointment_id', $data['appointment_id'])->first();
        if ($transaction) {
            $updateData = $data;
            unset($updateData['appointment_id']);
            if ($receiptPath) {
                if ($transaction->receipt_path) {
                    Storage::disk('public')->delete($transaction->receipt_path);
                }
                $updateData['receipt_path'] = $receiptPath;
            }
            $transaction->update($updateData);

            LogEntry::write(
                optional($request->user())->user_id ? (int) $request->user()->user_id : null,
                'transaction_updated',
                'transactions',
                (int) $transaction->transaction_id,
                [
                    'appointment_id' => (int) $transaction->appointment_id,
                    'payment_status' => (string) ($transaction->payment_status ?? ''),
                ]
            );

            return response()->json($transaction->refresh()->load('appointment'), 200);
        }

        if ($receiptPath) {
            $data['receipt_path'] = $receiptPath;
        }

        $transaction = Transaction::create($data);

        LogEntry::write(
            optional($request->user())->user_id ? (int) $request->user()->user_id : null,
            'transaction_created',
            'transactions',
            (int) $transaction->transaction_id,
            [
                'appointment_id' => (int) $transaction->appointment_id,
                'payment_status' => (string) ($transaction->payment_status ?? ''),
            ]
        );

        return response()->json($transaction->load('appointment'), 201);
    }

    public function show(Transaction $transaction)
    {
        $currentUser = request()->user();
        if ($currentUser && $currentUser->role === 'patient') {
            $transaction->loadMissing('appointment');
            $patientId = $transaction->appointment ? (int) $transaction->appointment->patient_id : 0;
            if (! $patientId || ! $currentUser->canAccessPatientId($patientId)) {
                abort(403);
            }
        }

        return $transaction->load([
            'appointment.patient',
            'appointment.doctor',
            'prescriptions.doctor',
            'prescriptions.items.medicine',
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $data = $request->validate([
            'amount' => ['sometimes', 'numeric'],
            'discount_amount' => ['sometimes', 'numeric'],
            'discount_type' => ['sometimes', 'in:none,senior,pwd'],
            'payment_mode' => ['sometimes', 'in:cash,gcash'],
            'payment_status' => ['sometimes', 'in:pending,paid,failed'],
            'reference_number' => ['sometimes', 'nullable', 'string'],
            'receipt' => ['sometimes', 'nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
            'transaction_datetime' => ['sometimes', 'nullable', 'date'],
            'visit_datetime' => ['sometimes', 'nullable', 'date'],
            'diagnosis' => ['sometimes', 'nullable', 'string'],
            'treatment_notes' => ['sometimes', 'nullable', 'string'],
        ]);

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            if ($transaction->receipt_path) {
                Storage::disk('public')->delete($transaction->receipt_path);
            }
            $data['receipt_path'] = $path;
        }
        unset($data['receipt']);

        $transaction->update($data);

        LogEntry::write(
            optional($request->user())->user_id ? (int) $request->user()->user_id : null,
            'transaction_updated',
            'transactions',
            (int) $transaction->transaction_id,
            [
                'appointment_id' => (int) $transaction->appointment_id,
                'fields' => array_keys($data),
            ]
        );

        return $transaction->refresh()->load([
            'appointment.patient',
            'appointment.doctor',
            'prescriptions.doctor',
            'prescriptions.items.medicine',
        ]);
    }

    public function destroy(Transaction $transaction)
    {
        $currentUser = request()->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $transaction->delete();

        LogEntry::write(
            optional(request()->user())->user_id ? (int) request()->user()->user_id : null,
            'transaction_deleted',
            'transactions',
            (int) $transaction->transaction_id,
            [
                'appointment_id' => (int) $transaction->appointment_id,
            ]
        );

        return response()->json([
            'message' => 'Transaction deleted',
        ]);
    }
}
