# Documentation guide

Updated **22 October 2026**. Check implementation details against the code and tests; a historical report records what was known at that stage.

## Current guides

| Document | Use it for |
| --- | --- |
| [README](../README.md) | Features, setup, payments, files and deployment |
| [Contributor guide](../CLAUDE.md) | Code structure and working conventions |
| [Verification backlog](../AUDIT-TODO.md) | Completed checks, remaining checks and planned features |
| [Start-here handover](../00_START_HERE_AI_AGENT_HANDOVER.md) | Current work status and how to continue |
| [Resume summary](../06_Next_Session_Summary.md) | Completed changes and next steps |
| [Changelog](../CHANGELOG.md) | Unreleased changes and baseline release |

## Audit history

| Record | Meaning |
| --- | --- |
| [Original plan status](../01_4D_Full_Audit_and_Fix_Plan.md) | Accepted decisions and links to the original proposal |
| [Independent review](../04_Audit_Validation.md) | Before-fix investigation; its defect probes are historical |
| [Initial execution](../05_Audit_Execution.md) | First implementation; 44 tests at that stage |
| [Follow-up verification](../07_Backlog_Verification.md) | Browser, upload and MySQL checks; 50 tests at that stage |
| [Data and upload review](../08_Final_Data_and_Upload_Review.md) | Latest application checks: 52 tests / 284 assertions on SQLite and MySQL 8.4.9 |
| [Evidence index](audit-validation/README.md) | Logs, diagnostics and their limits |
| [Archive](archive/README.md) | Old requirements, designs and rejected proposal details |

Earlier test counts and limitations describe their own runs; use the latest report and backlog for current status. Generated logs and diagnostic source are evidence, not setup instructions.
