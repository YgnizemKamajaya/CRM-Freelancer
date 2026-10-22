> Historical proposal, preserved on 22 October 2026. Several claims and suggested fixes were rejected or changed after validation. Do not execute this plan. Read [the current documentation index](../README.md) and [the independent review](../../04_Audit_Validation.md).

# CRM-Freelancer — Four-Dimension Full Scan, QA Audit & Completion Plan

**Project:** `CRM-Freelancer` (Laravel 12 · Livewire 4 · Alpine.js · Tailwind CSS 4 · Vite 7 · DomPDF)

**Architecture:** Single-user PIN-gated Freelancer Mini CRM + Personal Productivity Workspace (`Model → Service → Controller → Blade/Livewire`)

**Audit Scope:** 100% static & architectural code scan across all migrations, models, services, controllers, Livewire components, Blade views, CSS/JS assets, PWA files, tests, and documentation.

## 1. Executive Summary & Four-Dimension Scorecard

The codebase has a clean, well-structured `Model → Service → Controller → Blade/Livewire` foundation and a cohesive three-theme (`light`, `sepia`, `dark`) CSS-variable token system. However, because the repository evolved through multiple phases (an initial Breeze-like CRM MVP, a custom numpad lock screen, and a 6-module Personal Workspace expansion), several **hard database constraint crashes, soft-delete null-pointer exceptions, auth lockouts, disconnected UI routes, duplicate DOM buttons, broken PHPUnit tests, and 14+ orphan/noise files** remain in the tree.

| Audit Dimension | Critical (P0) | High (P1) | Medium (P2) | Low / Noise (P3) | Key Takeaway |
| ----- | ----- | ----- | ----- | ----- | ----- |
| **Dimension 1: Architecture, Schema & Data Layer** | 2 | 2 | 2 | 1 | `BoardShow::convertToProject()` crashes on `NOT NULL client_id`; archiving a client crashes Deals, Projects, Invoices, and CRM Dashboard with `null->name`; `checklist_items.for_date` bakes migration-day date into DDL. |
| **Dimension 2: Completeness, Scaffolding & Sync** | 0 | 4 | 5 | 3 | Home calendar date click (`?date=`) is ignored by `JournalController`; `tasks.destroy`, `workspace.update`, `workspace.destroy`, and `dashboard.crm` have backend routes but zero UI triggers; Client/Deal/Invoice `show` pages omit loaded relations. |
| **Dimension 3: Bug Hunting, UI Glitches & QA Tests** | 1 | 3 | 4 | 2 | Numpad login hardcodes 4 numeric digits (locks out users who set `CRM_PIN=change-this-pin` per `README.md`); all 5 Workspace edit forms render **two** red `Hapus` buttons; missing `chevron-right` icon; `PinAccessTest` fails. |
| **Dimension 4: File Noise, Dead Code & Stalled Docs** | 0 | 0 | 2 | 14 | 12 files are 100% unused (`mockuppanel.html`, `GuestLayout`, 8 Blade components, 2 boilerplate tests); `PRD.md` and `design-guideline-mini-crm.md` are out of sync; `flowbite` is unused; 52 CSS classes in `app.css` are dead. |
| **Total Findings** | **3** | **9** | **13** | **20** | **45 actionable engineering items** |

## 2. Dimension 1: Architecture, Database Schema & Configuration Alignment

| ID | Severity | Location (`File:Line`) | Issue Description & Root Cause |
| ----- | ----- | ----- | ----- |
| **D1-01** | **CRITICAL (P0)** | `app/Livewire/BoardShow.php:162-167` vs `database/migrations/2026_07_07_000001_create_crm_core_tables.php:57` | **`BoardShow::convertToProject()` Fatal SQLite/MySQL Crash (`NOT NULL constraint failed: projects.client_id`)**: The `projects` table defines `$table->foreignId('client_id')->constrained()->cascadeOnDelete()` (non-nullable). However, `BoardShow::convertToProject()` calls `Project::create(['title' => $card->title, 'description' => $card->body, 'status' => 'planned', 'priority' => $card->priority])` without `client_id`. Clicking "Jadikan project" on any Kanban card throws an immediate `QueryException`. Furthermore, while `home/index.blade.php:290` uses `$project->client?->name ?? 'Klien internal'`, `projects/index.blade.php:38` and `projects/show.blade.php:30` call `$project->client->name` without `?->`. |
| **D1-02** | **CRITICAL (P0)** | `app/Http/Controllers/ClientController.php:96-103` & `app/Models/{Deal,Project,Invoice,Task,Activity}.php` | **Archiving a Client Causes 500 Fatal Error (`Attempt to read property "name" on null`) Across CRM Views**: `ClientController::destroy()` archives a client by setting `archived_at = now()`, `status = 'archived'`, and calling `$client->delete()` (`SoftDeletes`). Because Eloquent `SoftDeletes` does not trigger SQL `ON DELETE CASCADE`, the client's deals, projects, and invoices remain active, but `$deal->client`, `$project->client`, and `$invoice->client` return `null`. Visiting `/deals`, `/deals/{deal}`, `/projects`, `/projects/{project}`, `/invoices`, `/invoices/{invoice}`, or `/dashboard/crm` crashes on `$model->client->name`. |
| **D1-03** | **HIGH (P1)** | `app/Http/Controllers/Concerns/ValidatesCrmRelations.php:11-19` | **`ownedExists()` Allows Linking Records to Soft-Deleted Models**: `Rule::exists($table, 'id')` does not filter `whereNull('deleted_at')` on soft-deletable tables (`clients`, `deals`, `projects`, `invoices`, `tasks`). Additionally, `authorizeOwner()` only checks `$model->exists` (which route-model binding already guarantees). |
| **D1-04** | **HIGH (P1)** | `database/migrations/2026_07_15_000004_create_checklist_tables.php:16` | **Static Migration-Time Date Baked into Schema Default**: `$table->date('for_date')->default(now()->toDateString())->index();` evaluates `now()` once when `php artisan migrate` runs and hardcodes that date string into the table DDL. |
| **D1-05** | **MEDIUM (P2)** | `resources/views/layouts/app.blade.php:49-52` & `resources/views/invoices/index.blade.php:235-237` | **Redundant DB Write/Read Queries in Views on Every Request**: `layouts/app.blade.php` runs `Setting::firstOrCreate(...)` directly inside the Blade template on every page render. In `invoices/index.blade.php:235-237`, `Setting::first()` is queried twice inline to build the E-Signature URL. Settings should be shared once via `View::composer` or a cached helper on `Setting`. |
| **D1-06** | **MEDIUM (P2)** | `database/seeders/DatabaseSeeder.php:140-154` | **Seeder Idempotency Bug & Missing Workspace Seed Data**: `Task::updateOrCreate(['title' => "Demo task {$i}"], ['title' => ...])` searches by `"Demo task {$i}"` but overwrites `title` with `"Follow up proposal {$i}"`, causing duplicate tasks on every re-seed. Moreover, `DatabaseSeeder` seeds zero records for `Wallet`, `Transaction`, `Budget`, `Goal`, `Board`, `ChecklistItem`, and `JournalEntry`, leaving Dashboard 2.0 and all Personal Workspace modules empty after `php artisan migrate --seed`. |
| **D1-07** | **LOW (P3)** | `.env.example:7` vs `app/Services/HomeService.php:54` & `app/Livewire/TodayChecklist.php:41` | **Locale Mismatch (`APP_LOCALE=en` vs Indonesian UI Copy)**: `.env.example` sets `APP_LOCALE=en`, so `now()->translatedFormat('l, d F Y')` outputs English day/month names (`Thursday, 22 October 2026`) amidst Indonesian copy (`Selamat pagi`, `Hari ini`). Setting `APP_LOCALE=id` makes dates match the Indonesian UI. |

