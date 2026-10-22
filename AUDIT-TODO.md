# Verification status and backlog

Updated **22 October 2026**. Completed checks are listed separately from remaining work. See [the evidence index](docs/audit-validation/README.md) for logs and [the latest report](08_Final_Data_and_Upload_Review.md) for details.

## Completed

| Check | Evidence and scope |
| --- | --- |
| Automated checks | 52 tests / 284 assertions passed on SQLite and MySQL 8.4.9; PHP lint, Pint, Blade compilation, Vite build and diff whitespace passed |
| Invoice editor | Browser saved an eighth item; automated tests cover row preservation, validation, tax and payments |
| Archived links | Tests cover lists, details, edit selectors, invoice print/PDF and validation |
| PIN | Tests cover valid/invalid input and rate limiting; browser exercised numeric and passphrase forms |
| Boards | Browser opened the full page/layout, created cards independently in two columns, dragged a card across columns and confirmed its position after reload |
| Mobile navigation and themes | Checked the drawer at 390 px and the board in light, dark and sepia at desktop width; not every page/device |
| Image cropping | Browser uploaded a PNG, cropped/saved it and loaded the saved image |
| Goal and Vault files | Tests cover PIN protection, upload/serve/download/removal, type handling and banner replacement failures |
| Concurrent requests | Eight separate MySQL workers produced one automatic invoice payment; eight promotion workers produced one linked project |
| Historical payment gaps | Read-only review found three demo-pattern invoices and zero wallets/transactions; no financial correction was made |

## Remaining checks

- [ ] **Real touch devices:** drag cards and checklist items, confirm saved order after reload, and inspect the mobile drawer.
- [ ] **Today checklist:** broader browser checks for reordering, moving yesterday's unfinished items, importing tasks once, and consistency with the home checklist. Date navigation and linked-task completion/progress already have targeted coverage.
- [ ] **Home checklist:** browser checks for add/toggle without a page reload and consistency with Today. The home summary counter is calculated when the page loads.
- [ ] **All pages and themes:** keyboard use, focus visibility, long text, small screens and measured color contrast. Prior visual checks do not establish accessibility compliance.
- [ ] **Deployment uploads:** test maximum-size files/batches, slow or interrupted requests, and storage failures on the intended server. Check PHP, web-server and proxy request limits. Terminal PHP was measured at 2 MB per file / 8 MB per request; web-serving PHP may differ.
- [ ] **Database coverage:** MariaDB, other supported deployment versions and sustained load beyond eight workers.
- [x] **Local commit:** audit fixes, tests, evidence and documentation committed at the user's request.
- [ ] **Delivery:** push, PR and deployment when requested; none has been performed.

## Planned product work

These are unimplemented features, not unresolved audit defects:

- Journal month calendar; date filtering already works.
- Create a board card directly from QuickCapture; board navigation and record search already work.
- Save focus-session history; the browser work/break timer already works.
- View and edit records offline; only a basic offline page exists.
- Other ideas listed in [README](README.md#planned-features), including time tracking and recurring invoices.

Financial savings goals are already implemented, including progress calculated from wallet balances. They are not an outstanding feature.

## Handling the three old payments

The review is complete. INV-0003, INV-0004 and INV-0007 match the demo seeder's numbers, amounts, statuses and notes. There are no wallet balances or transactions to reconcile. If they represent real receipts, choose/create the appropriate wallet and resave each invoice. Do not create assumed financial records automatically. See [the read-only evidence](docs/audit-validation/payment-gap-review.json).
