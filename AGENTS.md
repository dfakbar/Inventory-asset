# AGENTS.md - Inventory Asset Management

## Commands
- Development: `composer run dev` (php artisan serve)
- Queue worker: `composer run dev:queue`
- Log viewer: `composer run dev:logs`
- Clear + optimize: `php artisan optimize:clear && php artisan optimize`
- Cache all: `composer run cache`
- Migrate + seed: `php artisan migrate:fresh --seed`
- Run tests: `composer run test`

## Security Checklist
- [x] XSS flash messages fixed (`{!! !!}` → `{{ }}` in app.blade.php, login.blade.php)
- [x] `APP_DEBUG=false`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` in `.env`
- [x] Rate limiting (`throttle`) added to all POST/PUT/PATCH routes
- [x] CORS config published (restrictive: only APP_URL allowed)
- [x] FormRequest `authorize()` methods now check permissions (defense-in-depth)
- [x] CSV import: per-cell type validation + sanitized error messages
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
