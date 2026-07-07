# PRD + Implementation Plan

# Mini CRM for Freelancer

# Stack: Laravel 12, MySQL, Tailwind CSS 4.2, Flowbite 4, Heroicons SVG, Livewire SPA-like, Single-user PIN Auth, PWA

## Current Implementation Update

This project is now greenfield single-user PIN mode:

* No users table.
* No registration, password reset, email verification, or multi-user ownership path.
* `/login` unlocks the private workspace with `CRM_PIN`.
* `/logout` locks the workspace session.
* Owner name, owner email, avatar, business identity, and invoice defaults live in `settings`.
* CRM data tables are not scoped by `user_id`.

## 1. Product Name

Working name: Freelance CRM

Alternative names:

* ClientPilot
* FreelanceFlow CRM
* SoloCRM
* ClientDesk
* DealTrackr

Use “Freelance CRM” during development.

## 2. Product Summary

Freelance CRM is a single-user web application for freelancers to manage leads, clients, deals, projects, follow-ups, notes, invoices, and communication history in one clean dashboard.

The product is designed for personal freelancer use, not enterprise teams. The goal is to help a freelancer avoid scattered client information across WhatsApp, email, spreadsheets, notes, invoices, and task apps.

The application should be production-ready as a portfolio project, with clean architecture, mobile-first UI, secure authentication, CRUD flows, dashboard analytics, and PWA support.

## 3. Target User

Primary user:

* Solo freelancer
* Web developer
* Designer
* Writer
* Consultant
* Digital marketer
* Virtual assistant
* Small personal service provider

User problems:

* Leads are forgotten after initial contact.
* Follow-up timing is messy.
* Client notes are scattered.
* Project status is unclear.
* Invoice status is tracked manually.
* Revenue forecast is hard to estimate.
* Past client context is forgotten when they return months later.

## 4. Product Goals

Main goals:

1. Help freelancer track leads from first contact until project completion.
2. Provide a simple client database with full relationship history.
3. Provide deal pipeline tracking with value, stage, probability, and follow-up date.
4. Provide lightweight project tracking after a deal is won.
5. Provide task and reminder management for follow-ups.
6. Provide simple invoice/payment tracking.
7. Provide dashboard insight for revenue, pipeline value, unpaid invoices, and upcoming tasks.
8. Provide a polished portfolio-quality UI using Laravel, Tailwind, Flowbite, and Heroicons.
9. Support PWA for mobile-friendly use.
10. Keep the app simple, fast, and maintainable.

## 5. Non-Goals

Do not build these in MVP:

* Multi-company SaaS billing
* Real AI integration
* Complex team permission system
* Real payment gateway integration
* Real email sending automation
* Real WhatsApp Business API integration
* Accounting-grade finance module
* Public client portal
* E-signature contract system
* Complex kanban drag-and-drop if it delays MVP
* Overly fancy animation
* Over-engineered notification/push system

Optional after MVP:

* Client portal
* Proposal builder
* Contract storage
* Time tracking
* Email templates
* WhatsApp quick message templates
* CSV/Excel import/export
* PDF invoice
* Basic automation rules

## 6. Tech Stack

Backend:

* Laravel 12
* PHP 8.3+
* MySQL or MariaDB
* Eloquent ORM
* Laravel migrations
* Laravel seeders
* Laravel form requests
* Laravel policies where useful
* Laravel queues optional, not required for MVP

Frontend:

* Blade
* Livewire full-page components or Blade + Livewire partial components
* Tailwind CSS 4.2
* Flowbite 4
* Heroicons SVG inline or component-based
* Vite

Auth:

* Single-user PIN unlock
* No registration
* No password reset
* No email verification
* Login unlock and logout lock available
* Rate limit invalid PIN attempts

Database:

* MySQL/MariaDB
* Use foreign keys
* Use soft deletes for important business data
* Use indexes for frequently queried fields

PWA:

* manifest.webmanifest
* service worker
* offline fallback page
* app icons
* mobile installable
* cache static assets only
* do not cache sensitive dashboard API responses aggressively

Deployment:

* VPS or shared hosting-ready
* .env production config
* APP_ENV=production
* APP_DEBUG=false
* php artisan migrate --force
* npm run build
* config/route/view cache
* storage link
* proper file permission notes

## 7. MVP Scope

MVP modules:

1. Authentication
2. Dashboard
3. Clients
4. Contacts
5. Leads/Deals Pipeline
6. Projects
7. Tasks/Follow-ups
8. Notes/Activities
9. Simple Invoices
10. Settings
11. PWA
12. Basic reports
13. Seed demo data
14. Production-ready README

## 8. Core User Flow

### Flow A: New Lead to Won Project

1. User creates a new lead/deal.
2. User links the deal to an existing client or creates a new client.
3. User adds deal value, stage, source, expected close date, and next follow-up date.
4. User adds notes after each conversation.
5. User moves the deal through pipeline stages.
6. When deal becomes won, user can convert it into a project.
7. Project is created with title, client, start date, deadline, budget, and status.
8. User adds tasks to the project.
9. User creates invoice when work is complete or payment is due.
10. Dashboard updates revenue, active projects, unpaid invoices, and completed projects.

### Flow B: Daily Freelancer Workflow

1. User opens dashboard.
2. User sees today’s follow-ups.
3. User sees overdue tasks.
4. User sees active deals.
5. User sees unpaid invoices.
6. User clicks a client to review context.
7. User logs a note after contacting the client.
8. User schedules next follow-up.

### Flow C: Returning Client

1. User searches client name.
2. User opens client profile.
3. User sees previous deals, projects, invoices, notes, and tasks.
4. User creates a new deal/project with existing context.

## 9. Information Architecture

Main navigation:

* Dashboard
* Clients
* Deals
* Projects
* Tasks
* Invoices
* Reports
* Settings

Secondary navigation:

* Client details
* Deal details
* Project details
* Invoice details
* Activity log
* Owner profile/settings

## 10. Page Requirements

### 10.1 Login Page

Requirements:

* PIN unlock form
* Clean modern UI
* App name and short tagline
* Mobile responsive
* Rate-limited invalid attempts

### 10.2 Dashboard Page

Purpose:
Give a quick overview of freelancer business health.

KPI cards:

* Total clients
* Active deals
* Active projects
* Pipeline value
* Expected revenue
* Unpaid invoice amount
* Overdue invoices
* Tasks due today

Sections:

* Today’s follow-ups
* Overdue tasks
* Active deals by stage
* Recent clients
* Recent notes/activity
* Unpaid invoices
* Mini monthly revenue chart
* Quick action buttons

Quick actions:

* Add Client
* Add Deal
* Add Project
* Add Task
* Add Invoice

Dashboard filters:

* This month
* This quarter
* This year
* All time

### 10.3 Clients List Page

Features:

* Table/card view
* Search by name, company, email, phone
* Filter by status
* Filter by tag
* Sort by recent activity
* Sort by created date
* Sort by total revenue
* Add new client button

Client fields:

* Name
* Company name
* Email
* Phone/WhatsApp
* Website
* Address optional
* Source
* Status
* Tags
* Notes
* Created date
* Last contacted date

Client statuses:

* Lead
* Prospect
* Active Client
* Past Client
* Archived

### 10.4 Client Detail Page

Sections:

* Client summary card
* Contact information
* Tags
* Revenue summary
* Deals
* Projects
* Invoices
* Tasks
* Notes/activity timeline
* Files optional for later

Actions:

* Edit client
* Add deal
* Add project
* Add task
* Add note
* Add invoice
* Archive client
* Open WhatsApp link
* Open email mailto link

Client insight:

* Total deals
* Won deals
* Active projects
* Total invoiced
* Total paid
* Outstanding amount
* Last activity
* Next follow-up

### 10.5 Deals Pipeline Page

Purpose:
Track sales opportunities.

MVP layout:

* Use grouped cards by stage or table with stage filter.
* Kanban drag-and-drop is optional. If not implemented, provide stage dropdown update.

Deal fields:

* Title
* Client
* Value
* Currency
* Stage
* Probability
* Source
* Expected close date
* Next follow-up date
* Description
* Lost reason
* Won date
* Lost date

Default stages:

1. New Inquiry
2. Qualified
3. Proposal Sent
4. Negotiation
5. Won
6. Lost

Deal source options:

* Referral
* Website
* LinkedIn
* Instagram
* WhatsApp
* Marketplace
* Existing Client
* Other

Features:

* Add deal
* Edit deal
* Change stage
* Mark as won
* Mark as lost
* Convert won deal to project
* Add note
* Add task/follow-up
* Filter by stage
* Filter by source
* Filter by expected close date
* Search by title/client
* Pipeline value summary
* Weighted pipeline summary using probability

### 10.6 Deal Detail Page

Sections:

* Deal summary
* Client info
* Stage/probability
* Value
* Expected close date
* Next follow-up
* Notes timeline
* Related tasks
* Related project if converted

Actions:

* Edit deal
* Change stage
* Mark won
* Mark lost
* Convert to project
* Add note
* Add follow-up task

### 10.7 Projects Page

Purpose:
Track delivery after deal is won.

Project fields:

* Title
* Client
* Related deal optional
* Description
* Status
* Budget
* Start date
* Deadline
* Completed date
* Priority
* Progress percentage

Project statuses:

* Planned
* In Progress
* Waiting Client
* Revision
* Completed
* Cancelled

Features:

* Add project
* Edit project
* Filter by status
* Filter by client
* Sort by deadline
* Search
* Project task count
* Progress indicator
* Convert from won deal

### 10.8 Project Detail Page

Sections:

* Project summary
* Client info
* Deadline/progress
* Tasks
* Notes/activity
* Related invoices
* Related deal

Actions:

* Add task
* Add note
* Add invoice
* Mark completed
* Edit project

### 10.9 Tasks / Follow-ups Page

Purpose:
Prevent missed follow-ups and delivery tasks.

Task fields:

* Title
* Description
* Type
* Status
* Priority
* Due date
* Completed date
* Related client
* Related deal optional
* Related project optional
* Related invoice optional

Task types:

* Follow-up
* Call
* WhatsApp
* Email
* Meeting
* Project Task
* Invoice Reminder
* Admin

Task statuses:

* Pending
* In Progress
* Done
* Cancelled

Task priorities:

* Low
* Medium
* High
* Urgent

Features:

* Today view
* Upcoming view
* Overdue view
* Completed view
* Filter by type
* Filter by priority
* Mark done
* Quick reschedule
* Add task from client/deal/project/invoice detail page

### 10.10 Notes / Activity Timeline

Purpose:
Store relationship context.

Activity types:

* General note
* Call
* WhatsApp
* Email
* Meeting
* Proposal sent
* Invoice sent
* Payment received
* Status update

Fields:

* Body
* Type
* Related client
* Related deal optional
* Related project optional
* Related invoice optional
* Activity date
* Created by user

Features:

* Add note from any detail page
* Show notes in client timeline
* Show notes in related object detail pages
* Sort newest first
* Search notes optional

### 10.11 Invoices Page

Purpose:
Track simple billing status.

Invoice fields:

* Invoice number
* Client
* Project optional
* Issue date
* Due date
* Currency
* Subtotal
* Discount optional
* Tax optional
* Total
* Amount paid
* Status
* Notes

Invoice statuses:

* Draft
* Sent
* Partially Paid
* Paid
* Overdue
* Cancelled

Invoice item fields:

* Description
* Quantity
* Unit price
* Total

Features:

* Create invoice
* Add invoice items
* Edit invoice
* Mark as sent
* Mark as paid
* Mark as partially paid
* Mark as overdue automatically by due date
* Filter by status
* Filter by client
* Search invoice number
* Show unpaid total
* Show overdue total

Optional but recommended:

* PDF invoice generation
* Print-friendly invoice view

### 10.12 Reports Page

MVP reports:

* Revenue this month
* Paid invoices this month
* Unpaid invoice total
* Pipeline value by stage
* Deals won/lost count
* Top clients by revenue
* Active projects by status
* Tasks completed vs pending

Charts:

* Monthly revenue line/bar chart
* Deal stage distribution
* Invoice status distribution

Use simple chart library only if needed. Avoid overengineering.

### 10.13 Settings Page

Settings:

* Freelancer/business name
* Logo upload optional
* Email
* Phone/WhatsApp
* Website
* Default currency
* Invoice prefix
* Invoice next number
* Default tax percentage optional
* Theme preference optional
* PWA install info
* Owner profile/avatar settings

## 11. Data Model

### 11.1 sessions

Use Laravel database sessions for the PIN-unlocked workspace.

Fields:

* id
* ip_address nullable
* user_agent nullable
* payload
* last_activity

### 11.2 clients

Fields:

* id
* name
* company_name nullable
* email nullable
* phone nullable
* whatsapp nullable
* website nullable
* address nullable
* source nullable
* status enum/string
* tags json nullable
* notes text nullable
* last_contacted_at nullable
* archived_at nullable
* timestamps
* softDeletes

Indexes:

* status
* name
* email
* last_contacted_at

### 11.3 deals

Fields:

* id
* client_id
* title
* description nullable
* value decimal
* currency string default IDR
* stage string
* probability integer default 0
* source nullable
* expected_close_date nullable
* next_follow_up_at nullable
* status string default open
* lost_reason nullable
* won_at nullable
* lost_at nullable
* timestamps
* softDeletes

Deal status:

* open
* won
* lost
* archived

Indexes:

* client_id
* stage
* status
* expected_close_date
* next_follow_up_at

### 11.4 projects

Fields:

* id
* client_id
* deal_id nullable
* title
* description nullable
* status string
* priority string default medium
* budget decimal nullable
* currency string default IDR
* start_date nullable
* deadline nullable
* completed_at nullable
* progress integer default 0
* timestamps
* softDeletes

Indexes:

* client_id
* deal_id
* status
* deadline

### 11.5 tasks

Fields:

* id
* client_id nullable
* deal_id nullable
* project_id nullable
* invoice_id nullable
* title
* description nullable
* type string
* status string default pending
* priority string default medium
* due_at nullable
* completed_at nullable
* timestamps
* softDeletes

Indexes:

* client_id
* deal_id
* project_id
* invoice_id
* status
* priority
* due_at

### 11.6 activities

Fields:

* id
* client_id nullable
* deal_id nullable
* project_id nullable
* invoice_id nullable
* type string
* body text
* activity_at datetime nullable
* timestamps
* softDeletes

Indexes:

* client_id
* deal_id
* project_id
* invoice_id
* type
* activity_at

### 11.7 invoices

Fields:

* id
* client_id
* project_id nullable
* invoice_number
* issue_date
* due_date nullable
* currency string default IDR
* subtotal decimal default 0
* discount decimal default 0
* tax decimal default 0
* total decimal default 0
* amount_paid decimal default 0
* status string default draft
* notes text nullable
* sent_at nullable
* paid_at nullable
* timestamps
* softDeletes

Indexes:

* client_id
* project_id
* invoice_number
* status
* due_date

Unique:

* invoice_number unique

### 11.8 invoice_items

Fields:

* id
* invoice_id
* description
* quantity decimal default 1
* unit_price decimal default 0
* total decimal default 0
* sort_order integer default 0
* timestamps

Indexes:

* invoice_id

### 11.9 settings

Fields:

* id
* business_name nullable
* business_email nullable
* business_phone nullable
* business_whatsapp nullable
* business_website nullable
* logo_path nullable
* default_currency default IDR
* invoice_prefix default INV
* invoice_next_number default 1
* default_tax_percentage nullable
* timestamps

Unique:


## 12. Eloquent Relationships

User:

* hasMany Client
* hasMany Deal
* hasMany Project
* hasMany Task
* hasMany Activity
* hasMany Invoice
* hasOne Setting

Client:

* belongsTo User
* hasMany Deal
* hasMany Project
* hasMany Task
* hasMany Activity
* hasMany Invoice

Deal:

* belongsTo User
* belongsTo Client
* hasOne Project
* hasMany Task
* hasMany Activity

Project:

* belongsTo User
* belongsTo Client
* belongsTo Deal nullable
* hasMany Task
* hasMany Activity
* hasMany Invoice

Task:

* belongsTo User
* belongsTo Client nullable
* belongsTo Deal nullable
* belongsTo Project nullable
* belongsTo Invoice nullable

Activity:

* belongsTo User
* belongsTo Client nullable
* belongsTo Deal nullable
* belongsTo Project nullable
* belongsTo Invoice nullable

Invoice:

* belongsTo User
* belongsTo Client
* belongsTo Project nullable
* hasMany InvoiceItem
* hasMany Task
* hasMany Activity

InvoiceItem:

* belongsTo Invoice

Setting:

* belongsTo User

## 13. Validation Rules

### Client

Required:

* name
  Optional:
* email must be valid email
* website must be valid URL
* status must be one of allowed statuses

### Deal

Required:

* client_id
* title
* stage
  Optional:
* value must be numeric >= 0
* probability must be integer 0-100
* expected_close_date must be date
* next_follow_up_at must be datetime/date

### Project

Required:

* client_id
* title
* status
  Optional:
* budget numeric >= 0
* progress integer 0-100
* deadline date
* start_date date

### Task

Required:

* title
* type
* status
* priority
  Optional:
* due_at date/datetime

### Invoice

Required:

* client_id
* invoice_number
* issue_date
* status
  Optional:
