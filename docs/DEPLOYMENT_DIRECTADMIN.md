# DirectAdmin Shared-Hosting Deployment Guide (HostAfrica / No SSH)

Target environment verified on **HostAfrica DirectAdmin**:
- **PHP**: `8.4.28` (`mysqli`, `curl`, `mbstring`, `sodium`, `gd`)
- **Database**: `MariaDB 11.4.13`
- **Web Server**: `Apache/2`
- **Constraints**: No Redis, no queue workers, no guaranteed SSH.

---

## 1. Recommended Directory Layout (Secrets Outside Web Root)

Keep Laravel application code and `.env` **outside** `public_html`:

```text
/home/<da_user>/domains/darasahub.com/
├── app/                     <-- Laravel project root (contains .env, vendor/, storage/, public/)
└── public_html -> app/public   (symlink OR DirectAdmin Document Root pointed to app/public)
```

### Option A (Recommended): Point Document Root to `/public`
1. In DirectAdmin → **Domain Setup** → click `darasahub.com` → **Document Root** (or **Private HTML / Public HTML**).
2. Point the document root to `/domains/darasahub.com/app/public` (or replace `public_html` with a symlink to `app/public` via DirectAdmin File Manager / Cron).

### Option B (Fallback if Document Root Cannot Be Changed)
1. Upload the Laravel project (everything except `public/`) to `/home/<da_user>/domains/darasahub.com/app/`.
2. Copy the contents of `public/` into `/home/<da_user>/domains/darasahub.com/public_html/`.
3. Edit `public_html/index.php` so the two paths point to `../app/`:
   ```php
   if (file_exists($maintenance = __DIR__.'/../app/storage/framework/maintenance.php')) {
       require $maintenance;
   }
   require __DIR__.'/../app/vendor/autoload.php';
   $app = require_once __DIR__.'/../app/bootstrap/app.php';
   ```
4. Verify `.env` is in `../app/.env` and **never** inside `public_html/`.

---

## 2. Local Build & No-SSH Deployment Workflow

Because `composer.json` locks `"platform": { "php": "8.4.28" }`, build production artifacts locally before deploying:

```powershell
# 1. Install production-only PHP dependencies (no dev tools)
composer install --no-dev --optimize-autoloader --no-interaction

# 2. Build purged Tailwind CSS & Alpine.js production bundle
npm ci
npm run build

# 3. Publish Filament assets (already committed in public/js/filament & public/css/filament)
php artisan filament:assets
```

Deploy via DirectAdmin Git or SFTP, ensuring `vendor/` and `public/build/` are uploaded if the server has no SSH/Composer.

---

## 3. Production `.env` Configuration

Create `.env` in `/home/<da_user>/domains/darasahub.com/app/.env`:

```env
APP_NAME=DarasaHub
APP_ENV=production
APP_KEY=base64:GENERATE_AND_KEEP_SECRET
APP_DEBUG=false
APP_URL=https://darasahub.com
APP_CANONICAL_HOST=darasahub.com

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<prefixed_db_name>
DB_USERNAME=<prefixed_db_user>
DB_PASSWORD="<strong_password>"

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database
RESPONSE_CACHE_ENABLED=true
RESPONSE_CACHE_DRIVER=file
RESPONSE_CACHE_LIFETIME=3600

# Keep disabled except during brief no-SSH maintenance windows
MAINTENANCE_ROUTE_ENABLED=false
```

Ensure writable permissions (`0755` or `0775`) on `storage/` and `bootstrap/cache/`.

---

## 4. Running Migrations & Caching Without SSH

### Using the Secured Maintenance Route (Temporary Only)
1. Temporarily set in `.env`:
   ```env
   MAINTENANCE_ROUTE_ENABLED=true
   MAINTENANCE_ROUTE_TOKEN=<random-64-char-hex-string>
   MAINTENANCE_ROUTE_EXPIRES_AT=2026-11-23T15:00:00+03:00
   MAINTENANCE_ROUTE_ALLOWED_IPS=<your-current-public-ip>
   ```
2. Invoke only the allowlisted commands via `curl` (`POST`):
   ```bash
   curl -X POST https://darasahub.com/_maintenance/migrate -H "X-Maintenance-Token: YOUR_TOKEN"
   curl -X POST https://darasahub.com/_maintenance/optimize -H "X-Maintenance-Token: YOUR_TOKEN"
   curl -X POST https://darasahub.com/_maintenance/sitemap-generate -H "X-Maintenance-Token: YOUR_TOKEN"
   ```
3. **Immediately set `MAINTENANCE_ROUTE_ENABLED=false`** and clear `MAINTENANCE_ROUTE_TOKEN` in `.env` when done. Every call is logged in `audit_logs`.

### `storage:link` Alternative Without SSH
- Static OG images are written directly to `public/og/` and XML sitemaps to `public/sitemap.xml` + `public/sitemaps/`, so core SEO features do **not** require `storage:link`.
- If you later store local PDFs on the `public` disk and `symlink()` is disabled on the host, either run `POST /_maintenance/storage-link` once or point a DirectAdmin cron one-liner `ln -s /home/<da_user>/domains/darasahub.com/app/storage/app/public /home/<da_user>/domains/darasahub.com/public_html/storage`.

---

## 5. DirectAdmin Cron Job for Laravel Scheduler

In DirectAdmin → **Advanced Features** → **Cron Jobs** → **Create Cron Job**:
- **Minute**: `*`, **Hour**: `*`, **Day of Month**: `*`, **Month**: `*`, **Day of Week**: `*`
- **Command**:
  ```bash
  /usr/local/bin/php /home/<da_user>/domains/darasahub.com/app/artisan schedule:run >> /dev/null 2>&1
  ```
*(Confirm the PHP 8.4 CLI binary path in DirectAdmin, e.g. `/usr/local/php84/bin/php` if default `php` is an older version.)*

This automatically runs:
- `sitemap:generate` daily at `02:30`
- `links:check --limit=50` daily at `03:15`

---

## 6. Database Backup & Restore

- **Automated Backups**: DirectAdmin → **System Backup / Create/Restore Backups** → enable weekly/daily database backups.
- **Manual Export**: DirectAdmin → **Databases** → click **Download** (or phpMyAdmin → **Export** → SQL gzipped).
- **Restore**: DirectAdmin → **Databases** → **Upload a Database Backup** (`.sql.gz`), or via phpMyAdmin **Import**. Always take a snapshot before running new migrations.
