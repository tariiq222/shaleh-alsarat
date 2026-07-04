# Chalet MVP

A single-chalet management MVP built with Laravel 11 and Inertia.js. It serves a public
Arabic landing page with an inquiry form, and a private admin SPA for managing bookings,
payments, blocked dates, and chalet settings. The system is designed for **one chalet**
(no multi-tenant complexity) and prioritises fast CRUD with sensible business rules
(no overlapping bookings, automatic payment status recalculation, WhatsApp-ready templates).

## Tech Stack

- **Backend**: PHP 8.2, Laravel 11, SQLite (dev/tests) or MySQL 8 (prod)
- **Frontend**: Inertia.js 2, React 18, TypeScript, Tailwind CSS 3, shadcn/ui patterns
- **Calendar**: FullCalendar 6 (`dayGridPlugin` + `interactionPlugin`)
- **Tests**: Pest 3 (PHP unit + feature), Playwright (E2E browser)
- **Build**: Vite 5

## Requirements

- PHP 8.2 or newer (with `intl`, `mbstring`, `pdo_mysql` or `pdo_sqlite`)
- Composer 2
- Node.js 20 or newer
- npm 10+
- MySQL 8 (production) or SQLite (development / tests)

## Installation

```bash
git clone <repo-url> chalet-mvp
cd chalet-mvp

cp .env.example .env
# Edit .env and set:
#   APP_KEY=             (will be generated)
#   ADMIN_EMAIL=
#   ADMIN_PASSWORD=      (at least 8 characters)
#   DB_*                 (MySQL credentials)

composer install
php artisan key:generate

php artisan migrate --seed
# This creates the chalet settings row and the admin user from .env

npm install
npm run build
php artisan storage:link
```

The first run will:
1. Run all migrations
2. Create the singleton `chalet_settings` row (defaults to "شاليهات السراة")
3. Create the admin user from `ADMIN_EMAIL` / `ADMIN_PASSWORD`

## Development

Run two terminals:

```bash
# Terminal 1 — Laravel + Vite (combo script)
composer dev

# OR run them separately:
php artisan serve                # http://127.0.0.1:8000
npm run dev                      # Vite hot reload on :5173
```

Visit:

- `http://127.0.0.1:8000/` — public Arabic landing page
- `http://127.0.0.1:8000/login` — admin login (Inertia SPA)

## Default admin login

For local development, the `.env.example` ships with safe defaults so you can log in immediately:

```env
ADMIN_NAME="مدير الشاليه"
ADMIN_EMAIL=admin@chalet.local
ADMIN_PASSWORD=changeme
```

After running `php artisan migrate --seed`, you can log in at `/login` with:

| Field    | Value                  |
|----------|------------------------|
| Email    | `admin@chalet.local`   |
| Password | `changeme`             |

The `AdminSeeder` will print a clear warning if it detects you are still using `changeme` or `password` as the password — change it before going to production:

```bash
php artisan admin:reset-password admin@chalet.local --password="your-strong-password"
```

A small **"دخول الإدارة (تطوير)"** button appears in the public footer only when `APP_ENV=local` or `APP_DEBUG=true`, so you can reach `/login` with one click during development. It is hidden in production.

## Resetting the admin password

If you lose access to the admin account, use the included Artisan command from the CLI:

```bash
# Interactive (prompts for new password + confirmation)
php artisan admin:reset-password

# Explicit email + non-interactive password
php artisan admin:reset-password admin@chalet.local --password="new-strong-password"
```

The command validates length (min 8), confirms the email exists, and `Hash::make`s the
new password before saving.

## Running tests

### Pest (PHP — unit + feature)

```bash
# All tests
php artisan test

# Filtered
php artisan test --filter=Booking
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Single file
php artisan test tests/Unit/AvailabilityServiceTest.php
```

Pest uses an in-memory SQLite database (see `phpunit.xml`). Tests use `RefreshDatabase`
and the `adminUser()` helper defined in `tests/Pest.php`.

### Playwright (E2E browser)

```bash
# First time: install browsers
npx playwright install --with-deps chromium

# Seed the dev DB (Playwright tests hit the real /login form)
php artisan migrate:fresh --seed

# Run all E2E
npm run test:e2e

# Headed (visible browser)
npx playwright test --headed

# Specific file
npx playwright test tests/E2E/admin-booking-flow.spec.ts
```

The Playwright config (`playwright.config.ts`) automatically starts `php artisan serve`
on port 8000 if no server is running.