* due_date date after or equal issue_date
* subtotal numeric >= 0
* discount numeric >= 0
* tax numeric >= 0
* total numeric >= 0
* amount_paid numeric >= 0

### Invoice Item

Required:

* description
* quantity numeric > 0
* unit_price numeric >= 0

## 14. Business Logic

### 14.1 Deal Weighted Value

weighted_value = deal.value * deal.probability / 100

Use this for expected revenue.

### 14.2 Deal Status

If stage is Won:

* status = won
* won_at = current timestamp
* probability = 100

If stage is Lost:

* status = lost
* lost_at = current timestamp
* probability = 0

Otherwise:

* status = open

### 14.3 Convert Deal to Project

When converting:

* Create project using deal title/client/value
* Link project.deal_id
* Set project.status = Planned
* Set project.budget = deal.value
* Add activity: “Deal converted to project”
* Prevent duplicate conversion if project already exists for deal

### 14.4 Invoice Total

For invoice:

* subtotal = sum(invoice_items.total)
* total = subtotal - discount + tax
* status can be updated based on amount_paid

Rules:

* amount_paid <= 0: Sent or Draft
* amount_paid > 0 and amount_paid < total: Partially Paid
* amount_paid >= total: Paid
* due_date < today and status not paid/cancelled: Overdue

### 14.5 Client Last Contacted

When user creates an activity of type call, WhatsApp, email, meeting, or general note:

* update client.last_contacted_at = activity.activity_at or now()

### 14.6 Dashboard Calculations

Cards:

* total_clients = clients count excluding archived
* active_deals = deals where status open
* active_projects = projects where status not completed/cancelled
* pipeline_value = sum value of open deals
* expected_revenue = sum weighted value of open deals
* unpaid_invoice_amount = sum total - amount_paid where invoice not paid/cancelled
* overdue_invoice_amount = same where due_date < today
* tasks_due_today = tasks due today and not done

## 15. UI/UX Direction

Design style:

* Modern dark dashboard
* Clean but not too fancy
* Gradient sidebar
* Soft dark cards
* Rounded-xl components
* KPI cards
* Modern data tables
* Mobile-first responsive layout
* Clear empty states
* Clear call-to-action buttons
* Good spacing and typography
* Heroicons SVG for icons
* Flowbite components where useful

Layout:

* Desktop: sidebar + topbar
* Mobile: bottom nav or collapsible drawer
* Topbar contains search, quick add, profile dropdown
* Main content max width with clean spacing
* Use cards for dashboard sections
* Use tables for desktop list pages
* Use stacked cards for mobile list pages

Important UX principle:
The user should always know:

* Which client this item belongs to
* What needs follow-up next
* Which invoice is unpaid
* Which deal is close to closing
* Which project is overdue

## 16. Components Required

Reusable components:

* AppLayout
* Sidebar
* Topbar
* MobileNav
* StatCard
* EmptyState
* ConfirmModal
* StatusBadge
* PriorityBadge
* StageBadge
* CurrencyDisplay
* DateDisplay
* SearchInput
* FilterDropdown
* ActionButton
* Table
* Pagination
* TimelineItem
* FormSection
* QuickCreateModal optional

## 17. Routes

Use auth middleware for all app routes.

Dashboard:

* GET /dashboard

Clients:

* GET /clients
* GET /clients/create
* POST /clients
* GET /clients/{client}
* GET /clients/{client}/edit
* PUT /clients/{client}
* DELETE /clients/{client}

Deals:

* GET /deals
* GET /deals/create
* POST /deals
* GET /deals/{deal}
* GET /deals/{deal}/edit
* PUT /deals/{deal}
* POST /deals/{deal}/mark-won
* POST /deals/{deal}/mark-lost
* POST /deals/{deal}/convert-to-project
* DELETE /deals/{deal}

Projects:

* GET /projects
* GET /projects/create
* POST /projects
* GET /projects/{project}
* GET /projects/{project}/edit
* PUT /projects/{project}
* POST /projects/{project}/mark-completed
* DELETE /projects/{project}

Tasks:

* GET /tasks
* POST /tasks
* GET /tasks/{task}/edit
* PUT /tasks/{task}
* POST /tasks/{task}/complete
* POST /tasks/{task}/reopen
* DELETE /tasks/{task}

Activities:

* POST /activities
* DELETE /activities/{activity}

Invoices:

* GET /invoices
* GET /invoices/create
* POST /invoices
* GET /invoices/{invoice}
* GET /invoices/{invoice}/edit
* PUT /invoices/{invoice}
* POST /invoices/{invoice}/mark-sent
* POST /invoices/{invoice}/mark-paid
* DELETE /invoices/{invoice}
* GET /invoices/{invoice}/print
* GET /invoices/{invoice}/pdf optional

Reports:

* GET /reports

Settings:

* GET /settings
* PUT /settings

PWA:

* GET /offline
* GET /manifest.webmanifest
* GET /sw.js

## 18. Security Requirements

Must implement:

* Single-user PIN unlock
* Rate limit PIN attempts
* CSRF protection
* Form request validation
* Escaped output in Blade
* File upload validation for avatar/logo
* No public registration or user management
* Use environment variables for DB and app config
* APP_DEBUG=false in production
* Do not expose sensitive .env file
* Rate limit login if easy to implement

## 19. Demo Data

Seeder should create:

* 1 demo user
* 8 clients
* 12 deals across stages
* 5 active projects
* 20 tasks
* 30 activities
* 8 invoices with mixed statuses

Demo data should make dashboard look alive.

Suggested demo user:

* Name: Demo Freelancer
* Email: [demo@example.com](mailto:demo@example.com)
* Password: password

Only use this for local/demo. Mention clearly in README.

## 20. Acceptance Criteria

The MVP is complete when:

Authentication:

* User can login and logout.
* Registration is disabled or protected after first user setup.

Dashboard:

* User can see KPI cards.
* User can see follow-ups due today.
* User can see active deals/projects/invoices.

Clients:

* User can create, view, edit, delete/archive clients.
* User can search and filter clients.
* Client detail shows deals, projects, invoices, tasks, and activities.

Deals:

* User can create and manage deals.
* User can update deal stage.
* User can mark deal as won/lost.
* User can convert won deal to project.
* Pipeline value and expected revenue are calculated.

Projects:

* User can create and manage projects.
* Project can link to client and deal.
* Project detail shows tasks, notes, and invoices.

Tasks:

* User can create task/follow-up.
* User can mark task done.
* User can view overdue/today/upcoming tasks.

Activities:

* User can add notes from client/deal/project pages.
* Activities appear in client timeline.
* Client last contacted date updates.

Invoices:

* User can create invoices with items.
* Total is calculated correctly.
* Invoice status can be updated.
* Overdue invoices are shown.

Reports:

* User can view basic revenue, pipeline, invoice, and project reports.

PWA:

* App has manifest.
* App has service worker.
* App has offline fallback.
* App is installable on supported browsers.

Production:

* README explains installation, local setup, database migration, seed, build, and deployment.
* No obvious dead code, broken routes, or missing views.
* Mobile layout is usable.

## 21. Implementation Plan

### Phase 0: Project Setup

Tasks:

1. Create Laravel 12 project.
2. Configure MySQL.
3. Implement PIN unlock.
4. Install Tailwind CSS 4.2.
5. Install Flowbite 4.
6. Configure Vite.
7. Add Heroicons SVG strategy.
8. Create app layout.
9. Create dark dashboard theme.
10. Setup PWA basic files.
11. Setup README skeleton.

Deliverables:

* Laravel app boots.
* Login works.
* Dashboard route protected.
* Tailwind/Flowbite styling works.
* PWA files exist.

### Phase 1: Database Foundation

Tasks:

1. Create migrations:

   * clients
   * deals
   * projects
   * tasks
   * activities
   * invoices
   * invoice_items
   * settings
2. Add foreign keys.
3. Add indexes.
4. Add soft deletes.
5. Create models.
6. Define relationships.
7. Create factories.
8. Create seeders.

Deliverables:

* php artisan migrate works.
* php artisan db:seed creates useful demo data.
* Relationships work in tinker/tests.

### Phase 2: Core Layout and Components

Tasks:

1. Build sidebar.
2. Build topbar.
3. Build mobile navigation.
4. Build reusable cards.
5. Build badges.
6. Build empty state component.
7. Build table component or consistent table pattern.
8. Build confirmation modal pattern.
9. Build flash message component.

Deliverables:

* UI consistent across pages.
* Desktop and mobile navigation work.
* All pages can reuse components.

### Phase 3: Clients Module

Tasks:

1. Create ClientController or Livewire Client pages.
2. Build clients index.
3. Build create/edit form.
4. Build client detail page.
5. Add search/filter/sort.
6. Add archive/delete action.
7. Add client status badge.
8. Add WhatsApp/email quick action.
9. Add client-related summary cards.

