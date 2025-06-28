<?php

namespace Database\Seeders;

use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'role' => 'doctor',
                'name' => 'Geovanni',
                'lastname' => 'Mercz',
                'age' => 32,
                'gender' => 'male',
                'email' => 'geovanni.mercz@example.com',
                'address' => 'Av. Principal 123',
                'phonenumber' => '524491018804',
                'dateofborn' => '1992-05-10',
                'password' => Hash::make('password123'),
                'google_token' => null,
                'google_refresh_token' => null,
                'google_token_expires_at' => null,
                'is_doctor' => true,
            ],
            [
                'role' => 'patient',
                'name' => 'Marco',
                'lastname' => 'Carvajal',
                'age' => 23,
                'gender' => 'male',
                'email' => 'marco.carvajal@example.com',
                'address' => 'Av. Bosques 123',
                'phonenumber' => '524494677080',
                'dateofborn' => '1992-05-10',
                'password' => Hash::make('password123'),
                'google_token' => null,
                'google_refresh_token' => null,
                'google_token_expires_at' => null,
                'is_doctor' => false, // <-- Aquí
            ]
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
