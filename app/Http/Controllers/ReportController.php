<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : now()->startOfMonth();
        $to = isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : now()->endOfDay();
        $userId = auth()->id();
        $paidInvoices = Invoice::ownedBy($userId)->where('status', 'paid')->whereBetween('paid_at', [$from, $to])->get();
        $openDeals = Deal::ownedBy($userId)->where('status', 'open')->whereBetween('created_at', [$from, $to])->get();
        $invoices = Invoice::ownedBy($userId)->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])->get();

        return view('reports.index', [
            'cards' => [
                'Revenue in range' => $paidInvoices->sum('amount_paid'),
                'Paid invoices in range' => $paidInvoices->count(),
                'Unpaid invoice total' => $invoices->filter(fn (Invoice $invoice) => ! in_array($invoice->status, ['paid', 'cancelled'], true))->sum(fn (Invoice $invoice) => $invoice->balance),
                'Open pipeline value' => $openDeals->sum('value'),
            ],
            'dealStageTotals' => $openDeals->groupBy('stage')->map->count(),
            'invoiceStatusTotals' => $invoices->groupBy('status')->map->count(),
            'projectStatusTotals' => Project::ownedBy($userId)->whereBetween('created_at', [$from, $to])->get()->groupBy('status')->map->count(),
            'taskTotals' => Task::ownedBy($userId)->whereBetween('due_at', [$from, $to])->get()->groupBy('status')->map->count(),
            'topClients' => $this->topClients($userId, $from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    private function topClients(int $userId, Carbon $from, Carbon $to): Collection
    {
        return Client::ownedBy($userId)
            ->withSum([
                'invoices as paid_total' => fn ($query) => $query
                    ->where('status', 'paid')
                    ->whereBetween('paid_at', [$from, $to]),
            ], 'amount_paid')
            ->orderByDesc('paid_total')
            ->limit(5)
            ->get();
    }
}
