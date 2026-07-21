# CLAUDE.md

Guidance for AI assistants (and new contributors) working in this repo. Read this
first, then `README.md` for setup and `AUDIT-TODO.md` for items awaiting on-device
verification.

## What this project is

A **single-user** "desktop" for a solo freelancer: a mini CRM plus a personal
productivity workspace, all behind one PIN. There is **no `users` table, no
registration, no multi-tenancy**. Access is a PIN checked by the `pin` middleware.
Because it's single-user and local-first, "privacy" is largely free — uploads live
on a private disk, data is the owner's own.

## Stack

- PHP 8.2+, Laravel 12
- Livewire 4 (drag-and-drop uses built-in `wire:sort` — **no extra JS packages**)
- Alpine.js (bundled with Livewire), Blade
- Tailwind CSS 4, Vite 7
- SQLite (default) or MySQL/MariaDB
- barryvdh/laravel-dompdf for invoice PDFs

## Architecture conventions — follow these exactly

The codebase is consistent. New work must match existing patterns, not introduce new ones.

1. **`Model → Service → Controller → Blade/Livewire`.** Controllers stay thin.
   Business logic and aggregation go in a `Service` (see `FinanceService`,
   `JournalService`, `HomeService`). Don't put query logic in controllers.
2. **Controllers use the `ValidatesCrmRelations` trait** for `authorizeOwner()`
   (404s if the model doesn't exist) and `ownedExists('table')` (an `exists`
   validation rule). Use these instead of hand-rolling.
3. **Additive migrations only.** Every workspace module adds its own tables and
   never alters the original CRM tables. Nullable FKs (`client_id`, `project_id`,
   etc.) are the standard way to optionally link across modules — follow that.
4. **All routes live behind the `pin` middleware group** in `routes/web.php`.
5. **Theming is CSS-variable driven.** Use `--crm-*` tokens for every color.
   Never hard-code `oklch()`/hex in Blade `style=""`. Semantic colors already
   exist: `--crm-income`, `--crm-expense`, `--crm-over`, `--crm-mood-1..5`,
   `--crm-priority-{low,medium,high,urgent}-{bg,text}`. They're defined for all
   three themes (light / sepia / dark) in `resources/css/app.css`.
6. **Reusable components** live in `resources/views/components/`: `x-icon`,
   `x-stat-card`, `x-empty-state`, `x-confirm-action`, `x-input-error`,
   `x-coming-soon`. Reuse before building new. Meters use the `.crm-meter` class
   (with `.is-over` for overspend/over-target). Progress bars in the original CRM
   use `.project-progress-track` — **don't reuse that for new features**, use
   `.crm-meter`.
7. **Money formatting** is an inline closure per view:
   `$money = fn ($v) => 'IDR '.number_format((float) $v, 0, ',', '.');`
   (Indonesian locale — thousands `.`, no decimals.) There's no central helper yet.
8. **Copy is in Indonesian.** Match it (e.g. "Hari ini", "Simpan", "Perlu perhatian").
9. **Livewire components are class-based** in `app/Livewire/` with views in
   `resources/views/livewire/`. Full-page components use `#[Layout('layouts.app')]`.
   Embed with `<livewire:kebab-name />`.

## Module map

CRM (original): Clients, Deals, Projects, Tasks, Invoices, Reports, Settings.

Personal workspace (added):
| Module | Route(s) | Key files |
| --- | --- | --- |
| Dashboard 2.0 (home) | `dashboard` | `HomeDashboardController`, `HomeService`, `home/index.blade.php`, `HomeFocusList` (Livewire) |
| Legacy CRM KPIs | `dashboard.crm` | `DashboardController`, `dashboard.blade.php` |
| Today checklist | `today.index` | `TodayChecklist` (Livewire), `ChecklistItem` |
| Focus (Pomodoro) | `focus.index` | `focus/index.blade.php` (Alpine only, no backend) |
| Finance | `finance.index`, `wallets.*`, `transactions.*`, `budgets.*` | `FinanceController` + `WalletController` + `TransactionController` + `BudgetController`, `FinanceService`, `Wallet`/`Transaction`/`Budget` |
| Goals | `goals.*` (+ `goals.adjust`, `goals.pin`, `goals.banner`) | `GoalController`, `Goal` |
| Workspace (Kanban) | `workspace.*` | `BoardController`, `BoardShow` (Livewire), `Board`/`BoardColumn`/`Card` |
| Journal | `journal.*` (+ `journal.export`) | `JournalController`, `JournalService`, `JournalEntry` |
| Vault | `vault.*` | `VaultController`, `VaultFile` |
| Quick capture (Ctrl/Cmd+K) | — | `QuickCapture` (Livewire), embedded in `layouts/app.blade.php` |

## Cross-module wiring (don't break these)

- Marking an invoice **paid** auto-records income: `InvoiceController::markPaid`
  calls `FinanceService::recordInvoicePayment` (guarded against double entry).
- Financial goals with `auto_track` pull a live wallet balance via
  `FinanceService::goalCurrentAmount`.
- Kanban cards can be promoted to real CRM Projects (`BoardShow::convertToProject`,
  mirroring the Deal→Project flow).
- Today checklist can pull CRM tasks due today, and completing a linked item flips
  the CRM task to `done`.

## File uploads

Two features accept uploads, both on the **private `local` disk**, served through
PIN-gated routes (no `storage:link`, intentional for privacy):
- **Vault** → `storage/app/vault` (max 25 MB/file).
- **Goal banners** → `storage/app/goal-banners` (max 8 MB, images only).
Deleting the record removes the physical file via a model `deleting` hook.
PHP `upload_max_filesize` / `post_max_size` cap real-world size — mention this when
uploads fail.

## Gotchas / things that have bitten us

- **`wire:sort` needs `npm run build`** and Livewire ≥ 4 beta.6 (repo is on ^4.3).
  The handler receives `(itemId, newPosition, groupId)` — group-id only on the
  destination for cross-column moves.
- **Don't duplicate `class=""`** when editing Blade with sed — HTML keeps only the
  first, silently dropping utility classes. Merge into one attribute.
- **`routeIs()` patterns**: nav items can pass an array of patterns; the layout
  splats them (`routeIs(...(array) $pattern)`). Use `'dashboard*'` to match child
  routes like `dashboard.crm`.
- **Static-only checks here.** This environment has no `vendor/`, so migrations,
  uploads, and Livewire rendering can't be run — only `php -l`, Blade tag pairing,
  and CSS brace balance. Anything needing a running app goes in `AUDIT-TODO.md`
  with a `V#` id, and gets a matching `// TODO(audit V#)` marker in the code.

## Verification workflow before claiming done

1. `php -l` every touched PHP file.
2. Blade: `@foreach/@endforeach`, `@if/@endif`, `@error/@enderror` counts match.
3. CSS: `{` and `}` counts match in `resources/css/app.css`.
4. No duplicate `class="..." class="..."` in touched views.
5. Cross-check any `view(...)` data keys against what the Service returns.
6. Record anything needing a live app in `AUDIT-TODO.md`.

## Roadmap (not yet built)

Time tracking, recurring invoices, client portal, habit tracker, Journal calendar
view, Pomodoro session logging, CSV import/export, proposal builder.
