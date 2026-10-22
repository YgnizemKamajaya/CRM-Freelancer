# Independent validation of the four-dimension audit

> Historical review before fixes. The findings and test counts below describe that stage. See [the latest results](08_Final_Data_and_Upload_Review.md) for the current 52-test SQLite/MySQL checks and [the backlog](AUDIT-TODO.md) for remaining work. The original proposal is preserved in [the archive](docs/archive/01_Original_Audit_Proposal.md); its old line references are historical. The defect probes below are not current acceptance tests.

Validated on **22 October 2026 (Asia/Kuala_Lumpur)** against commit `c81371aac4dede628c8d10c6a6e8af668940a7dc`, with the existing working tree. Input: `01_4D_Full_Audit_and_Fix_Plan.md`.

**Verdict: many underlying defects are real, but the document is not safe to execute as written.** Its severity ratings, numerical scorecard, mobile-access claim, theme diagnosis, and several proposed fixes need correction. No application fixes or deletions were performed during this validation.

## Evidence and limits

- Used the repository knowledge graph for discovery and function/class inspection; checked Blade, migrations, configuration, and documentation directly where needed.
- Existing suite: `php artisan test` → **17 passed, 1 failed, 58 assertions**. Failure: `PinAccessTest.php:22` expects `Unlock workspace`; rendered copy is `Enter PIN to unlock`.
- Independent probes: **16 passed, 53 assertions** in an isolated SQLite `:memory:` database. These deliberately assert the presence of current defects; a passing probe does **not** mean the application is correct.
- `npm run build` passed: Vite **7.3.6**, 161 modules, CSS **137.17 kB** / gzip **23.74 kB**, JS **175.63 kB** / gzip **47.82 kB**. This is a baseline, not a measured saving from removing Flowbite.
- PHP syntax lint passed for **96 files** across `app`, `config`, `database`, `routes`, and `tests`.
- Browser interactions, actual visual contrast, offline browser behavior, concurrent requests, and MySQL behavior were **not** exercised. Findings labeled static below are source-level conclusions. This is validation of the supplied audit, not a fresh claim of 100% application coverage.

Reproducible probes and results:

- [AuditClaimValidationTest.php](docs/audit-validation/AuditClaimValidationTest.php)
- [probe-results.txt](docs/audit-validation/probe-results.txt)

Run from the repository root:

```bash
vendor/bin/phpunit -c phpunit.xml docs/audit-validation/AuditClaimValidationTest.php --testdox
```

The evidence class sits outside the configured normal test suites because it documents defects rather than desired regression behavior. One probe temporarily alters `projects.client_id` in its in-memory database to evaluate the suggested fix. It does not modify the application's migrations or stored database.

## Claim-by-claim assessment

Here, **P1** means a broken core workflow, data loss, or materially wrong financial data; **P2** means limited functional impact or a feature gap; **P3** means maintenance, cosmetic work, or a product preference. No broad P0 outage was demonstrated.

### Dimension 1

| ID | Verdict | Evidence and correction | Revised priority |
| --- | --- | --- | --- |
| D1-01 | Confirmed; proposed fix incomplete | `BoardShow.php:162` creates a project without `client_id`; migration `2026_07_07_000001_create_crm_core_tables.php:57` requires it. Probe reproduces `NOT NULL constraint failed: projects.client_id`. Existing projects still work; this is a broken promotion workflow, not a system-wide outage. | P1 |
| D1-02 | Confirmed, with wider impact | After `ClientController::destroy()` soft-deletes a client, the seven claimed routes all return 500 with representative related records. Invoice **print and PDF** also return 500. All seven named models' `client()` relations omit `withTrashed()`. | P1 |
| D1-03 | Confirmed, with naming caveat | `ValidatesCrmRelations::ownedExists()` uses plain `Rule::exists`; probe successfully creates a deal attached to a soft-deleted client. `authorizeOwner()` only checks `exists`, but this application intentionally has no users/tenants. That alone does not prove a cross-user authorization defect. | P2 |
| D1-04 | Confirmed, but current UI avoids it | `for_date` schema default is the migration date. Probe travels seven days and an insert omitting the field still gets that old date. However, all three current application creation paths explicitly provide `for_date` (`TodayChecklist::addItem`, `pullTask`, and `HomeFocusList::addItem`). This is latent schema debt rather than a demonstrated daily checklist failure. | P2 |
| D1-05 | Partly confirmed; wording overstated | The layout runs `Setting::firstOrCreate` and the invoice view calls `Setting::first` twice. These are redundant reads and undesirable view-side persistence. `firstOrCreate` only writes when the row is absent; it does **not** write on every request. | P3 |
| D1-06 | Confirmed defect plus optional enhancement | Actual reseeding changes task count **20 → 40**. Workspace seeding is absent; probes also observe no wallets, journals, or boards. Missing demo fixtures are optional completeness work, distinct from the idempotency bug. Seeder titles vary among four prefixes, not only `Follow up proposal`. | P2 bug / P3 fixtures |
| D1-07 | Confirmed configuration, preference-dependent | `.env.example:7` uses `APP_LOCALE=en`; translated date formatting follows locale. This establishes mixed-language defaults, not the user's desired locale or the deployed environment's configuration. | P3 |

