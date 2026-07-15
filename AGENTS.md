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
- [x] Bug fixes (LoanController checkin/store, CSV import, validation)
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

## Activity Logging
- `ActivityLog` model + `activity_logs` table tracks user actions (create/update/delete)
- `LogsActivity` trait can be added to any model to auto-log changes
- API available at `/api/assets` and `/api/assets/{id}`

## Employee Management (Karyawan Non-System)
- `Employee` model (soft-deletes), `EmployeeController` (full CRUD)
- Routes: `/admin/employees` (under `admin.*` prefix, permission-gated)
- 4 permissions: `employee.viewAny`, `employee.create`, `employee.edit`, `employee.delete`
- Views: `resources/views/admin/employees/{index,create,edit}.blade.php`
- Migrations: `create_employees_table`, `add_employee_id_to_assets_table`, `add_employee_fields_to_asset_mutation_logs_table`
- `employee_id` on `assets` table — separate from `assigned_to` (which tracks PIC system user)
- Mutation log tracks both `assigned_to` (system user) and `employee_id` (karyawan) changes
- Employee cannot be deleted if still assigned to any asset

## Asset Form Behavior
- **PIC (System)** — hidden input, auto-set to `auth()->id()` (terkunci, tidak bisa dipilih)
- **Pengguna / Karyawan** — searchable dropdown, bisa dipilih bebas
- **Catatan** — bisa diedit oleh semua user (termasuk staff mutation-only)
- Mutation-only users can change: `location_id`, `mutation_date`, `status`, `assigned_to`, `employee_id`, `notes`

## Known OOM Protection
- CSV Export: uses `chunk(200)` to stream rows without loading all records into memory
- PDF Reports: uses `chunk(200)` to build HTML rows string, avoiding full Eloquent model collection in memory

## Notes
- `bacon/bacon-qr-code` v3.1.1 — uses SvgImageBackEnd (no GD)
- `picqer/php-barcode-generator` — Code 128 PNG
- `barryvdh/laravel-dompdf` — PDF reports
- Notifications use queue (MailMessage)
- No Laravel Telescope or Debugbar in production
- All CSS/JS from CDN (Bootstrap 5.3.3, Chart.js, Bootstrap Icons)
- Rate limits: 60 req/min (general), 10 req/min (CSV import), 30 req/min (user management)
- 26 permissions total (22 original + 4 employee)
- 23 migrations total
