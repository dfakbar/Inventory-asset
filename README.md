# 📦 Sistem Informasi Manajemen Aset — AssetMS

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Database-SQLite%20%2F%20MySQL-blue?logo=mysql&logoColor=white" alt="Database">
  <img src="https://img.shields.io/badge/License-MIT-green" alt="License">
  <img src="https://img.shields.io/badge/Built%20With-AI%20Assisted-blueviolet" alt="AI Assisted">
</p>

> Aplikasi web manajemen inventaris aset perusahaan yang dibangun dengan **Laravel 12**, dilengkapi sistem otorisasi berbasis peran (*Role-Based Access Control*), pelacakan mutasi aset, dan dashboard analitik interaktif.

---

## ✨ Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| 🏠 **Dashboard Analitik** | Grafik distribusi status, kategori aset, trend mutasi 6 bulan, dan log mutasi real-time |
| 📋 **Manajemen Aset** | CRUD aset lengkap dengan kode unik otomatis berbasis kategori & tanggal |
| 🔁 **Mutasi Aset** | Pencatatan perpindahan aset antar lokasi/pengguna dengan tanggal aktual |
| 📍 **Manajemen Lokasi** | Pengelolaan lokasi/ruangan tempat penyimpanan aset |
| 👥 **Manajemen User** | Super Admin dapat mengatur hak akses (privilege) setiap staff |
| 🔐 **RBAC Granular** | Permission berbasis Spatie: `asset.manage_finances`, `asset.mutate`, dll. |
| 💰 **Privasi Finansial** | Data harga & tanggal beli hanya tampil untuk user dengan izin finansial |
| 📊 **Chart.js** | Visualisasi data interaktif dengan animasi halus |

---

## 🔐 Sistem Hak Akses

Sistem menggunakan **Spatie Laravel Permission** dengan dua role utama:

### Role: `admin` (Super Admin)
- Akses penuh ke seluruh sistem
- Dapat mengatur permission setiap staff
- Dapat melihat data finansial (harga, tanggal pembelian)

### Role: `staff`
Permission staff dikelola **secara individual** oleh Super Admin:

| Permission | Akses Yang Diberikan |
|---|---|
| `asset.viewAny` | Melihat daftar aset |
| `asset.view` | Melihat detail aset |
| `asset.create` | Membuat aset baru |
| `asset.edit` | Mengedit data aset |
| `asset.delete` | Menghapus aset |
| `asset.manage_finances` | Input/lihat harga & tanggal beli |
| `asset.mutate` | Melakukan mutasi/perpindahan aset |
| `location.viewAny` | Mengelola lokasi |

---

## 📁 Struktur Proyek

```
inventory-aset/
├── app/
│   ├── Enums/
│   │   └── AssetStatus.php       # Status aset: InUse, Spare, Service, Broken, dll
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php  # Logika dashboard & agregasi data
│   │   │   ├── AssetController.php      # CRUD aset + filter permission
│   │   │   └── UserController.php       # Manajemen user & permission
│   │   └── Requests/
│   │       ├── StoreAssetRequest.php
│   │       └── UpdateAssetRequest.php   # Validasi kondisional (mutation-only staff)
│   ├── Models/
│   │   ├── Asset.php
│   │   ├── AssetMutationLog.php   # Riwayat mutasi aset
│   │   ├── Location.php
│   │   └── User.php
│   ├── Observers/
│   │   └── AssetObserver.php     # Auto-generate kode + catat log mutasi
│   └── Services/
│       └── AssetCodeGenerator.php
├── database/
│   ├── migrations/
│   │   └── ..._create_asset_mutation_logs_table.php
│   └── seeders/
│       └── PermissionSeeder.php
├── resources/views/
│   ├── dashboard.blade.php        # Dashboard utama dengan Chart.js
│   ├── assets/
│   │   ├── index.blade.php
│   │   ├── show.blade.php
│   │   └── _form.blade.php
│   └── layouts/app.blade.php
└── tests/Feature/
    └── AssetMutationAndPrivacyTest.php  # 9 test cases
```

---

## 🚀 Cara Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd inventory-aset
```

### 2. Install Dependensi
```bash
composer install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

Konfigurasi database di `.env`:
```env
DB_CONNECTION=sqlite
# atau untuk MySQL:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_aset
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi & Seed Database
```bash
php artisan migrate
php artisan db:seed
```

Untuk menjalankan seeder permission saja:
```bash
php artisan db:seed --class=PermissionSeeder
```

### 5. Jalankan Server
```bash
php artisan serve
```

Akses di browser: `http://localhost:8000`

---

## 👤 Akun Default (Seeder)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@company.com | password123 |
| Staff | staff@company.com | password123 |

> ⚠️ Ganti password setelah login pertama di lingkungan produksi!

---

## 📊 Dashboard

Dashboard menampilkan informasi berikut secara real-time:

- **6 Kartu Metrik Utama**: Total Aset, Digunakan, Cadangan, Servis, Bermasalah, dan Nilai Total Aset (khusus user dengan izin finansial)
- **Doughnut Chart**: Distribusi status aset dengan animasi interaktif
- **Bar Chart**: Jumlah aset per kategori (maksimal 8 kategori teratas)
- **Line Chart**: Trend mutasi aset 6 bulan terakhir
- **Log Mutasi Terbaru**: 10 aktivitas perpindahan aset terkini
- **Tabel Aset Terbaru**: 5 aset yang baru ditambahkan

---

## 🧪 Testing

Jalankan test suite:
```bash
php artisan test
```

Atau test spesifik untuk fitur mutasi & privasi:
```bash
php artisan test tests/Feature/AssetMutationAndPrivacyTest.php
```

Test cases mencakup:
- ✅ Staff tanpa permission finansial tidak dapat melihat data harga
- ✅ Staff mutation-only hanya dapat mengubah lokasi/status/PIC
- ✅ Super Admin dapat mengakses semua data termasuk finansial
- ✅ Log mutasi tercatat otomatis saat aset dipindahkan

---

## 🛠️ Tech Stack

- **Framework**: Laravel 12.x (PHP 8.2+)
- **Authentication**: Laravel Breeze / Session
- **Authorization**: Spatie Laravel Permission
- **Database**: SQLite (dev) / MySQL (production)
- **Frontend**: Bootstrap 5.3, Bootstrap Icons
- **Charts**: Chart.js 4.4
- **Testing**: PHPUnit (Laravel Feature Test)

---

## 📝 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

<p align="center">
  Dibuat dengan ❤️ dan 🤖 AI — AssetMS v1.0.0 &copy; {{ date('Y') }}
</p>
