<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Wallet;
use App\Services\FinanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WalletController extends Controller
{
    use ValidatesCrmRelations;

    public function index(FinanceService $finance): View
    {
        return view('wallets.index', [
            'wallets' => $finance->walletsWithBalances(),
        ]);
    }

    public function create(): View
    {
        return view('wallets.form', [
            'wallet' => new Wallet(['type' => 'cash', 'currency' => 'IDR', 'opening_balance' => 0]),
            'types' => Wallet::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Wallet::create($this->validated($request));

        return redirect()->route('wallets.index')->with('status', 'Wallet created.');
    }

    public function edit(Wallet $wallet): View
    {
        $this->authorizeOwner($wallet);

        return view('wallets.form', [
            'wallet' => $wallet,
            'types' => Wallet::TYPES,
        ]);
    }

    public function update(Request $request, Wallet $wallet): RedirectResponse
    {
        $this->authorizeOwner($wallet);
        $wallet->update($this->validated($request));

        return redirect()->route('wallets.index')->with('status', 'Wallet updated.');
    }

    public function destroy(Wallet $wallet): RedirectResponse
    {
        $this->authorizeOwner($wallet);

        if ($wallet->transactions()->exists()) {
            $wallet->update(['is_archived' => true]);

            return redirect()->route('wallets.index')->with('status', 'Wallet has transactions, archived instead of deleted.');
        }

        $wallet->delete();

        return redirect()->route('wallets.index')->with('status', 'Wallet deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Wallet::TYPES))],
            'currency' => ['required', 'string', 'size:3'],
            'opening_balance' => ['nullable', 'numeric'],
            'is_archived' => ['sometimes', 'boolean'],
        ]);
    }
}
