# Follow-up verification — 22 October 2026

> Historical follow-up run: 50 tests at this stage. [Report 08](08_Final_Data_and_Upload_Review.md) completes the payment investigation and banner failure fix, with 52 tests / 284 assertions on both SQLite and MySQL 8.4.9. Use [the current backlog](AUDIT-TODO.md) for remaining work.

Continued from `06_Next_Session_Summary.md`. Work was local and uncommitted at this stage; it was subsequently committed with the documentation refresh. This round needed no migrations or changes to the user's database/configuration.

## Completed backlog checks

| Check | Result |
| --- | --- |
| MySQL compatibility | **50 tests / 274 assertions passed on MySQL 8.4.9**, including migration upgrade, adoption of legacy automatic income, and rollback/re-upgrade. Used a temporary MySQL data directory, Unix socket only, with dedicated test schemas. No connection to an existing MySQL service. |
| Concurrent payment requests | Eight separate PHP workers released through a barrier produced exactly **one** managed income row for the invoice, amount 100.00. |
| Concurrent card promotion | Eight separate PHP workers produced exactly **one** project and the expected card/project link. |
| SQLite regression and build | **50 tests / 274 assertions passed**, plus PHP lint, Pint, Blade compilation, Vite build, and diff whitespace checks. |
| Mobile navigation | At 390 px, the drawer exposed Personal modules and opened Workspace. Dashboard did not overflow the viewport. The Kanban board intentionally scrolls horizontally within its board area. |
| Three themes | Inspected light, dark, and sepia board rendering at desktop width. Restored the original light theme and reset the viewport override afterward. This was a visual check, not exhaustive WCAG contrast certification. |
| Kanban inputs and persistence | Added a destination column, created cards independently in both columns, dragged the first-column card into the second column, and confirmed the placement survived reload. |
| Cropper workflow | Generated a non-personal PNG fixture, opened it in the bundled editor, saved the crop, and confirmed the saved image loaded at 640 × 320. Inspected browser warning/error logs were empty. Removed the demo signature fixture afterward. |
| Goal/banner and Vault lifecycle | Added feature tests for private access, goal banner upload/replacement/removal, Vault upload/download/deletion, and signature validation/replacement/removal. |
| Existing SQLite data review | Read-only aggregate check found no duplicate task-title groups, no unmatched legacy auto-income markers, and no invoices with income above the recorded received amount. **Three existing invoices have received amounts but no linked income entry.** Those records were not altered. |

## Additional defects reproduced and fixed

1. **Goal checkboxes stayed enabled after unchecking.** Unchecked inputs were omitted and `sometimes` validation left stored true values untouched. Normalize both booleans; hidden false values also preserve unchecked state across validation errors.
2. **An archived wallet vanished from an existing goal's edit selector.** Keep the currently linked wallet available alongside active choices, preserving the documented all-assets tracking behavior.
3. **Failed signature replacement deleted the old signature.** Store the new file and update its setting before deleting the old one. Failed storage produces validation feedback; failed persistence cleans up the new file while retaining the old one.
4. **Vault trusted a client-provided MIME type and exposed unrestricted inline content.** Derive metadata from stored bytes, recheck bytes when previewing legacy uploads, allow inline previews only for raster images/PDF, and serve other types as attachments. Preview responses include `nosniff` and sandbox CSP. File storage failure no longer creates an invalid file record.
5. **Vault photo names were interpolated into Alpine JavaScript through HTML escaping.** Use `@js` for each photobox argument so quotes and other filename characters retain their exact value safely.

These changes were covered by six new feature tests in `tests/Feature/WorkspaceUploadRegressionTest.php`, bringing the regular suite to 50 tests at this stage. Report 08 adds two more tests.

## Evidence and reproduction

- SQLite/build: `docs/audit-validation/resume-verification.txt`
- MySQL suite: `docs/audit-validation/mysql-verification.txt`
- Concurrent worker result: `docs/audit-validation/mysql-concurrency.json`
- Read-only existing-data counts: `docs/audit-validation/legacy-review.json`
- Browser screenshot: `docs/audit-validation/kanban-browser-check.jpg`
- Portable concurrency probe: `tests/Support/ConcurrentAuditProbe.php`

Run normal acceptance checks with:

```bash
bash 03_VERIFICATION_SUITE.sh
```

The MySQL test run used externally supplied `DB_CONNECTION=mysql`, `DB_SOCKET`, `DB_DATABASE=minicrm_audit_test`, and test-only credentials. Normal PHPUnit defaults still use SQLite memory.

The concurrency probe is an explicit standalone diagnostic, excluded from the normal suite. It refuses to run unless `APP_ENV=testing`, the driver is MySQL, and the database is named exactly `minicrm_audit_concurrency`. **It recreates that disposable schema's tables** before running workers. Use only a dedicated temporary schema; never point test credentials at real data.

The final MySQL rerun initially encountered an expired temporary server/socket; the server was restarted with automatic shutdown around the test run, and all 50 tests then passed. This infrastructure retry did not require an application change.

## Remaining backlog

- Completed in report 08: all three received-payment records match demo fixtures; there are zero wallets and zero transactions. No automatic correction was made.
- Broader device/interaction matrix: real touch-device dragging, every theme/page combination, deployment-specific PHP upload limits, failed network requests, and very large files. Vault and goal lifecycle are now feature-tested; only Cropper upload/crop/save was exercised end-to-end in the browser this round.
- MariaDB/version-specific verification and sustained concurrent load beyond the eight-worker MySQL probe.
- At this stage the changes were uncommitted. The subsequent local commit is recorded in the current backlog; push, PR and deployment remain pending a request.
- Deferred product features remain separate: journal month view, one-shot QuickCapture card creation, persisted focus sessions, and full offline CRM editing.

Temporary browser/MySQL services were stopped after verification. The user's actual `.env`, PIN, database records, and existing uploaded files were preserved.
