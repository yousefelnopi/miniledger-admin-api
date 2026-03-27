<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str; // لتوليد التوكن العشوائي

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ================= Admin 1 =================
        User::firstOrCreate(
            ['email' => 'yousefelnopi2@gmail.com'],
            [
                'name' => 'Yousef Elnopi',
                'password' => 'yousef#146',
                'role' => User::ROLE_ADMIN,
                'api_token' => Str::random(60),
                'is_active' => true,
                'last_seen_at' => now(),
            ]
        );

        // ================= Admin 2 =================
        User::firstOrCreate(
            ['email' => 'yousefelnopi22@gmail.com'],
            [
                'name' => 'ALI Elnopi',
                'password' => 'yousef#146',
                'role' => User::ROLE_ADMIN,
                'api_token' => Str::random(60),
                'is_active' => true,
                'last_seen_at' => now(),
            ]
        );

        // ================= Cashier =================
        User::firstOrCreate(
            ['email' => 'yousefelnopi48@gmail.com'],
            [
                'name' => 'Elnopi Cashier',
                'password' => 'yousef#146',
                'role' => User::ROLE_CASHIER,
                'api_token' => Str::random(60),
                'is_active' => true,
                'last_seen_at' => now(),
            ]
        );
    }
}