## 3. Dimension 2: Functional Completeness, Unimplemented Wiring & Scaffolding Audit

| ID | Severity | Location (`File:Line`) | Unimplemented / Disconnected Feature |
| ----- | ----- | ----- | ----- |
| **D2-01** | **HIGH (P1)** | `resources/views/home/index.blade.php:229` → `app/Http/Controllers/JournalController.php:21-38` | **Home Calendar Date Click (`?date=YYYY-MM-DD`) Is Not Implemented in `JournalController@index`**: Clicking any date cell in the "Kalender & Agenda" card on Dashboard 2.0 navigates to `route('journal.index', ['date' => $tDate])`, but `JournalController::index()` only filters by `search`, `mood`, and `tag`—completely ignoring `date`. |
| **D2-02** | **HIGH (P1)** | `app/Http/Controllers/ClientController.php:59-75` & `resources/views/clients/show.blade.php:37-88` | **Client Detail Page Truncates Stat Card Totals at 8 Records & Omits Loaded `invoices` and `tasks` Lists**: `ClientController::show()` eager-loads `deals`, `projects`, `tasks`, and `invoices` with `->limit(8)`. `clients/show.blade.php` then computes `$client->deals->count()`, `$client->projects->count()`, and `$client->invoices->sum('total')` from those 8-item collections (under-reporting totals if a client has >8 items and including `cancelled` invoices). Furthermore, although `tasks` and `invoices` are eager-loaded, `clients/show.blade.php` never renders them and lacks quick "Add Task" / "Add Invoice" actions. |
| **D2-03** | **HIGH (P1)** | `routes/web.php:48,76-77` & `app/Http/Controllers/{BoardController,TaskController}.php` | **Orphan CRUD Actions (`tasks.destroy`, `workspace.update`, `workspace.destroy`) Have No UI Triggers**: (1) `TaskController::destroy()` exists, but neither `tasks/index.blade.php` nor `tasks/form.blade.php` has a Delete button. (2) `BoardController::update()` and `BoardController::destroy()` exist, but neither `workspace/index.blade.php` nor `livewire/board-show.blade.php` has UI to rename/edit or delete a Kanban board. |
| **D2-04** | **HIGH (P1)** | `routes/web.php:35`, `app/Services/DashboardService.php:43,52`, `resources/views/dashboard.blade.php` | **Legacy CRM Dashboard (`dashboard.crm`) Is Unreachable from UI & Omits Computed Data**: `/dashboard/crm` (`DashboardController`) has a `← Beranda` button back to `route('dashboard')`, but there is no link to `route('dashboard.crm')` anywhere in the sidebar, Command Centre (`home/index.blade.php`), or `QuickCapture`. In addition, `DashboardService::summary()` queries `overdue_tasks` and `monthly_revenue`, neither of which is rendered in `dashboard.blade.php`. |
| **D2-05** | **MEDIUM (P2)** | `app/Http/Controllers/DealController.php:66` (`deals/show.blade.php`) & `InvoiceController.php:88` (`invoices/show.blade.php`) | **Eager-Loaded `tasks` Never Rendered on Deal & Invoice Detail Pages**: Both `DealController::show()` and `InvoiceController::show()` eager-load `tasks`, but `deals/show.blade.php` and `invoices/show.blade.php` omit the related Tasks section and "Add Follow-up Task" shortcut. |
| **D2-06** | **MEDIUM (P2)** | `resources/views/invoices/form.blade.php:78-94` & `app/Http/Controllers/InvoiceController.php:50-69` | **Fixed 5-Row Invoice Item Limit & Ignored `default_tax_percentage`**: `invoices/form.blade.php` hardcodes `@for ($i = 0; $i < 5; $i++)` instead of an Alpine dynamic row repeater (truncating any invoice with >5 items on edit). Additionally, `Setting::default_tax_percentage` is configurable in Settings but ignored in `InvoiceController::create()`. |
| **D2-07** | **MEDIUM (P2)** | `app/Livewire/TodayChecklist.php:18-24` & `resources/views/livewire/today-checklist.blade.php` | **`TodayChecklist` Supports Arbitrary Dates (`$date`) But Has No Date Switcher in UI**: `TodayChecklist` accepts `$date` and computes `$isToday`, but the view lacks Previous Day / Today / Next Day controls. |
| **D2-08** | **MEDIUM (P2)** | `app/Livewire/QuickCapture.php:40,85-88` | **Empty Scaffold `comingSoonShortcuts()` & Missing Board/Goal Search in `QuickCapture`**: `QuickCapture::comingSoonShortcuts()` is an empty scaffold returning `[]`, and `QuickCapture` searches Clients, Deals, Projects, Invoices, Tasks, and Journal, but does not search Workspace Boards (`Board`), Goals (`Goal`), or Vault Files (`VaultFile`). |
| **D2-09** | **MEDIUM (P2)** | `resources/views/layouts/app.blade.php:40-48` | **Mobile Bottom Nav Excludes Personal Workspace**: A stale comment at line 40 states *"Finance/Workspace/Journal join it once they have real content (Fase 4)"*, leaving mobile users with only 5 CRM links (`Dashboard`, `Clients`, `Deals`, `Projects`, `Tasks`) on the bottom bar. |
| **D2-10** | **LOW (P3)** | `AUDIT-TODO.md`, `app/Services/FinanceService.php:11`, `app/Services/JournalService.php:9`, `resources/views/focus/index.blade.php:79` | **Stale & Deferred `TODO` Markers**: `FinanceService.php:11` still carries `// TODO(audit D2): add savings-goal progress` even though `AUDIT-TODO.md:103` marks `D2` as resolved via `GoalController`. Deferred roadmap items (`D1` Journal month calendar, `D6` QuickCapture card creation, and Pomodoro `focus_sessions` persistence) are documented in `AUDIT-TODO.md`. |

