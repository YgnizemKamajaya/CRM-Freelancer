# Data and upload follow-up — 22 October 2026

Continues [07_Backlog_Verification.md](07_Backlog_Verification.md). Changes were local and uncommitted during this check; they were subsequently committed with the documentation refresh.

For setup and plain-language behavior descriptions, use [README](README.md). For work still to do, use [the current backlog](AUDIT-TODO.md). This report records the application checks completed before the later documentation refresh; that refresh changed no runtime behavior.

## Historical payment investigation completed

Opened the actual SQLite database using `mode=ro` and `PRAGMA query_only=ON`. No application writes or seeding were performed.

| Invoice | Status | Received (IDR) | Evidence |
| --- | --- | ---: | --- |
| INV-0003 | partially_paid | 3,625,000 | Number, status, total, received amount and demo note match DatabaseSeeder |
| INV-0004 | paid | 8,500,000 | Same seed-pattern match |
| INV-0007 | paid | 12,250,000 | Same seed-pattern match |

The database contains **zero wallets and zero transactions**, so there are no opening balances, matching manual receipts, or compatible wallet destinations. These records match demo fixtures; their original creation history cannot be proven from that match alone. No automatic financial correction is warranted. If these represent real receipts, choose/create the correct wallet and resave each invoice to record its managed income. Creating a wallet alone does not backfill invoices.

Evidence: `docs/audit-validation/payment-gap-review.json`.

## Goal banner failure fixed

Two regression tests reproduced failure behavior before the fix:

- A failed file store was treated as an empty path and returned a successful redirect.
- A failed database update had already deleted the old banner and left the new upload behind.

GoalController now rejects failed storage with validation feedback, persists the new path before deleting the previous banner, and removes newly uploaded files when creating/updating the goal fails. Removing a banner also persists the cleared path before deleting the file. Existing successful replacement/removal and private-serving tests still pass.

## Upload limits reviewed

The local **CLI** runtime reports `upload_max_filesize=2M`, `post_max_size=8M`, `max_file_uploads=20`, and `memory_limit=128M`. This is below the application's per-file limits (Vault 25 MB, goal banners 8 MB, signatures 4 MB). This measurement does not establish production PHP-FPM/Apache settings.

README now identifies the actual private paths under `storage/app/private`, explains per-file versus combined-request limits, and calls out proxy limits and image memory. No global PHP or deployment settings were changed. Full-size upload acceptance remains a deployment check.

## Verification

The normal verification script passed: PHP lint, Pint, Blade compilation, **52 tests / 284 assertions on SQLite**, Vite production build, and diff whitespace checks. Two new tests cover goal banner storage and database failures. Evidence: `docs/audit-validation/data-upload-verification.txt`.

The same **52 tests / 284 assertions passed on MySQL 8.4.9**, using the dedicated temporary schema/server from report 07. The temporary server was stopped automatically afterward. Evidence: `docs/audit-validation/data-upload-mysql-verification.txt`.

## Remaining backlog

- Real touch-device dragging and a broader page/theme interaction matrix. Prior browser checks and their exact scope remain recorded in report 07; this round added no device coverage.
- Verify large uploads, HTTP/PHP/proxy limits, and network failures in the intended deployment environment.
- MariaDB/version-specific checks and sustained load beyond the completed eight-worker MySQL probe.
- The later documentation refresh and local commit are complete. Push, PR and deployment remain pending a request.
- Separate product roadmap: journal month calendar, one-shot card QuickCapture, persistent focus sessions, full offline CRM editing.

The payment investigation is complete; financial reconciliation is conditional on actual receipt/wallet choices, not an outstanding automatic repair.
