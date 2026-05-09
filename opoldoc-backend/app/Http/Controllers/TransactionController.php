<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\LogEntry;
use App\Models\Queue;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
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

        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'patient_id' => ['nullable', 'integer'],
            'service_id' => ['nullable', 'integer', 'exists:services,service_id'],
            'search' => ['nullable', 'string'],
            'order' => ['nullable', 'in:latest,oldest'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $query = Transaction::with([
            'appointment.patient',
            'appointment.doctor',
            'appointment.services',
        ]);

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

        if ($request->filled('service_id')) {
            $serviceId = (int) $request->query('service_id');
            $query->whereHas('appointment.services', function ($q) use ($serviceId) {
                $q->where('services.service_id', $serviceId);
            });
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $contains = '%'.$search.'%';
            $tokens = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $query->where(function ($q) use ($search, $contains, $tokens) {
                $q->where('reference_number', 'like', $contains);
                if (is_numeric($search)) {
                    $q->orWhere('transaction_id', (int) $search);
                }
                $q->orWhereHas('appointment.patient', function ($p) use ($contains, $tokens) {
                    $p->where('email', 'like', $contains)
                        ->orWhere('firstname', 'like', $contains)
                        ->orWhere('lastname', 'like', $contains)
                        ->orWhere('middlename', 'like', $contains)
                        ->orWhere('contact_number', 'like', $contains)
                        ->orWhereRaw("TRIM(CONCAT_WS(' ', firstname, middlename, lastname)) like ?", [$contains]);
                    foreach ($tokens as $token) {
                        $piece = '%'.$token.'%';
                        $p->orWhere(function ($w) use ($piece) {
                            $w->where('firstname', 'like', $piece)
                                ->orWhere('middlename', 'like', $piece)
                                ->orWhere('lastname', 'like', $piece);
                        });
                    }
                });
            });
        }

        if ($request->filled('start_date') || $request->filled('end_date')) {
            $startRaw = $request->query('start_date');
            $endRaw = $request->query('end_date');
            $start = $startRaw ? Carbon::parse($startRaw)->startOfDay() : null;
            $end = $endRaw ? Carbon::parse($endRaw)->endOfDay() : null;
            if (! $start && $end) {
                $start = $end->copy()->startOfDay();
            }
            if ($start && ! $end) {
                $end = $start->copy()->endOfDay();
            }
            if ($start && $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->whereBetween('transaction_datetime', [$start, $end])
                        ->orWhere(function ($w) use ($start, $end) {
                            $w->whereNull('transaction_datetime')->whereBetween('created_at', [$start, $end]);
                        });
                });
            }
        }

        $order = (string) $request->query('order', 'latest');
        if ($order === 'oldest') {
            $query->orderByRaw('transaction_datetime IS NULL ASC')
                ->orderBy('transaction_datetime')
                ->orderBy('transaction_id');
        } else {
            $query->orderByRaw('transaction_datetime IS NULL ASC')
                ->orderByDesc('transaction_datetime')
                ->orderByDesc('transaction_id');
        }

        return $query->paginate($perPage);
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
            'discount_type' => ['nullable', 'in:none,senior,pwd,pregnant'],
            'payment_mode' => ['nullable', 'in:cash,gcash'],
            'payment_status' => ['nullable', 'in:pending,paid,failed'],
            'reference_number' => ['nullable', 'string'],
            'receipt' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
            'transaction_datetime' => ['nullable', 'date'],
            'visit_datetime' => ['nullable', 'date'],
            'diagnosis' => ['nullable', 'string'],
            'treatment_notes' => ['nullable', 'string'],
        ]);

        $appointment = Appointment::query()->with('services')->findOrFail((int) $data['appointment_id']);

        if (! isset($data['discount_type'])) {
            $data['discount_type'] = 'none';
        }

        if (! isset($data['amount']) || $data['amount'] === null || $data['amount'] === '') {
            $data['amount'] = (float) $appointment->services->sum(function ($service) {
                return (float) ($service->price ?? 0);
            });
        }

        $discountRates = [
            'none' => 0.0,
            'pwd' => 0.15,
            'pregnant' => 0.10,
            'senior' => 0.05,
        ];
        $selectedDiscountType = (string) ($data['discount_type'] ?? 'none');
        if (! array_key_exists($selectedDiscountType, $discountRates)) {
            $selectedDiscountType = 'none';
        }
        $data['discount_type'] = $selectedDiscountType;
        $baseAmount = (float) ($data['amount'] ?? 0);
        if ($baseAmount < 0) {
            $baseAmount = 0;
            $data['amount'] = 0;
        }
        $data['discount_amount'] = round($baseAmount * (float) $discountRates[$selectedDiscountType], 2);

        if (! isset($data['payment_status']) || ! $data['payment_status']) {
            $data['payment_status'] = 'pending';
        }

        if (($data['payment_status'] ?? 'pending') === 'paid' && (! isset($data['payment_mode']) || ! $data['payment_mode'])) {
            $data['payment_mode'] = 'cash';
        }

        if (($data['payment_status'] ?? 'pending') === 'paid' && (! isset($data['transaction_datetime']) || ! $data['transaction_datetime'])) {
            $data['transaction_datetime'] = now();
        }

        if (! isset($data['reference_number']) || trim((string) $data['reference_number']) === '') {
            $data['reference_number'] = $this->generateReferenceNumber();
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
            $this->markLinkedAppointmentConsulted((int) $transaction->appointment_id, $updateData);
            $this->markLinkedAppointmentCompleted((int) $transaction->appointment_id);

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
        $this->markLinkedAppointmentConsulted((int) $transaction->appointment_id, $data);
        $this->markLinkedAppointmentCompleted((int) $transaction->appointment_id);

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
            'discount_type' => ['sometimes', 'in:none,senior,pwd,pregnant'],
            'payment_mode' => ['sometimes', 'in:cash,gcash'],
            'payment_status' => ['sometimes', 'in:pending,paid,failed'],
            'reference_number' => ['sometimes', 'nullable', 'string'],
            'receipt' => ['sometimes', 'nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
            'transaction_datetime' => ['sometimes', 'nullable', 'date'],
            'visit_datetime' => ['sometimes', 'nullable', 'date'],
            'diagnosis' => ['sometimes', 'nullable', 'string'],
            'treatment_notes' => ['sometimes', 'nullable', 'string'],
        ]);

        if (array_key_exists('payment_status', $data) && $data['payment_status'] === 'paid') {
            if (! array_key_exists('payment_mode', $data) && ! $transaction->payment_mode) {
                $data['payment_mode'] = 'cash';
            }
            if (! array_key_exists('transaction_datetime', $data) && ! $transaction->transaction_datetime) {
                $data['transaction_datetime'] = now();
            }
        }

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            if ($transaction->receipt_path) {
                Storage::disk('public')->delete($transaction->receipt_path);
            }
            $data['receipt_path'] = $path;
        }
        unset($data['receipt']);

        $transaction->update($data);
        $this->markLinkedAppointmentConsulted((int) $transaction->appointment_id, $data);
        $this->markLinkedAppointmentCompleted((int) $transaction->appointment_id);

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

    private function generateReferenceNumber(): string
    {
        do {
            $reference = 'TXN-'.now()->format('Ymd-His').'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Transaction::query()->where('reference_number', $reference)->exists());

        return $reference;
    }

    private function markLinkedAppointmentCompleted(int $appointmentId): void
    {
        if ($appointmentId < 1) {
            return;
        }

        $appointment = Appointment::query()->find($appointmentId);
        if (! $appointment) {
            return;
        }

        $hasPaidTransaction = Transaction::query()
            ->where('appointment_id', $appointmentId)
            ->where('payment_status', 'paid')
            ->exists();

        if (! $hasPaidTransaction) {
            return;
        }

        if ((string) $appointment->status !== 'completed') {
            $appointment->status = 'completed';
            $appointment->save();
        }

        Queue::query()
            ->where('appointment_id', (int) $appointment->appointment_id)
            ->whereIn('status', ['waiting', 'serving', 'consulted'])
            ->update(['status' => 'done']);
    }

    private function markLinkedAppointmentConsulted(int $appointmentId, array $payload = []): void
    {
        if ($appointmentId < 1 || ! $this->hasConsultationPayload($payload)) {
            return;
        }

        $appointment = Appointment::query()->find($appointmentId);
        if (! $appointment) {
            return;
        }

        if ((string) $appointment->status !== 'completed' && (string) $appointment->status !== 'consulted') {
            $appointment->status = 'consulted';
            $appointment->save();
        }

        Queue::query()
            ->where('appointment_id', (int) $appointment->appointment_id)
            ->whereNotIn('status', ['done', 'cancelled', 'no_show'])
            ->update(['status' => 'consulted']);
    }

    private function hasConsultationPayload(array $payload): bool
    {
        if (array_key_exists('visit_datetime', $payload)) {
            return true;
        }

        foreach (['diagnosis', 'treatment_notes'] as $field) {
            if (! array_key_exists($field, $payload)) {
                continue;
            }

            if ($payload[$field] !== null && trim((string) $payload[$field]) !== '') {
                return true;
            }
        }

        return false;
    }
}
