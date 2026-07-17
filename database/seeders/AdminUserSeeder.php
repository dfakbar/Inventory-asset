<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Super Admin ──
        $admin = User::updateOrCreate(
            ['email' => 'admin@company.com'],
            [
                'name'     => 'Super Admin',
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'role'     => UserRole::Admin->value,
            ]
        );
        // Assign Spatie role → admin role punya semua permission (via PermissionSeeder)
        $admin->syncRoles([UserRole::Admin->value]);

        // ── Staff (contoh, permission minimal) ──
        $staff = User::updateOrCreate(
            ['email' => 'staff@company.com'],
            [
                'name'     => 'Staff Operasional',
                'username' => 'staff',
                'password' => Hash::make('password123'),
                'role'     => UserRole::Staff->value,
            ]
        );
        $staff->syncRoles([UserRole::Staff->value]);
        // Berikan permission view-only sebagai default untuk staff contoh
        $staff->syncPermissions(['asset.viewAny']);
    }
}
