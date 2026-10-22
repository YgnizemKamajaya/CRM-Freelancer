# Original audit plan: current status

Updated **22 October 2026**. The supplied plan was independently checked, and the accepted fixes have been implemented. The original text is preserved in [the historical proposal](docs/archive/01_Original_Audit_Proposal.md).

Use [AUDIT-TODO.md](AUDIT-TODO.md) for remaining work and [README.md](README.md) for current behavior. This file no longer contains executable patch instructions because several original recommendations were incorrect or incomplete.

## What changed after validation

| Original recommendation | Implemented decision |
| --- | --- |
| Allow projects without a client | Keep the client requirement; ask for an active client when promoting a card |
| Exclude archived wallets from totals | Keep their balances in assets and goal progress |
| Treat Paid status as money received | Require an explicit received amount in the invoice editor |
| Record income only once | Update one automatic entry as payments change; preserve manually entered income |
| Reopen CRM tasks when unchecking checklist items | Keep completion one-way; reopen explicitly from Tasks |
| Delete every listed unused file/test | Remove confirmed unused app files; retain the two executable example tests |
| Trust static verification and the numerical scorecard | Use behavior tests, compiled templates, builds and scoped browser/database checks |

## Reports

- [Independent validation](04_Audit_Validation.md): original findings, rejected claims and corrected approach.
- [Initial execution](05_Audit_Execution.md): implemented fixes and the first verification run.
- [Browser, upload and MySQL follow-up](07_Backlog_Verification.md).
- [Final data/upload review](08_Final_Data_and_Upload_Review.md): latest **52 tests / 284 assertions on SQLite and MySQL 8.4.9**.

Do not run the old cleanup script. Completed audit work is recorded in a local commit; remaining device/deployment checks and planned product features are listed separately in the backlog.
