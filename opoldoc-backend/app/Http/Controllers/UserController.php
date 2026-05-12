<?php

namespace App\Http\Controllers;

use App\Mail\StaffInviteMail;
use App\Models\LogEntry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        return User::query()->paginate();
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,doctor,receptionist,patient'],
            'status' => ['nullable', 'in:active,inactive,suspended'],
            'firstname' => ['nullable', 'string'],
            'lastname' => ['nullable', 'string'],
            'middlename' => ['nullable', 'string'],
            'birthdate' => ['nullable', 'date'],
            'sex' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'contact_number' => ['nullable', 'string'],
        ]);

        $data['password_hash'] = Hash::make($data['password']);
        unset($data['password']);

        if (! isset($data['status'])) {
            $data['status'] = 'active';
        }

        if (($data['role'] ?? null) === 'patient') {
            $data['is_first_login'] = true;
            $data['must_change_credentials'] = true;
        }

        $user = User::create($data);

        LogEntry::write(
            optional($request->user())->user_id ? (int) $request->user()->user_id : null,
            'user_created',
            'users',
            (int) $user->user_id,
            [
                'role' => $user->role,
            ]
        );

        return response()->json($user, 201);
    }

    public function show(Request $request, User $user)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient' && $user->user_id !== $currentUser->user_id) {
            abort(403);
        }

        return $user;
    }

    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient' && $user->user_id !== $currentUser->user_id) {
            abort(403);
        }

        $emailRules = ['sometimes', 'email', "unique:users,email,{$user->user_id},user_id"];
        if ($user->role === 'admin' && $user->is_first_login) {
            $emailRules[] = 'regex:/@example\\.com$/i';
        }

        $mustChangeForUser = $user->role === 'patient'
            ? (bool) ($user->must_change_credentials ?? false)
            : (bool) $user->is_first_login;

        $requiresPasswordForFirstLogin = $mustChangeForUser
            && $request->has('must_change_credentials')
            && $request->boolean('must_change_credentials') === false;

        $passwordRules = [
            $requiresPasswordForFirstLogin ? 'required' : 'sometimes',
            'string',
            'min:8',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).+$/',
        ];

        $data = $request->validate([
            'email' => $emailRules,
            'password' => $passwordRules,
            'must_change_credentials' => ['sometimes', 'boolean'],
            'role' => ['sometimes', 'in:admin,doctor,receptionist,patient'],
            'status' => ['sometimes', 'in:active,inactive,suspended'],
            'firstname' => ['sometimes', 'nullable', 'string', 'regex:/^[\p{L}\p{M}][\p{L}\p{M}\s\.\'\-\x{00B7}]*$/u'],
            'lastname' => ['sometimes', 'nullable', 'string', 'regex:/^[\p{L}\p{M}][\p{L}\p{M}\s\.\'\-\x{00B7}]*$/u'],
            'middlename' => ['sometimes', 'nullable', 'string', 'regex:/^[\p{L}\p{M}][\p{L}\p{M}\s\.\'\-\x{00B7}]*$/u'],
            'birthdate' => ['sometimes', 'nullable', 'date'],
            'sex' => ['sometimes', 'nullable', 'string'],
            'address' => ['sometimes', 'nullable', 'string'],
            'contact_number' => ['sometimes', 'nullable', 'string', 'regex:/^(\\+63\\d{10}|0\\d{10})$/'],
            'account_activated' => ['sometimes', 'boolean'],
            'is_first_login' => ['sometimes', 'boolean'],
            'license_number' => ['sometimes', 'nullable', 'string'],
            'specialization' => ['sometimes', 'nullable', 'string'],
            'hire_date' => ['sometimes', 'nullable', 'date'],
        ], [
            'email.regex' => 'Email must be a valid email ending with @example.com.',
            'password.required' => 'Password is required.',
            'password.regex' => 'Password must be at least 8 characters and include uppercase, lowercase, a number, and a symbol.',
            'firstname.regex' => 'First name must contain letters only (accents allowed), plus hyphens, apostrophes, periods, and middle dots.',
            'middlename.regex' => 'Middle name must contain letters only (accents allowed), plus hyphens, apostrophes, periods, and middle dots.',
            'lastname.regex' => 'Last name must contain letters only (accents allowed), plus hyphens, apostrophes, periods, and middle dots.',
            'contact_number.regex' => 'Contact number must be a valid PH number.',
        ]);

        if ($currentUser && $currentUser->role === 'patient') {
            unset(
                $data['role'],
                $data['status'],
                $data['account_activated'],
                $data['is_first_login'],
                $data['license_number'],
                $data['specialization'],
                $data['hire_date']
            );
        }

        if (array_key_exists('password', $data)) {
            $data['password_hash'] = Hash::make($data['password']);
            unset($data['password']);
        }

        if (array_key_exists('must_change_credentials', $data)) {
            if ($data['must_change_credentials'] === false && $user->role !== 'patient') {
                $user->is_first_login = false;
            }
        }

        foreach (['firstname', 'middlename', 'lastname'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $normalized = preg_replace('/\s+/u', ' ', trim((string) $data[$key]));
                $normalized = preg_replace("/\\s*([\\.'\\-\\x{00B7}])\\s*/u", '$1', $normalized);
                $data[$key] = $normalized === '' ? null : $normalized;
            }
        }

        $user->update($data);

        LogEntry::write(
            optional($request->user())->user_id ? (int) $request->user()->user_id : null,
            'user_updated',
            'users',
            (int) $user->user_id,
            [
                'fields' => array_keys($data),
            ]
        );

        return $user->refresh();
    }

    public function destroy(Request $request, User $user)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $user->delete();

        LogEntry::write(
            optional($request->user())->user_id ? (int) $request->user()->user_id : null,
            'user_deleted',
            'users',
            (int) $user->user_id
        );

        return response()->json([
            'message' => 'User deleted',
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'firstname' => ['nullable', 'string'],
            'lastname' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'role' => 'patient',
            'status' => 'active',
            'is_dependent' => false,
            'account_activated' => true,
            'is_first_login' => true,
            'must_change_credentials' => false,
            'firstname' => $data['firstname'] ?? null,
            'lastname' => $data['lastname'] ?? null,
        ]);

        LogEntry::write(
            (int) $user->user_id,
            'patient_registered',
            'users',
            (int) $user->user_id
        );

        return response()->json($user, 201);
    }

    public function invite(Request $request)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:admin,doctor,receptionist,patient'],
            'status' => ['nullable', 'in:active,inactive,suspended'],
            'firstname' => ['nullable', 'string'],
            'lastname' => ['nullable', 'string'],
            'middlename' => ['nullable', 'string'],
            'birthdate' => ['nullable', 'date'],
            'sex' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'contact_number' => ['nullable', 'string'],
            'license_number' => ['nullable', 'string'],
            'specialization' => ['nullable', 'string'],
            'employee_number' => ['nullable', 'string'],
            'hire_date' => ['nullable', 'date'],
        ]);

        $plainPassword = Str::random(12);

        $data['password_hash'] = Hash::make($plainPassword);
        $data['status'] = $data['status'] ?? 'active';
        $data['is_first_login'] = true;
        if (($data['role'] ?? null) === 'patient') {
            $data['must_change_credentials'] = true;
        }

        $user = \Illuminate\Support\Facades\DB::transaction(function () use ($data, $plainPassword, $request) {
            $user = User::create($data);

            Mail::to($user->email)->send(new StaffInviteMail($user, $plainPassword));

            LogEntry::write(
                optional($request->user())->user_id ? (int) $request->user()->user_id : null,
                'user_invited',
                'users',
                (int) $user->user_id,
                [
                    'role' => $user->role,
                ]
            );

            return $user;
        });

        return response()->json($user, 201);
    }

    public function dependents(Request $request, User $user)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient' && (int) $currentUser->user_id !== (int) $user->user_id) {
            abort(403);
        }

        return $user->children()->get();
    }

    public function updateSignature(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser) {
            abort(401);
        }
        if ($currentUser->role !== 'doctor') {
            abort(403);
        }

        $data = $request->validate([
            'signature' => ['required', 'file', 'image', 'max:2048'],
        ]);

        $file = $data['signature'];
        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        if (! in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            return response()->json([
                'message' => 'Unsupported signature image type.',
            ], 422);
        }

        $oldPath = $currentUser->signature_path;

        $filename = 'signature_'.$currentUser->user_id.'_'.now()->format('YmdHis').'.'.$ext;
        $path = $file->storeAs('signatures', $filename, 'public');

        $currentUser->update([
            'signature_path' => $path,
        ]);

        if (is_string($oldPath) && trim($oldPath) !== '' && $oldPath !== $path) {
            Storage::disk('public')->delete($oldPath);
        }

        return $currentUser->refresh();
    }

    public function updateProfilePicture(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser) {
            abort(401);
        }

        $data = $request->validate([
            'prof_path' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $file = $data['prof_path'];
        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        if (! in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            return response()->json([
                'message' => 'Unsupported profile image type.',
            ], 422);
        }

        $oldPath = $currentUser->prof_path ?? null;

        $filename = 'profile_'.$currentUser->user_id.'_'.now()->format('YmdHis').'.'.$ext;
        $path = $file->storeAs('profiles', $filename, 'public');

        // We use DB to update if model doesn't have it in fillable
        DB::table('users')->where('user_id', $currentUser->user_id)->update([
            'prof_path' => $path,
        ]);

        if (is_string($oldPath) && trim($oldPath) !== '' && $oldPath !== $path) {
            Storage::disk('public')->delete($oldPath);
        }

        return $currentUser->refresh();
    }

    public function profilePicture(User $user)
    {
        $path = $user->prof_path ?? null;
        if (! is_string($path) || trim($path) === '') {
            abort(404);
        }
        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $absolute = storage_path('app/public/'.$path);
        return response()->file($absolute);
    }

    public function verifyCurrentPassword(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser) {
            abort(401);
        }

        $data = $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        $ip = (string) ($request->ip() ?? '');
        $verifyKey = 'password_verify:uid:'.(int) $currentUser->user_id.':ip:'.strtolower(trim($ip));
        $tokenKey = 'password_change_token:'.(int) $currentUser->user_id;

        if (RateLimiter::tooManyAttempts($verifyKey, 3)) {
            $retryAfter = RateLimiter::availableIn($verifyKey);

            LogEntry::write(
                (int) $currentUser->user_id,
                'password_verify_attempt',
                'users',
                (int) $currentUser->user_id,
                [
                    'success' => false,
                    'reason' => 'RATE_LIMITED',
                    'ip' => $ip,
                    'retry_after' => $retryAfter,
                ]
            );

            return response()->json([
                'message' => 'Too many password attempts. Please try again later.',
                'code' => 'PASSWORD_COOLDOWN',
                'retry_after' => $retryAfter,
            ], 429);
        }

        $currentPassword = (string) $data['current_password'];
        if (! Hash::check($currentPassword, (string) $currentUser->password_hash)) {
            RateLimiter::hit($verifyKey, 300);
            $tries = RateLimiter::attempts($verifyKey);

            LogEntry::write(
                (int) $currentUser->user_id,
                'password_verify_attempt',
                'users',
                (int) $currentUser->user_id,
                [
                    'success' => false,
                    'reason' => 'INVALID_CURRENT_PASSWORD',
                    'ip' => $ip,
                    'attempts' => $tries,
                ]
            );

            return response()->json([
                'message' => 'Current password is incorrect.',
                'code' => 'INVALID_CURRENT_PASSWORD',
                'tries_remaining' => max(0, 3 - $tries),
            ], 422);
        }

        RateLimiter::clear($verifyKey);

        $token = Str::random(48);
        $expiresAt = now()->addMinutes(10);

        Cache::store('file')->put($tokenKey, [
            'token' => $token,
            'expires_at' => $expiresAt->toIso8601String(),
        ], $expiresAt);

        LogEntry::write(
            (int) $currentUser->user_id,
            'password_verify_attempt',
            'users',
            (int) $currentUser->user_id,
            [
                'success' => true,
                'ip' => $ip,
            ],
            5
        );

        return response()->json([
            'verified' => true,
            'token' => $token,
            'expires_in' => 600,
        ]);
    }

    public function changePassword(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser) {
            abort(401);
        }

        $data = $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).+$/'],
        ], [
            'password.regex' => 'Password must be at least 8 characters and include uppercase, lowercase, a number, and a symbol.',
        ]);

        $store = Cache::store('file');
        $tokenKey = 'password_change_token:'.(int) $currentUser->user_id;

        $tokenState = $store->get($tokenKey);
        $tokenState = is_array($tokenState) ? $tokenState : null;

        $expectedToken = $tokenState && isset($tokenState['token']) ? (string) $tokenState['token'] : '';
        if ($expectedToken === '' || ! hash_equals($expectedToken, (string) $data['token'])) {
            return response()->json([
                'message' => 'Password verification is required. Please verify your current password again.',
                'code' => 'PASSWORD_VERIFY_REQUIRED',
            ], 422);
        }

        $currentUser->password_hash = Hash::make((string) $data['password']);
        if ($currentUser->role === 'patient') {
            $currentUser->must_change_credentials = false;
        } else {
            if ($currentUser->is_first_login) {
                $currentUser->is_first_login = false;
            }
        }
        $currentUser->save();

        $store->forget($tokenKey);

        LogEntry::write(
            (int) $currentUser->user_id,
            'password_changed',
            'users',
            (int) $currentUser->user_id,
            []
        );

        return response()->json([
            'message' => 'Password updated',
        ]);
    }

    public function signature(User $user)
    {
        $path = $user->signature_path;
        if (! is_string($path) || trim($path) === '') {
            abort(404);
        }
        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $absolute = storage_path('app/public/'.$path);
        return response()->file($absolute);
    }
}
