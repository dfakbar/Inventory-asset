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

## Konfigurasi Email (Notifikasi Mutasi Aset)

Sistem mengirim notifikasi email saat terjadi mutasi aset (lokasi, status, PIC, atau karyawan berubah). Email dikirim ke **semua admin** dan **PIC saat ini**. User yang melakukan mutasi tidak menerima notifikasi.

### Opsi 1 — SMTP (Gmail / apapun)

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

> Untuk Gmail: gunakan [App Password](https://myaccount.google.com/apppasswords) (2FA harus aktif), bukan password biasa.

### Opsi 2 — Mailgun

```
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-mailgun-api-key
MAILGUN_ENDPOINT=api.mailgun.net
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Opsi 3 — Log (debug, tidak kirim beneran)

```
MAIL_MAILER=log
```

### Jalankan Queue Worker

Notifikasi dikirim antrean (queue), jadi jalankan:

```bash
php artisan queue:work
```

atau via Composer script:

```bash
composer run dev:queue
```

> Pastikan `QUEUE_CONNECTION=database` di `.env` (sudah default).

---

## Deploy ke Server Linux

Panduan langkah demi langkah untuk memasang aplikasi di server production (Ubuntu/Debian). Cocok untuk pemula.

### 1. Persyaratan Server (Server Requirements)

Pastikan server sudah terinstall:

| Software | Version | Cek dengan perintah |
|----------|---------|-------------------|
| PHP | 8.2 atau lebih baru | `php -v` |
| MySQL / MariaDB | 8.0+ / 10.3+ | `mysql --version` |
| Composer | 2.x | `composer --version` |
| Web Server | Apache 2.4+ atau Nginx | `apache2 -v` / `nginx -v` |

**Ekstensi PHP yang wajib ada:** BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, MySQL (pdo_mysql), Tokenizer, XML, GD

Install semua ekstensi (Ubuntu 22.04):
```bash
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-common php8.2-mysql \
  php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-gd php8.2-curl \
  composer mysql-server apache2
```

---

### 2. Upload Project ke Server

**Cara A — Clone dari Git:**
```bash
cd /var/www
git clone <url-repositori> inventaris-aset
```

**Cara B — Upload Manual:**
Upload semua file project ke `/var/www/inventaris-aset` via SCP atau FTP.

---

### 3. Install Dependency PHP

```bash
cd /var/www/inventaris-aset
composer install --optimize-autoloader --no-dev
```

> **Penjelasan:** Perintah ini mengunduh semua library (paket PHP) yang dibutuhkan aplikasi. Bendera `--no-dev` artinya library untuk development tidak diinstall — lebih ringan dan aman untuk production.

---

### 4. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dengan nano/vim:
```bash
nano .env
```

Ubah baris berikut sesuai server kamu:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_DATABASE=inventaris_aset
DB_USERNAME=root
DB_PASSWORD=password_mysql_kuat

SESSION_SECURE_COOKIE=true
```

> **Penjelasan:** File `.env` berisi pengaturan penting aplikasi. Pastikan `APP_DEBUG=false` agar error tidak tampil ke pengguna. `SESSION_SECURE_COOKIE=true` memastikan session hanya dikirim lewat HTTPS.

---

### 5. Setup Database

Buat database MySQL terlebih dahulu:
```bash
sudo mysql -u root -p
```

Di dalam MySQL, jalankan:
```sql
CREATE DATABASE inventaris_aset CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

Jalankan migrasi & seeder (pengisian data awal):
```bash
php artisan migrate --force
php artisan db:seed --force
```

> **Penjelasan:** Migrasi membuat tabel-tabel di database. Seeder mengisi data awal seperti admin, staff, dan permission. Bendera `--force` diperlukan karena environment sudah production.

---

### 6. Set Permission (Izin Akses File)

```bash
sudo chown -R www-data:www-data /var/www/inventaris-aset
sudo chmod -R 775 /var/www/inventaris-aset/storage
sudo chmod -R 775 /var/www/inventaris-aset/bootstrap/cache
```

> **Penjelasan:** `www-data` adalah user khusus milik web server (Apache/Nginx). Tanpa perintah ini, web server tidak bisa menulis file log, upload, atau cache. Folder `storage/` dan `bootstrap/cache/` perlu izin tulis.

---

### 7. Cache Laravel (Optimasi Kecepatan)

```bash
php artisan config:cache   # simpan konfigurasi agar loading lebih cepat
php artisan route:cache    # simpan route agar tidak dibaca ulang tiap request
php artisan view:cache     # simpan template agar tidak dirender ulang
```

> **Penjelasan:** Di production, kita tidak ingin Laravel membaca ulang file konfigurasi, route, dan view setiap kali ada request. Cache ini mempercepat aplikasi secara signifikan.

---

### 8. Konfigurasi Web Server — Apache

Buat file virtual host:
```bash
sudo nano /etc/apache2/sites-available/inventaris-aset.conf
```

Isi dengan:
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

Aktifkan site dan module rewrite, lalu reload:
```bash
sudo a2ensite inventaris-aset
sudo a2enmod rewrite
sudo systemctl reload apache2
```

> **Penjelasan:** VirtualHost seperti "kartu alamat" di server yang menghubungkan domain kamu ke folder aplikasi. `DocumentRoot` harus mengarah ke folder `public/` — itu adalah pintu masuk aplikasi Laravel. `AllowOverride All` mengizinkan file `.htaccess` berfungsi (berguna untuk security headers dan URL bersih).

---

### 9. Konfigurasi Web Server — Nginx (Alternatif)

Jika pakai Nginx, buat file:
```bash
sudo nano /etc/nginx/sites-available/inventaris-aset
```

Isi dengan:
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

> **Catatan:** Jika versi PHP berbeda, sesuaikan `php8.2-fpm.sock` dengan versi PHP yang terinstall. Cek dengan `ls /var/run/php/`.

---

### 10. Pasang SSL/HTTPS (Let's Encrypt)

SSL membuat koneksi ke website terenkripsi (HTTPS), sehingga data login dan data aset tidak bisa dicuri.

**Untuk Apache:**
```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d domain-anda.com
```

**Untuk Nginx:**
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d domain-anda.com
```

> **Penjelasan:** Certbot otomatis mengurus pembuatan sertifikat SSL dan memperbarui konfigurasi web server. Sertifikat berlaku 90 hari dan akan diperpanjang otomatis via cron.

---

### 10b. HTTPS untuk Internal Server (tanpa domain) — via mkcert

Jika server hanya bisa diakses via **IP lokal** (misal `http://172.58.4.220`) dan kamu perlu akses kamera untuk fitur scan barcode, browser mewajibkan HTTPS. Gunakan **mkcert** untuk membuat sertifikat SSL self-signed yang dipercaya browser.

```bash
# 1. Install mkcert di server
sudo apt install -y libnss3-tools
curl -JLO "https://dl.filippo.io/mkcert/latest?for=linux/amd64"
chmod +x mkcert-v*-linux-amd64
sudo mv mkcert-v*-linux-amd64 /usr/local/bin/mkcert

# 2. Install Certificate Authority (CA) lokal
mkcert -install

# 3. Generate sertifikat untuk IP server kamu
mkcert 172.58.4.220 localhost 127.0.0.1
# Hasil: 172.58.4.220+2.pem (cert) dan 172.58.4.220+2-key.pem (key)

# 4. Pindahkan ke folder yang aman
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

Aktifkan site dan reload:
```bash
sudo ln -s /etc/nginx/sites-available/inventaris-aset /etc/nginx/sites-enabled/
sudo systemctl reload nginx
```

Akses: **`https://172.58.4.220`** — browser akan tampil peringatan "Not Secure" sekali, klik **Advanced → Proceed**. Setelah itu kamera bisa dipakai.

> **Catatan:** Untuk mengakses dari HP/komputer lain, Anda perlu menginstal CA certificate (`mkcert -install`) di setiap perangkat, atau cukup klik "Proceed to Website" setiap kali.

---

### 11. Queue Worker (Supervisor) — untuk Notifikasi Email

Notifikasi email dikirim secara antrean (queue). Kita perlu **Supervisor** agar queue worker jalan terus 24 jam tanpa dimatikan.

Install Supervisor:
```bash
sudo apt install -y supervisor
```

Buat file konfigurasi:
```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Isi dengan:
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
```

> **Penjelasan:** Supervisor akan menjalankan 2 proses worker (`numprocs=2`) sebagai user `www-data`. Jika worker crash, Supervisor akan otomatis menyalakan ulang. `--tries=3` artinya setiap job dikirim ulang maksimal 3 kali jika gagal.

---

### 12. Cron Job (Penjadwal Tugas)

Laravel perlu cron untuk tugas terjadwal (misal: pembersihan log, token expired, dll).

Buka crontab:
```bash
sudo crontab -e -u www-data
```

Tambahkan baris ini di akhir file:
```
* * * * * cd /var/www/inventaris-aset && php artisan schedule:run >> /dev/null 2>&1
```

> **Penjelasan:** Setiap menit, Laravel akan mengecek apakah ada tugas terjadwal yang harus dijalankan. `>> /dev/null 2>&1` artinya output tidak ditampilkan di mana pun.

---

### 13. Pengecekan Akhir (Post-Deploy Checklist)

Centang semua item di bawah untuk memastikan server berjalan dengan benar:

- [ ] Buka `https://domain-anda.com` — apakah muncul halaman login?
- [ ] Login dengan **admin@company.com** / **password123** — apakah dashboard muncul?
- [ ] Cek apakah grafik dan data aset tampil normal
- [ ] Lakukan mutasi aset (ganti lokasi/status/PIC) — apakah notifikasi email terkirim? (cek `storage/logs/laravel.log` jika pakai `MAIL_MAILER=log`)
- [ ] Cek status queue worker: `sudo supervisorctl status` — harus muncul `RUNNING`
- [ ] Cek cron berjalan: `grep CRON /var/log/syslog | tail -5`
- [ ] Cek log error aplikasi: `tail -f /var/www/inventaris-aset/storage/logs/laravel.log`
- [ ] Pastikan `APP_DEBUG=false` — akses URL random, harusnya tampil halaman 404 biasa (bukan stack trace)
- [ ] Cek HTTPS — pastikan ada icon gembok di browser

> **Penting:** Setelah deploy, **segera ganti password default** admin dan staff!

---

## Penting — Perintah Cepat untuk Maintenance

```bash
# Masuk ke folder project
cd /var/www/inventaris-aset

# Update kode (jika pakai git)
git pull

# Update dependency
composer install --optimize-autoloader --no-dev

# Reset cache (wajib setiap kali update kode)
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue worker (jika ada perubahan kode yang berkaitan queue)
sudo supervisorctl restart laravel-worker:*

# Lihat log real-time
tail -f storage/logs/laravel.log
```

---

## Akun Default (Seeder)

| Role | Username | Email | Password |
|------|----------|-------|----------|
| Super Admin | `admin` | admin@company.com | password123 |
| Staff | `staff` | staff@company.com | password123 |

> Ganti password setelah login pertama!

---

## Commands Penting

| Command | Fungsi |
|---------|--------|
| `composer run dev` | Jalankan dev server |
| `composer run dev:queue` | Jalankan queue worker |
| `composer run dev:logs` | Monitor log real-time |
| `composer run cache` | Cache view + config + routes |
| `composer run test` | Jalankan semua test (103 test, 270 assertions) |
| `php artisan optimize:clear` | Clear cache sebelum test (wajib setelah `composer run cache`) |
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

> Semua endpoint API memerlukan **autentikasi via `auth:sanctum`**.

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
- **Testing**: PHPUnit 11, 103 test cases

---

## Lisensi

MIT License — AssetMS v1.0.0