> **E2E environment variables.** Set `ADMIN_EMAIL` and `ADMIN_PASSWORD` to match the
> seeded admin user. Defaults: `admin@chalet.local` / `password` (if you used the
> included seeder values).

## Production deployment — Docker (recommended)

The project ships with a production-ready multi-stage `Dockerfile` + `docker-compose.yml`. **This is the recommended deployment path** — no need to install PHP/MySQL/Node on the host.

### What the stack looks like

| Container | Image | Role |
|---|---|---|
| `shaleh-app` | `php:8.2-apache` (custom build) | Serves Laravel + Inertia app on port 80 |
| `shaleh-db` | `mysql:8.0` | Persistent MySQL data |

Named volumes `chalet-alsarat-storage` (photos, logs, cache) and `chalet-alsarat-dbdata` (MySQL) persist across container rebuilds.

### One-time deployment on any VPS / Coolify / Portainer host

#### 1. Generate strong passwords

```bash
# DB password (paste into .env as DB_PASSWORD + DB_ROOT_PASSWORD)
openssl rand -base64 24 | tr -d '=+/' | head -c 24
echo

# Admin password (paste into .env as ADMIN_PASSWORD)
openssl rand -base64 18 | tr -d '=+/' | head -c 18
echo

# APP_KEY — generated automatically on first container boot, or set manually:
php artisan key:generate --show
```

#### 2. Clone the repo

```bash
git clone https://github.com/tariiq222/shaleh-alsarat.git ~/shaleh-alsarat
cd ~/shaleh-alsarat
```

#### 3. Configure environment

```bash
cp .env.production.example .env
nano .env          # fill DB_PASSWORD, DB_ROOT_PASSWORD, ADMIN_EMAIL, ADMIN_PASSWORD, APP_URL
```

#### 4. Start the stack

```bash
docker compose up -d --build
```

That's it. The `app` container's entrypoint script will:
1. Wait for MySQL to be healthy
2. Generate `APP_KEY` if missing
3. Run `php artisan storage:link`
4. Run `php artisan migrate --force`
5. Seed admin user (only on first boot)
6. Cache config/routes/views
7. Boot Apache

#### 5. Follow logs

```bash
docker compose logs -f app
# Look for: "Ready. Starting Apache..."
```

#### 6. Smoke-test

```bash
curl -I http://localhost/up          # → 200 OK (Laravel health endpoint)
curl  http://localhost/               # → landing page
```

#### 7. SSL (recommended: put a reverse proxy in front, then use certbot)

```bash
sudo apt install -y nginx certbot python3-certbot-nginx
# Edit /etc/nginx/sites-available/shaleh to reverse-proxy localhost:80
# Then:
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

The app listens on **port 80** inside the container; map host port → container port 80 (already configured in `docker-compose.yml`).

### Subsequent deploys

Just `git pull` on the host, then:

```bash
docker compose up -d --build
```

The entrypoint runs `migrate --force` each time, so DB schema updates are automatic (zero downtime-safe additions only — for breaking changes do them offline).

### Backup strategy

```bash
# Database dump
docker compose exec db mysqldump -uroot -p"$DB_ROOT_PASSWORD" chalet_mvp | gzip > backup-$(date +%F).sql.gz

# Uploads (photos)
docker run --rm -v shaleh-alsarat-storage:/data -v $(pwd):/backup alpine \
    tar czf /backup/photos-$(date +%F).tgz -C /data .
```

### Troubleshooting

| Symptom | Fix |
|---|---|
| `MySQL not reachable` | Check `.env` `DB_HOST=db`, run `docker compose logs db` |
| 500 on every request | `docker compose exec app php artisan config:clear` |
| Photos not loading | `docker compose exec app php artisan storage:link` |
| Forgot admin password | `docker compose exec app php artisan admin:reset-password admin@yourdomain.com --password='newpass'` |


Replace `example.com` with your domain:

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/chalet-mvp/public;
    index index.php;

    client_max_body_size 10M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    gzip on;
    gzip_types text/plain text/css application/json application/javascript
               application/xml application/xml+rss text/javascript
               image/svg+xml font/woff2;
    gzip_min_length 1024;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 60;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static asset caching (Vite-built)
    location ~* \.(?:css|js|woff2?|svg|png|jpe?g|gif|webp|ico)$ {
        expires 30d;
        access_log off;
        try_files $uri =404;
    }
}
```

Enable the site and reload:

