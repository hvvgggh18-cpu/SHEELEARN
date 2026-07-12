<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'dasinagee2@gmail.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('Admin@12345'),
                'is_super_admin' => true,
                'status' => 'active',
            ]
        );
    }
}
