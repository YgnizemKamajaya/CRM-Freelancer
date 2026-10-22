<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceService
{
    /**
     * Wallets with balance pre-computed in bulk (opening balance + income - expense),
     * avoiding an N+1 query per wallet.
     *
     * @return Collection<int, Wallet>
     */
    public function walletsWithBalances(): Collection
    {
        return Wallet::query()
            ->withSum(['transactions as income_sum' => fn ($query) => $query->where('type', 'income')], 'amount')
            ->withSum(['transactions as expense_sum' => fn ($query) => $query->where('type', 'expense')], 'amount')
            ->orderBy('name')
            ->get()
            ->each(function (Wallet $wallet) {
                $wallet->balance = (float) $wallet->opening_balance
                    + (float) $wallet->income_sum
                    - (float) $wallet->expense_sum;
            });
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $wallets = $this->walletsWithBalances();

        $thisMonth = Transaction::query()
            ->whereBetween('occurred_on', [now()->startOfMonth(), now()->endOfMonth()])
            ->get(['type', 'amount']);

        return [
            'wallets' => $wallets,
            'total_balance' => $wallets->sum('balance'),
            'month_income' => (float) $thisMonth->where('type', 'income')->sum(fn (Transaction $t) => (float) $t->amount),
            'month_expense' => (float) $thisMonth->where('type', 'expense')->sum(fn (Transaction $t) => (float) $t->amount),
            'monthly_cashflow' => $this->monthlyCashflow(),
            'category_breakdown' => $this->categoryBreakdown(),
            'recent_transactions' => Transaction::with('wallet')->latest('occurred_on')->latest()->limit(8)->get(),
            'budget_usage' => $this->budgetUsage(),
        ];
    }

    /**
     * @return Collection<int, array{label: string, income: float, expense: float}>
     */
    public function monthlyCashflow(int $months = 6): Collection
    {
        $start = now()->subMonths($months - 1)->startOfMonth();

        $rows = Transaction::query()
            ->where('occurred_on', '>=', $start)
            ->get(['type', 'amount', 'occurred_on']);

        return collect(range(0, $months - 1))->map(function (int $offset) use ($start, $rows) {
            $month = $start->copy()->addMonths($offset);
            $inMonth = $rows->filter(fn (Transaction $t) => $t->occurred_on->isSameMonth($month));

            return [
                'label' => $month->format('M'),
                'income' => (float) $inMonth->where('type', 'income')->sum(fn (Transaction $t) => (float) $t->amount),
                'expense' => (float) $inMonth->where('type', 'expense')->sum(fn (Transaction $t) => (float) $t->amount),
            ];
        });
    }

    /**
     * @return Collection<int, array{category: string, total: float}>
     */
    public function categoryBreakdown(): Collection
    {
        return Transaction::query()
            ->where('type', 'expense')
            ->whereBetween('occurred_on', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw("coalesce(nullif(category, ''), 'Uncategorized') as category, sum(amount) as total")
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['category' => $row->category, 'total' => (float) $row->total]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function budgetUsage(): Collection
    {
        $spent = Transaction::query()
            ->where('type', 'expense')
            ->whereBetween('occurred_on', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return Budget::query()->orderBy('category')->get()->map(function (Budget $budget) use ($spent) {
            $spentAmount = (float) ($spent[$budget->category] ?? 0);
            $limit = (float) $budget->monthly_limit;

            return [
                'budget' => $budget,
                'spent' => $spentAmount,
                'percent' => $limit > 0 ? (int) min(100, round(($spentAmount / $limit) * 100)) : 0,
            ];
        });
    }

    /**
     * Live amount for an auto-tracked financial goal:
     * a specific wallet's balance, or total balance across all wallets.
     */
    public function goalCurrentAmount(Goal $goal): float
    {
        if (! $goal->auto_track || $goal->type !== 'financial') {
            return (float) $goal->current_amount;
        }

        $wallets = $this->walletsWithBalances();

        if ($goal->wallet_id) {
            return (float) ($wallets->firstWhere('id', $goal->wallet_id)->balance ?? 0);
        }

        return (float) $wallets->sum('balance');
    }

    /** Reconcile one managed income entry; manually linked income is preserved. */
    public function recordInvoicePayment(Invoice $invoice): ?Transaction
    {
        return DB::transaction(function () use ($invoice) {
            $invoice = Invoice::withTrashed()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $payment = Transaction::where('invoice_payment_id', $invoice->id)->first();
            $manualIncome = (float) Transaction::where('invoice_id', $invoice->id)
                ->whereNull('invoice_payment_id')->where('type', 'income')
                ->where('currency', $invoice->currency)->sum('amount');
            $amount = $invoice->trashed() || $invoice->status === 'cancelled'
                ? 0 : round(max(0, (float) $invoice->amount_paid - $manualIncome), 2);

            if ($amount <= 0) {
                $payment?->delete();

                return null;
            }

            $wallet = $payment?->wallet;
            if (! $wallet || $wallet->currency !== $invoice->currency) {
                $wallet = Wallet::where('is_archived', false)->where('currency', $invoice->currency)->orderBy('id')->first();
            }
            if (! $wallet) {
                if ($payment) {
                    throw ValidationException::withMessages(['currency' => 'Buat wallet dengan mata uang invoice sebelum mengganti mata uang pembayaran.']);
                }

                return null;
            }

            return Transaction::updateOrCreate(['invoice_payment_id' => $invoice->id], [
                'wallet_id' => $wallet->id,
                'invoice_id' => $invoice->id,
                'type' => 'income',
                'category' => 'Client work',
                'amount' => $amount,
                'currency' => $invoice->currency,
                'note' => 'Auto dari invoice '.$invoice->invoice_number,
                'occurred_on' => ($payment?->occurred_on ?? $invoice->paid_at ?? today())->toDateString(),
                'project_id' => $invoice->project_id,
            ]);
        });
    }
}
