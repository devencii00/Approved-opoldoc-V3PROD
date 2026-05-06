<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            'doctor' => [
                'email' => 'doctor@example.com',
                'password' => 'doctor123',
                
            ],
            'receptionist' => [
                'email' => 'receptionist@example.com',
                'password' => 'reception123',
            ],
        ];

        foreach ($users as $role => $credentials) {
            User::firstOrCreate(
                ['email' => $credentials['email']],
                [
                    'role' => $role,
                    'status' => 'active',
                    'password_hash' => Hash::make($credentials['password']),
                    'account_activated' => 1,
                    'is_first_login' => 1,
                ]
            );
        }
    }
}