## 4. Dimension 3: Bug Hunting, Runtime Errors, UI Glitches & QA Test Failures

| ID | Severity | Location (`File:Line`) | Bug Description & Reproduction Steps |
| ----- | ----- | ----- | ----- |
| **D3-01** | **CRITICAL (P0)** | `resources/views/auth/login.blade.php:628-658,832` vs `app/Http/Controllers/AccessController.php:25` & `README.md:103` | **Permanent Auth Lockout When `CRM_PIN` Is Not Exactly 4 Numeric Digits**: `README.md` tells users to set `CRM_PIN=change-this-pin` in `.env`, and `AccessController::unlock()` validates `'pin' => ['required', 'string', 'max:32']`. However, `auth/login.blade.php` hardcodes `const MAX_PIN_LENGTH = 4;`, renders only 4 `.pin-dot` elements, and restricts keyboard/numpad input to digits `0–9`. Any user who configures a 6-digit PIN or an alphanumeric passphrase in `.env` cannot log in. |
| **D3-02** | **HIGH (P1)** | `resources/views/{goals,wallets,transactions,budgets,journal}/form.blade.php` & `resources/views/components/confirm-action.blade.php:14` | **Duplicate Red "Hapus" (Delete) Buttons on All 5 Personal Workspace Edit Pages**: To avoid nested `<form>` tags, all 5 edit views place `<x-confirm-action label="Hapus" ... />` at the top of `<div x-data="{}">` and also render a manual `<button type="button" class="crm-button-danger">Hapus</button>` in the form footer. Because `confirm-action.blade.php` always outputs its own trigger `<button>`, every edit screen renders **two** red `Hapus` buttons (one floating unaligned at the top-left of the page, and one in the form footer). |
| **D3-03** | **HIGH (P1)** | `resources/views/home/index.blade.php:122` & `resources/views/components/icon.blade.php:3-78` | **Missing `chevron-right` Icon Case in `x-icon` Component**: `home/index.blade.php:122` renders `<x-icon name="chevron-right" class="h-4 w-4" />` on every alert row in "Perhatian & Reminders", but `icon.blade.php` only defines `chevron-down`. Every alert arrow falls back to `@default` (rendering a document rectangle icon instead of a right chevron). |
| **D3-04** | **HIGH (P1)** | `tests/Feature/PinAccessTest.php:18-23` vs `resources/views/auth/login.blade.php:636` | **Failing Feature Test (`PinAccessTest::test_pin_login_screen_can_be_rendered`)**: The test asserts `->assertSee('Unlock workspace')`, which existed in the old Breeze login view before `auth/login.blade.php` was replaced by the numpad lock screen (`Enter PIN to unlock`). Running `php artisan test` fails on this assertion. |
| **D3-05** | **MEDIUM (P2)** | `app/Services/FinanceService.php:20-32,47,129-135` | **Archived Wallets Included in `total_balance`, Home Wallets Card & Auto-Tracked Goals**: `FinanceService::walletsWithBalances()` queries all `Wallet` rows without filtering `where('is_archived', false)`. Archiving a wallet dims it on `/wallets`, but its balance is still added to `total_balance` on `/finance` and `/dashboard`, listed in Dashboard 2.0's wallets preview, and counted in `goalCurrentAmount()` when `wallet_id` is null. |
| **D3-06** | **MEDIUM (P2)** | `app/Http/Controllers/InvoiceController.php:71-83,107-115` & `app/Services/InvoiceService.php:41-58,81-100` | **Direct Invoice Creation/Update as `paid` Skips Auto-Income Recording & Overwrites `paid` Status**: (1) `InvoiceController::markPaid()` calls `$financeService->recordInvoicePayment($invoice)`, but creating or updating an invoice with full `amount_paid` in `store()`/`update()` marks it `paid` without calling `recordInvoicePayment()`. (2) If a user selects `status = 'paid'` in the form while leaving `amount_paid = 0`, `InvoiceService::syncTotals()` resets `status` back to `'sent'` instead of defaulting `amount_paid` to `$total`. |
| **D3-07** | **MEDIUM (P2)** | `app/Livewire/TodayChecklist.php:63-77` & `app/Livewire/HomeFocusList.php:56-70` | **Unchecking a Linked Checklist Item Does Not Reopen the Linked CRM Task**: Checking a `ChecklistItem` with a `task_id` marks the CRM `Task` as `done`, but unchecking it (`is_done = false`) leaves the CRM `Task` stuck in `done` status instead of reverting it to `pending`. |
| **D3-08** | **MEDIUM (P2)** | `resources/views/invoices/index.blade.php:6,229` & `resources/views/auth/login.blade.php:21-23` | **External CDN Dependencies Break Offline/Local-First PWA Operation**: `invoices/index.blade.php` loads CropperJS 1.6.2 from `cdnjs.cloudflare.com`, and `auth/login.blade.php` loads Inter from `fonts.googleapis.com`. When offline or behind a strict firewall, E-Signature cropping fails with `ReferenceError: Cropper is not defined`. |
| **D3-09** | **LOW (P3)** | `resources/views/home/index.blade.php:235-238,250-252`, `layouts/app.blade.php:432`, `invoices/index.blade.php:138-220` | **Hardcoded Colors & Dark-Mode Contrast Glitches**: `home/index.blade.php` uses inline `oklch(...)` styles for calendar dots instead of the existing `.cal-dot--*` classes in `app.css`; `layouts/app.blade.php:432` uses light-only `bg-green-50 text-green-700` for session status alerts; `invoices/index.blade.php` E-Signature modal uses hardcoded `bg-white text-gray-900` without `--crm-*` tokens. |

