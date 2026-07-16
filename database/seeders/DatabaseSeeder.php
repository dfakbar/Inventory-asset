<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // URUTAN PENTING: Permission harus ada sebelum user di-assign role/permission
        $this->call([
            PermissionSeeder::class,    // 1. Buat roles & permissions
            AdminUserSeeder::class,     // 2. Buat user admin + staff
            AssetCategorySeeder::class,
            BrandSeeder::class,
            LocationSeeder::class,
            EmployeeSeeder::class,
        ]);
    }
}