### Dimension 2

| ID | Verdict | Evidence and correction | Revised priority |
| --- | --- | --- | --- |
| D2-01 | Confirmed | Calendar link includes `date`; `JournalController::index()` ignores it. Probe requests one date and receives entries from two dates. Add strict date validation as well as filtering and a visible active filter. | P2 |
| D2-02 | Confirmed; separates two issues | Controller limits loaded collections to eight and view computes totals from those collections. Probe proves a client with nine deals reports a collection count of eight. Invoice totals include cancelled invoices; the outstanding sum also includes them. Invoice/task panels are missing, but adding panels is separate from fixing incorrect totals. | P1 totals / P2 panels |
| D2-03 | Confirmed statically | Routes and controller actions exist for task deletion and board update/deletion; no named-route references occur in views or Livewire classes, and the relevant forms/board header expose no corresponding controls. This is a UI gap; direct backend actions exist. | P2 |
| D2-04 | Confirmed statically; intent unresolved | No UI reference to `dashboard.crm`; `overdue_tasks` and `monthly_revenue` are produced by `DashboardService` and unused in its view. Decide whether to expose or retire this legacy dashboard rather than assuming all old UI must return. | P2/P3 |
| D2-05 | Confirmed statically | Deal/invoice show actions load tasks, but their views omit task panels. Remove unnecessary loading or add panels according to intended UX. | P2 |
| D2-06 | Confirmed; severity understated | Form renders exactly five rows. Probe creates six items, verifies item six is absent from edit HTML, then submits the five rendered rows: invoice items become **six → five**, total **600 → 500**. This is silent data loss. `default_tax_percentage` is also ignored, but tax is currently an absolute amount; copying a percentage directly into `tax` would be wrong. | **P1 item loss** / P2 tax |
| D2-07 | Confirmed statically | Component accepts a date argument, view has no date controls. Current `/today` route also has no date segment and the property has no URL attribute; a date switcher needs explicit state/query wiring. | P3 |
| D2-08 | Mostly true; scaffold impact overstated | Board/goal/vault record search is absent. However, create/navigation shortcuts already reach those modules. Empty `comingSoonShortcuts()` is filtered out by `resultGroups()`, so it does not create a broken or empty visible group. | P3 |
| D2-09 | Misleading implication | Bottom bar excludes Personal modules and comment is stale. **Mobile users still have access** through the mobile drawer, which iterates the full `$navGroups` (`layouts/app.blade.php:253–292`). Changing a curated five-item bar is a UX choice, not a missing-access fix. | P3 |
| D2-10 | Confirmed, already distinguishes deferrals | Finance savings-goal TODO is stale relative to implemented `goalCurrentAmount()` and `AUDIT-TODO.md`. Journal calendar, quick card capture, and focus-session persistence remain documented deferrals; TODO presence is not a runtime defect. | P3 |

### Dimension 3

