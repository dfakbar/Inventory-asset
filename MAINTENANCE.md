# Panduan Maintenance — AssetMS

## Daftar Isi
1. [Daily Operations](#1-daily-operations)
2. [Security Maintenance](#2-security-maintenance)
3. [Database Maintenance](#3-database-maintenance)
4. [Queue & Notifications](#4-queue--notifications)
5. [Backup & Recovery](#5-backup--recovery)
6. [Troubleshooting](#6-troubleshooting)
7. [Deployment Checklist](#7-deployment-checklist)
8. [Adding New Features](#8-adding-new-features)
9. [Performance Tuning](#9-performance-tuning)

---

## 1. Daily Operations

### Server
```bash
# Jalankan dev server
composer run dev

# Jalankan queue worker (untuk email notifikasi)
composer run dev:queue

# Monitor logs real-time
composer run dev:logs
```

### Caching (sebelum deploy)
```bash
composer run cache
```
Menjalankan: `php artisan view:cache`, `config:cache`, `route:cache`

> **Penting**: Jalankan `php artisan optimize:clear` SEBELUM running tests, karena cached config mengganggu environment test.

### Reset & Seed Ulang
```bash
php artisan migrate:fresh --seed
```

---

## 2. Security Maintenance

### Checklist Bulanan

- [ ] **Update dependencies**: `composer update` + `composer audit`
- [ ] **Review logs**: Cek `storage/logs/laravel.log` untuk aktivitas mencurigakan
- [ ] **Check user accounts**: Pastikan tidak ada akun tidak dikenal di `admin/users`
- [ ] **Verify permissions**: Review staff permissions via UI admin
- [ ] **Check DB password**: `.env` `DB_PASSWORD` — jangan kosong di production
- [ ] **Verify HTTPS**: Pastikan `SESSION_SECURE_COOKIE=true` dan site via HTTPS

### Jika Terjadi Insiden Keamanan

1. Matikan akses: `php artisan down --secret="your-secret"`
2. Cek log: `storage/logs/laravel.log` dan database `activity_logs` table
3. Rotate APP_KEY: `php artisan key:generate`
4. Reset password semua user
5. Investigasi dan patch

### Security Headers (rekomendasi)

Tambahkan di Nginx/Apache config:
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

---

## 3. Database Maintenance

### Migrations

Semua migration ada di `database/migrations/` dan dijalankan berurutan.

**Jika migration gagal:**
```bash
# Cek status
php artisan migrate:status

# Rollback batch terakhir
php artisan migrate:rollback

# Rollback ke migration tertentu
php artisan migrate:rollback --step=3
```

### Menambah Migration Baru

```bash
php artisan make:migration add_column_to_assets_table
```

Ikuti konvensi penamaan: `YYYY_MM_DD_HHMMSS_deskripsi.php`

### Index yang Ada

| Table | Indexes |
|-------|---------|
| `assets` | `status`, `asset_category_id`, composite `(category_id, status)`, `purchase_date`, `location_id`, `vendor_id`, `brand_id`, `assigned_to`, `employee_id`, `mac_address` |
| `asset_loans` | `loan_date`, `returned_at`, `asset_id`, `created_by` |
| `asset_mutation_logs` | `asset_id`, `performed_by`, `mutation_date`, `from_employee_id`, `to_employee_id` |
| `activity_logs` | `(model_type, model_id)`, `action`, `created_at` |
| `asset_mutation_logs` | `asset_id`, `performed_by`, `mutation_date`, `from_employee_id`, `to_employee_id` |
| `employees` | `email` (unique), `department` |

### Soft Deletes

Model dengan soft deletes: `Asset`, `User`, `AssetLoan`, `Employee`

Query termasuk yang dihapus:
```php
Asset::withTrashed()->get();           // semua termasuk soft-deleted
Asset::onlyTrashed()->get();           // hanya yang soft-deleted
$asset->restore();                     // restore soft-deleted
```

---

## 4. Queue & Notifications

### Email Notifications

Notifikasi email dikirim via **queue** ketika aset ditugaskan ke user (`assigned_to` berubah).

**Aktifkan dengan:**
1. Set `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=your@email.com
```
2. Jalankan queue worker: `php artisan queue:work`
3. Untuk production: gunakan Supervisor untuk menjaga queue worker tetap hidup.

### Supervisor Config (Linux)

```ini
[program:assetms-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=1 --timeout=0
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/queue.log
```

### Failed Jobs

```bash
# Lihat failed jobs
php artisan queue:failed

# Retry semua failed jobs
php artisan queue:retry all

# Hapus semua failed jobs
php artisan queue:flush
```

---

## 5. Backup & Recovery

### Database Backup

```bash
# MySQL
mysqldump -u root -p inventoryasset_kbn > backup_$(date +%Y%m%d).sql

# SQLite
cp database/database.sqlite backup_$(date +%Y%m%d).sqlite
```

### File Backup

```bash
# Public storage (uploaded images)
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public/

# .env
cp .env .env.backup_$(date +%Y%m%d)
```

### Recovery

```bash
# 1. Restore database
mysql -u root -p inventoryasset_kbn < backup_20260715.sql

# 2. Restore files
tar -xzf storage_backup_20260715.tar.gz

# 3. Clear cache
php artisan optimize:clear

# 4. Regenerate cache
php artisan optimize
```

---

## 6. Troubleshooting

### Error: "Target class [controller] does not exist"

**Cause**: Route cache outdated.
**Fix**: `php artisan route:clear`

### Error: "No application encryption key"

**Cause**: APP_KEY not set.
**Fix**: `php artisan key:generate`

### Error: "Base table or view not found"

**Cause**: Migration belum dijalankan.
**Fix**: `php artisan migrate`

### Error: 419 Page Expired

**Cause**: CSRF token mismatch — session expired.
**Fix**: Refresh halaman. Jika terus terjadi, cek `SESSION_DRIVER` dan `SESSION_LIFETIME` di `.env`.

### Error: "Class 'App\Seeders\PermissionSeeder' not found" di StoreUserRequest

**Fix**: Hapus import yang tidak digunakan dari `app/Http/Requests/StoreUserRequest.php`.

### Error: Call to undefined method `paginate()` / `withCount()`

**Cause**: Query sudah dieksekusi sebelum paginate dipanggil.
**Fix**: Pastikan `paginate()` atau `withCount()` dipanggil pada Builder, bukan Collection.

### Email Tidak Terkirim

1. Cek `.env`: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, dll.
2. Pastikan queue worker jalan: `php artisan queue:work`
3. Cek failed jobs: `php artisan queue:failed`
4. Cek log: `storage/logs/laravel.log`

### CSV Import Gagal

1. Pastikan file CSV menggunakan separator koma (`,`)
2. Header harus sesuai (14 kolom): `Kode Aset,Nama,Kategori,Merek,Model,Serial Number,MAC Address,Lokasi,Vendor,Status,Tanggal Pembelian,Harga Pembelian,Jumlah,Catatan`
3. File maksimal 2MB
4. Gunakan tombol **Template** di halaman Reports untuk download template CSV + 1 baris contoh
5. Vendor akan auto-created jika belum ada di database (via `firstOrCreate`)
6. MAC Address divalidasi format `XX:XX:XX:XX:XX:XX` — baris tetap diimpor dengan MAC kosong jika format salah
7. Serial Number dicek unique — baris dengan SN duplikat dilewati
8. Error per-baris tidak menggagalkan seluruh batch (per-row transaction)
9. Cek log error di `storage/logs/laravel.log`

### PDF Report Tidak Muncul

1. Pastikan `barryvdh/laravel-dompdf` terinstal
2. Cek font: gunakan huruf Latin saja (dompdf tidak support semua Unicode)
3. Jika blank: cek PHP memory limit (min 128MB recommended)

### Sentry Not Sending Errors

1. Cek `.env`: `SENTRY_DSN` sudah diisi
2. Cek environment: Sentry di-set tidak mengirim di `local`/`testing` (lihat `AppServiceProvider::boot()` — DSN di-null kan untuk non-production)
3. Test koneksi: `php artisan sentry:test`
4. Cek log: `storage/logs/laravel.log`
5. Pastikan provider terdaftar di `bootstrap/providers.php`

### Employee Tidak Bisa Dihapus

**Cause**: Karyawan masih ditugaskan ke satu atau lebih aset.
**Fix**: Alihkan atau hapus aset yang masih menggunakan karyawan tersebut terlebih dahulu, lalu coba hapus lagi.

### Riwayat Mutasi Karyawan Tidak Tercatat

**Cause**: `AssetMutationLog::$fillable` tidak menyertakan `from_employee_id` / `to_employee_id`.
**Fix**: Tambahkan kedua field tersebut ke `$fillable` array di `app/Models/AssetMutationLog.php`.

### API Mengembalikan 401 Unauthorized

**Cause**: Endpoint API (`/api/assets`) sekarang dilindungi oleh middleware `auth:sanctum`.
**Fix**: Sertakan token Sanctum di header `Authorization: Bearer {token}`. Generate token via `php artisan sanctum:generate-token` atau via login endpoint.

### Check-In Aset Tidak Mengembalikan PIC

**Cause**: Sebelumnya `assigned_to` tidak di-reset saat check-in.
**Fix**: `LoanController::checkin()` sekarang mengembalikan `assigned_to` ke user yang melakukan check-in (`auth()->id()`).

### PHP Warning: "Attempt to read property on null" di Form Aset

**Cause**: Halaman create aset mengakses `$asset->status->value` saat `$asset` bernilai null.
**Fix**: Gunakan null-safe operator: `$asset?->status?->value ?? 'Spare'` di `resources/views/assets/_form.blade.php`.

### 500 Error Setelah Deploy

1. Cek storage writable: `chmod -R 775 storage/ bootstrap/cache/`
2. Clear cache: `php artisan optimize:clear`
3. Cek log: `storage/logs/laravel.log`
4. Cek Sentry dashboard jika sudah terkonfigurasi
5. Pastikan `APP_DEBUG=false` (jangan aktif di production)

---

## 7. Deployment Checklist

### Critical Checklist (wajib)

- [ ] `DB_PASSWORD` — **isi password kuat**, jangan kosong
- [ ] `APP_ENV=production` di `.env`
- [ ] `APP_DEBUG=false` di `.env`
- [ ] `APP_URL` sudah benar (domain production, pakai `https://`)

### Important Checklist (sangat disarankan)

- [ ] `SESSION_ENCRYPT=true` di `.env`
- [ ] `SESSION_SECURE_COOKIE=true` di `.env` (pastikan HTTPS aktif)
- [ ] `MAIL_MAILER` dikonfigurasi untuk production (SMTP/Mailgun)
- [ ] `SENTRY_DSN` diisi dengan DSN dari Sentry project
- [ ] `QUEUE_CONNECTION=database` (sudah default)
- [ ] `CACHE_STORE` diatur (file/redis untuk production)

### Pre-Deployment Steps

- [ ] Jalankan: `php artisan key:generate` (APP_KEY unik untuk production)
- [ ] Jalankan: `composer install --optimize-autoloader --no-dev`
- [ ] Jalankan: `php artisan migrate --force`
- [ ] Jalankan: `composer run cache` (cache config + route + view)
- [ ] Storage link: `php artisan storage:link`
- [ ] CORS config: `config/cors.php` — `allowed_origins` hanya domain sendiri
- [ ] Test Sentry: `php artisan sentry:test` (setelah DSN diisi)
- [ ] Setup queue worker: `php artisan queue:work` (atau Supervisor)

### After Deployment

- [ ] Akses halaman utama → 200 OK
- [ ] Login sebagai admin → berhasil
- [ ] Cek dashboard → data tampil
- [ ] Cek satu workflow CRUD aset
- [ ] Cek queue worker jalan
- [ ] Monitor logs: `storage/logs/laravel.log`
- [ ] Cek Sentry dashboard untuk error pertama
- [ ] Verifikasi security headers via browser devtools atau curl
- [ ] Setup backup cron job

### Server Requirements

- PHP 8.2+ dengan extensions: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `PDO`, `pdo_mysql`/`pdo_sqlite`, `tokenizer`, `xml`, `gd` (optional untuk barcode)
- Web server: Nginx / Apache
- Database: MySQL 8+ / MariaDB 10+ / SQLite
- Composer 2.x
- Queue worker (Supervisor untuk production)

---

## 8. Adding New Features

### Menambah Model Baru

```bash
# 1. Buat model dengan migration & factory
php artisan make:model NewModel -mf

# 2. Tambahkan fillable & casts
protected $fillable = ['name', 'description'];
protected function casts(): array { return [...]; }

# 3. Tambahkan relasi
public function assets(): HasMany { ... }

# 4. Daftarkan observer di AppServiceProvider jika perlu

# 5. Buat FormRequest untuk validasi
php artisan make:request StoreNewModelRequest

# 6. Buat Controller
php artisan make:controller NewModelController --resource

# 7. Tambahkan routes
Route::resource('admin/new-models', NewModelController::class);
```

### Menambah Permission Baru

```php
// 1. Tambahkan ke PermissionSeeder.php
public const GROUPS = [
    'new-feature' => ['new.viewAny', 'new.create', 'new.edit', 'new.delete'],
];

// 2. Seed ulang
php artisan db:seed --class=PermissionSeeder

// 3. Assign ke admin role di PermissionSeeder
$admin->givePermissionTo(['new.viewAny', 'new.create', 'new.edit', 'new.delete']);
```

### Menambah Endpoint API

```php
// 1. Tambahkan di routes/api.php (di dalam grup middleware auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/new-models', [NewModelController::class, 'index']);
});

// 2. Buat controller method
public function index(): JsonResponse
{
    return response()->json(NewModel::paginate(50));
}
```

> Semua endpoint API **harus** dilindungi dengan middleware `auth:sanctum`.

### Menambah Activity Log

```php
// Pada model, gunakan trait:
use App\Traits\LogsActivity;
```

Auto-log create/update/delete ke tabel `activity_logs`.

---

## 9. Performance Tuning

### Query Optimization

| Masalah | Solusi |
|---------|--------|
| N+1 queries | Tambahkan `with()` eager loading |
| Slow pagination | Pastikan index ada di kolom WHERE/ORDER BY |
| Large dataset | Gunakan `chunk()` untuk batch processing |
| Dashboard lambat | Cache agregasi (sudah di-cache 5 menit via `remember()`) |

### Caching Strategy

- **View caching**: `php artisan view:cache` — compile Blade ke plain PHP
- **Config caching**: `php artisan config:cache` — merge semua config
- **Route caching**: `php artisan route:cache` — compile route registrations
- **Permissions caching**: Otomatis oleh Spatie (cache 24 jam, reset saat permission diubah)

Untuk production:
```bash
# Cache semuanya
composer run cache
```

### Memori & OOM Protection

| Fitur | Mekanisme |
|-------|-----------|
| CSV Export | `chunk(200)` — stream rows tanpa load semua record |
| PDF Reports | `chunk(200)` — build HTML string, hindari full collection |
| Import CSV | Diproses per baris dengan per-row transaction (error 1 baris tidak menggagalkan batch) |
| Dashboard | Cache agregasi 5 menit (jika menggunakan `remember()`) |

### Monitoring

```bash
# Cek performa query lambat
php artisan pail --timeout=0

# Cek log error
tail -f storage/logs/laravel.log

# Cek queue
php artisan queue:status

# Cek failed jobs
php artisan queue:failed

# Test koneksi Sentry
php artisan sentry:test
```

### Sentry Error Tracking

Sentry terintegrasi untuk menangkap error & exception secara real-time:
- **DSN**: Set `SENTRY_DSN` di `.env` (dapatkan dari [sentry.io](https://sentry.io))
- **Sample Rate**: 100% error (`SENTRY_SAMPLE_RATE=1.0`), 25% performance traces (`SENTRY_TRACES_SAMPLE_RATE=0.25`)
- **Environment**: Otomatis mengikuti `APP_ENV`, tidak mengirim error dari `local`/`testing` (DSN dinonaktifkan di `AppServiceProvider::boot()`)
- **Tracing**: Merekam query SQL, view rendering, queue jobs, HTTP client, dan cache operations
- **PII**: Dimatikan secara default (`send_default_pii: false`)
- **Config Cache**: `config/sentry.php` sudah bebas closure (compatible dengan `config:cache`)

---

## Reference: Key Files & Locations

| Komponen | Path |
|----------|------|
| Routes | `routes/web.php`, `routes/api.php`, `routes/auth.php` |
| Controllers | `app/Http/Controllers/` |
| Models | `app/Models/` |
| Middleware | `app/Http/Middleware/CheckAdmin.php` |
| Form Requests | `app/Http/Requests/` (StoreEmployeeRequest, UpdateEmployeeRequest) |
| Observers | `app/Observers/AssetObserver.php` |
| Blade Views | `resources/views/` |
| Config | `config/` |
| Migrations | `database/migrations/` |
| Seeders | `database/seeders/` |
| Tests | `tests/Feature/`, `tests/Unit/` |
| Employee Controller | `app/Http/Controllers/EmployeeController.php` |
| Employee Model | `app/Models/Employee.php` |
| Employee Views | `resources/views/admin/employees/` |
| Log Controller | `app/Http/Controllers/LogController.php` |
| Log Views | `resources/views/admin/logs/` |
| MAC Address | Migration `2026_07_16_090000_add_mac_address_to_assets_table.php` — kolom di `assets` table |
| Disable User | Migration `2026_07_16_100000_add_is_active_to_users_table.php` — toggle via `admin.users.toggle-active` route |
| Disable Employee | Migration `2026_07_16_100001_add_is_active_to_employees_table.php` — toggle via `admin.employees.toggle-active` route |
| CSV Import | `AssetController::importCsv()` — per-row transaction, validasi vendor/MAC/SN |
| CSV Template | `GET /assets/import/template` — `AssetController::exportCsvTemplate()` — download template 14 kolom |
| AGENTS.md | Panduan development & agent AI |
| MAINTENANCE.md | Dokumentasi ini |

---

*Terakhir diperbarui: Juli 2026 — AssetMS v1.0.0*