Deliverables:

* Full client CRUD works.
* Client detail shows placeholder sections for related records.
* Client data is user-scoped.

### Phase 4: Deals Pipeline Module

Tasks:

1. Create DealController or Livewire Deal pages.
2. Build deals index.
3. Build pipeline grouped by stage.
4. Build create/edit deal form.
5. Build deal detail page.
6. Add stage update action.
7. Add mark won/lost actions.
8. Add convert to project action.
9. Add pipeline value summary.
10. Add weighted expected revenue calculation.

Deliverables:

* Full deal CRUD works.
* Deal stage flow works.
* Won deal can become project.
* Pipeline summary is visible.

### Phase 5: Projects Module

Tasks:

1. Create ProjectController or Livewire Project pages.
2. Build project index.
3. Build create/edit form.
4. Build project detail.
5. Link project to client and optional deal.
6. Add status/progress/priority.
7. Add mark completed action.
8. Show related tasks, invoices, and activities.

Deliverables:

* Full project CRUD works.
* Project detail gives clear delivery context.
* Deal conversion creates linked project correctly.

### Phase 6: Tasks and Follow-ups

Tasks:

1. Create TaskController or Livewire Task pages.
2. Build tasks index with tabs:

   * Today
   * Upcoming
   * Overdue
   * Completed
3. Build create/edit form.
4. Allow related client/deal/project/invoice.
5. Add complete/reopen actions.
6. Add task cards on dashboard.
7. Add task creation from client/deal/project pages.

Deliverables:

* Task management works.
* Follow-ups are visible on dashboard.
* Overdue tasks are clearly highlighted.

### Phase 7: Activities / Notes Timeline

Tasks:

1. Create ActivityController.
2. Add note form component.
3. Add activity timeline component.
4. Allow notes on client, deal, project, invoice pages.
5. Update client last_contacted_at when relevant activity is created.
6. Show latest activities on dashboard.

Deliverables:

* User can log relationship history.
* Client detail becomes a complete context hub.
* Activities are correctly linked.

### Phase 8: Invoices Module

Tasks:

1. Create InvoiceController or Livewire Invoice pages.
2. Build invoice index.
3. Build invoice create/edit form.
4. Support dynamic invoice items.
5. Auto-calculate subtotal, discount, tax, total.
6. Add mark sent/paid actions.
7. Add overdue detection.
8. Build invoice detail.
9. Build print-friendly invoice page.
10. Optional: generate PDF.

Deliverables:

* Simple invoicing works.
* Invoice item calculations are correct.
* Dashboard shows unpaid and overdue invoices.

### Phase 9: Reports and Dashboard Analytics

Tasks:

1. Build dashboard query service.
2. Build report query service.
3. Create monthly revenue chart data.
4. Create deal stage distribution.
5. Create invoice status distribution.
6. Create top clients by revenue.
7. Create project status summary.
8. Add filters by date range.

Deliverables:

* Dashboard has real metrics.
* Reports page has useful business insight.
* Queries are clean and not duplicated.

### Phase 10: Settings and Branding

Tasks:

1. Create settings page.
2. Store business profile.
3. Store default currency.
4. Store invoice prefix and next number.
5. Optional logo upload.
6. Validate settings.
7. Use settings in invoice print page.

Deliverables:

* User can configure basic freelancer/business identity.
* Invoice numbering works.

### Phase 11: PWA and Mobile Polish

Tasks:

1. Add manifest.webmanifest.
2. Add app icons.
3. Add service worker.
4. Add offline fallback page.
5. Cache static assets.
6. Test mobile layout.
7. Improve touch targets.
8. Ensure forms work on mobile.
9. Ensure tables become cards or scrollable on mobile.

Deliverables:

* App is installable.
* App has offline fallback.
* Mobile UX is acceptable.

### Phase 12: Testing and Quality Check

Tasks:

1. Test PIN unlock/lock.
2. Test CRUD for every module.
3. Test protected routes.
4. Test invoice calculation.
5. Test deal conversion.
6. Test dashboard metrics.
7. Test empty states.
8. Test seeders.
9. Test mobile layout.
10. Run formatting/linting if configured.
11. Remove dead code.
12. Remove duplicate UI paths.
13. Check missing routes/views.
14. Check broken links.

Deliverables:

* No obvious runtime errors.
* No missing pages.
* No duplicate legacy paths.
* Codebase is clean enough for portfolio showcase.

