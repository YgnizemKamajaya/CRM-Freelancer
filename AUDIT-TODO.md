# Workspace Expansion — Audit TODO

Status per 2026-07 expansion (Finance / Journal / Workspace / QuickCapture).
Static checks (php -l, blade pairing, css braces, schema cross-ref) all pass.
Items below need a running app (composer install + npm run build) to verify.

---

## 🟡 VERIFY ON A RUNNING APP (cannot be checked statically — no vendor/ in sandbox)

- [ ] **V1 — Livewire component auto-discovery.**
      `<livewire:quick-capture />` (in resources/views/layouts/app.blade.php)
      and `<livewire:board-show />` / the full-page route
      `App\Livewire\BoardShow` (routes/web.php `workspace.show`)
      must resolve to App\Livewire\*. Livewire 4 favours single-file components;
      class + separate view is still supported but confirm no
      "Unable to find component" error on first render.

- [ ] **V2 — wire:sort drag-and-drop persistence.**
      Requires `npm run build` (SortableJS ships inside Livewire 4).
      Drag a card across columns in the kanban → confirm `BoardShow::moveCard`
      fires, and the new column+position survives a page refresh.
      (Early Livewire 4 betas had a "drag doesn't hit backend" bug fixed in
      beta.6; repo is on ^4.3 so it should be fine — but verify.)

- [ ] **V3 — Dynamic array-key binding.**
      `wire:model="newCardTitle.{{ $column->id }}"` binds to an initially-empty
      array (BoardShow::$newCardTitle = []). Confirm each column's inline
      "add card" input is independent and Enter creates a card in the right column.

- [ ] **V4 — Full-page Livewire + component-style layout.**
      BoardShow uses #[Layout('layouts.app')]; that layout was built for
      <x-app-layout> and renders {{ $slot }}. Livewire injects its output into
      $slot fine, but $title is NOT set on the Livewire path (layout falls back
      to 'Freelance CRM'). Confirm /workspace/{board} opens with the sidebar and
      an acceptable browser-tab title. If a precise title is wanted, add a
      #[Title(...)] attribute to BoardShow.

- [ ] **V5 — Today Checklist (App\Livewire\TodayChecklist).**
      Same wire:sort + auto-discovery concerns as V1/V2. Requires npm run build.
      Verify: reorder persists; toggle marks done (and flips a linked CRM task to
      'done'); rollOver pulls yesterday's unfinished items; pullTask adds a
      due-today CRM task once.

- [ ] **V6 — File Vault upload/download (VaultController).**
      Cannot be tested statically (no vendor; PHP upload limits are env-specific).
      Verify on a running app:
        * php.ini `upload_max_filesize` and `post_max_size` >= 25 MB (the
          controller validates max:25600 KB per file, but PHP caps it first).
        * Files land in storage/app/vault (private disk 'local'), NOT public —
          no `storage:link` needed, that's intentional for privacy.
        * Download route is behind the `pin` middleware; deleting a record also
          removes the physical file (VaultFile deleting hook).

- [ ] **V7 — Goal banner image upload/serve (GoalController).**
      Same env caveats as V6. Verify: uploaded banners land in
      storage/app/goal-banners (private disk 'local'); the goals.banner route
      serves them behind `pin` middleware; gradient presets work with no upload;
      "remove_banner" reverts to gradient; deleting a goal removes its banner
      file (Goal deleting hook). php.ini upload limits apply (max 8 MB validated).

- [ ] **Pomodoro persistence (future).**
      resources/views/focus/index.blade.php is front-end only (Alpine). To record
      sessions later, add a `focus_sessions` table + a Livewire dispatch at the
      marked TODO(future) in that view. Also note the browser Notification
      permission prompt only fires on the user's first opt-in.

- [ ] **V8 — Interactive Focus card on Dashboard 2.0 (App\Livewire\HomeFocusList).**
      Embedded via <livewire:home-focus-list /> on the home. Requires npm run build
      + Livewire auto-discovery. Verify: toggling/adding a task updates in place
      without a full reload, writes to checklist_items, and stays consistent with
      the Today page. Note: the hero "fokus hari ini" counter is a page-load
      snapshot and won't live-update on toggle (acceptable — it's an at-a-glance
      number); the card itself is authoritative.

---

## 🟢 DEFERRED FEATURES (not bugs — planned scope not yet built)

- [ ] **D1 — Journal calendar view.** Currently list + 14-day mood chart only.
      Original plan mentioned a month calendar. Add later.
- [ ] **D2 — Finance savings-goal progress.** Wallets/transactions/budgets done;
      "target tabungan" progress card from the plan not yet implemented.
- [ ] **D6 — True quick-add from QuickCapture for cards.** The "Board workspace"
      shortcut only routes to the board list; there is no one-shot "create card"
      endpoint. Consider a lightweight modal or a dedicated route.

---

## ✅ RESOLVED during audit
- Dashboard 2.0 — new command-centre home at route `dashboard` (HomeService +
  HomeDashboardController + resources/views/home/index.blade.php). Legacy CRM KPI
  page preserved at `dashboard.crm`. Aggregates 6 cards (focus/cashflow/dream/
  attention/pipeline/mood) reusing Finance & Journal services; the cross-module
  "Perlu perhatian" alert list is the signature element. All --crm-* tokens,
  three themes. Verify live data renders once the app runs.
- B1 — Hard-coded oklch() colors in Finance/Journal/Workspace views replaced with
  theme-aware CSS vars (--crm-income/expense/over/mood-*/priority-*), defined in
  all three themes (light/sepia/dark). Dark-mode contrast now holds.
- B2 — Finance/budget meters no longer piggyback on the existing
  `.project-progress-track` class; they use a dedicated `.crm-meter` so the CRM
  project progress bar can't be affected by the "is-over" overspend rule.
- D2 — Financial savings-goals delivered via the Goal Tracker module
  (auto_track pulls live wallet balance through FinanceService::goalCurrentAmount).
- D4 — (withdrawn) `x-empty-state`'s gray utilities are already remapped to
  --crm-* by the existing @layer utilities block, so dark mode was never broken.