| ID | Verdict | Evidence and correction | Revised priority |
| --- | --- | --- | --- |
| D3-01 | Confirmed UI incompatibility; not permanent backend lockout | Login fixes length at four and accepts numeric input only. Probe sets a six-digit PIN: rendered JS stays four, while a direct valid POST unlocks successfully. README recommends an incompatible passphrase. Backend also limits input to 32 characters; longer configured values remain impossible even with a fallback UI. | P1 |
| D3-02 | Confirmed | All five edit templates place the component trigger above the form and another delete trigger in the footer. Rendered wallet edit HTML contains **two** `Hapus` trigger buttons; component also contains a separate modal confirmation button. | P2 |
| D3-03 | Confirmed, cosmetic | Home requests `chevron-right`, but icon switch only has `chevron-down` and defaults to a document icon. No functional navigation failure demonstrated; P1 is excessive. | P3 |
| D3-04 | Confirmed by actual suite | One stale text assertion fails. This does not establish that all tests are broken, or that replacing the assertion verifies login behavior. | P2 |
| D3-05 | Inclusion confirmed; desired accounting semantics unproven | An active wallet with 100 and archived wallet with 200 produce total balance **300**. The service includes archived wallets in all-wallet goal tracking too. Archive can reasonably hide an account while preserving net worth; removing money from totals is a product/accounting decision, not automatically a correctness fix. | P2 pending policy |
| D3-06 | Confirmed; proposed payment fix insufficient | A fully paid invoice created through the form has no income transaction, even with an active wallet. Selecting paid with zero amount becomes sent. Additional probe proves recorded income stays **100** when the paid invoice changes to **200**: `recordInvoicePayment()` skips any existing invoice-linked transaction. Simply calling it on update does not reconcile the ledger. | P1 missing/stale income / P2 status UX |
| D3-07 | Behavior confirmed; automatic reversal needs policy | Both checklist components leave a linked task done after check/uncheck. Existing code explicitly describes completion reflection as optional/one-way. Blindly setting pending would lose an original `in_progress` status, risk overriding manual changes, and ignore multiple linked checklist items. | P2 |
| D3-08 | CDN dependency confirmed; PWA claim overstated | Cropper and Google Fonts are remote. Cropper constructor has no fallback when its CDN script is unavailable. Fonts can fall back to system fonts. `public/sw.js` caches only a small static/offline asset set, not CRM pages or writes, so bundling Cropper alone does not produce an offline-capable CRM. Browser failure was not exercised. | P2 Cropper / P3 fonts |
| D3-09 | Partly confirmed; modal diagnosis unsupported | Inline calendar colors and green status utility classes exist. But `app.css:2556–2598` remaps `.bg-white`, `.bg-gray-*`, `.text-gray-900`, gray borders, and hover backgrounds to theme variables. Therefore those class names do **not** prove a light-only signature modal. Contrast still needs browser measurement. Fixed semantic calendar colors can also be intentional. | P3 / visual validation needed |

## Dimension 4: cleanup and numerical claims

1. **The scorecard is not a reliable inventory.** There are **26 individually numbered findings**: seven D1, ten D2, nine D3. D2 detail has one low item while the scorecard says three; D3 detail has one low item while the scorecard says two. Cleanup is counted inconsistently as files, groups, and items. The headline **45 actionable items** cannot be treated as a reconciled count of distinct defects.
2. **“12 files” is actually 13 paths.** Row 12 groups two tests. Of those 13, two are automatically discovered and executed by PHPUnit; “zero active references” does not establish they are unused. The feature ExampleTest overlaps the guest redirect test, and the unit example is a trivial assertion. They are cleanup candidates, not failed application behavior.
3. **Other listed orphan candidates have no discovered application consumers.** Searches cover Blade component names, the layout view name, and relevant source references. `GuestLayout` itself references `layouts.guest`; they form an unused pair, so claiming every file has zero references is literally inaccurate. The static prototype has no discovered runtime integration. This supports a proposed cleanup diff, not an unconditional guarantee against all future/dynamic use.
4. **Documentation is internally inconsistent, not wholly obsolete.** PRD has a current single-user PIN implementation note at lines 7–16 but retains old User relationships and password material later. Its Kanban scope text says complex drag-and-drop is excluded **if it delays MVP**, and later calls it optional; that is not an absolute prohibition. E-signature contract-system scope is also broader than the current uploaded signature feature. Archive or revise stale sections while preserving useful requirements/history; do not assume README/CLAUDE are automatically correct either.
5. **Flowbite is loaded, but widget usage was not found.** JS imports it and CSS runs its plugin/source scan. No current `data-modal-*`, `data-drawer-*`, `data-dropdown-*`, or `data-tooltip-*` consumers were found. Removal is plausible; actual JS/CSS savings and visual equivalence require a build comparison and UI checks. Baseline bundle sizes above do not prove a “substantial” saving.
6. **The list really contains 52 distinct selector names.** Exact class-token scans found no consumers of those 52 names in application/view/JS/test/public source outside CSS and generated build output. They are credible unused-selector candidates. Preserve shared rules and breakpoints, account for dynamic class construction, and keep the calendar classes if they are about to be reused. Unused CSS is maintenance work, not 52 independent bugs.
7. **Companion scripts were inspected, not trusted or executed blindly.** Cleanup script deletes 13 paths and archives two docs. Verification script's regex counts do not compile Blade or validate directive nesting; brace counts do not parse CSS. It has no npm build step despite mentioning `node_modules`, and normal PHPUnit does not cover most audit claims. Do not use script success as a release gate.

## Why the proposed fixes need revision

### Nullable internal projects affect more than two templates

The migration can support internal projects, but it is a product/schema decision. A smaller alternative is to require a client during promotion and preserve the current invariant.

