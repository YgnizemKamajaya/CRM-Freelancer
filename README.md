# Freelance CRM + Personal Workspace

A single-user "desktop" for solo freelancers: a mini CRM **plus** a personal productivity workspace — finances, journal, kanban idea boards, a daily checklist, focus timer, goal tracker, and a private file vault — all behind one PIN.

Built on Laravel 12, Livewire 4, Blade, Tailwind CSS 4, Vite 7, with SQLite/MySQL-ready migrations, three color themes (light / sepia / dark), and basic PWA support.

> Started as a mini CRM built from a local PRD, then expanded into an all-in-one personal workspace. Contributors (and AI assistants) should read [`CLAUDE.md`](CLAUDE.md) for architecture conventions, and [`AUDIT-TODO.md`](AUDIT-TODO.md) for items that still need on-device verification.

---

## Table of Contents

- [Highlights](#highlights)
- [Modules](#modules)
- [Tech Stack](#tech-stack)
- [Quick Start](#quick-start)
- [Local PIN](#local-pin)
- [Database](#database)
- [Production](#production)
- [Architecture](#architecture)
- [Verification Checklist](#verification-checklist)
- [Roadmap](#roadmap)

---

## Highlights

- **Command-centre home.** A glanceable Dashboard 2.0 opens the app: time-aware greeting, an interactive today's-focus checklist you can tick off in place, cashflow, your pinned dream goal, a cross-module "needs attention" list, pipeline, and mood — all on one screen.
- **One PIN, no accounts.** Single-user unlock with rate limiting — no registration, users table, passwords, or email verification.
- **CRM that connects.** Client → Deal → Project → Invoice are linked end-to-end, so no context is rebuilt when you reopen an old client.
- **Personal workspace built in.** Finance, Today checklist, Focus timer, Goals, Kanban boards, Journal, and a private File Vault live alongside the CRM.
- **Cross-module glue.** A global quick-capture palette (`Ctrl`/`Cmd`+`K`) searches everything and creates records from anywhere. Paying an invoice auto-records income in Finance.
- **Three themes**, theme-aware semantic colors, and SPA-like navigation via Livewire `wire:navigate`.

---

## Modules

### CRM
- **Clients** — CRUD, search, status/tag filters, profile page, WhatsApp/email quick links, activity timeline.
- **Deals** — pipeline with inline stage updates, probability, weighted value, mark won/lost, convert-to-project.
- **Projects** — status, priority, progress, deadline, tasks, invoices, notes.
- **Tasks** — open/today/upcoming/overdue/completed filters, quick reschedule, complete/reopen.
- **Invoices** — line items, totals, sent/paid actions, balance tracking, print view, PDF download.
- **Reports** — date-range revenue, pipeline, invoice/project/task status, top clients.
- **Settings** — owner profile, avatar/logo upload, business identity, invoice defaults.

### Personal Workspace
- **Today** — a clean daily checklist (separate from CRM tasks) with drag-to-reorder, roll-over of yesterday's unfinished items, and a one-click "pull" of CRM tasks due today.
- **Focus** — a front-end Pomodoro timer (work / short / long, auto-cycling, adjustable durations, sound + optional desktop notifications).
- **Finance** — wallets, income/expense transactions, budgets, and a dashboard (cashflow chart, category breakdown, budget usage). Marking an invoice **paid** auto-records income.
- **Goals** — personal and financial goals with progress meters. Financial goals can **auto-track** a wallet balance. Each goal gets a **banner** (gradient preset or uploaded image) and can be **pinned** as a "dream" at the top.
- **Workspace** — Kanban boards for ideas and project plans, with native Livewire `wire:sort` drag-and-drop across columns. Cards can be promoted into real CRM projects.
- **Journal** — markdown entries with mood tracking, a 14-day mood trend, tag cloud, streak counter, and per-entry Markdown export.
- **Vault** — a private file store (contracts, assets, PDFs). Files live on a private disk, gated by the same PIN, optionally linked to a client/project.

---

## Tech Stack

| Layer | Choice |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12 |
| Interactivity | Livewire 4, Alpine.js |
| Frontend | Blade, Tailwind CSS 4, Vite 7 |
| PDF | barryvdh/laravel-dompdf |
| Database | SQLite (default) or MySQL/MariaDB |
| Auth | Single-user PIN middleware |

---

## Quick Start

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

Open `http://127.0.0.1:8000` and unlock with the PIN.

For active frontend development:

```bash
npm run dev
php artisan serve
```

> **Note on uploads:** the Vault and Goal banners store files on the **private** `local` disk (`storage/app/...`), served through PIN-gated routes — no `storage:link` needed for those. If large uploads fail, raise `upload_max_filesize` and `post_max_size` in your `php.ini` (Vault allows up to 25 MB/file, goal banners up to 8 MB).

---

## Local PIN

Default local PIN is `1234`. Change it in `.env` before real use:

```env
CRM_PIN=change-this-pin
CRM_OWNER_NAME="Demo Freelancer"
CRM_OWNER_EMAIL=demo@example.com
```

---

## Database

Default local config uses SQLite:

```env
DB_CONNECTION=sqlite
```

For MySQL/MariaDB:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=freelance_crm
DB_USERNAME=root
DB_PASSWORD=
```

Then:

```bash
php artisan migrate --seed
```

All workspace tables (finance, journal, workspace, checklist, goals, vault) are **additive** — they don't touch the original CRM tables, so migrating an existing install is safe.

---

## Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.test
CRM_PIN=change-this-pin
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

Use HTTPS for PWA installability. Keep uploads on the private disk; do not expose `storage/app/vault` or `storage/app/goal-banners` publicly.

---

## Architecture

Consistent `Model → Service → Controller → Blade/Livewire` layering throughout.

```
app/
  Http/Controllers/   thin controllers (Finance, Wallet, Transaction, Budget,
                      Goal, Vault, Board, Journal, ...)
  Livewire/           QuickCapture, BoardShow (kanban), TodayChecklist,
                      HomeFocusList (interactive home card)
  Models/             CRM models + Wallet, Transaction, Budget, Goal,
                      Board, BoardColumn, Card, JournalEntry, ChecklistItem,
                      VaultFile
  Services/           DashboardService, InvoiceService, FinanceService,
                      JournalService, ...
resources/
  css/app.css         design tokens for 3 themes + component styles
  views/              per-module Blade views + livewire/ component views
routes/web.php        all routes behind the `pin` middleware group
```

- **Drag-and-drop** uses Livewire 4's built-in `wire:sort` (SortableJS bundled) — no extra JS packages.
- **Theming** is driven by `--crm-*` CSS variables; semantic colors (income/expense/mood/priority) are defined per theme, so dark mode stays readable.

---

## Verification Checklist

Some behavior can't be checked without a running app (uploads, drag-and-drop persistence, PHP limits). After `composer install && npm run build`, verify the items tracked in [`AUDIT-TODO.md`](AUDIT-TODO.md), notably:

- Kanban card drag persists across a refresh (`npm run build` required).
- Today checklist reorder/roll-over/pull-from-CRM.
- Vault upload/download is PIN-gated and files land on the private disk.
- Goal banner upload/serve + gradient presets + pin.

---

## Roadmap

- Time tracking (billable hours → invoice).
- Recurring invoices + due reminders.
- Client portal (share-link, read-only).
- Habit tracker; Journal calendar view; Pomodoro session logging.
- CSV import/export; proposal builder.