## 5. Dimension 4: File Noise, Dead Code, Orphans & Stalled Docs (`Safe to Delete`)

### 5.1 Files 100% Safe to Delete Immediately (12 Files)

Every file listed below has **zero active references** in routes, controllers, Livewire classes, or Blade views:

| \# | File Path | Size / Lines | Category | Why It Is Safe to Delete |
| ----- | ----- | ----- | ----- | ----- |
| 1 | `mockuppanel.html` | 33.1 KB (932 L) | Prototype Noise | Standalone static HTML prototype of the lock screen; already ported into `resources/views/auth/login.blade.php`. |
| 2 | `app/View/Components/GuestLayout.php` | 395 B (17 L) | Orphan Class | Laravel Breeze `<x-guest-layout>` class; `/login` uses standalone `auth/login.blade.php`. |
| 3 | `resources/views/layouts/guest.blade.php` | 2.6 KB (55 L) | Orphan View | Unused guest layout template from the initial Breeze scaffold; also contains outdated copy (*"8 CRM modules"*). |
| 4 | `resources/views/components/coming-soon.blade.php` | 1.3 KB (34 L) | Scaffold Leftover | Phase 1–3 placeholder card (`<x-coming-soon>`); all 7 Personal Workspace modules are now built and live. |
| 5 | `resources/views/components/application-logo.blade.php` | 341 B (6 L) | Orphan Component | Breeze scaffold component; `layouts/app.blade.php` uses `.crm-brand-mark` directly. |
| 6 | `resources/views/components/auth-session-status.blade.php` | 190 B (7 L) | Orphan Component | Breeze scaffold component; never referenced in any view. |
| 7 | `resources/views/components/danger-button.blade.php` | 115 B (3 L) | Orphan Component | Breeze scaffold component; views use `.crm-button-danger` directly. |
| 8 | `resources/views/components/input-label.blade.php` | 150 B (5 L) | Orphan Component | Breeze scaffold component; views use `.crm-label` directly. |
| 9 | `resources/views/components/primary-button.blade.php` | 116 B (3 L) | Orphan Component | Breeze scaffold component; views use `.crm-button-primary` directly. |
| 10 | `resources/views/components/secondary-button.blade.php` | 118 B (3 L) | Orphan Component | Breeze scaffold component; views use `.crm-button-secondary` directly. |
| 11 | `resources/views/components/text-input.blade.php` | 169 B (3 L) | Orphan Component | Breeze scaffold component; views use `.crm-field` directly. |
| 12 | `tests/Unit/ExampleTest.php` & `tests/Feature/ExampleTest.php` | 650 B (35 L) | Boilerplate Tests | Default Laravel skeleton tests (`assertTrue(true)` and duplicate of `PinAccessTest`). |

```
# Safe-to-delete cleanup command (run from project root)
rm -f mockuppanel.html \
      app/View/Components/GuestLayout.php \
      resources/views/layouts/guest.blade.php \
      resources/views/components/coming-soon.blade.php \
      resources/views/components/application-logo.blade.php \
      resources/views/components/auth-session-status.blade.php \
      resources/views/components/danger-button.blade.php \
      resources/views/components/input-label.blade.php \
      resources/views/components/primary-button.blade.php \
      resources/views/components/secondary-button.blade.php \
      resources/views/components/text-input.blade.php \
      tests/Unit/ExampleTest.php \
      tests/Feature/ExampleTest.php

```

### 5.2 Stalled / Out-of-Sync Documentation Files (2 Files to Delete or Archive)

| File Path | Status | Specific Contradictions vs Current Codebase | Recommended Action |
| ----- | ----- | ----- | ----- |
| `PRD.md` (34.7 KB, 1,886 lines) | **Stalled / Out of Sync** | Section 5 forbids E-Signature and Kanban drag-and-drop (both are now implemented); Section 12 defines `User` model relationships (`belongsTo User`), contradicting the single-user userless architecture; Section 19 specifies `Password: password` login; omits all 7 Personal Workspace modules. | Delete from root (or move to `docs/archive/PRD-v1-mvp.md` if kept for historical portfolio reference). `README.md` + `CLAUDE.md` are the authoritative docs. |
| `design-guideline-mini-crm.md` (11.7 KB, 239 lines) | **Stalled / Out of Sync** | Specifies pastel blue (`#3B82F6` / `primary-600`) Flowbite buttons and standard Tailwind `dark:bg-gray-900` classes, whereas the actual app uses a custom 3-theme (`light`, `sepia`, `dark`) `oklch()` CSS-variable token architecture (`--crm-*`) documented in `CLAUDE.md`. | Delete from root (or move to `docs/archive/`), as it misleads contributors/AI agents into writing non-compliant Tailwind colors instead of `--crm-*` tokens. |

### 5.3 Unused Package Dependency & Dead CSS Selectors

