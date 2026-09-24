# AssetMS — Sistem Informasi Manajemen Aset

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Database-MySQL-blue?logo=mysql&logoColor=white" alt="Database">
  <img src="https://img.shields.io/badge/Tests-188%20passing-brightgreen" alt="Tests">
  <img src="https://img.shields.io/badge/License-MIT-green" alt="License">
</p>

Aplikasi web inventaris aset perusahaan (IT & GA) berbasis **Laravel 12**: hak akses granular, mutasi tercatat, dokumen SOP + PDF, QR/Barcode, dashboard, dan REST API.

---

## Daftar Isi

1. [Mulai Cepat (5 Menit)](#mulai-cepat-5-menit)
2. [Panduan Belajar (urutan)](#panduan-belajar-urutan)
3. [Konsep Inti](#konsep-inti)
4. [Fitur Utama](#fitur-utama)
5. [Tech Stack](#tech-stack)
6. [Struktur Proyek](#struktur-proyek)
7. [Sistem Hak Akses (RBAC)](#sistem-hak-akses-rbac)
8. [Pola UI: Modal Create + Pencarian](#pola-ui-modal-create--pencarian)
9. [Alur Data Penting](#alur-data-penting)
10. [Dokumen SOP Aset](#dokumen-sop-aset)
11. [CSV Import & Export](#csv-import--export)
12. [Public Tracking (`/track`)](#public-tracking-track)
13. [REST API](#rest-api)
14. [Instalasi Lokal](#instalasi-lokal)
15. [Akun Default](#akun-default)
16. [Perintah Penting](#perintah-penting)
17. [Troubleshooting Umum](#troubleshooting-umum)
18. [Deploy Production](#deploy-production)
19. [Maintenance](#maintenance)
20. [Sentry](#sentry)
21. [Lisensi](#lisensi)

---

## Mulai Cepat (5 Menit)

```bash
composer install
copy .env.example .env          # Windows; macOS/Linux: cp .env.example .env
php artisan key:generate
# Pastikan di .env: DB_CONNECTION=sqlite  (atau isi MySQL)
# Jika SQLite: buat file kosong database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Buka **http://localhost:8000** → login:

| Role | Username / Email | Password |
|------|------------------|----------|
| Admin | `admin` / `admin@company.com` | `password123` |
| Staff | `staff` / `staff@company.com` | `password123` |

> **HTTP lokal:** set `SESSION_SECURE_COOKIE=false` di `.env` agar tidak error **419 Page Expired** saat login. Di production HTTPS, set `true`.

Jalankan test:

```bash
composer run test    # 188 tests, 517 assertions
```

---

## Panduan Belajar (urutan)

Urutan yang disarankan agar tidak kebingungan:

| # | Topik | Baca / Coba |
|---|-------|-------------|
| 1 | Jalankan aplikasi & login | [Mulai Cepat](#mulai-cepat-5-menit) |
| 2 | **Konsep inti** (RBAC, Observer, Scope) | [Konsep Inti](#konsep-inti) |
| 3 | Peta folder | [Struktur Proyek](#struktur-proyek) |
| 4 | CRUD aset (fitur utama) | `AssetController` + `resources/views/assets/` |
| 5 | Hak akses staff vs admin | [RBAC](#sistem-hak-akses-rbac) + `PermissionSeeder` |
| 6 | Pola UI yang dipakai di semua halaman | [Pola UI](#pola-ui-modal-create--pencarian) |
| 7 | Alur mutasi & notifikasi | [Alur Data](#alur-data-penting) |
| 8 | Dokumen SOP + PDF | [Dokumen SOP](#dokumen-sop-aset) |
| 9 | Test sebagai dokumentasi hidup | `tests/Feature/*Test.php` |

**Tips:** satu fitur = 4 file utama → `routes/web.php` → `Controller` → `Model` → `resources/views/...`. Baca berurutan dari situ.

---

## Konsep Inti

Istilah yang sering muncul di codebase ini:

### 1. RBAC (Role-Based Access Control) — Spatie Permission

- **2 role:** `admin` (semua permission otomatis), `staff` (permission dicentang satu-satu oleh admin).
- Permission didefinisikan di **`database/seeders/PermissionSeeder.php`** (const `GROUPS`).
- Dipakai di Blade: `@can('asset.edit')`, `@canany([...])`  
  di PHP: `$user->can('asset.edit')`, FormRequest `authorize()`.
- **Aset punya 2 sistem permission sekaligus** (transisi ke tipe IT/GA):
  - **Legacy:** `asset.*` — boleh semua tipe aset
  - **Tipe:** `asset.it.*` / `asset.ga.*` — hanya aset IT / aset GA  
  Helper di `AssetController`: `canEditAsset()`, `applyTypeScope()`, `authorizeViewAsset()`, dll.

### 2. Tipe aset: `it` | `ga`

Kolom `assets.type` (default `it`). Scope `Asset::ofType()` + filter di index. Menentukan permission mana yang berlaku.

### 3. Eloquent Scope

Query bisa dipakai ulang sebagai method model:

```php
// app/Models/Asset.php
Asset::search($term)      // cari kode, nama, serial, merek, model, nama karyawan
     ->ofStatus($status)
     ->ofCategory($id)
     ->ofType('it');
```

Dipakai di index, export CSV, dan API — satu sumber kebenaran.

### 4. Observer (otomatisasi)

| Observer | Tugas |
|----------|--------|
| `AssetObserver` | Generate kode aset, log mutasi, kirim email notif (queue) |
| `AssetCategoryObserver` | Regenerate kode aset lama saat abbreviation kategori berubah |

### 5. FormRequest (validasi + otorisasi)

`StoreAssetRequest`, `StoreAssetMaintenanceRequest`, …  
`rules()` = validasi, `authorize()` = cek permission (defense-in-depth).

### 6. Queue

Notifikasi email mutasi di-queue (`QUEUE_CONNECTION=database`).  
Dev: `composer run dev:queue`. Production: Supervisor (lihat [Deploy](#deploy-production)).

### 7. SoftDeletes

Aset, karyawan, loan, dokumen SOP, log — bisa di-restore dari halaman log terhapus.

---

## Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| Dashboard | Doughnut status, bar kategori, line trend mutasi 6 bulan, log real-time (Chart.js) |
| Manajemen Aset | CRUD + kode unik otomatis `AST{ABR}{YY}{MM}{SEQ}` + tipe IT/GA |
| Maintenance Aset | Catat penambahan/pengurangan komponen (inline form di detail aset) |
| Mutasi Aset | Lokasi / status / PIC / karyawan + tanggal aktual + log wajib |
| Pencarian | Kode, nama aset, serial, merek, model, **nama karyawan** |
| RBAC Granular | 53 permission · 13 grup · 2 role · privasi data finansial |
| Karyawan | CRUD karyawan non-system (`employees`), soft-deletes, bisa dinonaktifkan |
| Peripheral | Stok asesoris: issue / restok, log pengeluaran |
| QR & Barcode | SVG; QR encode URL `/track`, barcode encode `asset_code`; print 1–4 label |
| Scanner | html5-qrcode di login & `/track` (kamera HP) |
| Dokumen SOP | FRA / FTA / FPM / BAMA / FPN — nomor otomatis + PDF (dompdf) |
| Peminjaman | Check-out/in; form peminjaman (FPN) terbit **otomatis** saat check-out |
| CSV | Export chunk(200) + import per-validasi-per-baris + template |
| Laporan PDF | Aset & kategori (landscape A4) |
| REST API | `/api/assets`, `/api/assets/{id}` (Sanctum) |
| Activity Log | Trait `LogsActivity` + viewer (asset / mutasi / peripheral) |
| Notifikasi | Email via queue ke admin + PIC saat mutasi |
| Public Tracking | `/track` tanpa login: kode / serial / MAC (case- & format-insensitive) |
| Login | Username **atau** email (deteksi `@`); user nonaktif ditolak |
| Security | Rate limit per-area, SRI CDN, HSTS, session encrypted |

---

## Tech Stack

| Lapisan | Pilihan |
|---------|---------|
| Backend | Laravel 12, PHP 8.2+ |
| Database | MySQL (prod) / SQLite (dev) |
| Auth | Session (web), Sanctum (API), Bcrypt 12 |
| RBAC | Spatie Laravel Permission v6 |
| Frontend | Bootstrap 5.3.3 (CDN + SRI), Bootstrap Icons, Chart.js 4.4 — **tanpa Node build** |
| PDF | barryvdh/laravel-dompdf |
| QR / Barcode | bacon-qr-code (SVG), picqer/php-barcode-generator |
| Scanner | html5-qrcode |
| Queue | Database driver |
| Error tracking | Sentry (opsional, isi DSN) |
| Testing | PHPUnit 11 — **188 tests / 517 assertions** |

---

## Struktur Proyek

```
inventory-asset/
├── app/
│   ├── Console/
│   │   ├── Kernel.php                   # Scheduler (jika ada)
│   │   └── Commands/PurgeLogs.php       # Hapus log lama
│   ├── Enums/
│   │   ├── AssetStatus.php              # InUse, Spare, Service, Broken, BrokenCheck, Disposal
│   │   ├── UserRole.php                 # Admin, Staff
│   │   └── SopDocumentType.php          # Registrasi, TandaTerima, PermohonanMutasi, BeritaAcara, Peminjaman
│   ├── Http/
│   │   ├── Controllers/                 # 21 file (15 utama + Auth 5 + Api 1)
│   │   │   ├── Api/AssetController.php
│   │   │   └── Auth/                    # 5 — Login, password reset, confirm
│   │   ├── Middleware/CheckAdmin.php
│   │   └── Requests/                    # 23 FormRequest (validasi + authorize)
│   ├── Models/                          # 14 model (Asset, Employee, Peripheral, SopDocument, …)
│   ├── Observers/                       # 2 — AssetObserver, AssetCategoryObserver
│   ├── Services/
│   │   ├── AssetCodeGenerator.php       # AST{ABR}{YY}{MM}{SEQ}
│   │   └── SopDocumentService.php       # Nomor + render PDF
│   ├── Traits/LogsActivity.php          # Auto log create/update/delete
│   └── Notifications/AssetMutationNotification.php
├── config/                              # sentry, permission, cors, session, …
├── database/
│   ├── migrations/                      # 39 migrasi
│   └── seeders/
│       ├── PermissionSeeder.php         # 53 permission, 13 grup, 2 role
│       ├── AdminUserSeeder.php          # admin & staff default
│       ├── AssetCategorySeeder.php
│       ├── BrandSeeder.php
│       └── LocationSeeder.php
├── public/                              # Document root (satu-satunya folder diexpose)
│   ├── js/
│   │   ├── asset-maintenance.js         # Form maintenance inline (event delegation)
│   │   └── column-settings.js           # Preferensi kolom index aset
│   └── images/KOBINTILES.png
├── resources/views/                     # 109 blade
│   ├── layouts/                         # app (sidebar+topbar), guest (login)
│   ├── partials/                        # _create_modal_js, _search_bar, _not_found, …
│   ├── assets/                          # index, show, _show_content, _form, …
│   ├── admin/                           # brands, categories, employees, locations, logs, peripherals, users, vendors
│   ├── sop_documents/                   # + pdf/
│   ├── loans/, reports/, public/, auth/, dashboard.blade.php
├── routes/
│   ├── web.php                          # Web + throttle per-area
│   ├── auth.php                         # Login/logout/reset (register off)
│   ├── api.php                          # Sanctum
│   └── console.php                      # Closure routes (CLI)
├── tests/
│   ├── Unit/                            # AssetCodeGenerator
│   └── Feature/                         # 24 file (termasuk Auth/) — 188 tests / 517 assertions
├── AGENTS.md                            # Catatan untuk AI/agent development
└── MAINTENANCE.md                       # Runbook operasional server
```

---

## Sistem Hak Akses (RBAC)

### Role

| Role | Perilaku |
|------|----------|
| **admin** | Semua permission otomatis (sync di `PermissionSeeder`) |
| **staff** | Hanya permission yang dicentang Admin di **Manajemen User** |

### Grup permission (13 grup, 53 permission)

| Grup | Contoh permission |
|------|-------------------|
| Manajemen Aset IT | `asset.it.viewAny`, `.create`, `.edit`, `.delete`, `.manage_finances`, `.mutate` |
| Manajemen Aset GA | `asset.ga.*` (sama seperti IT) |
| Manajemen Aset (Legacy) | `asset.*` — akses semua tipe (master/transisi) |
| Lokasi / Kategori / Merek / Vendor | `{entity}.viewAny\|create\|edit\|delete` |
| Peminjaman | `loan.viewAny\|create\|checkin\|delete` |
| Karyawan | `employee.*` |
| Peripheral | `peripheral.*` + `peripheral.issue` |
| Dokumen SOP | `document.*` |
| Laporan | `report.viewAny` |
| Log | `log.delete` |

**Maintenance aset** tidak punya permission sendiri — mengikuti `asset.{it\|ga}.edit` / `.mutate` / legacy `asset.edit` / `asset.mutate` (lihat `$canMaintenance` di `_show_content.blade.php` dan `StoreAssetMaintenanceRequest`).

### Cara menambah permission baru

1. Tambah entri di `PermissionSeeder::GROUPS`
2. `php artisan db:seed --class=PermissionSeeder`
3. Centang untuk user/staff di **Manajemen User**
4. Pakai di Blade `@can(...)` / PHP `$user->can(...)` / FormRequest `authorize()`

---

## Pola UI: Modal Create + Pencarian

**Semua halaman manajemen** memakai pola sama — pahami sekali, paham semua:

1. Tombol **Tambah** → `button.js-open-create[data-create-url]` → modal AJAX  
   (`create()` return partial saat `Accept: application/json`).
2. `store()` → `RedirectResponse|JsonResponse`  
   sukses `{'success': true}` · validasi **422** `{'errors': …}` · server **500**.
3. Form partial: `resources/views/{area}/_create_form.blade.php`  
   (id `{entity}CreateForm`; halaman `create.blade.php` tetap `@include`).
4. `index()` selalu punya filter `search` (+ filter lain: status, tanggal, …).
5. Hasil kosong → alert **"Tidak Ditemukan"**; ada hasil → **"Pencarian selesai"** + jumlah.

**Shared partials** (`resources/views/partials/`):

| Partial | Fungsi |
|---------|--------|
| `_create_modal_js.blade.php` | JS generik: buka modal, fetch submit, render error, reload saat sukses |
| `_search_bar.blade.php` | Form GET pencarian |
| `_not_found.blade.php` / `_search_done.blade.php` | Alert hasil search |
| `_pagination_per_page.blade.php` | Ukuran halaman |

Dropdown pencarian (`select[data-searchable]`) → `window.initSearchableSelect` di `layouts/app.blade.php`.

---

## Alur Data Penting

### Mutasi aset → log + email

```
User ubah lokasi/status/PIC/karyawan
        │
        ▼
AssetController@update / bulkUpdate
        │
        ▼
AssetObserver::updated()
        ├── simpan AssetMutationLog (from_* → to_*)
        └── queue AssetMutationNotification → semua admin + PIC
```

### Kode aset otomatis

```
Asset::create() → AssetObserver::creating
        → AssetCodeGenerator  (AST + abbreviation kategori + YYMM + urutan)
        → jika kategori di-rename → AssetCategoryObserver regenerate kode aset lama
```

### Check-out peminjaman

```
LoanController@store (satu transaksi)
        ├── validasi aset belum dipinjam (lockForUpdate)
        ├── buat AssetLoan
        └── terbitkan SopDocument type=peminjaman (FPN-…) — gagal = rollback
```

---

## Dokumen SOP Aset

Menu **Dokumen SOP Aset** (`/admin/dokumen`, permission `document.*`).

| Type | Prefix | Nama |
|------|--------|------|
| `registrasi` | `FRA` | Form Registrasi Aset |
| `tanda_terima` | `FTA` | Form Tanda Terima Aset |
| `permohonan_mutasi` | `FPM` | Form Permohonan Mutasi |
| `berita_acara` | `BAMA` | Berita Acara Mutasi |
| `peminjaman` | `FPN` | Form Peminjaman (**otomatis** saat check-out) |

Format nomor: `{PREFIX}-{TAHUN}-{BULAN}-{SEQ:4}` (mis. `FTA-2026-08-0001`).  
Urutan **reset per bulan**, **tidak reuse** nomor yang dihapus (selalu `max+1`).

**Poin penting:**

- PDF digenerate saat `store` → `storage/app/public/documents/`.
- **Peminjaman tidak bisa dibuat manual** dari halaman dokumen — hanya otomatis + tombol "Buatkan Form" susulan.
- Tanda Terima: baris **Aset + Peripheral** (min. 1), penerima wajib, lokasi penempatan satu baris.
- Edit: jenis & nomor terkunci, PDF diregenerasi.
- Service: `SopDocumentService` (`generateNumber`, `renderPdf`, `archivePdf`, `viewData`).
- Kop PDF **tanpa logo gambar** → tidak butuh PHP GD.

---

## CSV Import & Export

### Export

- `chunk(200)` streaming — aman untuk data besar.
- Kolom mengikuti preferensi per-user (ikon kolom di index).
- BOM UTF-8 agar Excel membaca dengan benar.
- Ikut filter search/status/kategori/tipe yang sedang aktif.

### Import

- `POST /assets/import/csv` · permission `asset.create` · `throttle:10,1,import`
- Template: `/reports` → Download Template (`assets.import.template`)
- **14 kolom:**  
  `Kode Aset, Nama, Kategori, Merek, Model, Serial Number, MAC Address, Lokasi, Vendor, Status, Tanggal Pembelian, Harga Pembelian, Jumlah, Catatan`
- Validasi **per sel + per baris** (transaksi per baris):  
  kategori wajib ada · merek/vendor auto-create · status enum (default Spare) · SN unik · MAC regex · jumlah 1–9999 · harga ≥ 0
- `assigned_to` = user yang import.

---

## Public Tracking (`/track`)

- Publik, tanpa login · `throttle:60,1,track`
- Cari: **`asset_code`** ATAU **`serial_number`** ATAU **`mac_address`**
- MAC: case-insensitive & format-insensitive (`:` / `-` sama)
- Hasil: detail aset + riwayat mutasi (paginated)
- Ada scanner barcode (kamera)
- Test: `tests/Feature/PublicTrackTest.php`

---

## REST API

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/assets` | List (paginate 50; filter `search`, `status`, `category_id`) |
| GET | `/api/assets/{id}` | Detail + relasi |

Semua endpoint butuh **`auth:sanctum`**.  
Scope search API sama dengan web (termasuk nama karyawan).

---

## Instalasi Lokal

### Persyaratan

| Software | Versi |
|----------|-------|
| PHP | 8.2+ |
| Composer | 2.x |
| Database | SQLite (mudah) atau MySQL |
| Node.js | **Tidak perlu** (CSS/JS CDN) |

### Langkah

```bash
git clone <repo-url>
cd inventory-asset

composer install
copy .env.example .env
php artisan key:generate

# SQLite (paling mudah):
#   .env → DB_CONNECTION=sqlite
#   buat file kosong: database/database.sqlite

# MySQL:
#   DB_CONNECTION=mysql, DB_DATABASE=..., DB_USERNAME=..., DB_PASSWORD=...

php artisan migrate --seed
php artisan serve          # atau: composer run dev
```

Akses `http://localhost:8000`.

### Catatan development

- Sebelum test: `php artisan optimize:clear` (config cache mengganggu testing)  
  — atau cukup `composer run test` (sudah include clear).
- Log real-time: `composer run dev:logs`
- Queue (email): `composer run dev:queue`
- **419 saat login di HTTP?** → `SESSION_SECURE_COOKIE=false` di `.env`

---

## Akun Default

| Role | Username | Email | Password |
|------|----------|-------|----------|
| Super Admin | `admin` | admin@company.com | password123 |
| Staff | `staff` | staff@company.com | password123 |

> Staff default hanya punya `asset.viewAny`.  
> **Ganti password setelah login pertama!**

---

## Perintah Penting

### Development

| Command | Fungsi |
|---------|--------|
| `composer run dev` | Dev server |
| `composer run dev:queue` | Queue worker (notifikasi) |
| `composer run dev:logs` | Monitor log |
| `composer run test` | Clear cache + jalankan **188 tests** |
| `php artisan migrate:fresh --seed` | Reset DB + seed |
| `php artisan db:seed --class=PermissionSeeder` | Seed ulang permission |

### Production

| Command | Fungsi |
|---------|--------|
| `composer run cache` | view + config + route cache |
| `php artisan optimize:clear` | Hapus semua cache |
| `php artisan migrate --force` | Migrasi production |
| `php artisan storage:link` | Link upload/arsip PDF |
| `composer install --no-dev --optimize-autoloader` | Dependency production |

---

## Troubleshooting Umum

| Gejala | Penyebab paling umum | Solusi |
|--------|----------------------|--------|
| **419** saat login | `SESSION_SECURE_COOKIE=true` di HTTP | `SESSION_SECURE_COOKIE=false` (local) / pakai HTTPS (prod) |
| Dashboard chart kosong | Tag `<script>` Chart.js tidak ditutup / SRI salah | Pastikan `</script>` CDN + hash `integrity` cocok |
| Test gagal aneh | Config/route cache | `php artisan optimize:clear` |
| Icon tidak muncul | Bootstrap Icons CDN belum ada di layout | Cek `<link>` di `layouts/app.blade.php` |
| Email tidak terkirim | Queue worker mati / `MAIL_MAILER=log` | `composer run dev:queue` / Supervisor |
| Tombol maintenance mati di modal | Script inline tidak jalan (innerHTML) | Pakai `public/js/asset-maintenance.js` (delegation) |
| Permission tidak muncul di User Mgmt | Belum di `PermissionSeeder::GROUPS` | Tambah + seed ulang |

---

## Deploy Production

Panduan lengkap langkah demi langkah ada di bagian ini (Ubuntu/Debian).

### Ringkasan

1. **Server:** PHP 8.2+, MySQL, Composer, Apache/Nginx  
   Ekstensi: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO_mysql, Tokenizer, XML, Curl  
   *(GD opsional — PDF QR/kop tidak butuh GD.)*

2. **Upload + dependency**
   ```bash
   cd /var/www/inventaris-aset
   composer install --optimize-autoloader --no-dev
   ```

3. **`.env` production**
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda.com
   SESSION_SECURE_COOKIE=true      # wajib true di HTTPS
   QUEUE_CONNECTION=database
   ```
   ```bash
   php artisan key:generate
   ```

4. **Database**
   ```sql
   CREATE DATABASE inventaris_aset CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   ```

5. **Permission folder**
   ```bash
   sudo chown -R www-data:www-data /var/www/inventaris-aset
   sudo chmod -R 775 /var/www/inventaris-aset/storage /var/www/inventaris-aset/bootstrap/cache
   ```

6. **Cache**
   ```bash
   composer run cache
   ```

7. **Web server** — `DocumentRoot` → `.../public` (bukan root project).

8. **HTTPS wajib** (login aman + kamera scanner):
   - Domain publik: Let's Encrypt (`certbot --apache` / `--nginx`)
   - IP internal: mkcert (contoh config Nginx ada di riwayat / `MAINTENANCE.md`)

9. **Email** — `MAIL_MAILer=smtp` + kredensial (Gmail App Password / Mailgun).

10. **Queue Supervisor**
    ```ini
    [program:laravel-worker]
    command=php /var/www/inventaris-aset/artisan queue:work --sleep=3 --tries=3 --max-time=3600
    autostart=true
    autorestart=true
    user=www-data
    numprocs=2
    ```
    ```bash
    sudo supervisorctl reread && sudo supervisorctl update
    sudo supervisorctl start laravel-worker:*
    ```

11. **Cron**
    ```cron
    * * * * * cd /var/www/inventaris-aset && php artisan schedule:run >> /dev/null 2>&1
    ```

### Checklist pasca-deploy

- [ ] Login halaman muncul via HTTPS
- [ ] Dashboard + grafik normal
- [ ] Ganti password default admin & staff
- [ ] `APP_DEBUG=false` (404 biasa, bukan stack trace)
- [ ] `supervisorctl status` → RUNNING
- [ ] Log tidak menumpuk error
- [ ] Scan barcode dari HP berfungsi

---

## Maintenance

```bash
cd /var/www/inventaris-aset
git pull
composer install --optimize-autoloader --no-dev
php artisan optimize:clear
composer run cache
sudo supervisorctl restart laravel-worker:*
tail -f storage/logs/laravel.log
```

Detail operasional: **[MAINTENANCE.md](MAINTENANCE.md)**  
Catatan development: **[AGENTS.md](AGENTS.md)**

---

## Sentry

Sudah terkonfigurasi (`config/sentry.php`). Isi di `.env`:

```
SENTRY_LARAVEL_DSN=https://xxxx@sentry.io/xxxx
```

Otomatis nonaktif di `local` & `testing`. Uji: `php artisan sentry:test`.

---

## Lisensi

MIT License — AssetMS v1.0.0
Thanks Allah SWT
Thanks AI and Robot
