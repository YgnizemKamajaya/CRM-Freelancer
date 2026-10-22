# Audit execution — 22 October 2026

> Initial implementation record; its 44-test count and verification limits describe that run. See [the latest results](08_Final_Data_and_Upload_Review.md) for 52 tests / 284 assertions on SQLite and MySQL 8.4.9, [report 07](07_Backlog_Verification.md) for browser/concurrent-request checks, and [the backlog](AUDIT-TODO.md) for remaining work.

Implemented against the independently validated findings in `04_Audit_Validation.md`. The original `01_4D_Full_Audit_and_Fix_Plan.md` remains a historical input, not an acceptance checklist. Changes were local and uncommitted at this stage; they were subsequently committed with the documentation refresh.

## Implemented changes

| Findings | Result |
| --- | --- |
| D1-01 | Card promotion requires an active client, saves the new project and card link together, reuses an existing project, and reports an archived linked project instead of opening a missing page. Projects still require a client. |
| D1-02, D1-03 | Historical client relationships include archived clients. Labels avoid dead client-detail links. Create/reassignment validation excludes archived records; unchanged archived relationships remain available and valid during edits. Lists, details, selectors, print, and PDF have regression coverage. |
| D1-04 | New migration removes the frozen checklist date default. The model supplies the creation day's date when omitted. Existing dates are retained. |
| D1-05 | Settings for layout, login, and signature view are supplied by a view composer and shared through request attributes. No process-lifetime cache or repeated signature lookups. |
| D1-06 | Seeder looks up tasks by the actual saved title, so repeat runs do not add tasks. `WorkspaceDemoSeeder` supplies stable wallet/transaction/budget/goal/board/card/checklist/journal demo fixtures and is called by the normal demo seeder. |
| D1-07 | New-install locale default is Indonesian. Existing `.env` and locale preferences were not changed. |
| D2-01 | Journal validates and applies `date=YYYY-MM-DD`, keeps the filter in the form, preserves tag filters, and offers a clear-filter action. |
| D2-02 | Client counts and financial totals aggregate all records independently of eight-item previews. Cancelled invoices are excluded from invoice/outstanding totals. Related invoices/tasks are rendered. |
| D2-03 | Task deletion, board rename/type/description editing, and confirmed board deletion have UI controls. Task deletion redirects to the task list. |
| D2-04 | CRM summary is linked in navigation and QuickCapture. Previously unused overdue-task and monthly-revenue results are rendered. |
| D2-05 | Related tasks and follow-up creation links appear on deal/invoice details through a shared partial. |
| D2-06 | Invoice editor preserves every item, permits explicit add/remove, retains validation input, and previews totals. Percentage tax defaults from settings and is calculated after discount; clearing the percentage restores manual tax amounts. Monetary line totals are rounded before aggregation. |
| D2-07 | Checklist date is URL-backed, validates dates, and has previous/next/today controls. |
| D2-08 | Removed unused coming-soon code. QuickCapture searches board, goal, and Vault records in addition to existing groups. |
| D2-09 | Retained the five-item mobile bottom bar and full Personal-module drawer. Corrected the stale comment; this was not an access bug. |
| D2-10 | Removed the obsolete savings-goal TODO. Updated stale verification documentation. Deferred journal month calendar, one-shot card creation, and focus-session persistence remain roadmap items. |
| D3-01, D3-04 | Numpad supports numeric PIN lengths 1–8; longer numeric PINs and passphrases use a native password form. Accepted PINs are 1–128 characters; invalid configuration is explained. PIN trimming is disabled so configured whitespace is preserved. JavaScript exits safely for the native form. Login assertion now checks current copy. |
| D3-02 | `hideTrigger` suppresses the extra delete trigger in all five workspace edit forms, retaining the footer button and separate confirmation form. |
| D3-03 | Added left/right chevron icons. |
| D3-05 | Preserved archived-wallet assets in totals and financial-goal tracking. Finance copy states that policy. Archive does not silently remove money. |
| D3-06 | One unique managed income entry per invoice reconciles partial/full payment, amount changes, retries, cancellation, and invoice deletion. Uses a matching-currency wallet. Invoice header/items/totals/payment and numbering mutations are transactional. Manual linked income counts toward received amounts and is preserved. Managed transactions route editing back to the invoice and reject independent writes/deletion. Missing compatible wallet is visible on invoice detail. Selecting Paid without a full received amount produces validation feedback instead of inventing a receipt. |
| D3-07 | Kept one-way checklist completion, explained it in UI, and synchronized project progress after task completion. Unchecking does not overwrite CRM task state. Use explicit task reopen. |
| D3-08 | Cropper JS/CSS are bundled through Vite, with an initialization guard. Removed remote Google Fonts from login; system fonts provide fallback. Full offline CRM editing remains outside the PWA's capabilities. |
| D3-09 | Calendar dots use semantic theme classes. Status alerts use theme tokens. Existing gray/white utility overrides remain in place; no unnecessary modal rewrite. |

