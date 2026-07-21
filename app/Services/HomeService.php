<?php

namespace App\Services;

use App\Models\Board;
use App\Models\ChecklistItem;
use App\Models\Deal;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Task;
use App\Models\VaultFile;
use Illuminate\Support\Collection;

use App\Models\Project;
use App\Models\Setting;

/**
 * Aggregates the "command centre" home (Dashboard 2.0).
 * Reuses FinanceService / JournalService; leaves the legacy DashboardService untouched.
 */
class HomeService
{
    public function __construct(
        private FinanceService $finance,
        private JournalService $journal,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function home(): array
    {
        $today = today()->toDateString();

        $checklist = ChecklistItem::query()
            ->whereDate('for_date', $today)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $financeSummary = $this->finance->summary();
        $journalOverview = $this->journal->overview();

        $quotes = config('quotes.quotes', []);
        $quote = ! empty($quotes) ? $quotes[now()->dayOfYear % count($quotes)] : null;
        $setting = Setting::first();

        return [
            'greeting' => $this->greeting(),
            'owner_name' => $setting?->owner_name ?: config('crm.owner_name', 'Freelancer'),
            'quote' => $quote,
            'today_label' => now()->translatedFormat('l, d F Y'),

            // Card 1 — Focus today
            'checklist' => $checklist->take(6),
            'checklist_done' => $checklist->where('is_done', true)->count(),
            'checklist_total' => $checklist->count(),

            // Card 2 — Cashflow
            'total_balance' => $financeSummary['total_balance'],
            'month_income' => $financeSummary['month_income'],
            'month_expense' => $financeSummary['month_expense'],
            'cashflow' => $financeSummary['monthly_cashflow'],
            'wallets' => $financeSummary['wallets']->take(4),
            'recent_transactions' => $financeSummary['recent_transactions']->take(5),

            // Card 3 — Pinned dream / top goal
            'featured_goal' => $this->featuredGoal(),

            // Card 4 — Needs attention (the signature card)
            'alerts' => $this->alerts($financeSummary['budget_usage']),

            // Active Projects & Invoices
            'active_projects' => $this->activeProjects(),
            'upcoming_invoices' => $this->upcomingInvoices(),

            // Mini Calendar & Agenda
            'calendar_data' => $this->calendarData(),

            // Card 5 — Pipeline
            'pipeline' => $this->pipeline(),

            // Card 6 — Mood & journal
            'mood_trend' => $journalOverview['mood_trend'],
            'journal_streak' => $journalOverview['current_streak'],
            'journal_month' => $journalOverview['month_entries'],
            'recent_journal_entries' => $journalOverview['recent_entries']->take(3),

            // Command centre utilities
            'recent_vault_files' => VaultFile::query()
                ->with(['client', 'project'])
                ->latest()
                ->limit(5)
                ->get(),
            'workspace_boards' => Board::query()
                ->withCount('cards')
                ->latest()
                ->limit(4)
                ->get(),
        ];
    }

    private function greeting(): string
    {
        $hour = (int) now()->format('H');

        return match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 19 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }

    private function featuredGoal(): ?Goal
    {
        $goal = Goal::query()
            ->where('is_archived', false)
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->first();

        if ($goal) {
            $goal->live_amount = $this->finance->goalCurrentAmount($goal);
            $goal->live_percent = $goal->percent($goal->live_amount);
        }

        return $goal;
    }

    /**
     * Cross-module attention list, severity-ranked: red > orange > yellow.
     *
     * @param  Collection<int, array<string, mixed>>  $budgetUsage
     * @return Collection<int, array{level: string, icon: string, text: string, href: string}>
     */
    private function alerts(Collection $budgetUsage): Collection
    {
        $alerts = collect();

        // Red — overdue invoices
        $overdue = Invoice::query()
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereDate('due_date', '<', today())
            ->get(['id', 'total', 'amount_paid']);

        if ($overdue->isNotEmpty()) {
            $amount = $overdue->sum(fn (Invoice $i) => (float) $i->total - (float) $i->amount_paid);
            $alerts->push([
                'level' => 'red',
                'icon' => 'document',
                'text' => $overdue->count().' invoice lewat jatuh tempo · IDR '.number_format($amount, 0, ',', '.'),
                'href' => route('invoices.index'),
            ]);
        }

        // Orange — tasks due today
        $dueToday = Task::query()
            ->whereDate('due_at', today())
            ->whereNotIn('status', ['done', 'cancelled'])
            ->count();

        if ($dueToday > 0) {
            $alerts->push([
                'level' => 'orange',
                'icon' => 'check',
                'text' => $dueToday.' task jatuh tempo hari ini',
                'href' => route('tasks.index', ['filter' => 'today']),
            ]);
        }

        // Orange — overspent budgets
        $overBudget = $budgetUsage->filter(fn ($row) => ($row['percent'] ?? 0) >= 100);
        if ($overBudget->isNotEmpty()) {
            $alerts->push([
                'level' => 'orange',
                'icon' => 'wallet',
                'text' => $overBudget->count().' budget terlampaui bulan ini',
                'href' => route('budgets.index'),
            ]);
        }

        // Yellow — stale deals (open, no activity for 14+ days)
        $staleDeals = Deal::query()
            ->where('status', 'open')
            ->where('updated_at', '<', now()->subDays(14))
            ->count();

        if ($staleDeals > 0) {
            $alerts->push([
                'level' => 'yellow',
                'icon' => 'pipeline',
                'text' => $staleDeals.' deal tanpa aktivitas 14+ hari',
                'href' => route('deals.index'),
            ]);
        }

        // Yellow — no journal entry today
        $journaledToday = JournalEntry::query()->whereDate('entry_date', today())->exists();
        if (! $journaledToday) {
            $alerts->push([
                'level' => 'yellow',
                'icon' => 'book',
                'text' => 'Belum ada catatan jurnal hari ini',
                'href' => route('journal.create'),
            ]);
        }

        $rank = ['red' => 0, 'orange' => 1, 'yellow' => 2];

        return $alerts->sortBy(fn ($a) => $rank[$a['level']])->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function pipeline(): array
    {
        $open = Deal::query()->where('status', 'open')->get(['stage', 'value', 'probability']);

        return [
            'by_stage' => $open->groupBy('stage')->map->count(),
            'weighted' => $open->sum(fn (Deal $d) => (float) $d->value * ((int) $d->probability / 100)),
            'count' => $open->count(),
        ];
    }

    private function activeProjects(): Collection
    {
        return Project::query()
            ->with(['client'])
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done')])
            ->whereIn('status', ['in_progress', 'planned', 'waiting_client', 'revision'])
            ->orderByRaw('deadline IS NULL, deadline ASC')
            ->limit(4)
            ->get();
    }

    private function upcomingInvoices(): Collection
    {
        return Invoice::query()
            ->with(['client'])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereDate('due_date', '>=', today())
            ->orderBy('due_date')
            ->limit(4)
            ->get();
    }

    private function calendarData(): array
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $journalDays = JournalEntry::query()
            ->whereBetween('entry_date', [$startOfMonth, $endOfMonth])
            ->pluck('entry_date')
            ->map(fn ($d) => $d->day)
            ->unique()
            ->values()
            ->toArray();

        $taskDays = Task::query()
            ->whereBetween('due_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['done', 'cancelled'])
            ->pluck('due_at')
            ->map(fn ($d) => $d->day)
            ->unique()
            ->values()
            ->toArray();

        $projectDays = Project::query()
            ->whereBetween('deadline', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['in_progress', 'planned', 'waiting_client', 'revision'])
            ->pluck('deadline')
            ->map(fn ($d) => $d->day)
            ->unique()
            ->values()
            ->toArray();

        $invoiceDays = Invoice::query()
            ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->pluck('due_date')
            ->map(fn ($d) => $d->day)
            ->unique()
            ->values()
            ->toArray();

        // Collect agenda items for current month list
        $agendas = collect();

        Project::query()
            ->whereBetween('deadline', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['in_progress', 'planned', 'waiting_client', 'revision'])
            ->get()
            ->each(function (Project $p) use ($agendas) {
                $agendas->push([
                    'type' => 'project',
                    'date' => $p->deadline,
                    'title' => 'Deadline: '.$p->title,
                    'badge' => 'Project',
                    'href' => route('projects.show', $p),
                ]);
            });

        Task::query()
            ->whereBetween('due_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['done', 'cancelled'])
            ->get()
            ->each(function (Task $t) use ($agendas) {
                $agendas->push([
                    'type' => 'task',
                    'date' => $t->due_at,
                    'title' => 'Task: '.$t->title,
                    'badge' => 'Task',
                    'href' => route('tasks.index'),
                ]);
            });

        Invoice::query()
            ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->get()
            ->each(function (Invoice $i) use ($agendas) {
                $agendas->push([
                    'type' => 'invoice',
                    'date' => $i->due_date,
                    'title' => 'Invoice: '.$i->invoice_number,
                    'badge' => 'Invoice',
                    'href' => route('invoices.show', $i),
                ]);
            });

        return [
            'year' => $now->year,
            'month' => $now->month,
            'month_name' => $now->translatedFormat('F Y'),
            'days_in_month' => $now->daysInMonth,
            'first_day_of_week' => $startOfMonth->dayOfWeek, // 0 = Sunday, 1 = Monday...
            'today_day' => $now->day,
            'journal_days' => $journalDays,
            'task_days' => $taskDays,
            'project_days' => $projectDays,
            'invoice_days' => $invoiceDays,
            'agendas' => $agendas->sortBy('date')->values()->take(5),
        ];
    }
}

