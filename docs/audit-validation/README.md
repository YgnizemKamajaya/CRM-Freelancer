# Audit evidence

These files record specific checks. They do not replace the current [backlog](../../AUDIT-TODO.md) or prove that every device and deployment has been tested.

| File | Run and scope |
| --- | --- |
| `data-upload-verification.txt` | Latest SQLite run: 52 tests / 284 assertions, lint, formatting, Blade compilation and build |
| `data-upload-mysql-verification.txt` | Latest MySQL 8.4.9 run: 52 tests / 284 assertions |
| `payment-gap-review.json` | Read-only SQLite investigation of three demo-pattern invoices; no wallets or transactions existed |
| `mysql-concurrency.json` | Eight-worker payment and project-promotion results in a disposable MySQL schema |
| `kanban-browser-check.jpg` | Screenshot from the scoped board browser check |
| `legacy-review.json` | Earlier read-only counts; detailed payment investigation followed later |
| `resume-verification.txt` | Earlier SQLite/build run: 50 tests / 274 assertions |
| `mysql-verification.txt` | Earlier MySQL run: 50 tests / 274 assertions |
| `final-verification.txt` | Initial implementation run: 44 tests / 224 assertions |
| `AuditClaimValidationTest.php`, `probe-results.txt` | Before-fix probes and output; deliberately assert defects that have since been fixed |

The before-fix probes are outside the regular test suite. Do not run them expecting success on the fixed app. Use `bash 03_VERIFICATION_SUITE.sh` from the repository root for normal verification.

The separate `tests/Support/ConcurrentAuditProbe.php` deletes and recreates tables in its dedicated test schema. It has environment/database guards and must never target real data. Browser fixtures and temporary database services were isolated from the user's working records and stopped/cleaned up after the documented checks.
