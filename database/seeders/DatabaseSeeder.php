<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Region',
                'email' => 'region@qc.com',
                'password' => Hash::make('password123'),
                'role' => 'region',
                'branch_id' => null,
                'agency_id' => null,
                'is_active' => true,
                'created_by' => 'System Seeder',
            ],
            [
                'name' => 'Admin Branch',
                'email' => 'branch@qc.com',
                'password' => Hash::make('password123'),
                'role' => 'admin_branch',
                'branch_id' => null,
                'agency_id' => null,
                'is_active' => true,
                'created_by' => 'System Seeder',
            ],
            [
                'name' => 'User Agency',
                'email' => 'agency@qc.com',
                'password' => Hash::make('password123'),
                'role' => 'agency',
                'branch_id' => null,
                'agency_id' => null,
                'is_active' => true,
                'created_by' => 'System Seeder',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}