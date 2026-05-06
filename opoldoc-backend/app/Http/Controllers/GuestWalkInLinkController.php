<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GuestWalkInLinkController extends Controller
{
    private const CACHE_KEY = 'guest_walkin_active';

    public function current(Request $request)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $active = $this->getActive();

        return response()->json([
            'link' => $active ? [
                'link_id' => (string) $active['token'],
                'token' => (string) $active['token'],
                'created_by' => null,
                'deprecated_at' => null,
                'created_at' => (string) $active['created_at'],
                'updated_at' => (string) $active['created_at'],
            ] : null,
            'static_url' => url('/public/walk-in/guest'),
            'qr_url' => $active ? url('/public/walk-in/display/'.$active['token']) : url('/public/walk-in/display'),
            'active_url' => $active ? url('/public/walk-in/guest/'.$active['token']) : null,
        ]);
    }

    public function generate(Request $request)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        $token = now()->format('YmdHis').'_'.Str::random(16);

        $payload = [
            'token' => $token,
            'created_at' => now()->toDateTimeString(),
        ];
        Cache::forever(self::CACHE_KEY, $payload);

        return response()->json([
            'link' => [
                'link_id' => (string) $payload['token'],
                'token' => (string) $payload['token'],
                'created_by' => null,
                'deprecated_at' => null,
                'created_at' => (string) $payload['created_at'],
                'updated_at' => (string) $payload['created_at'],
            ],
            'static_url' => url('/public/walk-in/guest'),
            'qr_url' => url('/public/walk-in/display/'.$payload['token']),
            'active_url' => url('/public/walk-in/guest/'.$payload['token']),
        ], 201);
    }

    public function deprecate(Request $request, string $link)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            abort(403);
        }

        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'link' => null,
            'static_url' => url('/public/walk-in/guest'),
            'qr_url' => url('/public/walk-in/display'),
            'active_url' => null,
        ]);
    }

    private function getActive(): ?array
    {
        $payload = Cache::get(self::CACHE_KEY);
        if (! is_array($payload)) {
            return null;
        }

        $token = $payload['token'] ?? null;
        $createdAt = $payload['created_at'] ?? null;
        if (! is_string($token) || trim($token) === '' || ! is_string($createdAt) || trim($createdAt) === '') {
            return null;
        }

        return [
            'token' => $token,
            'created_at' => $createdAt,
        ];
    }
}
