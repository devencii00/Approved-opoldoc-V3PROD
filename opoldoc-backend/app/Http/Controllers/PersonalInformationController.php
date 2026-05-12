<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PersonalInformationController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();

        if ($currentUser && $currentUser->role === 'patient' && ! $currentUser->is_dependent) {
            return User::query()
                ->whereIn('user_id', $currentUser->accessiblePatientIds())
                ->get();
        }

        return User::query()
            ->where('user_id', $currentUser->user_id)
            ->get();
    }

    public function show(User $personal_information)
    {
        $currentUser = request()->user();
        if ($currentUser && $currentUser->role === 'patient') {
            if (! $currentUser->canAccessPatientId((int) $personal_information->user_id)) {
                abort(403);
            }
        } elseif ($currentUser && (int) $personal_information->user_id !== (int) $currentUser->user_id) {
            abort(403);
        }

        return $personal_information;
    }

    public function store(Request $request)
    {
        return $this->update($request, $request->user());
    }

    public function update(Request $request, User $personal_information)
    {
        $currentUser = $request->user();
        if ($currentUser && $currentUser->role === 'patient') {
            if (! $currentUser->canAccessPatientId((int) $personal_information->user_id)) {
                abort(403);
            }
        } elseif ($currentUser && (int) $personal_information->user_id !== (int) $currentUser->user_id) {
            abort(403);
        }

        $data = $request->validate([
            'firstname' => ['sometimes', 'nullable', 'string'],
            'lastname' => ['sometimes', 'nullable', 'string'],
            'middlename' => ['sometimes', 'nullable', 'string'],
            'birthdate' => ['sometimes', 'nullable', 'date'],
            'sex' => ['sometimes', 'nullable', 'string'],
            'address' => ['sometimes', 'nullable', 'string'],
            'contact_number' => ['sometimes', 'nullable', 'string'],
        ]);

        if (
            $currentUser &&
            $currentUser->role === 'patient' &&
            ! $currentUser->is_first_login &&
            (int) $personal_information->user_id === (int) $currentUser->user_id
        ) {
            $data = array_intersect_key($data, [
                'contact_number' => true,
            ]);
        }

        $personal_information->update($data);

        return $personal_information->refresh();
    }
}
