<?php

namespace App\Http\Controllers;

use App\Models\LogEntry;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private function rateKey(string $prefix, string $identifier, string $ip): string
    {
        $cleanId = trim(strtolower($identifier));
        $cleanIp = trim(strtolower($ip));
        return $prefix.':'.$cleanId.':ip:'.$cleanIp;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $email = strtolower(trim((string) $credentials['email']));
        $ip = (string) ($request->ip() ?? '');

        $user = User::where('email', $email)->first();
        $identifier = $user ? ('uid:'.$user->user_id) : ('email:'.$email);
        $rateKey = $this->rateKey('auth_login', $identifier, $ip);
        $slowKey = $this->rateKey('auth_login_slow', $identifier, $ip);

        if (RateLimiter::tooManyAttempts($rateKey, 9)) {
            $retryAfter = RateLimiter::availableIn($rateKey);

            LogEntry::write(
                $user ? (int) $user->user_id : null,
                'auth_login_attempt',
                'users',
                $user ? (int) $user->user_id : null,
                [
                    'success' => false,
                    'reason' => 'RATE_LIMITED_MAX_ATTEMPTS',
                    'email' => $email,
                    'ip' => $ip,
                    'retry_after' => $retryAfter,
                ]
            );

            return response()->json([
                'message' => 'Too many login attempts. Please try again later.',
                'code' => 'LOGIN_RATE_LIMITED',
                'retry_after' => $retryAfter,
            ], 429);
        }

        $failures = RateLimiter::attempts($rateKey);
        if ($failures >= 3 && RateLimiter::tooManyAttempts($slowKey, 1)) {
            $retryAfterSlow = RateLimiter::availableIn($slowKey);

            LogEntry::write(
                $user ? (int) $user->user_id : null,
                'auth_login_attempt',
                'users',
                $user ? (int) $user->user_id : null,
                [
                    'success' => false,
                    'reason' => 'RATE_LIMITED_BACKOFF',
                    'email' => $email,
                    'ip' => $ip,
                    'retry_after' => $retryAfterSlow,
                ]
            );

            return response()->json([
                'message' => 'Too many login attempts. Please try again shortly.',
                'code' => 'LOGIN_RATE_LIMITED',
                'retry_after' => $retryAfterSlow,
            ], 429);
        }

        $passwordOk = $user && Hash::check($credentials['password'], $user->password_hash);
        if (! $passwordOk) {
            RateLimiter::hit($rateKey, 300);
            $after = RateLimiter::attempts($rateKey);
            if ($after >= 3 && $after < 9) {
                RateLimiter::hit($slowKey, 5);
            }

            if ($after === 3) {
                Notification::notifyAdmins('[Failed Login Attempts] Multiple failed login attempts detected.');
            }

            LogEntry::write(
                $user ? (int) $user->user_id : null,
                'auth_login_attempt',
                'users',
                $user ? (int) $user->user_id : null,
                [
                    'success' => false,
                    'reason' => 'INVALID_CREDENTIALS',
                    'email' => $email,
                    'ip' => $ip,
                    'failed_attempts' => $after,
                ]
            );

            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->status !== 'active') {
            RateLimiter::hit($rateKey, 300);
            $after2 = RateLimiter::attempts($rateKey);
            if ($after2 >= 3 && $after2 < 9) {
                RateLimiter::hit($slowKey, 5);
            }

            if ($after2 === 3) {
                Notification::notifyAdmins('[Failed Login Attempts] Multiple failed login attempts detected.');
            }

            LogEntry::write(
                (int) $user->user_id,
                'auth_login_attempt',
                'users',
                (int) $user->user_id,
                [
                    'success' => false,
                    'reason' => 'ACCOUNT_NOT_ACTIVE',
                    'email' => $email,
                    'ip' => $ip,
                    'failed_attempts' => $after2,
                ]
            );

            return response()->json([
                'message' => 'Account is not active',
            ], 403);
        }

        RateLimiter::clear($rateKey);
        RateLimiter::clear($slowKey);

        if (! is_string($user->uuid ?? null) || trim((string) $user->uuid) === '') {
            $user->uuid = (string) Str::uuid();
            $user->save();
        }

        LogEntry::write(
            (int) $user->user_id,
            'auth_login_attempt',
            'users',
            (int) $user->user_id,
            [
                'success' => true,
                'email' => $email,
                'ip' => $ip,
            ],
            10
        );

        $user->tokens()->delete();

        $token = $user->createToken($credentials['device_name'] ?? 'api')->plainTextToken;

        LogEntry::write(
            (int) $user->user_id,
            'auth_login',
            'users',
            (int) $user->user_id,
            [
                'device_name' => $credentials['device_name'] ?? 'api',
            ],
            60
        );

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        if ($user) {
            LogEntry::write(
                (int) $user->user_id,
                'auth_logout',
                'users',
                (int) $user->user_id,
                [],
                60
            );
        }

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    public function requestPasswordReset(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim((string) $data['email']));
        $ip = (string) ($request->ip() ?? '');

        $user = User::where('email', $email)->first();
        $identifier = $user ? ('uid:'.$user->user_id) : ('email:'.$email);
        $rateKey = $this->rateKey('password_reset_request', $identifier, $ip);

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $retryAfter = RateLimiter::availableIn($rateKey);

            LogEntry::write(
                $user ? (int) $user->user_id : null,
                'password_reset_request',
                'users',
                $user ? (int) $user->user_id : null,
                [
                    'success' => false,
                    'reason' => 'RATE_LIMITED',
                    'email' => $email,
                    'ip' => $ip,
                    'retry_after' => $retryAfter,
                ]
            );

            return response()->json([
                'message' => 'Too many reset requests. Please try again later.',
                'code' => 'PASSWORD_RESET_REQUEST_RATE_LIMITED',
                'retry_after' => $retryAfter,
            ], 429);
        }

        RateLimiter::hit($rateKey, 600);

        if (! $user) {
            LogEntry::write(
                null,
                'password_reset_request',
                'users',
                null,
                [
                    'success' => true,
                    'email' => $email,
                    'ip' => $ip,
                    'note' => 'user_not_found',
                ],
                10
            );

            return response()->json([
                'message' => 'If the email exists, a reset token has been generated',
            ]);
        }

        $token = Str::random(64);

        $user->password_reset_token = $token;
        $user->password_reset_expires_at = now()->addMinutes(60);
        $user->save();

        LogEntry::write(
            (int) $user->user_id,
            'password_reset_request',
            'users',
            (int) $user->user_id,
            [
                'success' => true,
                'email' => $email,
                'ip' => $ip,
            ],
            10
        );

        return response()->json([
            'message' => 'Password reset token generated',
            'token' => $token,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = strtolower(trim((string) $data['email']));
        $ip = (string) ($request->ip() ?? '');

        $user = User::where('email', $email)->first();
        $identifier = $user ? ('uid:'.$user->user_id) : ('email:'.$email);
        $rateKey = $this->rateKey('password_reset_attempt', $identifier, $ip);

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $retryAfter = RateLimiter::availableIn($rateKey);

            LogEntry::write(
                $user ? (int) $user->user_id : null,
                'password_reset_attempt',
                'users',
                $user ? (int) $user->user_id : null,
                [
                    'success' => false,
                    'reason' => 'RATE_LIMITED',
                    'email' => $email,
                    'ip' => $ip,
                    'retry_after' => $retryAfter,
                ]
            );

            return response()->json([
                'message' => 'Too many reset attempts. Please try again later.',
                'code' => 'PASSWORD_RESET_RATE_LIMITED',
                'retry_after' => $retryAfter,
            ], 429);
        }

        if (! $user || $user->password_reset_token !== $data['token']) {
            RateLimiter::hit($rateKey, 300);
            $attempts = RateLimiter::attempts($rateKey);

            LogEntry::write(
                $user ? (int) $user->user_id : null,
                'password_reset_attempt',
                'users',
                $user ? (int) $user->user_id : null,
                [
                    'success' => false,
                    'reason' => $user ? 'INVALID_TOKEN' : 'USER_NOT_FOUND',
                    'email' => $email,
                    'ip' => $ip,
                    'attempts' => $attempts,
                ]
            );

            return response()->json([
                'message' => 'Invalid token or email',
            ], 422);
        }

        if (! $user->password_reset_expires_at || $user->password_reset_expires_at->isPast()) {
            RateLimiter::hit($rateKey, 300);
            $attempts2 = RateLimiter::attempts($rateKey);

            LogEntry::write(
                (int) $user->user_id,
                'password_reset_attempt',
                'users',
                (int) $user->user_id,
                [
                    'success' => false,
                    'reason' => 'TOKEN_EXPIRED',
                    'email' => $email,
                    'ip' => $ip,
                    'attempts' => $attempts2,
                ]
            );

            return response()->json([
                'message' => 'Token has expired',
            ], 422);
        }

        $user->password_hash = Hash::make($data['password']);
        $user->password_reset_token = null;
        $user->password_reset_expires_at = null;
        $user->save();

        RateLimiter::clear($rateKey);

        LogEntry::write(
            (int) $user->user_id,
            'password_reset_attempt',
            'users',
            (int) $user->user_id,
            [
                'success' => true,
                'email' => $email,
                'ip' => $ip,
            ]
        );

        return response()->json([
            'message' => 'Password reset successful',
        ]);
    }
}
