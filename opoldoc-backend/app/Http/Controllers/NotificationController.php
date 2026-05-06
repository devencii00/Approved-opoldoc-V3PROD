<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::query()->where('user_id', $request->user()->user_id);

        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        return $query->orderByDesc('created_at')->paginate();
    }

    public function update(Request $request, Notification $notification)
    {
        $this->authorizeNotificationOwner($request, $notification);

        $data = $request->validate([
            'is_read' => ['sometimes', 'boolean'],
        ]);

        $notification->update($data);

        return $notification->refresh();
    }

    public function destroy(Request $request, Notification $notification)
    {
        $this->authorizeNotificationOwner($request, $notification);

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted',
        ]);
    }

    protected function authorizeNotificationOwner(Request $request, Notification $notification): void
    {
        if ($notification->user_id !== $request->user()->user_id) {
            abort(403);
        }
    }
}