### Phase 13: Documentation and Deployment

Tasks:

1. Write README.
2. Add feature list.
3. Add screenshots section placeholder.
4. Add installation steps.
5. Add local development steps.
6. Add production deployment steps.
7. Add environment variables sample.
8. Add demo account info.
9. Add database migration/seed guide.
10. Add known limitations.
11. Add future roadmap.

Deliverables:

* README is beginner-friendly.
* Project can be installed by another developer.
* Project is portfolio-ready.

## 22. Suggested Folder Structure

Use standard Laravel structure.

Suggested additions:

* app/Enums
* app/Services/DashboardService.php
* app/Services/InvoiceService.php
* app/Services/DealService.php
* app/View/Components
* resources/views/components
* resources/views/dashboard
* resources/views/clients
* resources/views/deals
* resources/views/projects
* resources/views/tasks
* resources/views/invoices
* resources/views/reports
* resources/views/settings
* resources/views/pwa

If using Livewire:

* app/Livewire/Dashboard
* app/Livewire/Clients
* app/Livewire/Deals
* app/Livewire/Projects
* app/Livewire/Tasks
* app/Livewire/Invoices
* app/Livewire/Reports
* app/Livewire/Settings

## 23. Future Roadmap

Version 1.1:

* CSV client import/export
* PDF invoice
* Project time tracking
* Proposal builder
* Message templates
* WhatsApp quick templates
* Email template copy button
* Client tags management

Version 1.2:

* Client portal
* Public invoice link
* Payment gateway integration
* Recurring invoices
* Calendar view
* Better report charts

Version 1.3:

* Simple automation:

  * create follow-up when deal stage changes
  * remind invoice after due date
  * create onboarding tasks when project starts
* Template library for project tasks
* Backup/export system

Version 1.4:

* Multi-user/team support
* Role permissions
* Subcontractor access
* Advanced activity tracking

## 24. Portfolio Showcase Requirements

When finished, prepare portfolio case study:

Sections:

1. Problem
2. Solution
3. Key Features
4. Tech Stack
5. Database/Architecture Notes
6. Screenshots
7. Demo Credentials
8. GitHub Link
9. Live Demo Link
10. Lessons Learned

Screenshots needed:

* Dashboard
* Clients list
* Client detail
* Deals pipeline
* Project detail
* Tasks page
* Invoice detail
* Mobile view

Portfolio angle:
“This CRM was designed for solo freelancers who need one place to manage leads, clients, projects, follow-ups, invoices, and relationship history without paying for multiple disconnected tools.”

## 25. AI Agent Execution Prompt

You are an expert Laravel full-stack developer.

Build a production-ready Mini CRM for Freelancer based on this PRD.

Use this stack:

* Laravel 12
* PHP 8.3+
* MySQL/MariaDB
* Single-user PIN authentication
* Blade + Livewire SPA-like navigation where appropriate
* Tailwind CSS 4.2
* Flowbite 4
* Heroicons SVG
* Vite
* PWA support

Build the app as a single-user freelancer CRM, not a multi-tenant SaaS.

Important constraints:

* Keep it simple and production-ready.
* Do not add real AI features.
* Do not overengineer.
* Do not add payment gateway integration in MVP.
* Do not add complex team permissions in MVP.
* Use clean architecture and readable code.
* Use migrations, models, relationships, factories, seeders, controllers or Livewire components, form validation, and reusable Blade components.
* Make the UI mobile-first, modern, dark dashboard style, with gradient sidebar, rounded-xl cards, KPI cards, modern tables, clean spacing, and Heroicons.
* Implement PWA with manifest, service worker, app icons, and offline fallback.
* Add complete README with setup and deployment instructions.
* Add demo seed data.

Core modules to implement:

1. Auth
2. Dashboard
3. Clients
4. Deals pipeline
5. Projects
6. Tasks/follow-ups
7. Activities/notes timeline
8. Simple invoices
9. Reports
10. Settings
11. PWA

After building, perform a full code review:

* check missing routes
* check missing views
* check broken actions
* check validation
* check authorization/user ownership
* check invoice calculations
* check deal conversion
* check dashboard metrics
* check mobile UI
* remove dead code
* remove duplicate paths
* clean up unused files
* ensure app runs from fresh install

Final deliverables:

* Complete source code
* README.md
* .env.example
* Migration files
* Seeder files
* Build instructions
* Deployment instructions
* Demo account credentials
* Final walkthrough of implemented features
