# Changelog

Changes under Unreleased are recorded locally and have not been published as a release.

## Unreleased

### Added

- Home overview with today's checklist, income/expenses, pinned goal, work needing attention, sales opportunities and journal mood. The separate CRM summary remains available.
- Personal workspace: dated checklist, work/break timer, wallets/transactions/budgets, goals, boards, journal and private file storage.
- Search and creation shortcuts with `Ctrl+K` / `Cmd+K`. Search covers supported record titles/names and Vault filenames, not file contents.
- Invoice item add/remove controls, percentage tax, related task/invoice panels, board editing/deletion, task deletion and checklist date navigation.
- Stable demo fixtures for the personal workspace. Use only in disposable demo databases.
- Automated regression tests and a guarded diagnostic for simultaneous MySQL requests.

### Fixed

- Invoice editing no longer drops items beyond five rows; submitted rows survive validation errors.
- Invoice payments create or update one automatic income entry, including partial payments, changes, cancellation and deletion. Manually linked income is preserved; creating an automatic entry requires a suitable wallet.
- Archived client references remain readable. Existing links may be retained on edit; new links to archived records are rejected.
- Client totals include all relevant records rather than only the preview list.
- Numeric PINs and passphrases both work; configured spaces are preserved and invalid configurations are explained.
- Card promotion requires an active client and creates/links one project even on repeated requests.
- Goal options can be unchecked, and linked archived wallets remain available on edit.
- Goal banner and signature replacement retain existing files when saving fails. Goal save failure also removes the new upload.
- Vault determines file type from stored content, limits inline previews, and safely handles quoted filenames.
- Journal date filtering, duplicate delete buttons, missing icons, checklist project-progress updates, repeat-seeding task duplication and the frozen checklist-date default.

### Changed

- Archived wallet balances continue to count toward assets and goals. Unchecking a checklist item leaves its linked task completed; reopen it from Tasks.
- Cropper is bundled locally. Removed Flowbite, remote login fonts and confirmed unused app files/styles; retained the two example tests.
- Added migrations for invoice payment/tax fields and the checklist-date default. Back up existing installations before migrating; do not seed during upgrades.
- Refreshed setup, contributor, handover and backlog docs with simpler wording. Preserved old plans in the archive and separated historical test results from current status.

### Verification — 22 October 2026

- 52 tests / 284 assertions passed on SQLite and MySQL 8.4.9; lint, formatting, Blade compilation, Vite build and diff whitespace passed.
- Eight simultaneous workers produced one automatic payment and one linked project in separate checks.
- Selected browser flows were checked, including login, invoice rows, checklist dates, mobile navigation, board themes/dragging and image crop/save.
- Read-only review found three invoices matching demo fixtures with no wallets/transactions. No assumed financial entries were added.
- Broader device, deployment upload, MariaDB and sustained-load checks remain in [AUDIT-TODO.md](AUDIT-TODO.md). The app provides a basic offline page, not offline record editing.

## 1.0 — Mini CRM baseline

- Single-user PIN access, clients, deals, projects, tasks, invoices, reports, settings, PDF invoices and basic installable web-app support.
