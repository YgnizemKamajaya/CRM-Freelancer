<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BudgetController extends Controller
{
    use ValidatesCrmRelations;

    public function index(): View
    {
        return view('budgets.index', [
            'budgets' => Budget::orderBy('category')->get(),
        ]);
    }

    public function create(): View
    {
        return view('budgets.form', [
            'budget' => new Budget(['currency' => 'IDR']),
            'suggestedCategories' => Transaction::SUGGESTED_CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Budget::create($this->validated($request));

        return redirect()->route('budgets.index')->with('status', 'Budget created.');
    }

    public function edit(Budget $budget): View
    {
        $this->authorizeOwner($budget);

        return view('budgets.form', [
            'budget' => $budget,
            'suggestedCategories' => Transaction::SUGGESTED_CATEGORIES,
        ]);
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $this->authorizeOwner($budget);
        $budget->update($this->validated($request, $budget));

        return redirect()->route('budgets.index')->with('status', 'Budget updated.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorizeOwner($budget);
        $budget->delete();

        return redirect()->route('budgets.index')->with('status', 'Budget deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Budget $budget = null): array
    {
        return $request->validate([
            'category' => ['required', 'string', 'max:100', Rule::unique('budgets', 'category')->ignore($budget)],
            'monthly_limit' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'size:3'],
        ]);
    }
}