1. **Unused `flowbite` NPM Dependency**:

   * `package.json` depends on `"flowbite": "^4.0.2"`, `resources/js/app.js` imports `'flowbite'`, and `resources/css/app.css` loads `@plugin 'flowbite/plugin';` and `@source '../../node_modules/flowbite/**/*.js';`.

   * Zero Flowbite JS attributes (`data-modal-*`, `data-drawer-*`, `data-dropdown-*`, `data-tooltip-*`) are used anywhere in the project; interactivity is handled 100% by Livewire 4 and Alpine.js. Removing Flowbite shrinks the Vite JS/CSS bundle substantially.

2. **52 Dead Custom CSS Selectors in `resources/css/app.css`**:

   * **Superseded Home v1 / Command v1 classes (lines 1328–1694 & 1934–2135)**: `.home-shell`, `.home-hero`, `.home-hero-date`, `.home-hero-greeting`, `.home-hero-glance`, `.home-glance-num`, `.home-glance-label`, `.home-glance-sep`, `.home-grid`, `.home-col`, `.home-attention`, `.home-balance`, `.home-balance-label`, `.home-cashflow`, `.home-cashflow-col`, `.home-cashflow-bars`, `.home-cashflow-label`, `.home-cashflow-legend`, `.home-mood-meta`, `.command-hero-main`, `.command-copy`, `.command-grid`, `.command-now`, `.command-clock`, `.command-ring`, `.command-finance`, `.command-attention`, `.command-goal`, `.command-journal`, `.command-pipeline`, `.command-empty-card`, `.is-command`.

   * **Superseded Sidebar Theme Switcher classes (lines 300–325, 487–558, 658–676)**: `.crm-sidebar-toggle-floating`, `.crm-theme-panel`, `.crm-theme-choice`, `.crm-theme-label`, `.crm-theme-swatch`, `.is-light`, `.is-sepia`, `.is-dark`, `.crm-settings-trigger`, `.crm-user-avatar`, `.crm-logout`.

   * **Unused Mini-Calendar classes (lines 2625–2684)**: `.mini-calendar-grid`, `.mini-calendar-head`, `.mini-calendar-day`, `.mini-calendar-dots`, `.cal-dot`, `.cal-dot--journal`, `.cal-dot--task`, `.cal-dot--project`, `.cal-dot--invoice` — these should be **re-connected** to `resources/views/home/index.blade.php` to replace the inline `style=""` attributes!

## 6. Refined Phased Execution Plan

### Phase 1 — P0 Critical Crash & Auth Lockout Fixes (Immediate)

1. **Fix `BoardShow::convertToProject()` (`D1-01`)**: Create an additive migration making `projects.client_id` nullable (supporting internal/workspace-promoted projects), update `ProjectController::validated()` so `client_id` is nullable, and use null-safe `$project->client?->name ?? 'Internal'` across `projects/index.blade.php` and `projects/show.blade.php`.

2. **Fix Soft-Deleted Client Null-Pointer Crashes (`D1-02`, `D1-03`)**: Add `->withTrashed()` to the `client()` `BelongsTo` relationship on `Deal`, `Project`, `Invoice`, `Task`, `Activity`, `JournalEntry`, and `VaultFile` so historical records still display the archived client's name, and update `ValidatesCrmRelations::ownedExists()` to enforce `->whereNull('deleted_at')` on soft-deletable tables.

3. **Fix Numpad PIN Length & Alphanumeric Fallback (`D3-01`, `D3-04`)**: Update `resources/views/auth/login.blade.php` to dynamically adapt to the configured `CRM_PIN` length (or provide a standard password input fallback when `CRM_PIN` is non-numeric/longer than 4 digits) and align `PinAccessTest` with the lock screen copy.

### Phase 2 — P1 UI Glitches, Missing Icon & Disconnected Routes

1. **Eliminate Duplicate `Hapus` Buttons (`D3-02`)**: Add a `hideTrigger` prop (`@props([..., 'hideTrigger' => false])`) to `resources/views/components/confirm-action.blade.php` and pass `:hide-trigger="true"` in `wallets/form.blade.php`, `transactions/form.blade.php`, `budgets/form.blade.php`, `goals/form.blade.php`, and `journal/form.blade.php`.

2. **Add Missing `chevron-right` & `chevron-left` Icons (`D3-03`)**: Add `@case('chevron-right')` and `@case('chevron-left')` to `resources/views/components/icon.blade.php`.

3. **Wire `?date=` Filter in `JournalController@index` (`D2-01`)**: Handle `$request->filled('date')` in `JournalController::index()` with `whereDate('entry_date', $request->date)` and display an active date filter chip in `journal/index.blade.php`.

4. **Fix Client Detail Stats & Render Missing `Invoices` and `Tasks` Sections (`D2-02`, `D2-05`)**: Use `loadCount(['deals', 'projects'])` and `loadSum` on `ClientController::show()` for accurate totals, and render the `Invoices` and `Tasks` panels on `clients/show.blade.php` (plus related `Tasks` on `deals/show.blade.php` and `invoices/show.blade.php`).

5. **Expose Orphan Routes (`tasks.destroy`, `workspace.update`, `workspace.destroy`, `dashboard.crm`) (`D2-03`, `D2-04`)**:

   * Add Delete confirmation to `tasks/form.blade.php`.

   * Add Board rename/edit and delete controls to `livewire/board-show.blade.php`.

   * Add a link to `route('dashboard.crm')` ("Ringkasan CRM Lengkap →") on `home/index.blade.php` and in `QuickCapture.php`, and render `overdue_tasks` and `monthly_revenue` on `dashboard.blade.php`.

### Phase 3 — P2 Business Logic Polish, Dynamic Forms & Seeder Completion

1. **Exclude Archived Wallets from Active Totals (`D3-05`)**: Update `FinanceService::walletsWithBalances(bool $includeArchived = false)` so `FinanceService::summary()` and `goalCurrentAmount()` only sum non-archived wallets, while `WalletController::index()` passes `includeArchived: true`.

2. **Sync Invoice `paid` Status & Auto-Income Transaction on Create/Update (`D3-06`)**: Update `InvoiceService::syncTotals()` and `InvoiceController::{store,update}` so marking an invoice `paid` via form submission defaults `amount_paid = total` (if `0`) and triggers `FinanceService::recordInvoicePayment($invoice)`.

