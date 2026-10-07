<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'username' => 'admin',
                'first_name' => 'Admin',
                'last_name' => 'AMCortex',
                'name' => 'Admin AMCortex',
                'password' => \Illuminate\Support\Facades\Hash::make('Admin@123'),
                'role' => 'admin',
                'is_approved' => true,
            ]
        );
    }
}
