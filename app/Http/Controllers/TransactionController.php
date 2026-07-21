<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionController extends Controller
{
    use ValidatesCrmRelations;

    public function index(Request $request): View
    {
        $transactions = Transaction::query()
            ->with(['wallet', 'project'])
            ->search($request->string('search')->toString())
            ->when($request->filled('wallet_id'), fn ($query) => $query->where('wallet_id', $request->wallet_id))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->orderByDesc('occurred_on')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('transactions.index', [
            'transactions' => $transactions,
            'wallets' => Wallet::orderBy('name')->get(),
            'types' => Transaction::TYPES,
        ]);
    }

    public function create(Request $request): View
    {
        return view('transactions.form', $this->formData() + [
            'transaction' => new Transaction([
                'wallet_id' => $request->integer('wallet_id') ?: Wallet::query()->orderBy('id')->value('id'),
                'type' => $request->string('type')->toString() ?: 'expense',
                'occurred_on' => today(),
                'currency' => 'IDR',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Transaction::create($this->validated($request));

        return redirect()->route('transactions.index')->with('status', 'Transaction recorded.');
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorizeOwner($transaction);

        return view('transactions.form', $this->formData() + [
            'transaction' => $transaction,
        ]);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeOwner($transaction);
        $transaction->update($this->validated($request));

        return redirect()->route('transactions.index')->with('status', 'Transaction updated.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorizeOwner($transaction);
        $transaction->delete();

        return redirect()->route('transactions.index')->with('status', 'Transaction deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'wallet_id' => ['required', $this->ownedExists('wallets')],
            'type' => ['required', Rule::in(array_keys(Transaction::TYPES))],
            'category' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'size:3'],
            'note' => ['nullable', 'string', 'max:1000'],
            'occurred_on' => ['required', 'date'],
            'project_id' => ['nullable', $this->ownedExists('projects')],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'wallets' => Wallet::orderBy('name')->get(),
            'projects' => Project::with('client')->orderBy('title')->get(),
            'types' => Transaction::TYPES,
            'suggestedCategories' => Transaction::SUGGESTED_CATEGORIES,
        ];
    }
}