3. **Upgrade Invoice Line Items to Dynamic Alpine Repeater & Pre-fill Default Tax (`D2-06`)**: Replace the hardcoded `@for ($i = 0; $i < 5; $i++)` loop in `invoices/form.blade.php` with an Alpine `x-for` repeater supporting unlimited items and live subtotal/tax preview.

4. **Sync Checklist Uncheck with CRM Task & Add Date Switcher to `TodayChecklist` (`D2-07`, `D3-07`)**: Revert linked CRM task to `pending` when unchecked, and add Previous/Today/Next date navigation to `livewire/today-checklist.blade.php`.

5. **Fix `DatabaseSeeder` Idempotency & Seed Personal Workspace Modules (`D1-06`)**: Fix the task title key in `DatabaseSeeder.php` and seed demo Wallets, Transactions, Budgets, Goals, Kanban Boards/Cards, Checklist Items, and Journal Entries.

### Phase 4 — P3 Codebase Cleanup & CSS Consolidation

1. **Run the Safe-to-Delete Script**: Remove the 12 unused files (`mockuppanel.html`, `GuestLayout`, 8 unused Blade components, 2 boilerplate tests) and archive/delete `PRD.md` and `design-guideline-mini-crm.md`.

2. **Remove Unused `flowbite` Dependency & Prune Dead CSS**: Remove `flowbite` from `package.json`, `resources/js/app.js`, and `resources/css/app.css`, and replace inline calendar styles in `home/index.blade.php` with `.mini-calendar-*` and `.cal-dot--*` classes.

## 7. Complete Code Fix Examples (Copy-Paste Ready)

### Fix 1: Make `projects.client_id` Nullable & Fix `BoardShow::convertToProject()` (`D1-01`)

Create a new additive migration `database/migrations/2026_07_22_000001_make_projects_client_id_nullable.php` (and fix `checklist_items.for_date` default):

```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable(false)->change();
        });
    }
};

```

Update `app/Http/Controllers/ProjectController.php` (`validated()` method, line 173) so internal projects promoted from Kanban boards can be saved without a client:

```
    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['nullable', $this->ownedExists('clients')],
            'deal_id' => ['nullable', $this->ownedExists('deals')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(array_keys(Project::STATUSES))],
            'priority' => ['required', Rule::in(array_keys(Project::PRIORITIES))],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'start_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
        ]);
    }

```

And in `resources/views/projects/index.blade.php` (line 38) and `resources/views/projects/show.blade.php` (line 30), use the null-safe operator:

```
{{-- resources/views/projects/index.blade.php:38 --}}
<p class="mt-1 text-sm text-gray-500">{{ $project->client?->name ?? 'Klien internal' }}</p>

{{-- resources/views/projects/show.blade.php:30 --}}
<p class="mt-2 text-sm text-gray-500">{{ $project->client?->name ?? 'Klien internal' }} · {{ $money($project->budget) }}</p>

```

### Fix 2: Prevent 500 Crashes When a Client Is Archived (`D1-02`, `D1-03`)

In `app/Models/Deal.php`, `app/Models/Project.php`, `app/Models/Invoice.php`, `app/Models/Task.php`, `app/Models/Activity.php`, `app/Models/JournalEntry.php`, and `app/Models/VaultFile.php`, include soft-deleted clients on the `client()` relationship so archived clients still resolve their name on historical records:

```
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

```

And update `app/Http/Controllers/Concerns/ValidatesCrmRelations.php` so new records cannot be linked to soft-deleted entities:

```
<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

trait ValidatesCrmRelations
{
    private const SOFT_DELETED_TABLES = [
        'clients',
        'deals',
        'projects',
        'invoices',
        'tasks',
        'activities',
        'journal_entries',
    ];

    protected function ownedExists(string $table): Exists
    {
        $rule = Rule::exists($table, 'id');

        if (in_array($table, self::SOFT_DELETED_TABLES, true)) {
            $rule->whereNull('deleted_at');
        }

        return $rule;
    }

    protected function authorizeOwner(Model $model): void
    {
        abort_unless($model->exists, 404);
    }
}

```

### Fix 3: Eliminate Duplicate `Hapus` Buttons on All 5 Workspace Edit Forms (`D3-02`)

Update `resources/views/components/confirm-action.blade.php` to support `hideTrigger`:

```
@props([
    'action',
    'method' => 'DELETE',
    'label' => 'Delete',
    'title' => 'Confirm action',
    'body' => 'This action cannot be undone.',
    'confirmLabel' => 'Confirm',
    'buttonClass' => 'crm-button-danger',
    'hideTrigger' => false,
])

@php($modalName = 'confirm-action-'.md5($action.$label.$title))

<div>
    @if (! $hideTrigger)
        <button type="button" class="{{ $buttonClass }}" x-data x-on:click.prevent="$dispatch('open-modal', '{{ $modalName }}')">
            {{ $label }}
        </button>
    @endif

    <x-modal :name="$modalName" focusable>
        <form method="POST" action="{{ $action }}" class="p-6">
            @csrf
            @method($method)

            <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ $body }}</p>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="crm-button-secondary" x-on:click="$dispatch('close')">Cancel</button>
                <button class="crm-button-danger">{{ $confirmLabel }}</button>
            </div>
        </form>
    </x-modal>
</div>

```

Then in `resources/views/wallets/form.blade.php`, `resources/views/transactions/form.blade.php`, `resources/views/budgets/form.blade.php`, `resources/views/goals/form.blade.php`, and `resources/views/journal/form.blade.php`, pass `:hide-trigger="true"` on the top `<x-confirm-action>` call:

```
@if ($editing)
    <x-confirm-action
        :action="route('goals.destroy', $goal)"
        label="Hapus"
        title="Hapus goal?"
        body="Tindakan ini tidak bisa dibatalkan."
        :hide-trigger="true"
    />
@endif

```

### Fix 4: Add Missing `chevron-right` & `chevron-left` Icons (`D3-03`)

In `resources/views/components/icon.blade.php`, add `@case('chevron-right')` and `@case('chevron-left')` right after `@case('chevron-down')` (line 45):

