# Panduan Maintenance — AssetMS

> Panduan instalasi & deploy (Linux/Windows/LAN/mkcert/Docker) ada di **[README.md](README.md)**.  
> Dokumen ini fokus pada **operasional harian setelah aplikasi berjalan**.

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
10. [Scheduler (`logs:purge`)](#10-scheduler-logspurge)
11. [Health Check & Monitoring](#11-health-check--monitoring)
12. [Rollback](#12-rollback)
13. [Operasi Docker](#13-operasi-docker)

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

### Health check (sebelum & sesudah perubahan)
```bash
curl -I <APP_URL>/up     # harus HTTP 200 — aplikasi hidup
```
Endpoint `/up` berguna untuk monitoring uptime (Uptime Kuma, load balancer, cron cek).

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
| `peripherals` | `category`, `brand`, `total_stock` |
| `peripheral_issuances` | `peripheral_id`, `peripheral_issuance_id`, `created_at` |
| `sop_documents` | `document_type`, `document_number` (unique) |

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

```bash
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl start assetms-queue:*
sudo supervisorctl status assetms-queue:*
```

### NSSM Config (Windows)

Supervisor tidak ada di Windows — pakai [NSSM](https://nssm.cc/):

```bat
nssm install AssetMSQueue "C:\php\php.exe" "C:\inventory-asset\artisan" queue:work --sleep=3 --tries=3 --max-time=3600
nssm set AssetMSQueue AppDirectory "C:\inventory-asset"
nssm set AssetMSQueue AppStdout "C:\inventory-asset\storage\logs\queue.log"
nssm set AssetMSQueue AppStderr "C:\inventory-asset\storage\logs\queue-err.log"
nssm start AssetMSQueue
nssm status AssetMSQueue
```

Kelola: `nssm restart|stop|remove AssetMSQueue`.

### Docker

Service `queue` di `docker-compose.yml` sudah menjalankan `queue:work` dengan `restart: unless-stopped`:

```bash
docker compose ps queue
docker compose logs -f queue
docker compose restart queue
```

### Windows alternatif tanpa NSSM (Task Scheduler)

```bat
schtasks /create /tn "AssetMS Queue" /tr "\"C:\php\php.exe\" C:\inventory-asset\artisan queue:work" /sc hourly /ru SYSTEM
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
# Public storage (uploaded images + arsip PDF dokumen SOP)
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public/

# .env
cp .env .env.backup_$(date +%Y%m%d)
```

Docker: `storage/app/public` di-bind ke host, tar di atas tetap berlaku. Data MySQL Docker berada di named volume `db-data`:

```bash
docker compose exec db sh -c 'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" $MYSQL_DATABASE' > db_docker_$(date +%Y%m%d).sql
```

### Rotasi & jadwal backup (cron)

```cron
# Backup DB tiap malam (sesuaikan user/password/path)
0 1 * * * mysqldump -u assetms -p'PASSWORD' inventaris_aset | gzip > /var/backups/inventaris_aset_$(date +\%Y\%m\%d).sql.gz
# Simpan 14 hari terakhir
0 2 * * * find /var/backups -name 'inventaris_aset_*.sql.gz' -mtime +14 -delete
```

Windows: Task Scheduler memanggil `mysqldump.exe` (folder `bin` MySQL) dengan argumen yang sama.

> **Aturan empat backup:** backup yang belum pernah diuji restore = bukan backup. Uji restore minimal sebulan sekali di DB sementara.

### Recovery

Urutan restore — **database dulu, lalu file**:

```bash
# 1. Restore database
mysql -u root -p inventaris_aset < backup_20260715.sql

# 2. Restore files (arsip PDF & upload)
tar -xzf storage_backup_20260715.tar.gz

# 3. Restore .env bila ikut berubah
#    ⚠️ APP_KEY harus KONSISTEN — jika berganti, data ter-encrypt lama jadi tak terbaca
cp .env.backup_20260715 .env

# 4. Clear + regenerate cache
php artisan optimize:clear
composer run cache

# 5. Verifikasi
curl -I http://localhost/up      # harus 200
php artisan migrate:status       # semua Ran
```

Docker: `docker compose stop app queue scheduler` → restore dump ke service `db` → `docker compose up -d`.

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

### Error: 429 Too Many Requests

**Cause**: Rate limiter (middleware `throttle`) kehabisan kuota. Semua `throttle:X,1` dengan decay sama berbagi **satu bucket counter per user/IP** — aktivitas normal (buka halaman, CRUD) bisa cepat menghabiskan kuota route sensitif (hapus dokumen, import CSV).
**Fix**: Route sudah dipisah per area dengan prefix (`throttle:300,1,assets`, `throttle:10,1,import`, `throttle:30,1,documents.destroy`, dst. — lihat `routes/web.php`). Jika masih muncul:
1. Tunggu ±1 menit (decay window) atau `php artisan cache:clear` — counter tersimpan di tabel cache karena `CACHE_STORE=database`
2. Pastikan server memakai route terbaru: `php artisan route:clear` lalu `composer run cache`
3. Hindari reload/refresh berulang dalam 1 menit

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
2. Header harus sesuai (15 kolom): `Kode Aset,Nama,Tipe Aset,Kategori,Merek,Model,Serial Number,MAC Address,Lokasi,Vendor,Status,Tanggal Pembelian,Harga Pembelian,Jumlah,Catatan`
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

### PDF Dokumen SOP Tidak Tergenerate

1. Cek folder `storage/app/public/documents/` writable (`chmod -R 775 storage/`)
2. Pastikan `php artisan storage:link` sudah dijalankan (PDF diakses via `/storage/documents/...`)
3. Cek log: `storage/logs/laravel.log` — `SopDocumentController::store()` menangkap exception dan rollback
4. Jika error **"The PHP GD extension is required, but is not installed."** → itu dari dompdf saat PDF memuat gambar raster (PNG/WebP). Kop surat dokumen kini **tanpa gambar logo** (teks saja), jadi error ini sudah tidak muncul lagi. Pastikan server memakai versi terbaru (`git pull` + `composer run cache`). Jika suatu saat menambah gambar ke PDF, aktifkan GD di server (mis. `sudo apt install php-gd` lalu restart PHP-FPM/Apache).
5. Jika dokumen lama tidak punya `pdf_path`, route `pdf` akan otomatis meng-generate ulang via `storePdf()`
6. Jika isi PDF tidak sesuai template terbaru (mis. perubahan Form Tanda Terima), generate ulang lewat route `print` atau hapus file lama di `storage/app/public/documents/`

### Lokasi Penempatan Salah di Dokumen SOP (Tanda Terima)

1. Lokasi tunggal diambil dari `data['location_id']` saat membuat dokumen
2. Jika kosong, fallback ke **lokasi aset pertama**, lalu **lokasi peripheral pertama**
3. Untuk memperbaiki: buat ulang dokumen dengan memilih lokasi yang benar di dropdown **Lokasi Penempatan** (opsional)

### Sentry Not Sending Errors

1. Cek `.env`: `SENTRY_DSN` sudah diisi
2. Cek environment: Sentry di-set tidak mengirim di `local`/`testing` (lihat `AppServiceProvider::boot()` — DSN di-null kan untuk non-production)
3. Test koneksi: `php artisan sentry:test`
4. Cek log: `storage/logs/laravel.log`
5. Pastikan provider terdaftar di `bootstrap/providers.php`

### Peripheral Stock Tidak Akurat

**Cause**: Race condition saat `issue()` — dua request bersamaan membaca `current_stock` yang sama sebelum ada yang mengurangi.
**Fix**: `PeripheralController::issue()` sekarang menggunakan `lockForUpdate()` di dalam transaksi database — pastikan tabel `peripherals` menggunakan engine InnoDB (row-level locking).

### Restock Tidak Tercatat dengan Prefix

**Cause**: Catatan restok tidak otomatis diprefiks `Restok:`.
**Fix**: `PeripheralController::restock()` sudah menambahkan prefix `Restok: ` secara otomatis di kolom `notes`.

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

- PHP 8.2+ dengan extensions: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `PDO`, `pdo_mysql`/`pdo_sqlite`, `tokenizer`, `xml`, `curl` (`gd` opsional — hanya untuk PDF yang menyertakan gambar; QR/barcode & kop surat dokumen tidak butuh GD)
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

> **Semua halaman manajemen baru** wajib mengikuti pola **Popup Create (Modal) + Pencarian** yang sama seperti entitas lain (users, brands, categories, dst.):
> 1. Buat form partial `resources/views/admin/new_models/_create_form.blade.php` dengan id `newModelCreateForm`, dan `create.blade.php` cukup `@include` partial tersebut.
> 2. Di controller: `create()` return partial saat `$request->wantsJson()`, `store()` return type `RedirectResponse|JsonResponse` (+ `response()->json(['success'=>true])` saat AJAX), `index()` tambahkan filter `search`.
> 3. Di `index.blade.php`: tombol Tambah = `button.js-open-create[data-create-url]`, modal container, `@include('partials._search_bar', [...])` (dengan `'empty' => $items->isEmpty()`, `'emptyEntity'`, `'count' => $items->total()`) dan `@include('partials._create_modal_js', ['formId' => 'newModelCreateForm'])`.
> 4. Alert hasil pencarian otomatis: kosong → `_not_found` (amber "Tidak Ditemukan"), ada hasil → `_search_done` (hijau + jumlah).

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

## 10. Scheduler (`logs:purge`)

`routes/console.php` menjadwalkan `logs:purge` **harian**: menghapus permanen (`forceDelete`) log `ActivityLog` & `AssetMutationLog` yang sudah soft-deleted dan berumur > 30 hari. Tanpa scheduler, tabel log terus membengkak.

> **Penting — Laravel 12:** `app/Console/Kernel.php` **tidak aktif** di bootstrap Laravel 12 (`withKernels()` men-bind `Illuminate\Foundation\Console\Kernel`). Schedule baru harus didaftarkan via `Schedule::command(...)` di **`routes/console.php`**. Verifikasi dengan `php artisan schedule:list` — jika kosong, scheduler TIDAK akan menjalankan apa pun.

### Menjalankan scheduler per platform

**Linux (cron):**
```bash
crontab -e
* * * * * cd /var/www/inventaris-aset && php artisan schedule:run >> /dev/null 2>&1
```

**Windows (Task Scheduler):**
```bat
schtasks /create /tn "AssetMS Scheduler" /tr "\"C:\php\php.exe\" C:\inventory-asset\artisan schedule:run" /sc minute /mo 1 /ru SYSTEM
```

**Docker:** service `scheduler` menjalankan `php artisan schedule:work` (otomatis, `restart: unless-stopped`).

**Dev lokal:** `php artisan schedule:work` di terminal terpisah.

### Uji manual
```bash
php artisan logs:purge            # jalankan sekali, lihat jumlah yang dibuang
php artisan schedule:list         # pastikan logs:purge terdaftar daily
```

### Cek apakah scheduler benar-benar jalan
- Log harian muncul entri `logs:purge` (atau cek `storage/logs/laravel.log` pagi hari).
- Cek `schedule:run` di cron: `grep CRON /var/log/syslog` atau `journalctl -u cron | grep artisan`.

---

## 11. Health Check & Monitoring

### Endpoint `/up`

Laravel 12 health endpoint (`bootstrap/app.php` → `health: '/up'`) — tanpa DB check, tapi membuktikan PHP-FPM + routing hidup.

```bash
curl -I https://domain-anda.com/up     # HTTP 200 = sehat
```

Pasang di Uptime Kuma / UptimeRobot / ping LB: interval 1–5 menit, alert jika non-200.

### Checklist monitoring harian

```bash
# 1. Aplikasi hidup
curl -I <APP_URL>/up

# 2. Queue worker jalan
sudo supervisorctl status laravel-worker:*      # Linux
nssm status AssetMSQueue                        # Windows
docker compose ps queue                         # Docker
php artisan queue:failed                        # antrean gagal (harus 0)

# 3. Log error tidak menumpuk
tail -n 50 storage/logs/laravel.log

# 4. Disk & database
df -h                                          # sisa disk (backup!)
mysql -e "SELECT COUNT(*) FROM activity_logs"  # ukuran log

# 5. Scheduler jalan
php artisan schedule:list
```

### Log rotation

`LOG_CHANNEL=stack` → `single` (`storage/logs/laravel.log`) tidak dirotasi otomatis. Untuk produksi:

```bash
# logrotate (Linux) — /etc/logrotate.d/laravel
/var/www/inventaris-aset/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    copytruncate
}
```

Atau set `LOG_CHANNEL=daily` di `.env` (Laravel membuat `laravel-YYYY-MM-DD.log`, auto-hapus sesuai `LOG_RETENTION`). Windows: rutin hapus manual / pakai script terjadwal.

---

## 12. Rollback

Gunakan ketika versi terbaru (git pull / deploy) bikin error.

### Strategi: selalu deploy dari tag/commit yang diketahui baik

```bash
# 1. Catat versi yang sedang jalan SEBELUM update
cd /var/www/inventaris-aset && git log --oneline -1     # mis. abc1234

# 2. Saat update bermasalah — kembali ke versi lama
git fetch --tags
git checkout abc1234          # atau: git checkout v1.0.0

# 3. Samakan dependency dengan versi lama
composer install --optimize-autoloader --no-dev

# 4. Jika update tadi membawa migration, JANGAN langsung rollback migration
#    di production (bisa menghapus data). Options:
#    - restore backup DB (lihat §5), ATAU
#    - buat migration "koreksi kebalikan" yang baru (forward-fix), ATAU
#    - php artisan migrate:rollback --step=N hanya jika yakin aman

# 5. Clear + cache ulang
php artisan optimize:clear
composer run cache

# 6. Restart worker
sudo supervisorctl restart laravel-worker:*
# Docker: docker compose up -d --build

# 7. Verifikasi
curl -I <APP_URL>/up
```

### Checklist sebelum rollback
- [ ] Backup DB & storage **sebelum** rollback (rollback bisa butuh restore)
- [ ] Tahu apakah ada migration baru yang sudah jalan (cek `php artisan migrate:status`)
- [ ] Konfirmasi user bahwa aplikasi akan maintenance singkat: `php artisan down --retry=30`
- [ ] Setelah selesai: `php artisan up`

### Tips pencegahan
- Selalu deploy via **git tag** (`git tag v1.0.1 && git push --tags`) — bukan langsung `main` tanpa jaminan.
- Uji dulu di staging/local: `composer run test` (193 tests harus hijau) sebelum push production.
- Simpan diff: `git diff v1.0.0 v1.0.1 --stat` — tahu file apa saja yang berubah.

---

## 13. Operasi Docker

### Perintah harian

```bash
docker compose ps                       # status service
docker compose logs -f app              # log PHP (append)
docker compose logs -f nginx            # log web server
docker compose logs -f queue            # log worker notifikasi
docker compose logs --since 1h db       # log MySQL 1 jam terakhir

docker compose exec app php artisan tinker
docker compose exec app php artisan migrate --force
docker compose exec app composer run cache
docker compose restart app nginx        # restart setelah ubah .env
```

### Update aplikasi (git pull) di Docker

```bash
docker compose exec app composer install --optimize-autoloader --no-dev
docker compose exec app php artisan optimize:clear
docker compose exec app composer run cache
docker compose up -d --build            # rebuild image jika Dockerfile berubah
docker compose ps                       # semua service Up
curl -I http://localhost/up
```

### Backup volume MySQL Docker

```bash
# Dump
docker compose exec db sh -c 'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" $MYSQL_DATABASE' > backup_$(date +%Y%m%d).sql

# Restore (stop app dulu!)
docker compose stop app queue scheduler
docker compose exec -T db sh -c 'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" $MYSQL_DATABASE' < backup_20260715.sql
docker compose up -d
```

### Masalah umum Docker

| Gejala | Penyebab | Solusi |
|--------|----------|--------|
| `permission denied` tulis `storage/` (Linux) | UID container ≠ UID host | `chmod -R ug+rwx storage bootstrap/cache` |
| `DB_PASSWORD ... is required` saat `compose up` | `.env` belum isi `DB_PASSWORD`/`DB_ROOT_PASSWORD` | Isi keduanya (wajib untuk MySQL container) |
| App tidak bisa koneksi DB | `DB_HOST` masih `127.0.0.1` | Pastikan `environment: DB_HOST: db` di compose (sudah default) |
| Port 80 bentrok (XAMPP aktif) | Host memakai 80 | Set `HTTP_PORT=8080` di `.env` |
| Container `app` restart terus | Error boot (cek `.env`/APP_KEY) | `docker compose logs app` |
| PDF hilang setelah rebuild | `storage/` tidak di-bind | Bind mount `./:/var/www` sudah menanganinya — cek `storage:link` |
| MySQL `data` rusak setelah kill -9 | shutdown tidak bersih | `docker compose down` → backup volume → `docker compose up -d` |

### Kebersihan

```bash
docker compose down            # stop + hapus container (volume AMAN)
docker image prune -f          # hapus image dangling
docker system df               # lihat pemakaian
# ⚠️ docker compose down -v  = MENGHAPUS data MySQL (volume db-data)
```

---

## Reference: Key Files & Locations

| Komponen | Path |
|----------|------|
| Routes | `routes/web.php`, `routes/api.php`, `routes/auth.php` |
| Controllers | `app/Http/Controllers/` |
| Models | `app/Models/` |
| Middleware | `app/Http/Middleware/CheckAdmin.php` |
| Form Requests | `app/Http/Requests/` (StoreEmployeeRequest, UpdateEmployeeRequest, StorePeripheralRequest, UpdatePeripheralRequest, IssuePeripheralRequest) |
| Observers | `app/Observers/AssetObserver.php` |
| Blade Views | `resources/views/` |
| Config | `config/` |
| Migrations | `database/migrations/` |
| Seeders | `database/seeders/` |
| Tests | `tests/Feature/`, `tests/Unit/` |
| Employee Controller | `app/Http/Controllers/EmployeeController.php` |
| Employee Model | `app/Models/Employee.php` |
| Employee Views | `resources/views/admin/employees/` |
| Peripheral Controller | `app/Http/Controllers/PeripheralController.php` |
| Peripheral Model | `app/Models/Peripheral.php` |
| PeripheralIssuance Model | `app/Models/PeripheralIssuance.php` |
| Peripheral Views | `resources/views/admin/peripherals/{index,create,edit,show}.blade.php` |
| Log Controller | `app/Http/Controllers/LogController.php` |
| Log Views | `resources/views/admin/logs/` |
| MAC Address | Migration `2026_07_16_090000_add_mac_address_to_assets_table.php` — kolom di `assets` table |
| Disable User | Migration `2026_07_16_100000_add_is_active_to_users_table.php` — toggle via `admin.users.toggle-active` route |
| Disable Employee | Migration `2026_07_16_100001_add_is_active_to_employees_table.php` — toggle via `admin.employees.toggle-active` route |
| CSV Import | `AssetController::importCsv()` — per-row transaction, validasi vendor/MAC/SN |
| CSV Template | `GET /assets/import/template` — `AssetController::exportCsvTemplate()` — download template 15 kolom |
| Dokumen SOP Controller | `app/Http/Controllers/SopDocumentController.php` — `generateNumber()`, `storePdf()`, `print()`, `viewData()` |
| Dokumen SOP Enum | `app/Enums/SopDocumentType.php` — Registrasi (FRA), TandaTerima (FTA), PermohonanMutasi (FPM), BeritaAcara (BAMA) |
| Dokumen SOP Model | `app/Models/SopDocument.php` — soft-deletes, kolom `data` JSON |
| Dokumen SOP Views | `resources/views/sop_documents/{index,create,show}.blade.php`, `partials/_form_{type}.blade.php`, `pdf/{type}.blade.php` |
| Dokumen SOP Request | `app/Http/Requests/StoreSopDocumentRequest.php` — validasi `data.location_id` (nullable), `data.giver_name`, `data.purpose` |
| Dokumen SOP PDF | Tersimpan di `storage/app/public/documents/` (via `storePdf()`) |
| UI Partials (Popup & Pencarian) | `resources/views/partials/_create_modal_js.blade.php`, `_search_bar.blade.php`, `_not_found.blade.php`, `_search_done.blade.php` — pola modal create AJAX + search bar untuk semua halaman manajemen |
| Scheduler | `routes/console.php` — `Schedule::command(PurgeLogs)->daily()`; command: `app/Console/Commands/PurgeLogs.php` (`app/Console/Kernel.php` tidak aktif di Laravel 12) |
| Health Check | `GET /up` (route `health` di `bootstrap/app.php`) |
| Docker | `Dockerfile`, `docker-compose.yml`, `docker/nginx/default.conf`, `.dockerignore` |
| README.md | Panduan instalasi & deploy (Linux/Windows/LAN/mkcert/Docker) |
| AGENTS.md | Panduan development & agent AI |
| MAINTENANCE.md | Dokumentasi ini |

---

*Terakhir diperbarui: September 2026 — AssetMS v1.0.0*
