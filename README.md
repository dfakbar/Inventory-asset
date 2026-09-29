# AssetMS — Sistem Informasi Manajemen Aset

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Database-MySQL-blue?logo=mysql&logoColor=white" alt="Database">
  <img src="https://img.shields.io/badge/Tests-193%20passing-brightgreen" alt="Tests">
  <img src="https://img.shields.io/badge/License-MIT-green" alt="License">
</p>

Aplikasi web inventaris aset perusahaan (IT & GA) berbasis **Laravel 12**: hak akses granular, mutasi tercatat, dokumen SOP + PDF, QR/Barcode, dashboard, dan REST API.

Panduan ini lengkap dari **lokal → LAN kantor → server produksi publik**, untuk **Linux, Windows, maupun Docker**.

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
14. [Persyaratan Sistem](#persyaratan-sistem)
15. [Instalasi Lokal](#instalasi-lokal)
16. [Referensi Variabel `.env`](#referensi-variabel-env)
17. [Server Produksi Linux (Ubuntu/Debian)](#server-produksi-linux-ubuntudebian)
18. [Server Produksi Windows (Apache)](#server-produksi-windows-apache)
19. [Mode Akses Local, LAN, dan Public](#mode-akses-local-lan-dan-public)
20. [HTTPS Internal LAN (mkcert)](#https-internal-lan-mkcert)
21. [Deployment Docker](#deployment-docker)
22. [Akun Default](#akun-default)
23. [Perintah Penting](#perintah-penting)
24. [Troubleshooting Umum](#troubleshooting-umum)
25. [Checklist Pasca-Deploy](#checklist-pasca-deploy)
26. [Maintenance](#maintenance)
27. [Sentry](#sentry)
28. [Lisensi](#lisensi)

---

## Mulai Cepat (5 Menit)

```bash
composer run setup
# = composer install + copy .env.example .env (jika belum ada) + key:generate + migrate --force
php artisan db:seed
php artisan serve
```

Manual (jika tidak pakai `composer run setup`):

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

Verifikasi sehat: buka **`/up`** → balas `200 OK` berarti aplikasi hidup (dipakai untuk cek pasca-deploy).

Jalankan test:

```bash
composer run test    # 193 tests, 530 assertions
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
| 9 | Deploy ke server | [Mode Akses](#mode-akses-local-lan-dan-public) / [Linux](#server-produksi-linux-ubuntudebian) / [Windows](#server-produksi-windows-apache) / [Docker](#deployment-docker) |
| 10 | Test sebagai dokumentasi hidup | `tests/Feature/*Test.php` |

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
Dev: `composer run dev:queue`. Produksi: Supervisor / NSSM / container `queue` (lihat masing-masing panduan deploy).

### 7. Scheduler (`logs:purge`)

`routes/console.php` menjadwalkan **`logs:purge` harian** — menghapus permanen log mutasi/aktivitas soft-deleted yang > 30 hari.  
(Catatan: `app/Console/Kernel.php` yang lama **tidak aktif** di bootstrap Laravel 12 — schedule wajib didaftarkan di `routes/console.php`.)  
Butuh cron (Linux), Task Scheduler (Windows), atau service `scheduler` (Docker). Tanpa ini, log terhapus menumpuk di database.

### 8. SoftDeletes

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
| Scanner | html5-qrcode di login & `/track` (kamera HP — butuh HTTPS) |
| Dokumen SOP | FRA / FTA / FPM / BAMA / FPN — nomor otomatis + PDF (dompdf) |
| Peminjaman | Check-out/in; form peminjaman (FPN) terbit **otomatis** saat check-out |
| CSV | Export chunk(200) + import per-validasi-per-baris + template |
| Laporan PDF | Aset & kategori (landscape A4) |
| REST API | `/api/assets`, `/api/assets/{id}` — lihat [catatan Sanctum](#rest-api) |
| Activity Log | Trait `LogsActivity` + viewer (asset / mutasi / peripheral) |
| Notifikasi | Email via queue ke admin + PIC saat mutasi |
| Public Tracking | `/track` tanpa login: kode / serial / MAC (case- & format-insensitive) |
| Login | Username **atau** email (deteksi `@`); user nonaktif ditolak |
| Health Check | Endpoint **`/up`** untuk monitoring / LB / cek pasca-deploy |
| Scheduler | `logs:purge` harian (buang log terhapus > 30 hari) |
| Security | Rate limit per-area, SRI CDN, HSTS, session encrypted |
| Deployment | Linux (Nginx/Apache), Windows (Apache), Docker Compose, mode LAN/Public |

---

## Tech Stack

| Lapisan | Pilihan |
|---------|---------|
| Backend | Laravel 12, PHP 8.2+ |
| Database | MySQL (prod) / SQLite (dev) |
| Auth | Session (web), Sanctum (API, **belum terpasang — lihat [REST API](#rest-api)**), Bcrypt 12 |
| RBAC | Spatie Laravel Permission v6 |
| Frontend | Bootstrap 5.3.3 (CDN + SRI), Bootstrap Icons, Chart.js 4.4 — **tanpa Node build** |
| PDF | barryvdh/laravel-dompdf |
| QR / Barcode | bacon-qr-code (SVG), picqer/php-barcode-generator |
| Scanner | html5-qrcode |
| Queue | Database driver |
| Error tracking | Sentry (opsional, isi DSN) |
| Testing | PHPUnit 11 — **193 tests / 530 assertions** |
| Deployment | Nginx/Apache (Linux), Apache (Windows), Docker Compose |

---

## Struktur Proyek

```
inventory-asset/
├── app/
│   ├── Console/
│   │   ├── Kernel.php                   # TIDAK AKTIF di Laravel 12 (lihat catatan scheduler)
│   │   └── Commands/PurgeLogs.php       # Hapus log soft-deleted > 30 hari
│   ├── Enums/
│   │   ├── AssetStatus.php              # InUse, Spare, Service, Broken, BrokenCheck, Disposal
│   │   ├── UserRole.php                 # Admin, Staff
│   │   └── SopDocumentType.php          # Registrasi, TandaTerima, PermohonanMutasi, BeritaAcara, Peminjaman
│   ├── Http/
│   │   ├── Controllers/                 # 21 file (15 utama + Auth 5 + Api 1)
│   │   │   ├── Api/AssetController.php
│   │   │   └── Auth/                    # 5 — Login, password reset, confirm
│   │   ├── Middleware/CheckAdmin.php
│   │   └── Requests/                    # 22 FormRequest (validasi + authorize)
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
├── docker/
│   └── nginx/default.conf               # Konfigurasi nginx container (root → public)
├── public/                              # Document root (satu-satunya folder diexpose)
│   ├── js/
│   │   ├── asset-maintenance.js         # Form maintenance inline (event delegation)
│   │   └── column-settings.js           # Preferensi kolom index aset
│   ├── images/KOBINTILES.png
│   └── .htaccess                        # Rewrite + security headers (Apache)
├── resources/views/                     # 109 blade (91 aplikasi + 18 vendor publish)
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
│   └── console.php                      # Closure routes + schedule (logs:purge daily)
├── tests/                               # 25 file test (24 Feature + 1 Unit) — 193 tests / 530 assertions
├── Dockerfile                           # php:8.2-fpm + ekstensi + Composer
├── docker-compose.yml                   # app + nginx + db + queue + scheduler
├── .dockerignore
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
- **15 kolom:**  
  `Kode Aset, Nama, Tipe Aset, Kategori, Merek, Model, Serial Number, MAC Address, Lokasi, Vendor, Status, Tanggal Pembelian, Harga Pembelian, Jumlah, Catatan`
- Tipe Aset: `IT`/`GA` (case-insensitive) — kosong = fallback berdasarkan permission; tanpa akses = baris dilewati
- Validasi **per sel + per baris** (transaksi per baris):  
  kategori wajib ada · merek/vendor auto-create · status enum (default Spare) · SN unik · MAC regex · jumlah 1–9999 · harga ≥ 0
- `assigned_to` = user yang import.

---

## Public Tracking (`/track`)

- Publik, tanpa login · `throttle:60,1,track`
- Cari: **`asset_code`** ATAU **`serial_number`** ATAU **`mac_address`**
- MAC: case-insensitive & format-insensitive (`:` / `-` sama)
- Hasil: detail aset + riwayat mutasi (paginated)
- Ada scanner barcode (kamera) — **kamera hanya jalan di HTTPS/localhost** (secure context)
- Test: `tests/Feature/PublicTrackTest.php`

---

## REST API

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/assets` | List (paginate 50; filter `search`, `status`, `category_id`) |
| GET | `/api/assets/{id}` | Detail + relasi |

Scope search API sama dengan web (termasuk nama karyawan).

> ### ⚠️ Status: butuh `laravel/sanctum` (belum terpasang)
>
> Route API terdaftar dengan middleware `auth:sanctum`, tetapi paket **`laravel/sanctum` belum terpasang** di repo ini (tidak ada di `composer.json`/`composer.lock`, dan guard `sanctum` tidak didefinisikan di `config/auth.php`). Tanpa paket tersebut, request ke `/api/assets` akan **error**. Untuk mengaktifkan:
>
> ```bash
> composer require laravel/sanctum
> php artisan config:clear
> ```
>
> (Migrasi `personal_access_tokens` ikut dibawa otomatis oleh paket.) Lalu buat token:
>
> ```bash
> php artisan tinker
> >>> $user = App\Models\User::where('email','admin@company.com')->first();
> >>> $token = $user->createToken('api')->plainTextToken;   # simpan hasilnya
> ```
>
> Uji:
>
> ```bash
> curl -H "Authorization: Bearer <TOKEN>" http://localhost/api/assets
> ```
>
> **Catatan lain:** header CORS diizinkan untuk origin = `APP_URL` (`config/cors.php`), jadi pastikan `APP_URL` cocok dengan origin pemanggil. Tanpa Sanctum, alternatifnya adalah memakai session login web yang sama (same-origin).

---

## Persyaratan Sistem

| Komponen | Development | Produksi |
|----------|-------------|----------|
| OS | Windows / Linux / macOS | Ubuntu 22.04/24.04 LTS, Windows Server 10/12, atau Docker |
| PHP | 8.2+ (CLI) | 8.2-FPM (Linux) / 8.2 Thread-Safe x64 (Windows) / `php:8.2-fpm` (Docker) |
| Ekstensi PHP | `pdo_sqlite`/`pdo_mysql`, `mbstring`, `xml`, `curl`, `bcmath`, `zip`, `fileinfo`, `openssl` | Sama + `gd` (opsional — hanya untuk PDF yang menyertakan gambar) |
| Database | SQLite (mudah) atau MySQL | MySQL 8 / MariaDB 10.6+ |
| Web server | `php artisan serve` | Nginx/Apache (Linux), Apache (Windows), nginx container (Docker) |
| Composer | 2.x | 2.x |
| Node.js | **Tidak perlu** (CSS/JS CDN) | **Tidak perlu** |
| Resource | – | ≥ 1 GB RAM (PHP+MySQL), ≥ 2 GB disk termasuk backup |

---

## Instalasi Lokal

### Langkah (one-liner)

```bash
git clone <repo-url>
cd inventory-asset
composer run setup     # install + .env + key + migrate
php artisan db:seed    # buat akun default + master data
php artisan serve      # atau: composer run dev
```

### Langkah (manual)

```bash
git clone <repo-url>
cd inventory-asset

composer install
copy .env.example .env          # Windows: copy · Linux/macOS: cp
php artisan key:generate

# SQLite (paling mudah):
#   .env → DB_CONNECTION=sqlite
#   buat file kosong: database/database.sqlite

# MySQL:
#   .env → DB_CONNECTION=mysql, DB_DATABASE=..., DB_USERNAME=..., DB_PASSWORD=...

php artisan migrate --seed
php artisan serve
```

Akses `http://localhost:8000`.

### Catatan development

- Sebelum test: `php artisan optimize:clear` (config cache mengganggu testing)  
  — atau cukup `composer run test` (sudah include clear).
- Log real-time: `composer run dev:logs`
- Queue (email): `composer run dev:queue`
- **419 saat login di HTTP?** → `SESSION_SECURE_COOKIE=false` di `.env`

---

## Referensi Variabel `.env`

| Variabel | Fungsi | Local | LAN (HTTP) | Public (HTTPS) |
|----------|--------|-------|------------|----------------|
| `APP_ENV` | Environment | `local` | `production` | `production` |
| `APP_DEBUG` | Tampilkan stack trace | `true` | `false` | `false` |
| `APP_URL` | URL absolut (QR, mail, CORS) | `http://localhost:8000` | `http://192.168.1.10` | `https://domain-anda.com` |
| `APP_KEY` | Enkripsi | `php artisan key:generate` | idem | idem (**jangan pernah sama dengan lokal**) |
| `DB_CONNECTION` | Driver DB | `sqlite` / `mysql` | `mysql` | `mysql` |
| `DB_HOST` | Host DB | `127.0.0.1` | `127.0.0.1` | `127.0.0.1` |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | Kredensial DB | – | – | **password kuat, bukan kosong** |
| `SESSION_DRIVER` | Penyimpanan session | `database` | `database` | `database` |
| `SESSION_ENCRYPT` | Enkripsi isi session | `true` | `true` | `true` |
| `SESSION_SECURE_COOKIE` | Cookie hanya via HTTPS | `false` (HTTP) | `false` (HTTP) / `true` (HTTPS mkcert) | **`true`** |
| `SESSION_DOMAIN` | Domain cookie | `null` | `null` | `null` (atau `.domain.com` jika subdomain) |
| `CACHE_STORE` | Cache | `database` | `database` | `database` / `file` / `redis` |
| `QUEUE_CONNECTION` | Antrean email | `database` | `database` | `database` |
| `MAIL_MAILER` | Pengiriman email | `log` | `smtp` (opsional) | `smtp` |
| `LOG_LEVEL` | Level log | `debug` | `warning` | `error` |
| `SENTRY_DSN` | Error tracking | kosong | kosong | opsional |
| `HTTP_PORT` | Port nginx **Docker saja** | – | – | `80` (default), ubah jadi `8080` bila port bentrok |
| `DB_ROOT_PASSWORD` | Root MySQL **Docker saja** | – | – | wajib diisi saat pakai Docker |

**Jebakan umum:**

- `SESSION_SECURE_COOKIE=true` di HTTP → **419 Page Expired** saat login.
- `APP_URL` salah → QR code & URL di email salah sasaran; CORS menolak origin lain.
- `APP_DEBUG=true` di produksi → kebocoran data sensitif ke pengunjung.

---

## Server Produksi Linux (Ubuntu/Debian)

Panduan langkah demi langkah. Contoh: aplikasi di `/var/www/inventaris-aset`, domain `domain-anda.com`.

### 1. Install perangkat lunak

```bash
sudo apt update && sudo apt upgrade -y

# PHP 8.2 — Ubuntu 22.04 default PHP 8.1, tambahkan PPA ini dulu.
# (Ubuntu 24.04 sudah PHP 8.3 — boleh lewati PPA, PHP >= 8.2 tetap lolos.)
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update

sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-sqlite3 \
  php8.2-mbstring php8.2-xml php8.2-curl php8.2-bcmath php8.2-zip \
  php8.2-gd php8.2-intl
php -v                              # harus 8.2+

# MySQL
sudo apt install -y mysql-server

# Web server — pilih salah satu
sudo apt install -y nginx           # opsi A (disarankan)
# sudo apt install -y apache2 libapache2-mod-php8.2   # opsi B

# Composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
composer --version
```

### 2. Database

```sql
CREATE DATABASE inventaris_aset CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'assetms'@'localhost' IDENTIFIED BY 'GANTI-DENGAN-PASSWORD-KUAT';
GRANT ALL PRIVILEGES ON inventaris_aset.* TO 'assetms'@'localhost';
FLUSH PRIVILEGES;
```

(Masuk MySQL: `sudo mysql`, keluar: `exit`.)

### 3. Upload aplikasi + dependency

```bash
sudo mkdir -p /var/www/inventaris-aset
sudo chown -R $USER:$USER /var/www/inventaris-aset
cd /var/www/inventaris-aset

# Pilih salah satu: git clone, rsync, atau upload archive
git clone <repo-url> .

composer install --optimize-autoloader --no-dev
```

### 4. `.env` produksi

```bash
cp .env.example .env
php artisan key:generate
```

Isi minimal:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com        # lihat Mode Akses bila IP LAN
SESSION_SECURE_COOKIE=true             # true hanya jika HTTPS aktif
SESSION_ENCRYPT=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventaris_aset
DB_USERNAME=assetms
DB_PASSWORD=GANTI-DENGAN-PASSWORD-KUAT

QUEUE_CONNECTION=database
CACHE_STORE=database
MAIL_MAILER=smtp                       # + kredensial SMTP agar notifikasi jalan
LOG_LEVEL=error
```

### 5. Migrasi, seed, storage

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link                # arsip PDF dokumen SOP
```

### 6. Permission folder

```bash
sudo chown -R www-data:www-data /var/www/inventaris-aset
sudo chmod -R 775 /var/www/inventaris-aset/storage /var/www/inventaris-aset/bootstrap/cache
```

### 7a. Web server — Nginx (disarankan)

```bash
sudo nano /etc/nginx/sites-available/inventaris-aset
```

```nginx
server {
    listen 80;
    server_name domain-anda.com;                 # atau IP LAN
    root /var/www/inventaris-aset/public;         # ← harus folder public/
    index index.php;
    charset utf-8;

    client_max_body_size 3m;                      # CSV import 2MB + overhead

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_read_timeout 120;
    }

    location ~ /\.(?!well-known).* { deny all; }

    gzip on;
    gzip_types text/plain text/css application/json application/javascript
               text/xml application/xml image/svg+xml;
}
```

```bash
sudo ln -s /etc/nginx/sites-available/inventaris-aset /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

> Apache? Lihat konfigurasi `AllowOverride All` di [Server Produksi Windows](#server-produksi-windows-apache) — prinsipnya sama, `public/.htaccess` harus diizinkan (mod_rewrite + mod_headers aktif).

### 7b. Web server — Apache (alternatif)

```bash
sudo a2enmod rewrite headers expires deflate
sudo nano /etc/apache2/sites-available/inventaris-aset.conf
```

```apache
<VirtualHost *:80>
    ServerName domain-anda.com
    DocumentRoot /var/www/inventaris-aset/public

    <Directory /var/www/inventaris-aset/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/inventaris-aset-error.log
    CustomLog ${APACHE_LOG_DIR}/inventaris-aset-access.log combined
</VirtualHost>
```

```bash
sudo a2ensite inventaris-aset && sudo a2dissite 000-default
sudo systemctl reload apache2
```

### 8. Firewall

```bash
sudo apt install -y ufw
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'          # atau 'Apache Full'
sudo ufw enable
sudo ufw status
```

Untuk **LAN-only** (tidak dibuka ke internet), batasi ke subnet saja — lihat [Mode Akses](#mode-akses-local-lan-dan-public).

### 9. HTTPS (domain publik)

```bash
sudo apt install -y certbot python3-certbot-nginx     # atau python3-certbot-apache
sudo certbot --nginx -d domain-anda.com -d www.domain-anda.com
# sertifikat auto-renew; uji: sudo certbot renew --dry-run
```

Setelah HTTPS jalan: `SESSION_SECURE_COOKIE=true` dan `APP_URL=https://domain-anda.com`.  
Untuk **IP tanpa domain**, pakai [mkcert](#https-internal-lan-mkcert).

### 10. Queue worker (Supervisor)

```bash
sudo apt install -y supervisor
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

```ini
[program:laravel-worker]
command=php /var/www/inventaris-aset/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/inventaris-aset/storage/logs/queue.log
```

```bash
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
sudo supervisorctl status
```

### 11. Scheduler (`logs:purge`)

```bash
sudo crontab -e
```

```cron
* * * * * cd /var/www/inventaris-aset && php artisan schedule:run >> /dev/null 2>&1
```

### 12. Cache & verifikasi

```bash
composer run cache                      # view + config + route cache
php artisan optimize                     # + class + event cache

curl -I https://domain-anda.com/up      # harus HTTP 200
```

Selengkapnya di [Checklist Pasca-Deploy](#checklist-pasca-deploy) dan [MAINTENANCE.md](MAINTENANCE.md).

---

## Server Produksi Windows (Apache)

Untuk Windows 10/11 maupun Windows Server. Contoh lokasi aplikasi: `C:\inventory-asset`, Apache di `C:\Apache24`, PHP di `C:\php`.

### 1. Install PHP 8.2 (Thread Safe x64)

1. Unduh **PHP 8.2.x VC17 x64 Thread Safe** dari <https://windows.php.net/download/> → ekstrak ke `C:\php`.
2. Salin `C:\php\php.ini-development` → `php.ini`, lalu edit:

```ini
extension_dir = "ext"
extensions = curl
             bcmath
             fileinfo
             gd
             mbstring
             openssl
             pdo_mysql
             pdo_sqlite
             zip
```

> `xml`, `tokenizer`, `json`, `ctype` sudah aktif/ter-compile di PHP 8.2.

3. Tambahkan `C:\php` ke **PATH** sistem, lalu cek: `php -v`.

### 2. Install Apache 2.4 (x64)

1. Unduh **httpd-2.4.x-win64-VS17** dari <https://www.apachelounge.com/download/> → ekstrak ke `C:\Apache24`.
2. Daftarkan sebagai **Windows Service** (buka CMD as Administrator):

```bat
cd C:\Apache24\bin
httpd -k install
httpd -t
net start Apache2.4
```

### 3. Aktifkan modul yang dibutuhkan

Edit `C:\Apache24\conf\httpd.conf`, pastikan baris berikut tidak dikomentari (`#` di depan):

```apache
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule headers_module modules/mod_headers.so
LoadModule expires_module modules/mod_expires.so
LoadModule deflate_module modules/mod_deflate.so
LoadModule ssl_module modules/mod_ssl.so          # hanya untuk HTTPS/mkcert
```

Daftarkan module PHP di baris paling bawah `httpd.conf`:

```apache
PHPIniDir "C:/php"
LoadModule php_module "C:/php/php8apache2_4.dll"
AddType application/x-httpd-php .php
```

### 4. Virtual host (DocumentRoot → `public/`)

Di `httpd.conf`, set `DocumentRoot` **atau** (disarankan) buat vhost terpisah di `conf/extra/httpd-vhosts.conf` dan aktifkan `Include conf/extra/httpd-vhosts.conf`:

```apache
Listen 80

<VirtualHost *:80>
    ServerName localhost
    DocumentRoot "C:/inventory-asset/public"

    <Directory "C:/inventory-asset/public">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "C:/Apache24/logs/inventaris-error.log"
    CustomLog "C:/Apache24/logs/inventaris-access.log" common
</VirtualHost>
```

> **Penting:** `DocumentRoot` harus folder `public/`, **bukan** root project. `AllowOverride All` wajib agar `public/.htaccess` (rewrite + security header) aktif.

```bat
httpd -t
net stop Apache2.4 && net start Apache2.4
```

### 5. Aplikasi Laravel di Windows

```bat
cd C:\inventory-asset
composer install --optimize-autoloader --no-dev
copy .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
composer run cache
```

Isi `.env` mengikuti [Referensi `.env`](#referensi-variabel-env) (produksi: `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true` hanya jika HTTPS).

### 6. Firewall Windows (izinkan port 80)

```bat
netsh advfirewall firewall add rule name="AssetMS HTTP" dir=in action=allow protocol=TCP localport=80
```

Untuk akses LAN-only, tambahkan `remoteip`:

```bat
netsh advfirewall firewall add rule name="AssetMS LAN" dir=in action=allow protocol=TCP localport=80 remoteip=192.168.1.0/24
```

### 7. Queue worker — NSSM (Supervisor versi Windows)

Supervisor tidak tersedia di Windows; pakai [NSSM](https://nssm.cc/) (Non-Sucking Service Manager):

```bat
nssm install AssetMSQueue "C:\php\php.exe" "C:\inventory-asset\artisan" queue:work --sleep=3 --tries=3 --max-time=3600
nssm set AssetMSQueue AppDirectory "C:\inventory-asset"
nssm start AssetMSQueue
nssm status AssetMSQueue
```

Alternatif tanpa NSSM — Task Scheduler (echo on-fail):

```bat
schtasks /create /tn "AssetMS Queue" /tr "\"C:\php\php.exe\" C:\inventory-asset\artisan queue:work" /sc hourly /ru SYSTEM
```

### 8. Scheduler — Task Scheduler (`logs:purge`)

```bat
schtasks /create /tn "AssetMS Scheduler" /tr "\"C:\php\php.exe\" C:\inventory-asset\artisan schedule:run" /sc minute /mo 1 /ru SYSTEM
```

### 9. Verifikasi

```bat
curl -I http://localhost/up
```

Buka `http://<IP-_SERVER>/` dari komputer lain di jaringan (pastikan firewall sudah izinkan — lihat [Mode Akses](#mode-akses-local-lan-dan-public)).

### Alternatif cepat (non-produksi)

Untuk demo/ujian cepat tanpa Apache:

```bat
php artisan serve --host=0.0.0.0 --port=8000
```

Akses dari device lain: `http://<IP-_SERVER>:8000`. Bukan untuk produksi (1 proses, tanpa HTTPS, tanpa queue otomatis).

---

## Mode Akses Local, LAN, dan Public

| Aspek | Local | LAN kantor | Public |
|-------|-------|-----------|--------|
| URL akses | `http://localhost:8000` | `http://192.168.1.10` (IP server) | `https://domain-anda.com` |
| `APP_URL` | `http://localhost:8000` | `http://192.168.1.10` | `https://domain-anda.com` |
| `SESSION_SECURE_COOKIE` | `false` | `false` (HTTP) / `true` (mkcert HTTPS) | **`true`** |
| Domain DNS | tidak perlu | tidak perlu | wajib (A record → IP server) |
| Port dibuka (firewall) | – | 80 (+443) **hanya subnet LAN** | 80 + 443 publik |
| HTTPS | tidak perlu | opsional (mkcert) — **wajib agar kamera scanner jalan** | wajib (Let's Encrypt) |
| Router | tidak perlu | tidak perlu | port-forward 80/443 → server |

### Akses LAN langkah demi langkah

1. **Cari IP server:**
   - Windows: `ipconfig` (kolom IPv4 Address)
   - Linux: `ip a` atau `hostname -I`

2. **Izinkan firewall hanya untuk subnet LAN** (contoh `192.168.1.0/24`):

   ```bash
   # Linux (ufw)
   sudo ufw allow from 192.168.1.0/24 to any port 80 proto tcp
   sudo ufw allow from 192.168.1.0/24 to any port 443 proto tcp
   ```

   ```bat
   :: Windows
   netsh advfirewall firewall add rule name="AssetMS LAN" dir=in action=allow protocol=TCP localport=80 remoteip=192.168.1.0/24
   ```

3. **Set `.env`:**

   ```env
   APP_URL=http://192.168.1.10
   SESSION_SECURE_COOKIE=false        # selama masih HTTP
   APP_DEBUG=false
   ```

   Lalu: `php artisan optimize:clear && composer run cache`.

4. **Test dari device lain** (HP/laptop): buka `http://192.168.1.10` → halaman login muncul.

5. **Login error 419?** → `SESSION_SECURE_COOKIE` masih `true` di HTTP. Set `false` (atau pasang [mkcert](#https-internal-lan-mkcert) supaya bisa tetap `true`).

**Catatan:**

- Jangan buka port 80 ke publik jika memang hanya untuk LAN — cukup aturan firewall per-subnet seperti di atas.
- Matikan *AP isolation*/*client isolation* di Wi-Fi kantor bila device tidak saling terlihat.
- Scanner kamera (login & `/track`) **tidak jalan di HTTP per-device** — browser memblokir kamera di origin non-HTTPS. Solusinya [mkcert](#https-internal-lan-mkcert).

---

## HTTPS Internal LAN (mkcert)

`mkcert` membuat sertifikat TLS lokal yang dipercaya komputer/HP di jaringan kamu — tanpa domain, tanpa internet. **Syarat scanner kamera (getUserMedia) adalah secure context (HTTPS/localhost)**, jadi ini wajib bila ingin scan QR dari HP lewat LAN.

### 1. Install mkcert (di mesin admin/PC dev)

- Windows: `choco install mkcert` (atau unduh dari <https://github.com/FiloSottile/mkcert/releases>)
- Linux: `sudo apt install mkcert` / `brew install mkcert`
- macOS: `brew install mkcert`

### 2. Buat & pasang Certificate Authority lokal

```bash
mkcert -install        # membuat & memasang root CA ke trust store mesin ini
mkcert 192.168.1.10    # hasil: 192.168.1.10.pem dan 192.168.1.10-key.pem
```

(Ganti IP dengan IP server LAN-mu; bisa juga beberapa nama: `mkcert 192.168.1.10 assetms.local`.)

### 3. Pasang `rootCA.pem` di setiap client

Tanpa langkah ini, browser HP/laptop lain akan menolak sertifikatnya.

- **Windows (11/10):** buka PowerShell **Admin** →  
  `certutil -addstore -f Root rootCA.pem`  
  (atau klik kanan `rootCA.pem` → Install Certificate → **Trusted Root Certification Authorities**)
- **Android:** Settings → Security → *Encryption & credentials* → *Install a certificate* → **CA certificate** → pilih `rootCA.pem` (nama file mungkin perlu diubah jadi `.crt`)
- **macOS:** `sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain rootCA.pem`
- **iOS/iPadOS:** kirim `rootCA.pem` ke device (AirDrop/Mail) → instal profile → Settings → General → About → Certificate Trust Settings → aktifkan

### 4. Vhost SSL di server

**Apache Linux** (`a2enmod ssl` dulu):

```apache
<VirtualHost *:443>
    ServerName 192.168.1.10
    DocumentRoot /var/www/inventaris-aset/public

    SSLEngine on
    SSLCertificateFile "/etc/ssl/mkcert/192.168.1.10.pem"
    SSLCertificateKeyFile "/etc/ssl/mkcert/192.168.1.10-key.pem"

    <Directory /var/www/inventaris-aset/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Apache Windows** (mod_ssl sudah diaktifkan di langkah 3):

```apache
Listen 443

<VirtualHost *:443>
    ServerName 192.168.1.10
    DocumentRoot "C:/inventory-asset/public"

    SSLEngine on
    SSLCertificateFile "C:/certs/192.168.1.10.pem"
    SSLCertificateKeyFile "C:/certs/192.168.1.10-key.pem"

    <Directory "C:/inventory-asset/public">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Nginx: `ssl_certificate` + `ssl_certificate_key` di server block 443 (`listen 443 ssl;`).

### 5. `.env` mode HTTPS-LAN

```env
APP_URL=https://192.168.1.10
SESSION_SECURE_COOKIE=true
```

Lalu `php artisan optimize:clear && composer run cache`, restart web server.

### 6. Verifikasi

Buka `https://192.168.1.10` dari device yang sudah pasang root CA → **tidak ada peringatan sertifikat**, login lancar, **kamera scanner aktif**.

---

## Deployment Docker

Untuk yang ingin setup seragam (dev tim, server internal, atau produksi kecil) tanpa install PHP/MySQL manual. Butuh **Docker Engine + Docker Compose** (Docker Desktop untuk Windows/Mac).

### Service yang dibuat

| Service | Gambar | Peran |
|---------|--------|-------|
| `app` | build `Dockerfile` (php:8.2-fpm) | PHP-FPM, menjalankan aplikasi |
| `nginx` | `nginx:1.27-alpine` | Web server → `docker/nginx/default.conf` (root ke `public/`) |
| `db` | `mysql:8.0` | MySQL 8 + healthcheck + volume data |
| `queue` | build sama dengan `app` | `php artisan queue:work` (notifikasi email) |
| `scheduler` | build sama dengan `app` | `php artisan schedule:work` (`logs:purge` harian) |

### Langkah

```bash
cp .env.example .env
```

Edit `.env` untuk Docker:

```env
DB_CONNECTION=mysql
DB_HOST=db                        # akan dioverride otomatis oleh compose
DB_DATABASE=inventoryasset_kbn
DB_USERNAME=inventoryasset        # JANGAN 'root' (MySQL image membuat user terpisah)
DB_PASSWORD=password-anda
DB_ROOT_PASSWORD=password-root-anda   # wajib (compose menolak jika kosong)

APP_URL=http://localhost           # atau http://<IP-LAN> / https://domain
SESSION_SECURE_COOKIE=false        # true jika sudah HTTPS
```

```bash
docker compose up -d --build

# Inisialisasi aplikasi (sekali di awal)
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan storage:link
docker compose exec app composer run cache
```

Buka **http://localhost** (atau `http://localhost:8080` bila set `HTTP_PORT=8080`; port 80 bisa bentrok dengan XAMPP/Apache lokal).

### Perintah sehari-hari

```bash
docker compose ps                      # status semua service
docker compose logs -f nginx           # log web server
docker compose logs -f queue           # log worker notifikasi
docker compose exec app php artisan migrate --force   # update schema
docker compose exec app php artisan tinker
docker compose restart app nginx       # restart setelah ubah .env
docker compose down                    # berhenti (data MySQL tetap di volume)
docker compose down -v                 # berhenti + HAPUS data MySQL (hati-hati!)
```

### Catatan Docker

- Data MySQL tersimpan di named volume `db-data` — aman saat `docker compose down`.
- Folder `storage/` (arsip PDF) menempel ke host lewat bind mount `./:/var/www`.
- **Linux + permission denied di `storage/`:** `chmod -R ug+rwx storage bootstrap/cache` (container menulis sebagai www-data/UID berbeda dari host).
- Backup tetap perlu — lihat [MAINTENANCE.md](MAINTENANCE.md) (dump volume + `storage/app/public`).
- Produksi di belakang domain + HTTPS: tambahkan sertifikat ke nginx container (mount `fullchain.pem`/`privkey.pem` + `listen 443 ssl`) atau pakai reverse proxy (Traefik/Caddy) — konfigurasikan `APP_URL=https://...` & `SESSION_SECURE_COOKIE=true`.

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
| `composer run setup` | Install + `.env` + `key:generate` + migrate (sekali jalan) |
| `php artisan db:seed` | Seed permission, akun default, master data |
| `composer run dev` | Dev server |
| `composer run dev:queue` | Queue worker (notifikasi) |
| `composer run dev:logs` | Monitor log |
| `composer run test` | Clear cache + jalankan **193 tests** |
| `php artisan migrate:fresh --seed` | Reset DB + seed |
| `php artisan db:seed --class=PermissionSeeder` | Seed ulang permission |

### Produksi

| Command | Fungsi |
|---------|--------|
| `composer run cache` | view + config + route cache |
| `php artisan optimize:clear` | Hapus semua cache |
| `php artisan migrate --force` | Migrasi production |
| `php artisan db:seed --force` | Seed production |
| `php artisan storage:link` | Link upload/arsip PDF |
| `php artisan logs:purge` | Paksa buang log soft-deleted > 30 hari (normalnya otomatis via cron) |
| `composer install --no-dev --optimize-autoloader` | Dependency production |
| `curl -I <APP_URL>/up` | Health check (harus 200) |

---

## Troubleshooting Umum

| Gejala | Penyebab paling umum | Solusi |
|--------|----------------------|--------|
| **419** saat login | `SESSION_SECURE_COOKIE=true` di HTTP | `SESSION_SECURE_COOKIE=false` (local/LAN) / pakai HTTPS (prod) |
| **419** dari device LAN | Sama, tapi `.env` server yang diubah lokal saja | Pastikan `.env` **di server** yang diedit, lalu `optimize:clear` |
| **500** di `/api/assets` | `laravel/sanctum` belum terpasang | Lihat [REST API](#rest-api): `composer require laravel/sanctum` |
| Dashboard chart kosong | Tag `<script>` Chart.js tidak ditutup / SRI salah | Pastikan `</script>` CDN + hash `integrity` cocok |
| Test gagal koneksi MySQL 127.0.0.1 | Config cache lama (`DB_CONNECTION=mysql`) menimpa `phpunit.xml` | `php artisan optimize:clear` (atau hapus `bootstrap/cache/*.php`) lalu `composer run test` |
| `optimize:clear` error "connection refused" | `CACHE_STORE=database` tapi MySQL tidak jalan | Nyalakan MySQL, atau set `CACHE_STORE=file` sementara, atau hapus manual `bootstrap/cache/*.php` |
| 404 semua halaman (Nginx) | `root` menunjuk folder project, bukan `public` | `root .../public;` — lihat konfigurasi Nginx di atas |
| 403 / rewrite tidak jalan (Apache) | `AllowOverride All` belum diset | Aktifkan di `<Directory .../public>` + `a2enmod rewrite` |
| Icon tidak muncul | Bootstrap Icons CDN belum ada di layout | Cek `<link>` di `layouts/app.blade.php` |
| Email tidak terkirim | Queue worker mati / `MAIL_MAILER=log` | `composer run dev:queue` / Supervisor (Linux) / NSSM (Windows) / service `queue` (Docker) |
| Scanner kamera tidak bisa dibuka | Origin HTTP (bukan secure context) | Pasang [mkcert](#https-internal-lan-mkcert) atau akses via HTTPS |
| Tombol maintenance mati di modal | Script inline tidak jalan (innerHTML) | Pakai `public/js/asset-maintenance.js` (delegation) |
| Permission tidak muncul di User Mgmt | Belum di `PermissionSeeder::GROUPS` | Tambah + seed ulang |
| Log terhapus menumpuk | Scheduler (`logs:purge`) tidak jalan | Cek cron/schedule:run, Task Scheduler, atau service `scheduler` Docker |
| `docker compose` gagal: `DB_PASSWORD ... is required` | `.env` belum isi `DB_PASSWORD`/`DB_ROOT_PASSWORD` | Isi keduanya (wajib untuk MySQL container) |

---

## Checklist Pasca-Deploy

- [ ] `GET /up` → **200 OK** (dari dalam server maupun luar)
- [ ] Login halaman muncul (via HTTPS / IP LAN sesuai mode)
- [ ] Login admin berhasil, dashboard + grafik normal
- [ ] Ganti password default admin & staff
- [ ] `APP_DEBUG=false` (404 biasa, bukan stack trace)
- [ ] `SESSION_SECURE_COOKIE` sesuai mode akses (lihat [tabel mode](#mode-akses-local-lan-dan-public))
- [ ] Firewall hanya membuka port yang diperlukan (80/443, subnet benar)
- [ ] Queue worker **RUNNING** (Supervisor / NSSM / container `queue`)
- [ ] Scheduler jalan (cron / Task Scheduler / container `scheduler`) — cek `logs:purge`
- [ ] `php artisan storage:link` ada → PDF dokumen SOP bisa dibuka
- [ ] `composer run cache` sudah dijalankan
- [ ] Scan barcode dari HP berfungsi (butuh HTTPS)
- [ ] Backup DB & `storage/app/public` terjadwal (lihat [MAINTENANCE.md](MAINTENANCE.md))
- [ ] Log tidak menumpuk error (`storage/logs/laravel.log`)

---

## Maintenance

Update & restart:

```bash
# Linux / Docker host
cd /var/www/inventaris-aset
git pull
composer install --optimize-autoloader --no-dev
php artisan optimize:clear
composer run cache
sudo supervisorctl restart laravel-worker:*
tail -f storage/logs/laravel.log

# Docker
docker compose exec app composer install --optimize-autoloader --no-dev
docker compose exec app php artisan optimize:clear
docker compose exec app composer run cache
docker compose up -d --build
```

Detail operasional — **queue (Linux/Windows/Docker), backup & restore, rollback, monitoring log, security hardening, performa**: **[MAINTENANCE.md](MAINTENANCE.md)**  
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
