# Start here: current project handover

Updated **22 October 2026**. The original proposed audit has been validated and the accepted fixes implemented. Do not restart its old checklist or copy its sample patches.

## Read in this order

1. [README](README.md): installation, features and current behavior.
2. [CLAUDE](CLAUDE.md): code conventions and working rules.
3. [AUDIT-TODO](AUDIT-TODO.md): completed checks and remaining work.
4. [Latest follow-up](08_Final_Data_and_Upload_Review.md): payment review and upload fixes.
5. [Documentation index](docs/README.md): earlier reports and evidence.

## Current state

- The audit fixes, tests, evidence and documentation are recorded in a local commit. No push, PR or deployment was performed; inspect `git log` and `git status` for the latest checkout state.
- Latest acceptance: **52 tests / 284 assertions on SQLite and MySQL 8.4.9**, plus lint, formatting, Blade compilation, frontend build and diff whitespace.
- Both October migrations were applied to the local SQLite database. It was not seeded. The existing `.env`, PIN, locale and uploaded user files were preserved.
- Three received-payment invoices match demo fixtures; the database has no wallets or transactions. The read-only investigation is complete and no financial entries were invented.
- Browser and MySQL services started for verification were stopped afterward. Temporary files under `/tmp` may expire.

## Continue safely

Inspect `git status` and the relevant diff first. Use the codebase-memory graph for code discovery as required by the project instructions. Work from the current code and tests, then select remaining work from `AUDIT-TODO.md`.

Use `bash 03_VERIFICATION_SUITE.sh` for the normal full check. Keep demo and destructive database tests separate from real data. `composer setup` seeds sample data, so do not use it to update a working installation.

**Do not run `02_SAFE_TO_DELETE_CLEANUP.sh`.** Its old deletion list includes two tests that were deliberately retained. The original audit proposal is preserved in [the archive](docs/archive/01_Original_Audit_Proposal.md); [the review](04_Audit_Validation.md) explains why parts were rejected.

The audit backup was created in `/tmp`; that path is historical evidence, not a durable backup guarantee. Make a fresh backup before further database changes.
