<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@imigrasi.go.id'],
            [
                'name' => 'Administrator Sistem SPRI',
                'password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD', 'Password123!')),
                'is_admin' => true,
                'role' => UserRole::ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'verifikator@imigrasi.go.id'],
            [
                'name' => 'Petugas Verifikator Lantaskim',
                'password' => Hash::make(env('DEFAULT_VERIFIKATOR_PASSWORD', 'Password123!')),
                'is_admin' => false,
                'role' => UserRole::VERIFIKATOR,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'pimpinan@imigrasi.go.id'],
            [
                'name' => 'Kepala Seksi Lantaskim (Pimpinan)',
                'password' => Hash::make(env('DEFAULT_PIMPINAN_PASSWORD', 'Password123!')),
                'is_admin' => false,
                'role' => UserRole::PIMPINAN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
