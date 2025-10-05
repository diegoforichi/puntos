<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!User::where('email', 'superadmin@puntos.local')->exists()) {
            User::create([
                'name' => 'SuperAdmin',
                'email' => 'superadmin@puntos.local',
                'password' => Hash::make('superadmin123'),
                'role' => User::ROLE_SUPERADMIN,
                'status' => User::STATUS_ACTIVE,
            ]);
        }
    }
}
