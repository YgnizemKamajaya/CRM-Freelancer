# Changelog

All notable changes to this project. Format loosely follows
[Keep a Changelog](https://keepachangelog.com/). The base mini-CRM release is 1.0;
the workspace expansion is grouped as the 2.x line.

## [Unreleased]

### Added — Personal Workspace expansion

- **Dashboard 2.0** — a command-centre home (`dashboard`) with a time-aware
  greeting and six cards: an interactive Focus checklist, cashflow, pinned dream
  goal, a cross-module "Perlu perhatian" alert list, pipeline, and mood. The old
  CRM KPI dashboard moved to `dashboard.crm`.
- **Today checklist** — a daily list (`today.index`) separate from CRM tasks:
  drag-to-reorder, roll-over of unfinished items, and one-click pull of CRM tasks
  due today. Also embedded as an interactive card on the home.
- **Focus (Pomodoro)** — front-end timer (`focus.index`): work/short/long cycles,
  adjustable durations, sound + optional desktop notifications. No persistence yet.
- **Finance** — wallets, income/expense transactions, budgets, and a dashboard
  (cashflow chart, category breakdown, budget usage). Marking an invoice paid now
  auto-records income.
- **Goals** — personal and financial goals with progress meters, financial
  auto-tracking of wallet balances, per-goal banners (gradient preset or uploaded
  image), and pin-as-dream.
- **Workspace (Kanban)** — idea/project boards with native Livewire `wire:sort`
  drag-and-drop across columns; cards can be promoted to CRM projects.
- **Journal** — markdown entries with mood tracking, 14-day trend, tag cloud,
  writing streak, and per-entry Markdown export.
- **Vault** — private file store (25 MB/file) optionally linked to clients/projects.
- **Quick capture** — global `Ctrl`/`Cmd`+`K` palette to search everything and jump
  to create actions from anywhere.
- Grouped sidebar navigation (CRM / Personal), three-theme semantic color tokens
  (`--crm-income`, `--crm-expense`, `--crm-mood-*`, `--crm-priority-*`), and new
  reusable pieces (`x-coming-soon`, `.crm-meter`, goal banners).
- `CLAUDE.md` (architecture guide) and `AUDIT-TODO.md` (on-device verification list).

### Changed

- Sidebar restructured into CRM and Personal groups.
- `.gitignore` rewritten with clearer sections; user uploads under `storage/app`
  are kept out of version control by Laravel's per-directory ignores.

### Notes

- All new tables are additive; the original CRM schema is untouched, so existing
  installs migrate safely.
- Several behaviors (uploads, drag-and-drop persistence, Livewire rendering) require
  a running app to verify — tracked in `AUDIT-TODO.md`.

## [1.0] — Mini CRM (baseline)

- Single-user PIN access, Client → Deal → Project → Invoice pipeline, tasks,
  reports, settings, PDF invoices, and basic PWA support. Built from the local PRD.
