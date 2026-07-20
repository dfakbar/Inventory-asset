# AGENTS.md - Inventory Asset Management

## Commands
- Development: `composer run dev` (php artisan serve)
- Queue worker: `composer run dev:queue`
- Log viewer: `composer run dev:logs`
- Clear + optimize: `php artisan optimize:clear && php artisan optimize`
- Cache all: `composer run cache`
- Migrate + seed: `php artisan migrate:fresh --seed`
- Seed permissions only: `php artisan db:seed --class=PermissionSeeder`
- Run tests: `composer run test`

## Security Checklist
- [x] XSS flash messages fixed (`{!! !!}` → `{{ }}` in app.blade.php, login.blade.php)
- [x] `APP_DEBUG=false`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` in `.env`
- [x] Rate limiting (`throttle`) added to all POST/PUT/PATCH routes
- [x] CORS config published (restrictive: only APP_URL allowed)
- [x] FormRequest `authorize()` methods now check permissions (defense-in-depth)
- [x] CSV import: per-cell type validation + sanitized error messages
- [x] Bootstrap Icons CDN added to `app.blade.php` (was missing, icon tidak muncul)
- [ ] Review DB password — currently empty root password in `.env`

## Optimization Checklist
- [x] Remove unused Composer dependencies (removed `laravel/sail`, `laravel/pint`)
- [x] Remove unused NPM dependencies (Tailwind, Alpine, Axios, Vite — app uses Bootstrap CDN)
- [x] Remove stale files (AssetLocation model/seeder, ProfileController, stale views)
- [x] Eager-loading optimization (fixed Dashboard N+1, removed unused loads)
- [x] Database query optimization (added indexes migration for FK columns)
- [x] Bug fixes (LoanController checkin/store, CSV import, validation, null safety, API auth, employee mutation log, notification to previous PIC)
- [x] CSV import: vendor_id mapping + MAC format validation + SN unique check + per-row transaction + template download
- [x] View/Blade caching (`php artisan view:cache` — via `composer run cache`)
- [x] Config caching (`php artisan config:cache` — via `composer run cache`)
- [x] Route caching (`php artisan route:cache` — via `composer run cache`)
- [x] Queue worker for notifications
- [x] Asset minification (all CSS/JS via CDN with SRI + gzip)
- [x] Security headers (.htaccess: HSTS, X-Frame-Options, X-Content-Type-Options, etc.)
- [x] Error monitoring via Sentry (config ready, needs DSN in .env)

## Production Caching
Before deployment, run:
```
composer run cache
```

⚠️ Run `php artisan optimize:clear` before running tests — cached config interferes with test environment.

## Email Notifications (Enabled)
Notification logic in `AssetObserver::updated()` is now **active**.
To make it work:
1. Set `MAIL_MAILER=smtp` (or mailgun/sendmail) in `.env`
2. Configure SMTP credentials
3. Run queue worker: `php artisan queue:work`

Notifications (`AssetMutationNotification`) are sent to **all admin users** and the **current PIC** when any asset mutation occurs (location, status, PIC, or employee changes). The user who performed the mutation does not receive a notification.

## Login by Username / Email
- Login menerima **username** ATAU **email** — deteksi otomatis via ada/tidaknya karakter `@`
- Jika input mengandung `@` → cari user by `email`
- Jika input tanpa `@` → cari user by `username`
- Kolom `username` diatur oleh Admin saat create/edit user
- Validasi `alpha_dash` (hanya huruf, angka, strip, underscore)
- User yang dinonaktifkan (`is_active = false`) tetap ditolak login

## Disable User / Employee
- Kolom `is_active` (boolean, default true) pada tabel `users` dan `employees`
- User yang dinonaktifkan **tidak bisa login** — ditolak dengan pesan "Akun Anda telah dinonaktifkan."
- Employee yang dinonaktifkan **tidak muncul** di dropdown pemilihan aset
- Hanya **Administrator** yang bisa toggle aktif/nonaktif
- Guard: admin tidak bisa menonaktifkan akun sendiri
- Tombol toggle di index users (`bi-pause-fill` / `bi-play-fill`) dan employees
- Route: `PATCH /admin/users/{user}/toggle-active`, `PATCH /admin/employees/{employee}/toggle-active`
- Tidak ada permission baru — cukup `isAdmin()`

## Activity & Mutation Logging
- `ActivityLog` model + `activity_logs` table tracks user actions (create/update/delete)
- `LogsActivity` trait can be added to any model to auto-log changes
- API available at `/api/assets` and `/api/assets/{id}` (requires `auth:sanctum`)
- Log viewer pages at `/admin/logs/asset` and `/admin/logs/mutation` (gated with `asset.viewAny`)
- `LogController` handles both log views with search, filter by date & action

## Employee Management (Karyawan Non-System)
- `Employee` model (soft-deletes), `EmployeeController` (full CRUD)
- Routes: `/admin/employees` (under `admin.*` prefix, permission-gated)
- 4 permissions: `employee.viewAny`, `employee.create`, `employee.edit`, `employee.delete`
- Views: `resources/views/admin/employees/{index,create,edit}.blade.php`
- Migrations: `create_employees_table`, `add_employee_id_to_assets_table`, `add_employee_fields_to_asset_mutation_logs_table`
- `employee_id` on `assets` table — separate from `assigned_to` (which tracks PIC system user)
- Mutation log tracks both `assigned_to` (system user) and `employee_id` (karyawan) changes
  - `AssetMutationLog::$fillable` includes `from_employee_id` / `to_employee_id`
- Employee cannot be deleted if still assigned to any asset

## Asset Form Behavior
- **PIC (System)** — hidden input, auto-set to `auth()->id()` (terkunci, tidak bisa dipilih)
- On loan check-in, `assigned_to` is auto-restored to the checking-in user (`auth()->id()`)
- Null-safe operator (`$asset?->status?->value`) used for create form to avoid PHP warnings
- `AssetMutationLog` has relations: `fromEmployee()`, `toEmployee()` for employee mutation tracking
- **Pengguna / Karyawan** — searchable dropdown, bisa dipilih bebas
- **Catatan** — bisa diedit oleh semua user (termasuk staff mutation-only)
- Mutation-only users can change: `location_id`, `mutation_date`, `status`, `assigned_to`, `employee_id`, `notes`
- **MAC Address** — field opsional dengan validasi format (`XX:XX:XX:XX:XX:XX`), tersedia di form, index, detail, CSV

## CSV Import Details
- 14 kolom: `Kode Aset,Nama,Kategori,Merek,Model,Serial Number,MAC Address,Lokasi,Vendor,Status,Tanggal Pembelian,Harga Pembelian,Jumlah,Catatan`
- Validation dilakukan per-cell: kategori (required, must exist), merek (auto-create), vendor (auto-create), status (enum check, default Spare), jumlah (1-9999), harga (>=0), tanggal (parsable), MAC Address (regex `XX:XX:XX:XX:XX:XX`), Serial Number (unique)
- **Per-row transaction** — error 1 baris tidak menggagalkan seluruh batch
- **Null-safe header mapping** — jika kolom tidak ada di CSV header, fallback ke null (tidak pakai index)
- File limit: 2MB, rate limit: 10 req/min, permission: `asset.create`
- Template download di `/reports` via route `assets.import.template`

## Known OOM Protection
- CSV Export: uses `chunk(200)` to stream rows without loading all records into memory
- CSV Import: per-row transaction prevents large batch memory issues
- PDF Reports: uses `chunk(200)` to build HTML rows string, avoiding full Eloquent model collection in memory

## QR / Barcode URL Encoding
- QR Code dan Barcode sekarang encode **URL absolut** ke `route('public.track', ['search' => $asset->asset_code])`
- Saat discan (via HP), langsung membuka halaman `/track?search=AST...` — tanpa login
- Berlaku untuk generate baru; label lama masih encode plain asset_code

## Print Label (1-4)
- Dropdown cetak label di halaman detail aset: pilihan **1–4 label**
- Controller membatasi max 4 (`AssetController::printCode`)
- Tipe: QR Code atau Barcode (Code 128 SVG)

## Known OOM Protection
- CSV Export: uses `chunk(200)` to stream rows without loading all records into memory
- CSV Import: per-row transaction prevents large batch memory issues
- PDF Reports: uses `chunk(200)` to build HTML rows string, avoiding full Eloquent model collection in memory

## Bug Fixes (Latest)
- [x] `UserController::store()` — `username` tidak dikirim ke `User::create()` (CRITICAL)
- [x] `LoginRequest::authenticate()` — null-safety saat user tidak ditemukan sebelum `Auth::attempt()` (MEDIUM)
- [x] `AssetObserver::updating()` — strict comparison `!==` menyebabkan regenerate kode saat edit field apa pun (MEDIUM)
- [x] `AssetObserver::sendMutationNotification()` — performer name fallback `?? 'System'` tidak pernah jalan karena string concatenation (MEDIUM)
- [x] `AssetCategoryObserver` — `->exists()` tidak cek soft-deleted, potensi unique constraint violation (MEDIUM)
- [x] `AssetController::store()` — `assigned_to` tidak di-server-side enforce (MEDIUM)
- [x] `AssetController::importCsv()` — `!empty($harga)` skip harga 0 (LOW)
- [x] `AssetController::importCsv()` — `assigned_to` tidak di-set, aset hasil import punya null PIC (MEDIUM)
- [x] `StoreAssetRequest::employee_id` + `UpdateAssetRequest::employee_id` — tidak validasi `is_active` (LOW)
- [x] `StoreCategoryRequest` + `UpdateCategoryRequest` — abbreviation unique validation tidak account untuk model mutator `strtoupper(trim())` (HIGH)
- [x] `LoanController::store()` — validasi `activeLoans()` di luar transaksi, race condition (MEDIUM)
- [x] `Api/AssetController::index()` — N+1 query, `assignedUser` + `employee` tidak di eager-load (LOW)
- [x] Migration `2026_07_15_043600` — `dropIfExists('asset_locations')` tanpa `Schema::hasTable()` guard (HIGH)
- [x] `config/session.php` — `'encrypt' => env('SESSION_ENCRYPT', false)` diubah jadi `true` (MEDIUM)

## Notes
- `bacon/bacon-qr-code` v3.1.1 — uses SvgImageBackEnd (no GD)
- `picqer/php-barcode-generator` — Code 128 SVG
- `barryvdh/laravel-dompdf` — PDF reports
- Notifications use queue (MailMessage)
- No Laravel Telescope or Debugbar in production
- All CSS/JS from CDN (Bootstrap 5.3.3, Chart.js, Bootstrap Icons)
- Rate limits: 60 req/min (general), 10 req/min (CSV import), 30 req/min (user management)
- 26 permissions total (22 original + 4 employee)
- 30 migrations total
