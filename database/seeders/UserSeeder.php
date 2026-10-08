<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@kbtech.com'],
            [
                'code' => 'NV-ADMIN',
                'name' => 'Admin KBTech',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'phone' => '0988.888.999',
                'department' => 'Executive',
                'status' => 'Đang làm việc',
                'base_salary' => 25000000,
                'branch' => 'Công ty chính (Hà Nội)',
            ]
        );

        // Gán vai trò Manager với đầy đủ quyền cho admin
        $managerRole = Role::where('slug', 'Manager')->first();
        if ($managerRole) {
            $admin->assignRoleWithPermissions($managerRole);
        }
    }
}
