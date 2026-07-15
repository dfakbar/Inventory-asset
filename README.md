# Sistem Informasi Manajemen Aset — AssetMS

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Database-SQLite%20%2F%20MySQL-blue?logo=mysql&logoColor=white" alt="Database">
  <img src="https://img.shields.io/badge/Tests-104%20passing-brightgreen" alt="Tests">
  <img src="https://img.shields.io/badge/License-MIT-green" alt="License">
</p>

> Aplikasi web manajemen inventaris aset perusahaan berbasis **Laravel 12**, dilengkapi RBAC granular, pelacakan mutasi, notifikasi email, dashboard analitik, QR/Barcode, dan REST API.

---

## Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| Dashboard Analitik | Grafik status (doughnut), kategori (bar), trend mutasi 6 bulan, log real-time |
| Manajemen Aset | CRUD dengan kode unik otomatis `AST{ABR}{YY}{MM}{SEQ}` |
| Mutasi Aset | Catat perpindahan lokasi/user/status/karyawan dengan tanggal aktual |
| RBAC Granular | 26 permission, 2 role (admin/staff), privasi data finansial |
| Manajemen Karyawan | CRUD data karyawan non-system untuk penugasan aset |
| QR Code & Barcode | Generate & print label aset (SVG QR + Code 128 PNG) |
| Laporan PDF | Download laporan aset dan kategori (dompdf, landscape A4) |
| CSV Import/Export | Export dengan chunk(200), import dengan validasi per-cell |
| Check-In/Out | Catat peminjaman aset ke pihak luar, soft-deletes |
| Notifikasi Email | Dikirim via queue saat aset ditugaskan ke user |
| REST API | Endpoint `/api/assets` & `/api/assets/{id}` dengan pagination |
| Activity Log | Auto-log semua create/update/delete via `LogsActivity` trait |
| Error Monitoring | Terintegrasi Sentry untuk tracking error real-time |
| Security Hardening | SRI, HSTS, CSP headers, rate limiting, encrypted sessions |

---

## Sistem Hak Akses

RBAC menggunakan **Spatie Laravel Permission** dengan 2 role:

### Admin
Akses penuh ke seluruh sistem, termasuk data finansial & manajemen user.

### Staff
Permission dikelola individual oleh Admin:

| Permission | Akses |
|------------|-------|
| `asset.viewAny` | Lihat daftar & detail aset |
| `asset.create` | Tambah aset baru |
| `asset.edit` | Edit data aset |
| `asset.delete` | Hapus aset |
| `asset.manage_finances` | Lihat/input harga & tanggal beli |
| `asset.mutate` | Mutasi (lokasi/status/karyawan/catatan) |
| `location.*`, `category.*`, `brand.*`, `vendor.*` | CRUD masing-masing master data |
| `employee.*` | CRUD data karyawan non-system |
| `loan.*` | Check-in/out peminjaman |
| `report.viewAny` | Akses laporan PDF |

---

## Struktur Proyek

```
inventory-aset/
├── app/
│   ├── Enums/
│   │   ├── AssetStatus.php       # Spare, InUse, Service, Broken, Disposed
│   │   └── UserRole.php          # Admin, Staff
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/AssetController.php   # REST API
│   │   │   ├── Auth/                     # Login, reset password
│   │   │   ├── AssetController.php       # CRUD aset + CSV + QR/Barcode
│   │   │   ├── LoanController.php        # Check-in/out
│   │   │   ├── UserController.php        # Manajemen user & permission
│   │   │   ├── ReportController.php      # PDF reports
│   │   │   ├── CategoryController.php
│   │   │   ├── BrandController.php
│   │   │   ├── VendorController.php
│   │   │   ├── EmployeeController.php  # CRUD karyawan
│   │   │   ├── LocationController.php
│   │   │   └── DashboardController.php
│   │   ├── Middleware/CheckAdmin.php
│   │   └── Requests/              # 16 FormRequest dengan validasi
│   ├── Models/
│   │   ├── Asset.php              # SoftDeletes, search scope
│   │   ├── AssetLoan.php          # SoftDeletes
│   │   ├── AssetCategory.php
│   │   ├── AssetMutationLog.php   # Riwayat mutasi
│   │   ├── ActivityLog.php        # Activity logging
│   │   ├── Employee.php           # Karyawan non-system (soft-deletes)
│   │   ├── Brand.php, Vendor.php, Location.php, User.php
│   ├── Observers/
│   │   └── AssetObserver.php      # Auto-generate kode + log mutasi + email notif
│   ├── Services/
│   │   └── AssetCodeGenerator.php # Format: AST{ABR}{YY}{MM}{SEQ}
│   ├── Traits/
│   │   └── LogsActivity.php       # Auto-log create/update/delete
│   └── Notifications/
│       └── AssetAssignedNotification.php  # Queueable mail
├── config/
│   ├── cors.php                   # Restrictive CORS
│   ├── permission.php             # Spatie config
│   └── session.php                # Encrypted, HTTP-only, SameSite=Lax
├── database/
│   ├── migrations/                # 23 migrations
│   └── seeders/
│       ├── PermissionSeeder.php   # 26 permissions + 2 roles
│       └── AdminUserSeeder.php
├── routes/
│   ├── web.php                    # 45+ web routes
│   ├── api.php                    # REST API routes
│   └── auth.php                   # Auth routes
├── tests/
│   ├── Unit/                      # 8 unit tests
│   └── Feature/                   # 96 feature tests (104 total)
└── AGENTS.md                      # Panduan maintenance
```

