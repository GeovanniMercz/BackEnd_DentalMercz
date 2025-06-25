<?php

namespace Database\Seeders;

use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                User::create([
                    'role' => 'doctor',
                    'name' => 'Geovanni',
                    'lastname' => 'Mercz',
                    'age' => 32,
                    'gender' => 'male',
                    'email' => 'geovanni.mercz@example.com',
                    'address' => 'Av. Principal 123',
                    'phonenumber' => '5551234567',
                    'dateofborn' => '1992-05-10',
                    'password' => Hash::make('password123'),
                ])
        ]];

        foreach ($users as $user) {
            if (! User::withTrashed()->where('id', $user['id'])->exists()) {
                User::create($user);
            }
        }
    }
}