```
        @case('chevron-down')
            <path stroke-linecap="round" stroke-linejoin="round" d="m6.75 9.75 5.25 5.25 5.25-5.25" />
            @break
        @case('chevron-right')
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 6.75 5.25 5.25L9 17.25" />
            @break
        @case('chevron-left')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17.25 9.75 12 15 6.75" />
            @break

```

### Fix 5: Implement `?date=` Filter in `JournalController@index` (`D2-01`)

Update `app/Http/Controllers/JournalController.php` (`index()` method, lines 21–38):

```
    public function index(Request $request, JournalService $journal): View
    {
        $entries = JournalEntry::query()
            ->with('client')
            ->search($request->string('search')->toString())
            ->when($request->filled('mood'), fn ($query) => $query->where('mood', $request->mood))
            ->when($request->filled('tag'), fn ($query) => $query->whereJsonContains('tags', $request->string('tag')->toString()))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('entry_date', $request->string('date')->toString()))
            ->orderByDesc('entry_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('journal.index', $journal->overview() + [
            'entries' => $entries,
            'moods' => JournalEntry::MOODS,
            'moodEmoji' => JournalEntry::MOOD_EMOJI,
        ]);
    }

```

### Fix 6: Exclude Archived Wallets from Active Balance & Sync Invoice Payment on Store/Update (`D3-05`, `D3-06`)

Update `app/Services/FinanceService.php` (`walletsWithBalances()` and `summary()`):

```
    public function walletsWithBalances(bool $includeArchived = false): Collection
    {
        return Wallet::query()
            ->when(! $includeArchived, fn ($query) => $query->where('is_archived', false))
            ->withSum(['transactions as income_sum' => fn ($query) => $query->where('type', 'income')], 'amount')
            ->withSum(['transactions as expense_sum' => fn ($query) => $query->where('type', 'expense')], 'amount')
            ->orderBy('is_archived')
            ->orderBy('name')
            ->get()
            ->each(function (Wallet $wallet) {
                $wallet->balance = (float) $wallet->opening_balance
                    + (float) $wallet->income_sum
                    - (float) $wallet->expense_sum;
            });
    }

```

And in `app/Http/Controllers/WalletController.php` (line 20), pass `includeArchived: true` so the Wallets management screen still lists archived wallets:

```
    public function index(FinanceService $finance): View
    {
        return view('wallets.index', [
            'wallets' => $finance->walletsWithBalances(includeArchived: true),
        ]);
    }

```

Update `app/Services/InvoiceService.php` (`syncTotals()`, lines 41–58) so selecting `status = 'paid'` defaults `amount_paid` to `$total`, and trigger `FinanceService::recordInvoicePayment($invoice)` inside `InvoiceController::store()` and `InvoiceController::update()`:

```
    // In app/Services/InvoiceService.php
    public function syncTotals(Invoice $invoice): Invoice
    {
        $subtotal = (float) $invoice->items()->sum('total');
        $discount = (float) $invoice->discount;
        $tax = (float) $invoice->tax;
        $total = max(0, $subtotal - $discount + $tax);

        $rawPaid = (float) $invoice->amount_paid;
        if ($invoice->status === 'paid' && $rawPaid <= 0 && $total > 0) {
            $rawPaid = $total;
        }
        $amountPaid = min($rawPaid, $total);
        $nextStatus = $this->statusFor($invoice, $amountPaid, $total);

        $invoice->forceFill([
            'subtotal' => $subtotal,
            'total' => $total,
            'amount_paid' => $amountPaid,
            'status' => $nextStatus,
            'paid_at' => $nextStatus === 'paid' ? ($invoice->paid_at ?? now()) : null,
        ])->save();

        return $invoice;
    }

```

```
    // In app/Http/Controllers/InvoiceController.php (store & update)
    public function store(Request $request, InvoiceService $invoiceService, FinanceService $financeService): RedirectResponse
    {
        $data = $this->validated($request);
        $invoice = Invoice::create(collect($data)->except('items')->all());
        $invoice = $invoiceService->replaceItemsAndSync($invoice, $data['items'] ?? []);

        if ($invoice->status === 'paid') {
            $financeService->recordInvoicePayment($invoice);
        }

        $setting = Setting::firstOrCreate([]);
        if (str_starts_with($invoice->invoice_number, $setting->invoice_prefix.'-')) {
            $setting->increment('invoice_next_number');
        }

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice created.');
    }

```

### Fix 7: Support Arbitrary PIN Length / Passphrase in `auth/login.blade.php` & Fix `PinAccessTest` (`D3-01`, `D3-04`)

In `resources/views/auth/login.blade.php`, compute the PIN length and numeric vs alphanumeric mode from `config('crm.pin')` so users who change `CRM_PIN` in `.env` are never locked out:

```
@php
    $configuredPin = (string) config('crm.pin', '1234');
    $isNumericPin = ctype_digit($configuredPin) && strlen($configuredPin) >= 4 && strlen($configuredPin) <= 8;
    $pinLength = $isNumericPin ? strlen($configuredPin) : 4;
@endphp

```

Render `$pinLength` dots dynamically when `$isNumericPin` is true (and set `const MAX_PIN_LENGTH = {{ $pinLength }};` in the script), or show a clean password input form when `$isNumericPin` is false:

```
@if ($isNumericPin)
    <div class="pin-display" id="pinDisplay">
        @for ($i = 0; $i < $pinLength; $i++)
            <div class="pin-dot" data-index="{{ $i }}"></div>
        @endfor
    </div>
    <div class="status-message" id="statusMessage">Enter PIN to unlock</div>
    {{-- Numpad --}}
@else
    <form method="POST" action="{{ route('login') }}" style="width: 100%; display: grid; gap: 0.75rem;">
        @csrf
        <input type="password" name="pin" required autofocus placeholder="Enter workspace PIN"
               style="width: 100%; padding: 0.7rem 0.9rem; border-radius: 8px; border: 1px solid var(--crm-line-strong); background: var(--crm-surface-soft); color: var(--crm-heading);">
        @error('pin')
            <div class="status-message error">{{ $message }}</div>
        @enderror
        <button type="submit" class="btn-continue" style="margin-top: 0;">Unlock workspace</button>
    </form>
@endif

```

