<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessagingController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser) {
            abort(401);
        }

        $query = Conversation::query()->with('user');

        if ($currentUser->role === 'patient') {
            $query->whereIn('user_id', $currentUser->accessiblePatientIds());
        } elseif ($currentUser->role === 'receptionist') {
            if ($request->filled('patient_id')) {
                $query->where('user_id', (int) $request->query('patient_id'));
            }
        } else {
            abort(403);
        }

        return $query
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->orderByDesc('conversation_id')
            ->paginate((int) $request->query('per_page', 20));
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();
        if (! $currentUser) {
            abort(401);
        }

        $data = $request->validate([
            'patient_id' => ['nullable', 'exists:users,user_id'],
        ]);

        $targetPatientId = null;
        if ($currentUser->role === 'patient') {
            $targetPatientId = (int) $currentUser->user_id;
            if (array_key_exists('patient_id', $data) && $data['patient_id']) {
                $candidate = (int) $data['patient_id'];
                if (! $currentUser->canAccessPatientId($candidate)) {
                    abort(403);
                }
                $targetPatientId = $candidate;
            }
        } elseif ($currentUser->role === 'receptionist') {
            $targetPatientId = (int) ($data['patient_id'] ?? 0);
            if (! $targetPatientId) {
                return response()->json([
                    'message' => 'patient_id is required.',
                ], 422);
            }
        } else {
            abort(403);
        }

        $patient = User::query()->findOrFail($targetPatientId);
        if ($patient->role !== 'patient') {
            return response()->json([
                'message' => 'Target user must be a patient.',
            ], 422);
        }

        $conversation = Conversation::ensureForPatient($targetPatientId);

        return $conversation->load('user');
    }

    public function messages(Request $request, Conversation $conversation)
    {
        $currentUser = $request->user();
        if (! $currentUser) {
            abort(401);
        }

        if (! $this->canAccessConversation($currentUser, $conversation)) {
            abort(403);
        }

        $perPage = (int) $request->query('per_page', 50);
        if ($perPage < 1) {
            $perPage = 50;
        }
        if ($perPage > 200) {
            $perPage = 200;
        }

        return Message::query()
            ->where('conversation_id', $conversation->conversation_id)
            ->orderByDesc('message_id')
            ->paginate($perPage);
    }

    public function send(Request $request, Conversation $conversation)
    {
        $currentUser = $request->user();
        if (! $currentUser) {
            abort(401);
        }

        if (! $this->canAccessConversation($currentUser, $conversation)) {
            abort(403);
        }

        $data = $request->validate([
            'message_text' => ['required', 'string'],
        ]);

        $sender = null;

        if ($currentUser->role === 'patient') {
            $sender = 'user';
        } elseif ($currentUser->role === 'receptionist') {
            $sender = 'bot';
        } else {
            abort(403);
        }

        $message = Message::create([
            'conversation_id' => $conversation->conversation_id,
            'sender' => $sender,
            'message_text' => $data['message_text'],
        ]);

        return response()->json($message, 201);
    }

    private function canAccessConversation(User $currentUser, Conversation $conversation): bool
    {
        if ($currentUser->role === 'patient') {
            return $currentUser->canAccessPatientId((int) $conversation->user_id);
        }

        if ($currentUser->role === 'receptionist') {
            return true;
        }

        return false;
    }
}