```bash
sudo ln -s /etc/nginx/sites-available/chalet-mvp /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### SSL with Let's Encrypt

```bash
sudo certbot --nginx -d example.com -d www.example.com
sudo systemctl status certbot.timer    # verify auto-renewal is scheduled
```

Certbot modifies the Nginx block in place and reloads. Auto-renewal runs twice daily.

### Deploying updates

```bash
cd /var/www/chalet-mvp
./deploy.sh                  # defaults to main branch
./deploy.sh release/v1.2.0   # specific branch
```

The script is non-interactive (`set -euo pipefail`) and:

1. Pulls the branch via `git pull --ff-only`
2. Runs `composer install --no-dev`
3. Runs `npm ci` + `npm run build`
4. Runs `php artisan migrate --force`
5. Clears and caches config/routes/views
6. Reloads `php8.2-fpm` via `systemctl reload`

If your VPS uses a non-standard PHP-FPM service name, override it:

```bash
RELOAD_CMD="sudo systemctl reload php8.1-fpm" ./deploy.sh
```

## Project structure

```
chalet-mvp/
├── app/
│   ├── Console/Commands/AdminResetPassword.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # Inertia SPA controllers
│   │   │   │   ├── BookingController.php
│   │   │   │   ├── CalendarController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── InquiryController.php
│   │   │   │   ├── PaymentController.php
│   │   │   │   ├── SettingsController.php
│   │   │   │   └── BlockedDateController.php
│   │   │   ├── Auth/AuthenticatedSessionController.php
│   │   │   └── Public/             # SEO-critical Blade controllers
│   │   │       ├── PageController.php
│   │   │       └── InquiryController.php
│   │   ├── Middleware/HandleInertiaRequests.php
│   │   └── Requests/               # FormRequests (validation)
│   ├── Models/                     # Eloquent models (User, Booking, Payment, ...)
│   ├── Providers/AppServiceProvider.php
│   └── Services/                   # Domain logic
│       ├── AvailabilityService.php
│       ├── BookingService.php
│       └── PaymentService.php
├── database/
│   ├── factories/                  # Test factories
│   ├── migrations/                 # Schema migrations
│   └── seeders/                    # Default data
├── resources/
│   ├── css/app.css                 # Tailwind entry
│   ├── js/
│   │   ├── Components/             # shadcn/ui primitives + FlashMessage
│   │   ├── Layouts/                # AdminLayout, AuthLayout
│   │   ├── Pages/
│   │   │   ├── Admin/              # Inertia admin pages
│   │   │   └── Auth/Login.tsx
│   │   ├── app.tsx
│   │   ├── bootstrap.ts
│   │   └── ssr.tsx
│   └── views/
│       ├── app.blade.php           # Inertia root
│       └── public/                 # SEO-critical Blade views
│           ├── layout.blade.php
│           ├── home.blade.php
│           └── partials/
│               ├── header.blade.php
│               └── footer.blade.php
├── routes/
│   ├── api.php
│   ├── auth.php                    # /login, /logout
│   ├── console.php
│   └── web.php                     # Public + admin routes
├── tests/
│   ├── E2E/admin-booking-flow.spec.ts
│   ├── Feature/                    # Pest HTTP / DB feature tests
│   ├── Pest.php                    # TestCase bindings + adminUser() helper
│   ├── TestCase.php
│   └── Unit/                       # Pest service / model tests
├── deploy.sh
├── playwright.config.ts
├── phpunit.xml
├── composer.json
└── package.json
```

## Domain model

**`ChaletSettings`** — singleton row (the system supports exactly one chalet).
Holds name, description, feature list, prices (weekday / weekend), check-in / check-out
times, WhatsApp number, location, map URL, and `is_active` flag.

**`Booking`** — core reservation record. Has auto-generated `booking_number` (e.g.
`CHL-2026-0042`), customer name/phone, date range, total/deposit/remaining amounts,
`booking_status` (`pending`/`confirmed`/`cancelled`/`completed`),
`payment_status` (`unpaid`/`partially_paid`/`paid`), `source` (`admin`/`website`),
optional link to the originating `Inquiry`, and free-form notes.

**`Payment`** — partial payment recorded against a booking. Holds amount, method
(`cash`/`bank_transfer`/`other`), date, optional receipt file (stored under
`storage/app/public/receipts/`), and a note.

**`Inquiry`** — public website lead. Captures name, phone, preferred date, message,
and a status (`new`/`contacted`/`converted_to_booking`/`closed`). When the admin
converts it into a booking, the inquiry's status is updated to `converted_to_booking`.

**`BlockedDate`** — explicit date ranges when the chalet is unavailable (maintenance,
private use). The availability check rejects any booking that intersects a non-empty
block.

**`ChaletPhoto`** — gallery images. Belongs to `ChaletSettings`. Stores path, caption,
and `sort_order` (displayed in ascending order).

## Key business rules

- **No overlapping bookings.** New bookings are checked against all
  `pending` + `confirmed` bookings (cancelled ones are ignored). Updates skip
  the check against the same booking being edited.
- **No booking inside a `BlockedDate`.** The availability check rejects any
  range that intersects a blocked period.
- **Auto-generated booking number.** `Booking::generateNextBookingNumber()`
  produces `CHL-YYYY-NNNN` (zero-padded sequence within the year).
- **Payment status auto-recalculates.** After every payment create/delete and
  every booking update, `PaymentService::recalculateForBooking` recomputes
  `remaining_amount` and `payment_status` from the sum of payments.
- **Payments blocked on cancelled bookings.** Attempting to add a payment to a
  cancelled booking throws `ValidationException`. Admin must un-cancel first.
- **End date must be after start date.** Enforced in both the service layer
  (`guardDates`) and the form request validator.
- **Settings singleton.** `ChaletSettings::current()` returns the single row,
  creating it on first call. There is no CRUD listing.
- **Rate limiting.** Public inquiry submissions are limited to 10 per minute per
  IP (via the `inquiries` rate limiter). Login is limited to 5 per minute per
  email + IP.
- **Inquiry → Booking is one-way.** Once an inquiry's status is
  `converted_to_booking`, its status cannot be changed back via
  `PATCH /admin/inquiries/{id}/status`.

## WhatsApp message templates

The admin booking show page exposes four ready-to-copy WhatsApp deep links. Each is a
`https://wa.me/<phone>?text=<encoded-arabic-message>` URL built from the configured
`whatsapp_number` and the booking's customer/dates/amounts.

