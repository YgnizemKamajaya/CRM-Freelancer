<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $openDeals = Deal::where('status', 'open')->get(['value', 'probability']);
        $unpaidInvoices = Invoice::query()
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->get(['total', 'amount_paid', 'due_date']);

        return [
            'metrics' => [
                'total_clients' => Client::whereNull('archived_at')->count(),
                'active_deals' => $openDeals->count(),
                'active_projects' => Project::whereNotIn('status', ['completed', 'cancelled'])->count(),
                'pipeline_value' => $openDeals->sum(fn (Deal $deal) => (float) $deal->value),
                'expected_revenue' => $openDeals->sum(fn (Deal $deal) => $deal->weighted_value),
                'unpaid_invoice_amount' => $unpaidInvoices->sum(fn (Invoice $invoice) => $invoice->balance),
                'overdue_invoice_amount' => $unpaidInvoices
                    ->filter(fn (Invoice $invoice) => $invoice->due_date?->lt(today()))
                    ->sum(fn (Invoice $invoice) => $invoice->balance),
                'tasks_due_today' => Task::query()
                    ->whereDate('due_at', Carbon::today())
                    ->whereNotIn('status', ['done', 'cancelled'])
                    ->count(),
            ],
            'today_tasks' => $this->todayTasks(),
            'overdue_tasks' => $this->overdueTasks(),
            'active_deals_by_stage' => $this->dealsByStage(),
            'recent_clients' => Client::latest()->limit(5)->get(),
            'recent_activities' => Activity::with('client')->latest('activity_at')->latest()->limit(8)->get(),
            'unpaid_invoices' => Invoice::with('client')
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->orderByRaw('due_date is null, due_date asc')
                ->limit(5)
                ->get(),
            'monthly_revenue' => $this->monthlyRevenue(),
        ];
    }

    public function todayTasks(): Collection
    {
        return Task::query()
            ->with(['client', 'project'])
            ->whereDate('due_at', today())
            ->whereNotIn('status', ['done', 'cancelled'])
            ->orderBy('due_at')
            ->limit(8)
            ->get();
    }

    public function overdueTasks(): Collection
    {
        return Task::query()
            ->with(['client', 'project'])
            ->where('due_at', '<', now()->startOfDay())
            ->whereNotIn('status', ['done', 'cancelled'])
            ->orderBy('due_at')
            ->limit(8)
            ->get();
    }

    public function dealsByStage(): Collection
    {
        return Deal::query()
            ->where('status', 'open')
            ->selectRaw('stage, count(*) as total, coalesce(sum(value), 0) as value')
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');
    }

    public function monthlyRevenue(): Collection
    {
        return Invoice::query()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['paid_at', 'amount_paid'])
            ->groupBy(fn (Invoice $invoice) => $invoice->paid_at->format('M Y'))
            ->map(fn (Collection $invoices) => $invoices->sum(fn (Invoice $invoice) => (float) $invoice->amount_paid));
    }
}