## Cleanup and dependencies

- Removed **11** confirmed unused prototype/layout/component files. Kept the two executable smoke tests rather than treating test discovery as an unused reference.
- Archived PRD/design documents under `docs/archive` with historical notices. Updated README/CLAUDE references and conventions.
- Removed **47 unused class selectors**, affecting **83 selector branches**, through a PostCSS parse. Retained the five calendar-dot selectors now used in the UI.
- Removed Flowbite. Installed CropperJS 1.6.2 locally. Applied compatible dependency fixes after npm reported advisories in existing dependencies; npm then reported **zero vulnerabilities**. No major-version audit fix was forced.
- Updated `03_VERIFICATION_SUITE.sh` to use PHP lint, Pint, actual Blade compilation, feature tests, and Vite. The old cleanup script and original defect probes remain historical artifacts; do not use them as the current release gate.

## Database execution

Added and applied these migrations to the **local SQLite database**, without seeding the user's database:

1. `2026_10_22_000001_track_invoice_payments_and_tax.php`
2. `2026_10_22_000002_remove_static_checklist_date_default.php`

A consistent SQLite backup was created before migration, mode `0600`:

`/tmp/minicrm-before-audit-mus2v3p6jrla86kpSbd`

The payment migration adopts the first legacy income entry matching the old exact generated note for each invoice. It does not delete additional legacy/manual entries. Existing renamed notes or historical duplicates may need manual review; they are not silently rewritten. No blanket repair of old financial data or duplicate tasks was performed.

Other checkouts/deployments need `php artisan migrate` and `npm ci && npm run build`. Do not run the demo seeder against real records: the existing demo seeder deliberately updates demo CRM data/settings.

## Verification

- PHPUnit: **44 tests passed, 224 assertions**, including **26 new regression tests**. Covers invoice rows and validation recovery, rate/amount tax modes, managed/manual payments and reversals, archived relations, complete aggregates, promotion validation/idempotency, PIN modes/rate limiting, checklist dates/progress, demo idempotency/search, and CRUD controls.
- Migration regression exercises upgrade adoption and rollback/re-upgrade while retaining the payment amount. Both new migrations also ran successfully against the separate demo database and the existing local SQLite database.
- Browser tests used a separate SQLite demo database. Confirmed six-digit numpad login, native passphrase login, all seven preexisting invoice rows, adding/saving an eighth row (800 total), and previous-date/next-date checklist navigation with URL state. No browser warning/error logs were returned for the inspected flows.
- Production build passed. Combined output after cleanup/dependency changes: JS **89.85 kB** / gzip **31.44 kB**; CSS **114.94 kB** combined / gzip **20.64 kB**. Earlier baseline: JS 175.63 kB and CSS 137.17 kB. These figures reflect the complete change set, not an isolated Flowbite benchmark.
- Final verification command: `bash 03_VERIFICATION_SUITE.sh` — **passed**, including PHP syntax, Pint, Blade compilation, all 44 tests, production build, and diff whitespace checks. Full output: `docs/audit-validation/final-verification.txt`.

The pre-fix probes under `docs/audit-validation` check for old defects; they are preserved as evidence and excluded from normal suites. This first run did not check MySQL or simultaneous requests; later reports record those checks. Broad device/theme/upload coverage remains incomplete, and full offline operation is not implemented.