And update `tests/Feature/PinAccessTest.php` (line 18):

```
    public function test_pin_login_screen_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Enter PIN to unlock');
    }

```

### Fix 8: Fix Client Detail Page Truncated Totals & Render Client Invoices + Tasks (`D2-02`)

Update `app/Http/Controllers/ClientController.php` (`show()`, lines 59–75):

```
    public function show(Client $client): View
    {
        $this->authorizeOwner($client);

        $client->load([
            'deals' => fn ($query) => $query->latest()->limit(8),
            'projects' => fn ($query) => $query->latest()->limit(8),
            'tasks' => fn ($query) => $query->orderByRaw('due_at is null, due_at asc')->latest()->limit(8),
            'invoices' => fn ($query) => $query->latest('issue_date')->limit(8),
            'activities' => fn ($query) => $query->latest('activity_at')->latest()->limit(12),
        ])->loadCount(['deals', 'projects']);

        $invoiceTotals = $client->invoices()
            ->where('status', '!=', 'cancelled')
            ->get(['total', 'amount_paid', 'status']);

        return view('clients.show', [
            'client' => $client,
            'activityTypes' => Activity::TYPES,
            'invoicedTotal' => $invoiceTotals->sum(fn ($inv) => (float) $inv->total),
            'outstandingTotal' => $invoiceTotals->where('status', '!=', 'paid')->sum(fn ($inv) => $inv->balance),
        ]);
    }

```

Then in `resources/views/clients/show.blade.php`, update the 4 stat cards (lines 37–42) and append the `Invoices` and `Tasks` cards below `Deals` and `Projects`:

```
        <section class="grid gap-4 md:grid-cols-4">
            <x-stat-card label="Deals" :value="$client->deals_count" icon="pipeline" />
            <x-stat-card label="Projects" :value="$client->projects_count" icon="briefcase" tone="green" />
            <x-stat-card label="Invoiced" :value="$money($invoicedTotal)" icon="document" tone="amber" />
            <x-stat-card label="Outstanding" :value="$money($outstandingTotal)" icon="document" tone="rose" />
        </section>

```

```
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="crm-card overflow-hidden">
                        <div class="flex items-center justify-between border-b border-gray-200 p-4">
                            <h3 class="font-semibold text-gray-900">Invoices</h3>
                            <a href="{{ route('invoices.create', ['client_id' => $client->id]) }}" class="text-sm font-medium text-primary-600">Add</a>
                        </div>
                        @forelse ($client->invoices as $invoice)
                            <a href="{{ route('invoices.show', $invoice) }}" class="flex items-center justify-between border-b border-gray-100 p-4 last:border-0 hover:bg-gray-50">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $invoice->invoice_number }}</p>
                                    <p class="text-sm text-gray-500">Due {{ $invoice->due_date?->format('d M Y') ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-gray-900">{{ $money($invoice->total) }}</p>
                                    <x-status-badge :status="$invoice->status" />
                                </div>
                            </a>
                        @empty
                            <div class="p-4 text-sm text-gray-500">No invoices yet.</div>
                        @endforelse
                    </div>

                    <div class="crm-card overflow-hidden">
                        <div class="flex items-center justify-between border-b border-gray-200 p-4">
                            <h3 class="font-semibold text-gray-900">Tasks</h3>
                            <a href="{{ route('tasks.create', ['client_id' => $client->id]) }}" class="text-sm font-medium text-primary-600">Add</a>
                        </div>
                        @forelse ($client->tasks as $task)
                            <a href="{{ route('tasks.edit', $task) }}" class="flex items-center justify-between border-b border-gray-100 p-4 last:border-0 hover:bg-gray-50">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $task->title }}</p>
                                    <p class="text-sm text-gray-500">Due {{ $task->due_at?->format('d M Y H:i') ?? 'not set' }}</p>
                                </div>
                                <x-status-badge :status="$task->status" />
                            </a>
                        @empty
                            <div class="p-4 text-sm text-gray-500">No tasks yet.</div>
                        @endforelse
                    </div>
                </div>

```

### Fix 9: Wire Orphan UI Actions (`Board` Edit/Delete & `Task` Delete) (`D2-03`)

In `resources/views/livewire/board-show.blade.php` (header actions, lines 10–15), add a Delete Board form (`workspace.destroy`) alongside the Add Column form:

```
        <div class="flex flex-wrap items-center gap-2">
            <form wire:submit="addColumn" class="flex items-center gap-2">
                <input wire:model="newColumnName" class="crm-field mt-0" style="width: 11rem;" placeholder="Kolom baru...">
                <button class="crm-button-secondary"><x-icon name="plus" class="h-4 w-4" />Kolom</button>
            </form>
            <form method="POST" action="{{ route('workspace.destroy', $board) }}" onsubmit="return confirm('Hapus board ini beserta semua kolom dan kartunya?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="crm-button-secondary text-expense" title="Hapus board">
                    <x-icon name="trash" class="h-4 w-4" />
                </button>
            </form>
        </div>

```

And in `resources/views/tasks/form.blade.php` (lines 85–88), wire the existing `tasks.destroy` route when editing a task:

```
        <div class="flex items-center justify-between gap-3">
            <div>
                @if ($editing)
                    <button type="button" class="crm-button-danger"
                        x-on:click.prevent="$dispatch('open-modal', '{{ 'confirm-action-'.md5(route('tasks.destroy', $task).'Delete'.'Delete task?') }}')">
                        Delete
                    </button>
                @endif
            </div>
            <div class="flex gap-3">
                <a href="{{ route('tasks.index') }}" class="crm-button-secondary">Cancel</a>
                <button class="crm-button-primary">{{ $editing ? 'Save Task' : 'Create Task' }}</button>
            </div>
        </div>

```

(With `<x-confirm-action :action="route('tasks.destroy', $task)" label="Delete" title="Delete task?" body="This task will be removed." :hide-trigger="true" />` placed above `<form>` inside `<div x-data="{}">`).
