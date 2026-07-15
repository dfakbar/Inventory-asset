<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Definisi lengkap semua permission sistem.
     * Format: 'resource.action' → 'Label Indonesia'
     *
     * @var array<string, array<string, string>>
     */
    public const GROUPS = [
        'Manajemen Aset' => [
            'asset.viewAny'         => 'Lihat Daftar & Detail Aset',
            'asset.create'          => 'Tambah Aset Baru',
            'asset.edit'            => 'Edit Data Aset',
            'asset.delete'          => 'Hapus Aset',
            'asset.manage_finances' => 'Input/Edit Keuangan (Tanggal & Harga Pembelian)',
            'asset.mutate'          => 'Lakukan Mutasi & Perpindahan Aset',
        ],
        'Manajemen Lokasi' => [
            'location.viewAny' => 'Lihat Daftar Lokasi',
            'location.create'  => 'Tambah Lokasi Baru',
            'location.edit'    => 'Edit Data Lokasi',
            'location.delete'  => 'Hapus Lokasi',
        ],
        'Manajemen Kategori' => [
            'category.viewAny' => 'Lihat Daftar Kategori',
            'category.create'  => 'Tambah Kategori Baru',
            'category.edit'    => 'Edit Data Kategori',
            'category.delete'  => 'Hapus Kategori',
        ],
        'Manajemen Merek' => [
            'brand.viewAny' => 'Lihat Daftar Merek',
            'brand.create'  => 'Tambah Merek Baru',
            'brand.edit'    => 'Edit Data Merek',
            'brand.delete'  => 'Hapus Merek',
        ],
        'Manajemen Vendor' => [
            'vendor.viewAny' => 'Lihat Daftar Vendor',
            'vendor.create'  => 'Tambah Vendor Baru',
            'vendor.edit'    => 'Edit Data Vendor',
            'vendor.delete'  => 'Hapus Vendor',
        ],
        'Peminjaman Aset' => [
            'loan.viewAny'  => 'Lihat Daftar Peminjaman',
            'loan.create'   => 'Check-Out Aset',
            'loan.checkin'  => 'Check-In Aset',
            'loan.delete'   => 'Hapus Data Peminjaman',
        ],
        'Manajemen Pengguna (Karyawan)' => [
            'employee.viewAny' => 'Lihat Daftar Pengguna (Karyawan)',
            'employee.create'  => 'Tambah Pengguna Baru',
            'employee.edit'    => 'Edit Data Pengguna',
            'employee.delete'  => 'Hapus Pengguna',
        ],
        'Laporan' => [
            'report.viewAny' => 'Lihat & Cetak Laporan',
        ],
    ];

    public function run(): void
    {
        // Reset cached roles & permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat semua permission
        $allPerms = [];
        foreach (self::GROUPS as $perms) {
            foreach (array_keys($perms) as $permName) {
                $allPerms[] = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            }
        }

        // Buat roles
        $adminRole = Role::firstOrCreate(['name' => UserRole::Admin->value, 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => UserRole::Staff->value, 'guard_name' => 'web']);

        // Admin role mendapatkan SEMUA permission secara otomatis
        $adminRole->syncPermissions($allPerms);
    }
}
