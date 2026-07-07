# Freelance CRM

Mini CRM for solo freelancers. Built from the local PRD with Laravel 12, Breeze auth, Blade, Tailwind CSS 4, Flowbite, Vite, SQLite/MySQL-ready migrations, seeded demo data, and basic PWA support.

## Features

- Breeze authentication with registration blocked after the first user exists.
- Livewire-powered `wire:navigate` links for SPA-like authenticated navigation.
- Dashboard KPIs for clients, deals, projects, pipeline value, expected revenue, unpaid invoices, overdue invoices, and due tasks.
- Client CRUD with search, status/tag filters, profile page, tags, WhatsApp/email quick links, and activity timeline.
- Deal pipeline with inline stage updates, probability, weighted value, mark won/lost, and convert-to-project action.
- Project CRUD with status, priority, progress, deadline, tasks, invoices, and notes.
- Task/follow-up workflow with open/today/upcoming/overdue/completed filters, quick reschedule, and complete/reopen actions.
- Invoice CRUD with line items, total calculation, sent/paid actions, balance tracking, print view, and PDF download.
- Reports page with date range filters for revenue, pipeline, invoice status, project status, task status, and top clients.
- Settings page for business identity, logo upload, and invoice defaults.
- PWA manifest, service worker, SVG + PNG install icons, and offline fallback.

## Demo Account

Seeded local account:

- Email: `demo@example.com`
- Password: `password`

Use demo credentials only for local development.

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`.

For active frontend development:

```bash
npm run dev
php artisan serve
```

## Database

Default local config uses SQLite:

```env
DB_CONNECTION=sqlite
```

For MySQL/MariaDB, set:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=freelance_crm
DB_USERNAME=root
DB_PASSWORD=
```

Then run:

```bash
php artisan migrate --seed
```

## Production Notes

Set production env:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.test
```

Deploy checklist:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

Use HTTPS for PWA installability. Service worker caches static/offline assets only; authenticated dashboard data is not aggressively cached.

## Known MVP Limits

- Blade-first implementation with Livewire `wire:navigate`; CRUD pages are not full Livewire components yet.
- No client portal, payment gateway, email automation, WhatsApp API, or team permissions.

## Roadmap

- CSV import/export.
- PDF invoice.
- Proposal builder.
- Time tracking.
- WhatsApp/email template copy buttons.
- Public invoice link and client portal.
