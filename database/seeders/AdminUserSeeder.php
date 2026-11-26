<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@dbpi.local'],
            [
                'name' => 'Administrador DBPI',
                'password' => Hash::make(env('DBPI_ADMIN_PASSWORD', 'password')),
                'user_type' => UserType::Administrador,
                'is_active' => true,
            ],
        );
    }
}