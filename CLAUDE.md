# Contributor and assistant guide

Read [README.md](README.md) for setup and behavior, [AUDIT-TODO.md](AUDIT-TODO.md) for remaining checks, and [docs/README.md](docs/README.md) for audit history. Check the current code before relying on any document.

## Project structure

This is a single-user app unlocked with a PIN. It has no `users` table, registration or per-user ownership fields. The `pin` middleware protects workspace routes. Login, logout, the root redirect, offline page and framework health endpoint sit outside that route group. Private file access requires the PIN; public profile/business images and signatures use the public storage disk.

The stack is Laravel 12, Livewire 4, Blade, Alpine.js, Tailwind CSS 4, Vite 7 and DomPDF. SQLite is the default database. SQLite and MySQL 8.4.9 have passed the latest suite; MariaDB remains unverified.

| Location | Purpose |
| --- | --- |
| `app/Models` | Stored records and relationships |
| `app/Services` | Shared calculations and business operations |
| `app/Http/Controllers` | Request validation, service calls and responses |
| `app/Livewire` | Interactive server-backed components |
| `resources/views` | Blade pages and reusable components |
| `resources/css/app.css` | Theme variables and shared styles |
| `routes/web.php` | Application routes |
| `tests/Feature` | Request and behavior tests |

Controllers currently contain ordinary queries and validation. Keep complex or shared operations in services; follow nearby code rather than introducing a new structure for a small change.

## Working rules

1. Prefer codebase-memory MCP tools for finding code and reading symbols. Index the repository if it is not indexed. Use text search for literals/configuration and when graph results are insufficient.
2. Protect workspace routes with `pin`. `authorizeOwner()` only checks that a model exists; it does not implement user-based authorization. Do not treat its name as evidence of separate users.
3. Use `ValidatesCrmRelations::ownedExists()` for related-record validation and `relationOptions()` where an edit must retain an existing archived link. Historical client relations include archived clients; render their labels with `x-client-link` to avoid broken links.
4. Add new migrations to change an installed schema. Do not rewrite already-applied migrations. Projects require a client; card promotion must request an active client.
5. Use the existing `--crm-*` CSS variables for theme-dependent colors. They cover light, sepia and dark themes, including income, expenses, moods and priorities.
6. Reuse Blade components such as `x-icon`, `x-stat-card`, `x-empty-state`, `x-confirm-action`, `x-input-error` and `x-client-link`. New meters use `.crm-meter`.
7. Match the surrounding Indonesian interface text. Many views format money with local closures; inspect the view's currency handling rather than assuming every amount is IDR.
8. Livewire classes live in `app/Livewire` and templates in `resources/views/livewire`. Full-page components use `#[Layout('layouts.app')]`. Dragging uses Livewire's built-in `wire:sort`.
9. Keep database changes that must succeed together inside a transaction. Preserve manual financial entries when updating invoice-managed income.
10. Do not seed a working database. `DatabaseSeeder` updates sample records and settings; `composer setup` runs it too.

## Module map

| Area | Named route | Main files |
| --- | --- | --- |
| Home | `dashboard` | `HomeDashboardController`, `HomeService`, `HomeFocusList` |
| CRM summary | `dashboard.crm` | `DashboardController`, `DashboardService` |
| Clients, deals, projects, tasks, invoices | Corresponding resource routes | Matching controllers, models and views; invoice calculations in `InvoiceService` |
| Today | `today.index` | `TodayChecklist`, `ChecklistItem` |
| Focus | `focus.index` | `focus/index.blade.php`; timer runs in the browser |
| Finance | `finance.index`, `wallets.*`, `transactions.*`, `budgets.*` | Matching controllers, `FinanceService` |
| Goals | `goals.*` | `GoalController`, `Goal` |
| Boards | `workspace.*` | `BoardController`, `BoardShow`, `Board`, `BoardColumn`, `Card` |
| Journal | `journal.*` | `JournalController`, `JournalService`, `JournalEntry` |
| Files | `vault.*` | `VaultController`, `VaultFile` |
| Search/shortcuts | Embedded in the app layout | `QuickCapture` |

## Behavior to preserve

- Invoice create/update/payment actions keep one automatic income row per invoice using unique `transactions.invoice_payment_id`. Manual linked income reduces the automatic amount; manual rows remain unchanged. Missing matching-currency wallets are explained on the invoice. Edit automatic income through its invoice.
- Invoice rows survive edits and validation errors. Percentage tax applies after discount; blank percentage selects fixed-amount tax.
- Card promotion creates and links a project together and reuses an existing linked project on repeat requests.
- Archived wallet assets remain in total balance and financial goal tracking.
- Completing a linked checklist item completes its CRM task and updates project progress. Unchecking does not reopen the task.
- Goal banner and signature replacement save the new path before deleting the old file. A failed save cleans up the new upload.
- Vault determines file type from stored bytes. Inline previews are limited to supported raster images and PDFs; other files download as attachments.
- Cropper is bundled locally; Flowbite was removed. Full offline editing and saved focus sessions are not implemented.

## File storage

Vault files use `storage/app/private/vault`; goal banners use `storage/app/private/goal-banners`. Their routes require the PIN. Signatures use the public disk under `storage/app/public/signatures`. Refer to README for upload limits and the public storage link. Filesystem settings may change these roots, so inspect `config/filesystems.php` when debugging storage.

## Verification

Dependencies are installed in the audited workspace; live tests and builds are available. Run checks appropriate to the change. The full check is:

```bash
bash 03_VERIFICATION_SUITE.sh
```

It runs PHP lint, Pint, Blade compilation, tests, Vite build and `git diff --check`. Do not substitute brace/directive counts for parsers. For behavior changes, reproduce the issue and add a regression test where useful. For interaction changes, inspect the browser and record what was actually exercised.

Latest completed suite: **52 tests / 284 assertions on SQLite and MySQL 8.4.9**, 22 October 2026. See [the evidence index](docs/audit-validation/README.md). The old defect probes are outside the regular test suite and should not pass after the fixes.

`tests/Support/ConcurrentAuditProbe.php` recreates tables and only permits its dedicated testing schema. Never run it against real data. Do not run `02_SAFE_TO_DELETE_CLEANUP.sh`; its obsolete deletion list includes retained tests.
