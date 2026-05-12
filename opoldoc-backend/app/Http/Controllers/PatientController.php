<?php

namespace App\Http\Controllers;

use App\Mail\StaffInviteMail;
use App\Models\LogEntry;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $perPage = (int) $request->query('per_page', 15);
        if ($perPage < 1) {
            $perPage = 15;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $request->validate([
            'search' => ['nullable', 'string'],
            'parents_only' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort' => ['nullable', 'in:asc,desc'],
        ]);

        $search = trim((string) $request->query('search', ''));
        $parentsOnly = $request->boolean('parents_only');

        $query = User::query()->where('role', 'patient');

        if ($parentsOnly) {
            $query->where('is_dependent', false);
        }

        if ($search !== '') {
            $contains = '%'.$search.'%';
            $tokens = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            $query->where(function ($q) use ($search, $contains, $tokens) {
                $q->where('email', 'like', $contains)
                    ->orWhere('firstname', 'like', $contains)
                    ->orWhere('lastname', 'like', $contains)
                    ->orWhere('middlename', 'like', $contains)
                    ->orWhere('contact_number', 'like', $contains)
                    ->orWhere('address', 'like', $contains)
                    ->orWhereRaw("TRIM(CONCAT_WS(' ', firstname, middlename, lastname)) like ?", [$contains]);

                if (is_numeric($search)) {
                    $q->orWhere('user_id', (int) $search);
                }

                foreach ($tokens as $token) {
                    $piece = '%'.$token.'%';
                    $q->orWhere(function ($w) use ($piece) {
                        $w->where('firstname', 'like', $piece)
                            ->orWhere('middlename', 'like', $piece)
                            ->orWhere('lastname', 'like', $piece);
                    });
                }
            });
        }

        $sort = strtolower((string) $request->query('sort', 'desc'));
        $direction = $sort === 'asc' ? 'asc' : 'desc';

        return $query->orderBy('user_id', $direction)->paginate($perPage);
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],
            'firstname' => ['nullable', 'string', 'regex:/^[\p{L}\p{M}][\p{L}\p{M}\s\.\'\-\x{00B7}]*$/u'],
            'lastname' => ['nullable', 'string', 'regex:/^[\p{L}\p{M}][\p{L}\p{M}\s\.\'\-\x{00B7}]*$/u'],
            'middlename' => ['nullable', 'string', 'regex:/^[\p{L}\p{M}][\p{L}\p{M}\s\.\'\-\x{00B7}]*$/u'],
            'birthdate' => ['required', 'date'],
            'sex' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'contact_number' => ['nullable', 'string', 'regex:/^\+639\d{9}$/'],
        ]);

        foreach (['firstname', 'middlename', 'lastname'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $normalized = preg_replace('/\s+/u', ' ', trim((string) $data[$key]));
                $normalized = preg_replace("/\\s*([\\.'\\-\\x{00B7}])\\s*/u", '$1', $normalized);
                $data[$key] = $normalized === '' ? null : $normalized;
            }
        }

        $plainPassword = isset($data['password']) ? (string) $data['password'] : '';
        $passwordWasProvided = $plainPassword !== '';
        if (! $passwordWasProvided) {
            $plainPassword = Str::random(12);
        }

        $user = DB::transaction(function () use ($data, $plainPassword) {
            $user = User::create([
                'email' => $data['email'],
                'password_hash' => Hash::make($plainPassword),
                'role' => 'patient',
                'status' => 'active',
                'firstname' => $data['firstname'] ?? null,
                'lastname' => $data['lastname'] ?? null,
                'middlename' => $data['middlename'] ?? null,
                'birthdate' => $data['birthdate'] ?? null,
                'sex' => $data['sex'] ?? null,
                'address' => $data['address'] ?? null,
                'contact_number' => $data['contact_number'] ?? null,
                'is_first_login' => true,
                'must_change_credentials' => true,
                'account_activated' => true,
            ]);

            Mail::to($user->email)->send(new StaffInviteMail($user, $plainPassword));

            return $user;
        });

        return response()->json([
            'user' => $user->refresh(),
            'credentials_emailed' => true,
            'generated_password' => ! $passwordWasProvided,
        ], 201);
    }

    public function show(Request $request, User $patient)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        if ($patient->role !== 'patient') {
            abort(404);
        }

        return $patient;
    }

    public function update(Request $request, User $patient)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        if ($patient->role !== 'patient') {
            abort(404);
        }

        return app(UserController::class)->update($request, $patient);
    }

    public function destroy(Request $request, User $patient)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        if ($patient->role !== 'patient') {
            abort(404);
        }

        return app(UserController::class)->destroy($patient);
    }

    public function dependents(Request $request)
    {
        $currentUser = $request->user();

        if (! $currentUser || $currentUser->role === 'patient') {
            abort(403);
        }

        $data = $request->validate([
            'parent_user_id' => ['required', 'exists:users,user_id'],
        ]);

        $parent = User::query()->findOrFail((int) $data['parent_user_id']);
        if ($parent->role !== 'patient' || $parent->is_dependent) {
            return [];
        }

        return $parent->children()->get();
    }

    public function storeDependent(Request $request)
    {
        $currentUser = $request->user();

        if (! $currentUser || $currentUser->role === 'patient') {
            abort(403);
        }

        $data = $request->validate([
            'parent_user_id' => ['required', 'exists:users,user_id'],
            'firstname' => ['nullable', 'string', 'regex:/^[\p{L}\p{M}][\p{L}\p{M}\s\.\'\-\x{00B7}]*$/u'],
            'lastname' => ['nullable', 'string', 'regex:/^[\p{L}\p{M}][\p{L}\p{M}\s\.\'\-\x{00B7}]*$/u'],
            'middlename' => ['nullable', 'string', 'regex:/^[\p{L}\p{M}][\p{L}\p{M}\s\.\'\-\x{00B7}]*$/u'],
            'birthdate' => ['required', 'date'],
            'sex' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'contact_number' => ['nullable', 'string', 'regex:/^\+639\d{9}$/'],
            'relationship' => ['required', 'in:mother,father,guardian'],
            'email' => ['nullable', 'email'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        foreach (['firstname', 'middlename', 'lastname'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $normalized = preg_replace('/\s+/u', ' ', trim((string) $data[$key]));
                $normalized = preg_replace("/\\s*([\\.'\\-\\x{00B7}])\\s*/u", '$1', $normalized);
                $data[$key] = $normalized === '' ? null : $normalized;
            }
        }

        $parent = User::query()->findOrFail((int) $data['parent_user_id']);
        if ($parent->role !== 'patient' || $parent->is_dependent) {
            return response()->json([
                'message' => 'Parent must be a non-dependent patient.',
            ], 422);
        }

        $birthdate = Carbon::parse($data['birthdate']);
        $age = $birthdate->diffInYears(now());

        $requestedEmail = isset($data['email']) ? trim((string) $data['email']) : '';
        if ($requestedEmail === '') {
            $requestedEmail = null;
        }

        $plainPassword = isset($data['password']) ? (string) $data['password'] : '';
        $passwordProvided = $plainPassword !== '';

        if ($requestedEmail !== null) {
            $request->validate([
                'email' => ['email', 'unique:users,email'],
            ]);
        }

        $shouldAutoCredentials = $age < 5;
        $requiresEmailActivation = $age >= 5 && $requestedEmail === null;

        if (! $requiresEmailActivation && ! $passwordProvided) {
            $plainPassword = Str::random(12);
        }

        if (! isset($data['address']) || trim((string) $data['address']) === '') {
            $data['address'] = $parent->address;
        }

        $user = User::create([
            'parent_user_id' => $parent->user_id,
            'email' => $requiresEmailActivation ? null : $requestedEmail,
            'password_hash' => $requiresEmailActivation ? null : Hash::make($plainPassword),
            'role' => 'patient',
            'status' => $requiresEmailActivation ? 'inactive' : 'active',
            'firstname' => $data['firstname'] ?? null,
            'lastname' => $data['lastname'] ?? null,
            'middlename' => $data['middlename'] ?? null,
            'birthdate' => $data['birthdate'] ?? null,
            'sex' => $data['sex'] ?? null,
            'address' => $data['address'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
            'is_dependent' => true,
            'account_activated' => ! $requiresEmailActivation,
            'relationship' => $data['relationship'] ?? null,
            'is_first_login' => true,
            'must_change_credentials' => ! $requiresEmailActivation,
        ]);

        if ($requiresEmailActivation) {
            return response()->json([
                'dependent' => $user->refresh(),
                'activation' => [
                    'requires_email' => true,
                    'prompt' => 'Add email to activate account',
                ],
            ], 201);
        }

        if ($user->email === null) {
            $generatedEmail = 'dependent'.$user->user_id.'@temp.com';
            if (User::where('email', $generatedEmail)->exists()) {
                $generatedEmail = 'dependent'.$user->user_id.'-'.Str::lower(Str::random(4)).'@temp.com';
            }
            $user->update(['email' => $generatedEmail]);
        }

        $payload = [
            'dependent' => $user->refresh(),
            'activation' => [
                'requires_email' => false,
                'prompt' => null,
            ],
        ];

        $payload['credentials'] = [
            'email' => $user->email,
            'password' => $plainPassword,
            'generated' => ! $passwordProvided || $requestedEmail === null,
        ];

        return response()->json($payload, 201);
    }

    public function activateDependent(Request $request, User $dependent)
    {
        $currentUser = $request->user();

        if (! $currentUser || $currentUser->role === 'patient') {
            abort(403);
        }

        if (! $dependent->is_dependent) {
            return response()->json([
                'message' => 'User is not a dependent.',
            ], 422);
        }

        $data = $request->validate([
            'email' => ['required', 'email', "unique:users,email,{$dependent->user_id},user_id"],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $wasActivated = (bool) $dependent->account_activated;

        $dependent->update([
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'account_activated' => true,
            'status' => 'active',
            'is_first_login' => true,
            'must_change_credentials' => true,
        ]);

        if (! $wasActivated && (bool) $dependent->account_activated) {
            Notification::notifyAdmins('[Account Activated] A user activated their account.');
        }

        return $dependent->refresh();
    }

    public function vitals(Request $request)
    {
        $currentUser = $request->user();
        $isPatient = $currentUser && $currentUser->role === 'patient';

        $data = $request->validate([
            'patient_id' => [$isPatient ? 'sometimes' : 'required', 'integer', 'exists:users,user_id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,appointment_id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = (int) ($data['per_page'] ?? 50);
        if ($perPage < 1) {
            $perPage = 50;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        if ($isPatient) {
            $patientId = (int) ($currentUser->user_id ?? 0);
            if (array_key_exists('patient_id', $data)) {
                $candidate = (int) $data['patient_id'];
                if (! $currentUser->canAccessPatientId($candidate)) {
                    abort(403);
                }
                $patientId = $candidate;
            }
        } else {
            $patientId = (int) $data['patient_id'];
        }

        LogEntry::write(
            $currentUser ? (int) $currentUser->user_id : null,
            'access_patient_vitals',
            'patients',
            $patientId,
            [],
            120
        );

        $query = DB::table('vitals')
            ->where('vitals.patient_id', $patientId)
            ->leftJoin('appointments', 'vitals.appointment_id', '=', 'appointments.appointment_id')
            ->leftJoin('users as doctors', 'appointments.doctor_id', '=', 'doctors.user_id')
            ->select([
                'vitals.*',
                'appointments.appointment_datetime',
                'appointments.doctor_id',
                'doctors.firstname as doctor_firstname',
                'doctors.middlename as doctor_middlename',
                'doctors.lastname as doctor_lastname',
            ]);

        if (! empty($data['appointment_id'])) {
            $query->where('vitals.appointment_id', (int) $data['appointment_id']);
        }

        return $query
            ->orderByDesc('vitals.recorded_at')
            ->orderByDesc('vitals.vital_id')
            ->paginate($perPage);
    }

    public function storeVital(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser || $currentUser->role === 'patient') {
            abort(403);
        }

        $data = $request->validate([
            'appointment_id' => ['required', 'integer', 'exists:appointments,appointment_id'],
            'height_cm' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'blood_pressure' => ['nullable', 'string', 'max:20'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:99.9'],
            'pulse_rate' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $vitalValues = [
            'height_cm' => array_key_exists('height_cm', $data) ? $data['height_cm'] : null,
            'weight_kg' => array_key_exists('weight_kg', $data) ? $data['weight_kg'] : null,
            'blood_pressure' => isset($data['blood_pressure']) ? trim((string) $data['blood_pressure']) : null,
            'temperature' => array_key_exists('temperature', $data) ? $data['temperature'] : null,
            'pulse_rate' => array_key_exists('pulse_rate', $data) ? $data['pulse_rate'] : null,
        ];

        if ($vitalValues['blood_pressure'] === '') {
            $vitalValues['blood_pressure'] = null;
        }

        $hasAnyValue = false;
        foreach ($vitalValues as $value) {
            if ($value !== null && $value !== '') {
                $hasAnyValue = true;
                break;
            }
        }

        if (! $hasAnyValue) {
            return response()->json([
                'message' => 'Provide at least one vital sign or close the modal to skip.',
            ], 422);
        }

        $appointment = DB::table('appointments')
            ->select('appointment_id', 'patient_id')
            ->where('appointment_id', (int) $data['appointment_id'])
            ->first();

        if (! $appointment) {
            return response()->json([
                'message' => 'Appointment not found.',
            ], 404);
        }

        $payload = array_merge($vitalValues, [
            'patient_id' => (int) $appointment->patient_id,
            'appointment_id' => (int) $appointment->appointment_id,
            'recorded_at' => now(),
        ]);

        $existing = DB::table('vitals')
            ->where('appointment_id', (int) $appointment->appointment_id)
            ->orderByDesc('vital_id')
            ->first();

        if ($existing) {
            DB::table('vitals')
                ->where('vital_id', (int) $existing->vital_id)
                ->update($payload);

            $vitalId = (int) $existing->vital_id;
            $action = 'patient_vitals_updated';
        } else {
            $vitalId = (int) DB::table('vitals')->insertGetId($payload, 'vital_id');
            $action = 'patient_vitals_created';
        }

        LogEntry::write(
            (int) $currentUser->user_id,
            $action,
            'vitals',
            $vitalId,
            [
                'patient_id' => (int) $appointment->patient_id,
                'appointment_id' => (int) $appointment->appointment_id,
            ]
        );

        $vital = DB::table('vitals')
            ->leftJoin('appointments', 'vitals.appointment_id', '=', 'appointments.appointment_id')
            ->leftJoin('users as doctors', 'appointments.doctor_id', '=', 'doctors.user_id')
            ->where('vitals.vital_id', $vitalId)
            ->select([
                'vitals.*',
                'appointments.appointment_datetime',
                'appointments.doctor_id',
                'doctors.firstname as doctor_firstname',
                'doctors.middlename as doctor_middlename',
                'doctors.lastname as doctor_lastname',
            ])
            ->first();

        return response()->json($vital, $existing ? 200 : 201);
    }
}