```
1. رسالة تأكيد مبدئي (pending message):
   "مرحباً {customer_name}،
   نشكرك على حجزك رقم {booking_number}. سيتم تأكيد الحجز قريباً بعد مراجعة التفاصيل."

2. تأكيد الحجز (confirmed message):
   "مرحباً {customer_name}،
   تم تأكيد حجزك رقم {booking_number} من {start_date} إلى {end_date}. نتطلع لاستضافتكم."

3. تذكير (reminder message):
   "مرحباً {customer_name}،
   نود تذكيركم بموعد وصولكم غداً. وقت الدخول {check_in_time}. حجزكم رقم {booking_number}."

4. المتبقي (remaining message):
   "مرحباً {customer_name}،
   نود تذكيركم بالمبلغ المتبقي وقدره {remaining} ريال على الحجز رقم {booking_number}."
```

Click a button to copy the link to your clipboard, then paste into WhatsApp to start
the conversation with the customer's number pre-filled.

## Limitations / out-of-scope

The MVP is intentionally narrow. The following are **not** included and would be
the next iteration:

- **Multi-chalet / multi-tenant.** The system models exactly one chalet; the
  `ChaletSettings` table is a singleton.
- **Online payment gateway integration** (Moyasar, Stripe, Mada, Apple Pay, etc.).
  All payments are recorded manually by the admin.
- **Customer-facing accounts.** Customers do not log in; the public page is a
  one-way lead form.
- **Email/SMS notifications.** Inquiries and booking confirmations are not
  emailed. All communication flows through WhatsApp (manual copy).
- **Two-way calendar sync** (Google Calendar, Airbnb, etc.). Only internal
  FullCalendar is exposed.
- **Reporting / analytics.** The dashboard shows a small summary; no historical
  reports, charts, or exports (CSV/PDF).
- **Multi-language UI.** The UI is Arabic-only (`APP_LOCALE=ar`,
  `APP_FALLBACK_LOCALE=ar`).
- **File management UI for receipts.** Receipts are uploaded as part of a
  payment; there's no separate gallery or audit trail.
- **Audit log / activity history.** Booking edits overwrite fields; no
  `updated_by` or revision history is kept.
- **Refund / partial-refund flow.** Cancelled bookings keep their payment records
  for accounting but have no dedicated refund model.
- **Automated scheduled jobs** (e.g. nightly cleanup, auto-mark past bookings
  as completed). The `complete()` endpoint exists but is manual.
- **CI/CD.** This repo ships a manual `deploy.sh` script only — no GitHub
  Actions / GitLab CI / deploy keys automation.

## License

MIT License. See `LICENSE` (placeholder — add the standard MIT text before
publishing).

## Author

Built for a single chalet operator in the Sarat region (شاليهات السراة). For
support or feature requests, contact the project owner.