---

## Cara Instalasi

```bash
# 1. Clone & masuk direktori
git clone <repo-url>
cd inventory-aset

# 2. Install PHP dependencies
composer install

# 3. Setup environment
copy .env.example .env
php artisan key:generate

# 4. Konfigurasi database di .env (SQLite default / MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventoryasset_kbn
DB_USERNAME=root
DB_PASSWORD=isi_password_kuat

# 5. Migrasi & seed
php artisan migrate
php artisan db:seed

# 6. Jalankan server
php artisan serve
# atau: composer run dev
```

Akses di `http://localhost:8000`

---

## Checklist Sebelum Go-Live

### Critical (wajib sebelum deploy ke production)

| Item | Keterangan |
|------|-----------|
| `DB_PASSWORD` | **Jangan kosong** — isi password kuat untuk user MySQL di `.env` |
| `APP_ENV=production` | Ubah dari `local` ke `production` di `.env` |
| `APP_URL` | Set ke domain production (misal `https://aset.perusahaan.com`) |

### Important (sangat disarankan)

| Item | Keterangan |
|------|-----------|
| `MAIL_MAILER` | Konfigurasi SMTP/ Mailgun agar notifikasi email berfungsi |
| `SENTRY_DSN` | Isi DSN dari [sentry.io](https://sentry.io) untuk monitoring error |
| `SESSION_SECURE_COOKIE=true` | Pastikan sudah aktif dan server menggunakan HTTPS |

### Recommended (optimalisasi production)

```bash
# 1. Regenerate APP_KEY khusus untuk production
php artisan key:generate

# 2. Install tanpa dev dependencies
composer install --optimize-autoloader --no-dev

# 3. Cache semuanya (config, route, view)
composer run cache

# 4. Setup queue worker untuk notifikasi email
php artisan queue:work

# 5. Setup scheduled task (cron) untuk scheduler Laravel
# Tambahkan ke crontab: * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1

# 6. Storage link untuk file upload
php artisan storage:link
```

---

## Akun Default (Seeder)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@company.com | password123 |
| Staff | staff@company.com | password123 |

> Ganti password setelah login pertama!

---

## Commands Penting

| Command | Fungsi |
|---------|--------|
| `composer run dev` | Jalankan dev server |
| `composer run dev:queue` | Jalankan queue worker |
| `composer run dev:logs` | Monitor log real-time |
| `composer run cache` | Cache view + config + routes |
| `composer run test` | Jalankan semua test (104 test) |
| `php artisan key:generate` | Regenerate APP_KEY |
| `composer install --no-dev` | Install tanpa dev dependencies |
| `php artisan migrate` | Jalankan migration |
| `php artisan migrate:fresh --seed` | Reset DB + seed ulang |

---

## REST API

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/assets` | List aset (paginate 50, filter: search, status, category_id) |
| GET | `/api/assets/{id}` | Detail aset dengan relasi |

Response JSON dengan struktur pagination Laravel standar.

---

## Tech Stack

- **Backend**: Laravel 12.x, PHP 8.2+
- **Database**: SQLite (dev) / MySQL (production)
- **Auth**: Session-based, Bcrypt rounds=12, encrypted sessions
- **RBAC**: Spatie Laravel Permission v6
- **Frontend**: Bootstrap 5.3.3 (SRI + crossorigin), Bootstrap Icons, Chart.js 4.4
- **Error Tracking**: Sentry (sentry/sentry-laravel)
- **Security Headers**: HSTS, X-Frame-Options, X-Content-Type-Options via `.htaccess`
- **PDF**: barryvdh/laravel-dompdf
- **QR**: bacon/bacon-qr-code (SVG)
- **Barcode**: picqer/php-barcode-generator (Code 128 PNG)
- **Queue**: Database driver
- **Testing**: PHPUnit 11, 104 test cases

---

## Lisensi

MIT License — AssetMS v1.0.0
