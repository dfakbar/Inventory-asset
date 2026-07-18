# Sistem Informasi Manajemen Aset — AssetMS

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Database-SQLite%20%2F%20MySQL-blue?logo=mysql&logoColor=white" alt="Database">
  <img src="https://img.shields.io/badge/Tests-103%20passing-brightgreen" alt="Tests">
  <img src="https://img.shields.io/badge/License-MIT-green" alt="License">
</p>

> Aplikasi web manajemen inventaris aset perusahaan berbasis **Laravel 12**, dilengkapi RBAC granular, pelacakan mutasi, notifikasi email, dashboard analitik, QR/Barcode, dan REST API.

---

## Daftar Isi

1. [Fitur Utama](#fitur-utama)
2. [Tech Stack](#tech-stack)
3. [Struktur Proyek](#struktur-proyek)
4. [Sistem Hak Akses](#sistem-hak-akses)
5. [Instalasi Lokal (Development)](#instalasi-lokal-development)
6. [Akun Default](#akun-default)
7. [REST API](#rest-api)
8. [Deploy ke Server Linux (Production)](#deploy-ke-server-linux-production)
9. [Perintah Penting](#perintah-penting)
10. [Maintenance](#maintenance)
11. [Lisensi](#lisensi)

---

## Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| Dashboard Analitik | Grafik status (doughnut), kategori (bar), trend mutasi 6 bulan, log real-time |
| Manajemen Aset | CRUD dengan kode unik otomatis `AST{ABR}{YY}{MM}{SEQ}` |
| Mutasi Aset | Catat perpindahan lokasi/user/status/karyawan dengan tanggal aktual |
| RBAC Granular | 26 permission, 2 role (admin/staff), privasi data finansial |
| Manajemen Karyawan | CRUD data karyawan non-system untuk penugasan aset |
| QR Code & Barcode | Generate & print label aset (SVG QR + Code 128 SVG), QR encode URL tracking publik, Barcode encode kode aset untuk scanner gudang |
| Laporan PDF | Download laporan aset dan kategori (dompdf, landscape A4) |
| CSV Import/Export | Export chunk(200), import per-row transaction + validasi vendor/MAC/SN + download template |
| Check-In/Out | Catat peminjaman aset ke pihak luar, soft-deletes |
| Notifikasi Email | Dikirim via queue saat terjadi mutasi aset (lokasi/status/PIC/karyawan) |
| REST API | Endpoint `/api/assets` & `/api/assets/{id}` dengan pagination |
| Activity Log | Auto-log semua create/update/delete via `LogsActivity` trait + halaman viewer |
| Log Mutasi | Riwayat perpindahan lokasi, PIC, karyawan, dan status aset |
| MAC Address | Kolom opsional untuk mencatat alamat MAC perangkat |
| Error Monitoring | Terintegrasi Sentry untuk tracking error real-time |
| Security Hardening | SRI, HSTS, CSP headers, rate limiting, encrypted sessions |
| Disable User/Employee | Nonaktifkan akun user (tidak bisa login) atau karyawan (tidak bisa dipilih) |
| Login by Username/Email | Login pakai **username** atau **email**, deteksi otomatis berdasarkan input |
| Public Tracking | Halaman `/track` publik untuk lacak aset via kode/serial number, tanpa login |
| Cetak Label | Print QR/Barcode 1-4 label per lembar, dengan link otomatis ke halaman tracking |
| Barcode Scanner | Scan barcode via kamera HP langsung dari halaman login atau halaman `/track`, auto-fill & submit |

---

## Tech Stack

- **Backend**: Laravel 12.x, PHP 8.2+
- **Database**: SQLite (dev) / MySQL (production)
- **Auth**: Session-based (web), Sanctum token (API), Bcrypt rounds=12, encrypted sessions, login via username/email
- **RBAC**: Spatie Laravel Permission v6
- **Frontend**: Bootstrap 5.3.3 (SRI + crossorigin), Bootstrap Icons, Chart.js 4.4
- **Error Tracking**: Sentry (sentry/sentry-laravel)
- **Security Headers**: HSTS, X-Frame-Options, X-Content-Type-Options via `.htaccess`
- **PDF**: barryvdh/laravel-dompdf
- **QR**: bacon/bacon-qr-code (SVG) — encode URL ke `/track?search=...`
- **Barcode**: picqer/php-barcode-generator (Code 128 SVG) — encode asset_code
- **Scanner**: html5-qrcode (WebRTC, scan QR & Code 128 via kamera)
- **Queue**: Database driver
- **Testing**: PHPUnit 11, 103 test cases (270 assertions)

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
│   │   │   ├── LogController.php           # Log viewer (activity + mutation)
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
│       └── AssetMutationNotification.php  # Queueable mail (mutasi)
├── config/
│   ├── cors.php                   # Restrictive CORS
│   ├── permission.php             # Spatie config
│   └── session.php                # Encrypted, HTTP-only, SameSite=Lax
├── database/
│   ├── migrations/                # 30 migrations
│   └── seeders/
│       ├── PermissionSeeder.php   # 26 permissions + 2 roles
│       └── AdminUserSeeder.php
├── routes/
│   ├── web.php                    # 50+ web routes
│   ├── api.php                    # REST API routes
│   └── auth.php                   # Auth routes
├── tests/
│   ├── Unit/                      # 8 unit tests
│   └── Feature/                   # 95 feature tests (103 total)
└── AGENTS.md                      # Panduan maintenance
```

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

## Instalasi Lokal (Development)

### Persyaratan

| Software | Version |
|----------|---------|
| PHP | 8.2 atau lebih baru |
| Composer | 2.x |
| Database | SQLite (bawaan) atau MySQL |
| Node.js | Tidak diperlukan (CSS/JS via CDN) |

### Langkah Instalasi

```bash
# 1. Clone repositori
git clone <repo-url>
cd inventory-aset

# 2. Install PHP dependencies
composer install

# 3. Setup environment
copy .env.example .env
php artisan key:generate

# 4. Konfigurasi database di .env
#    SQLITE (default — paling mudah):
DB_CONNECTION=sqlite
#    lalu buat file database.sqlite di folder database/

#    MYSQL (alternatif):
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventoryasset_kbn
DB_USERNAME=root
DB_PASSWORD=isi_password_kuat

# 5. Migrasi & seed data awal
php artisan migrate
php artisan db:seed

# 6. Jalankan development server
php artisan serve
# atau: composer run dev
```

Akses di `http://localhost:8000`

### Catatan Development

- Sebelum menjalankan test, jalankan `php artisan optimize:clear` agar cached config tidak mengganggu environment test.
- Untuk melihat log: `composer run dev:logs`
- Untuk menjalankan queue worker (notifikasi email): `composer run dev:queue`

---

## Akun Default

| Role | Username | Email | Password |
|------|----------|-------|----------|
| Super Admin | `admin` | admin@company.com | password123 |
| Staff | `staff` | staff@company.com | password123 |

> Ganti password setelah login pertama!

---

## REST API

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/assets` | List aset (paginate 50, filter: search, status, category_id) |
| GET | `/api/assets/{id}` | Detail aset dengan relasi |

Response JSON dengan struktur pagination Laravel standar.

> Semua endpoint API memerlukan **autentikasi via `auth:sanctum`**.

---

## Deploy ke Server Linux (Production)

Panduan langkah demi langkah untuk Production Server (Ubuntu/Debian). Cocok untuk pemula.

### 1. Persyaratan Server

| Software | Version | Cek perintah |
|----------|---------|-------------|
| PHP | 8.2+ | `php -v` |
| MySQL / MariaDB | 8.0+ / 10.3+ | `mysql --version` |
| Composer | 2.x | `composer --version` |
| Web Server | Apache 2.4+ atau Nginx | `apache2 -v` / `nginx -v` |

**Ekstensi PHP wajib:** BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, MySQL (pdo_mysql), Tokenizer, XML, GD, Curl

Install semua ekstensi (Ubuntu 22.04):
```bash
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-common php8.2-mysql \
  php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-gd php8.2-curl \
  composer mysql-server apache2
```

### 2. Upload Project ke Server

**Cara A — Clone dari Git:**
```bash
cd /var/www
git clone <url-repositori> inventaris-aset
```

**Cara B — Upload Manual:**
Upload semua file project ke `/var/www/inventaris-aset` via SCP atau FTP.

### 3. Install Dependency PHP

```bash
cd /var/www/inventaris-aset
composer install --optimize-autoloader --no-dev
```

> `--no-dev` artinya library development tidak diinstall — lebih ringan dan aman.

### 4. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```bash
nano .env
```

Ubah baris berikut:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_DATABASE=inventaris_aset
DB_USERNAME=root
DB_PASSWORD=password_mysql_kuat

SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
```

> **Wajib:** `APP_DEBUG=false` agar error tidak tampil ke pengguna. `SESSION_SECURE_COOKIE=true` memastikan session hanya dikirim lewat HTTPS.

### 5. Setup Database

Buat database MySQL:
```bash
sudo mysql -u root -p
```

Di dalam MySQL:
```sql
CREATE DATABASE inventaris_aset CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

Jalankan migrasi & seeder:
```bash
php artisan migrate --force
php artisan db:seed --force
```

> `--force` diperlukan karena environment sudah production.

### 6. Set Permission Folder

```bash
sudo chown -R www-data:www-data /var/www/inventaris-aset
sudo chmod -R 775 /var/www/inventaris-aset/storage
sudo chmod -R 775 /var/www/inventaris-aset/bootstrap/cache
```

> Web server (user `www-data`) perlu izin tulis di `storage/` dan `bootstrap/cache/`.

### 7. Optimasi Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Di production, Laravel membaca file cache, bukan file asli — lebih cepat.

### 8. Konfigurasi Web Server

#### Opsi A — Apache

Buat virtual host:
```bash
sudo nano /etc/apache2/sites-available/inventaris-aset.conf
```

Isi:
```apache
<VirtualHost *:80>
    ServerName domain-anda.com
    DocumentRoot /var/www/inventaris-aset/public

    <Directory /var/www/inventaris-aset/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/inventaris-aset-error.log
    CustomLog ${APACHE_LOG_DIR}/inventaris-aset-access.log combined
</VirtualHost>
```

Aktifkan:
```bash
sudo a2ensite inventaris-aset
sudo a2enmod rewrite
sudo systemctl reload apache2
```

#### Opsi B — Nginx

Buat file:
```bash
sudo nano /etc/nginx/sites-available/inventaris-aset
```

Isi:
```nginx
server {
    listen 80;
    server_name domain-anda.com;
    root /var/www/inventaris-aset/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan:
```bash
sudo ln -s /etc/nginx/sites-available/inventaris-aset /etc/nginx/sites-enabled/
sudo systemctl reload nginx
```

### 9. Pasang SSL/HTTPS

HTTPS wajib untuk **keamanan data login** dan **mengaktifkan fitur kamera** (scan barcode). Pilih salah satu:

#### 9a. Let's Encrypt — untuk domain publik

Gratis, otomatis, sertifikat berlaku 90 hari (diperpanjang otomatis via cron).

**Apache:**
```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d domain-anda.com
```

**Nginx:**
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d domain-anda.com
```

#### 9b. mkcert — untuk internal server via IP (tanpa domain)

Gunakan jika server hanya bisa diakses via IP lokal (misal `http://172.58.4.220`).

```bash
# Install mkcert
sudo apt install -y libnss3-tools
curl -JLO "https://dl.filippo.io/mkcert/latest?for=linux/amd64"
chmod +x mkcert-v*-linux-amd64
sudo mv mkcert-v*-linux-amd64 /usr/local/bin/mkcert

# Install CA lokal
mkcert -install

# Generate sertifikat untuk IP server
mkcert 172.58.4.220 localhost 127.0.0.1

# Pindahkan ke folder aman
sudo mkdir -p /etc/ssl/mkcert
sudo mv 172.58.4.220+2.pem /etc/ssl/mkcert/cert.pem
sudo mv 172.58.4.220+2-key.pem /etc/ssl/mkcert/key.pem
```

**Konfigurasi Nginx:**
```nginx
server {
    listen 443 ssl;
    server_name 172.58.4.220;

    ssl_certificate     /etc/ssl/mkcert/cert.pem;
    ssl_certificate_key /etc/ssl/mkcert/key.pem;

    root /var/www/inventaris-aset/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Aktifkan:
```bash
sudo ln -s /etc/nginx/sites-available/inventaris-aset /etc/nginx/sites-enabled/
sudo systemctl reload nginx
```

Akses **`https://172.58.4.220`** — browser tampil peringatan sekali, klik **Advanced → Proceed**.

> Dari HP/komputer lain: cukup klik "Proceed to Website" (tidak perlu install CA di tiap perangkat).

### 10. Konfigurasi Email

Notifikasi dikirim saat terjadi mutasi aset ke semua admin dan PIC saat ini.

Edit `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email@gmail.com
MAIL_PASSWORD=password_app_gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

> Gmail: gunakan [App Password](https://myaccount.google.com/apppasswords) (2FA harus aktif).

**Alternatif — Mailgun:**
```
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-mailgun-api-key
MAILGUN_ENDPOINT=api.mailgun.net
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Debug (tidak kirim beneran):**
```
MAIL_MAILER=log
```
Cek di `storage/logs/laravel.log`.

### 11. Queue Worker (Supervisor)

Notifikasi dikirim via antrean. Supervisor menjaga worker tetap jalan 24 jam.

Install Supervisor:
```bash
sudo apt install -y supervisor
```

Buat konfigurasi:
```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Isi:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/inventaris-aset/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/inventaris-aset/storage/logs/worker.log
stopwaitsecs=3600
```

Jalankan:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

Cek status:
```bash
sudo supervisorctl status
# Harus muncul: RUNNING
```

### 12. Cron Job (Penjadwal Tugas)

```bash
sudo crontab -e -u www-data
```

Tambahkan:
```
* * * * * cd /var/www/inventaris-aset && php artisan schedule:run >> /dev/null 2>&1
```

Setiap menit Laravel akan mengecek tugas terjadwal.

### 13. Pengecekan Akhir (Post-Deploy Checklist)

- [ ] Buka `https://domain-anda.com` — apakah muncul halaman login?
- [ ] Login dengan **admin@company.com** / **password123** — apakah dashboard muncul?
- [ ] Cek grafik dan data aset tampil normal
- [ ] Mutasi aset (ganti lokasi/status) — notifikasi email terkirim? (cek log jika pakai `MAIL_MAILER=log`)
- [ ] `sudo supervisorctl status` — harus `RUNNING`
- [ ] `grep CRON /var/log/syslog | tail -5` — cron berjalan?
- [ ] `tail -f /var/www/inventaris-aset/storage/logs/laravel.log` — tidak ada error?
- [ ] Pastikan `APP_DEBUG=false` — akses URL random, tampil 404 biasa (bukan stack trace)
- [ ] Pastikan ada icon gembok HTTPS di browser
- [ ] Test scan barcode — kamera berfungsi?

> **Setelah deploy: segera ganti password default admin dan staff!**

---

## Perintah Penting

### Development

| Command | Fungsi |
|---------|--------|
| `composer run dev` | Jalankan dev server (`php artisan serve`) |
| `composer run dev:queue` | Jalankan queue worker untuk notifikasi |
| `composer run dev:logs` | Monitor log real-time |
| `composer run test` | Jalankan semua test (103 test, 270 assertions) |
| `php artisan optimize:clear` | Clear cache sebelum test |
| `php artisan migrate:fresh --seed` | Reset DB + seed ulang |

### Production

| Command | Fungsi |
|---------|--------|
| `composer run cache` | Cache view + config + routes |
| `php artisan config:cache` | Cache konfigurasi |
| `php artisan route:cache` | Cache route |
| `php artisan view:cache` | Cache blade template |
| `php artisan optimize:clear` | Hapus semua cache |
| `php artisan key:generate` | Regenerate APP_KEY |
| `php artisan storage:link` | Symlink storage |
| `composer install --no-dev` | Install tanpa dev dependencies |
| `sudo supervisorctl restart laravel-worker:*` | Restart queue worker |

---

## Maintenance

```bash
# Masuk folder project
cd /var/www/inventaris-aset

# Update kode (jika pakai git)
git pull

# Update dependency
composer install --optimize-autoloader --no-dev

# Reset & rebuild cache (WAJIB setiap update kode)
php artisan optimize:clear
composer run cache

# Restart queue worker (jika ada perubahan kode terkait queue)
sudo supervisorctl restart laravel-worker:*

# Lihat log aplikasi
tail -f storage/logs/laravel.log
```

---

## Lisensi

MIT License — AssetMS v1.0.0