If nullable projects are intended, also remove the project form's HTML `required` client constraint, audit **every** project-client consumer, and clarify billing for internal projects. `invoices/form.blade.php:31` dereferences every listed project's client. A probe applies the proposed nullable schema and creates one internal project: **GET `/invoices/create` still returns 500**. Existing archived deals can likewise break `projects/form.blade.php:30` when selecting related deals.

The sample rollback changes the column back to non-nullable without handling newly created null rows. Define a rollback/data policy and verify the migration/foreign keys on each supported database. Put project creation and card linking in one transaction, and test repeated promotion and stale links.

### Historical client display and new relation validation are different policies

`withTrashed()` is a reasonable way to display historical client names. It does not make the linked `clients.show` route resolve soft-deleted clients; links on deal/invoice detail pages need an archived-client policy or a non-link label.

Rejecting all soft-deleted IDs also affects edits to existing historical records. Define whether an unchanged archived relationship may be retained, while preventing a newly selected archived relationship. Test create, unchanged edit, reassignment, and exports. Avoid adding tenant ownership machinery to this intentionally userless app solely because a helper is named `ownedExists`.

### Payment synchronization needs a ledger lifecycle

The proposed create/update calls fix only the first missing-income case. They leave stale records after amount changes, status reversal/cancellation, or soft deletion. Invoice writes, item totals, payment records, and invoice numbering are not all protected by one transaction in the shown patch. Existing `exists()` before create is also not a demonstrated concurrency guarantee.

Define the ledger model: one reconciled payment per invoice or multiple payment events; explicitly handle partial payments, edits, reversals, and retries. Select a compatible wallet/currency. Add atomic persistence and uniqueness/idempotency appropriate to that model. Do not infer that choosing status paid with a zero amount should silently count as receipt of money without a clear UI contract.

### PIN fallback must include its JavaScript behavior

The example replaces the PIN display with a password form, but existing JS unconditionally calls `pinDisplay.querySelectorAll()` and `numpad.querySelectorAll()` (`login.blade.php:836–841`) and intercepts digit keys globally. The fallback needs its own guarded initialization; otherwise it causes null errors or captures input keys if elements are retained. Test four/six-digit PINs, leading zeroes, passphrases, invalid configuration lengths, rate limits, and real browser input. Updating only the text assertion is insufficient.

### Checklist, wallet, and tax changes require explicit semantics

- Checklist: decide whether uncheck reverses CRM completion. If it does, protect manual changes, original task state, cancelled tasks, and multiple checklist links. Existing direct task updates also bypass the progress-service flow used by normal CRM task completion; check project progress as part of the fix.
- Wallets: distinguish active-account display from total assets and historical cashflow. The suggested default filter also makes a goal tied specifically to an archived wallet return zero unless explicitly handled.
- Tax: a percentage setting must be applied to a defined taxable base, with rounding; the current `tax` input is a currency amount.

The component `hideTrigger` addition is a small plausible fix for duplicate delete triggers. The remaining examples are implementation sketches, not uniformly copy-paste-ready patches. The board-action example adds deletion but does not implement the promised rename UI.

## Revised execution order and acceptance criteria

1. **Prevent silent invoice item loss (D2-06).** Preserve all items on edit and validation errors; test an invoice with six or more rows through a normal save. Treat automatic row removal as an explicit user action.
2. **Fix archive-induced 500s (D1-02/D1-03).** Decide historical relation and link behavior; test lists, detail pages, create/edit selectors, print/PDF, and validation after archive.
3. **Restore valid PIN input (D3-01), then correct the stale assertion (D3-04).** Verify browser behavior as well as valid/invalid server requests. Fix README/config contract together.
4. **Fix board promotion (D1-01).** Choose required-client promotion or full nullable-project support. Test promotion, editing, invoicing selectors, repeated calls, and schema compatibility.
5. **Reconcile invoice payments and client aggregates (D3-06/D2-02).** Cover create/update payment paths, changing totals, cancellation/reversal, retries, and more than eight related records. These are financial correctness issues.
6. **Repair limited functional gaps.** Journal date filtering, delete/edit UI, duplicate triggers, default tax semantics, and seeder idempotency. Handle checklist/wallet behavior after agreeing the intended semantics.
7. **Perform cleanup last as a reviewable diff.** Reconcile docs, remove proven unused pieces, compare dependency build output, and inspect mobile/three-theme UI. Keep optional enhancements separate from defect acceptance.

Completion should require regression tests for desired outcomes plus relevant browser checks. Neither the original scorecard nor the defect-confirming probes constitutes a clean bill of health.
