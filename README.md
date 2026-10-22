# Freelance CRM + Personal Workspace

A web app for one freelancer to manage clients, sales opportunities, projects, tasks and invoices, alongside personal finances, daily planning, goals, notes and files. One PIN unlocks the workspace. There are no separate user accounts.

The interface is mainly in Indonesian and offers light, sepia and dark themes.

## What you can do

| Area | Features |
| --- | --- |
| Home | Today's checklist, money received and spent, pinned goal, work needing attention, sales opportunities and journal mood |
| Clients and deals | Contact details, activity history, sales stages and conversion from a won deal to a project |
| Projects and tasks | Deadlines, progress, follow-ups, completion and reopening |
| Invoices | Add or remove item rows, percentage or fixed-amount tax, partial/full payments, printing and PDF download |
| Finance | Wallets, income, expenses, budgets and balance summaries |
| Today and Focus | Dated checklist, moving unfinished items to today, importing today's CRM tasks, and a work/break timer |
| Goals | Manual progress or a balance calculated from wallets, optional banner images and pinned goals |
| Workspace | Boards with columns and movable cards; turn a card into a project after choosing a client |
| Journal | Markdown notes, date/tag/mood filters, mood history and entry export |
| Vault | Upload, preview, download and delete files; optionally link them to a client or project |

Press `Ctrl+K` or `Cmd+K` to search clients, deals, projects, invoices, tasks, journal entries, boards, goals and Vault filenames. The menu also opens forms for creating records. It does not search file contents or create board cards directly.

## Requirements

- PHP 8.2 or later, Composer, and the PHP extensions required by Laravel and the chosen database.
- Node.js 20 (20.19 or later in that release line), or Node.js 22.12 or later, and npm.
- SQLite for the default setup, or MySQL. The latest full suite passed on SQLite and MySQL 8.4.9; MariaDB has not been verified.

The app uses Laravel 12, Livewire 4, Blade, Alpine.js, Tailwind CSS 4 and Vite 7. Invoice PDFs use DomPDF; image cropping uses locally bundled CropperJS. Dependency versions are recorded in `composer.lock` and `package-lock.json`.

## Install locally

Run these commands in a **new checkout**:

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

Open [the local app](http://127.0.0.1:8000). The example PIN is `1234`; change `CRM_PIN` in `.env` before using the app for your own records. Keep your existing `.env` and application key when updating an installed copy.

To add sample content to a disposable demo database, run `php artisan db:seed`. The seeder updates demo records and settings, so do not run it against your working database. `composer setup` also seeds data; use the explicit commands above for an empty installation.

For development, run `npm run dev` and `php artisan serve` in separate terminals.

## PIN and owner details

```env
CRM_PIN="replace-with-your-own-pin"
CRM_OWNER_NAME="Your Name"
CRM_OWNER_EMAIL=you@example.com
```

PINs may contain 1–128 characters. Numeric PINs of up to eight digits use the on-screen number pad; longer numbers and passphrases use a password field. Quote values containing spaces. Failed unlock attempts are rate limited.

## Database and updates

SQLite is the default:

```env
DB_CONNECTION=sqlite
```

For MySQL, create a database and set its connection details:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=freelance_crm
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

Back up the database and uploaded files before updating an existing installation, then run:

```bash
composer install
npm ci
php artisan migrate
npm run build
```

Recent migrations add invoice tax/payment fields and remove a fixed checklist-date default. They change existing tables as well as adding fields; review migrations before applying them. No demo seeding is needed for an update.

## Payments and archived records

When you save an invoice with money received, Finance creates or updates one automatic income entry for the amount not already covered by manually linked income. It needs an active wallet in the invoice's currency when creating that entry. If no suitable wallet exists, the invoice saves and shows a notice. Create the wallet and resave the invoice; creating the wallet alone does not record old payments.

Edit an automatic income entry through its invoice. Cancelling or deleting the invoice removes that automatic entry; manually entered transactions remain. Choosing Paid in the invoice editor requires enough received money to cover the total.

Projects require a client. Existing links to archived clients remain readable and can be kept when editing, but new links must use active records. Archived wallet balances still count toward assets and goal progress. Completing a checklist item completes its linked CRM task and updates project progress; unchecking it does not reopen the task. Use the Tasks page to reopen it.

## Files and upload limits

| File | Storage | App limit |
| --- | --- | --- |
| Vault upload | `storage/app/private/vault`, accessed through PIN-protected routes | 25 MB per file, up to 20 files per request |
| Goal banner | `storage/app/private/goal-banners`, accessed through PIN-protected routes | 8 MB, images only |
| Signature | `storage/app/public/signatures`, served through the public storage link | 4 MB, supported images only |

The public storage link is also used for profile/business images. Keep the private storage directory outside public web access.

The server may impose smaller limits than the app. In the PHP service serving web requests, `upload_max_filesize` limits each file and `post_max_size` limits the whole request. For example, `25M` and `32M` allow one maximum-size Vault file, but not twenty of them together. Also check the web server's request limit, `max_file_uploads`, free storage and memory for image processing. Restart the relevant service after changing its settings. Terminal PHP settings may differ from the web server's settings.

## Deploy

Set the server's document root to `public`, enable HTTPS, and configure `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
CRM_PIN="replace-with-your-own-pin"
```

After configuring the database and writable storage, run:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Preserve the application key and existing data on updates. Test uploads against the actual server limits. The installable web app currently offers a basic offline page; viewing and editing CRM records offline is not implemented.

## Checks and remaining work

Run `bash 03_VERIFICATION_SUITE.sh` with development dependencies installed. It checks PHP syntax, formatting, Blade template compilation, automated tests, the frontend build and diff whitespace.

Latest completed run, **22 October 2026: 52 tests / 284 assertions passed on SQLite and MySQL 8.4.9**. Browser checks covered selected login, invoice, checklist, mobile navigation, theme, board and image-crop flows. This does not establish coverage of every device or page.

See [the current backlog](AUDIT-TODO.md) for remaining device, upload and database checks, and [the documentation index](docs/README.md) for reports and evidence. Only `AuditClaimValidationTest.php` and `probe-results.txt` in the evidence folder describe failures before the fixes; later logs record successful checks.

## Planned features

Not implemented: a journal month calendar, direct card creation from QuickCapture, saved focus-session history, full offline record editing, billable time tracking, recurring invoices, reminders, a client portal, habit tracking, CSV import/export and a proposal builder. These are ideas, not scheduled delivery commitments.

Contributors should read [CLAUDE.md](CLAUDE.md) for code conventions and [the handover](00_START_HERE_AI_AGENT_HANDOVER.md) for the current work status.
