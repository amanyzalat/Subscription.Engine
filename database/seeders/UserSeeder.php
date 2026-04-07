<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'developer@developer.com'],
            [
                'name' => 'Admin',
                'email' => 'developer@developer.com',
                'password' => Hash::make('p0ssw0rd!@$'),
                'is_admin' => true,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
    }
}
