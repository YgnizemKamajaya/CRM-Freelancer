<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Deal;
use App\Services\DealService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DealController extends Controller
{
    use ValidatesCrmRelations;

    public function index(Request $request): View
    {
        $deals = Deal::query()
            ->with('client')
            ->search($request->string('search')->toString())
            ->when($request->filled('stage'), fn ($query) => $query->where('stage', $request->stage))
            ->when($request->filled('source'), fn ($query) => $query->where('source', $request->source))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $pipeline = Deal::query()
            ->where('status', 'open')
            ->get()
            ->groupBy('stage');

        return view('deals.index', compact('deals', 'pipeline') + [
            'stages' => Deal::STAGES,
            'sources' => Deal::SOURCES,
        ]);
    }

    public function create(Request $request): View
    {
        return view('deals.form', [
            'deal' => new Deal([
                'client_id' => $request->integer('client_id') ?: null,
                'stage' => 'new_inquiry',
                'probability' => 10,
                'currency' => 'IDR',
            ]),
            'clients' => $this->relationOptions(Client::class, 'client_id')->orderBy('name')->get(),
            'stages' => Deal::STAGES,
            'sources' => Deal::SOURCES,
        ]);
    }

    public function store(Request $request, DealService $dealService): RedirectResponse
    {
        $deal = Deal::create($dealService->normalizeStage($this->validated($request)));

        return redirect()->route('deals.show', $deal)->with('status', 'Deal created.');
    }

    public function show(Deal $deal): View
    {
        $this->authorizeOwner($deal);
        $deal->load(['client', 'project', 'tasks', 'activities' => fn ($query) => $query->latest('activity_at')->latest()]);

        return view('deals.show', [
            'deal' => $deal,
            'activityTypes' => Activity::TYPES,
            'stages' => Deal::STAGES,
        ]);
    }

    public function edit(Request $request, Deal $deal): View
    {
        $this->authorizeOwner($deal);

        return view('deals.form', [
            'deal' => $deal,
            'clients' => $this->relationOptions(Client::class, 'client_id')->orderBy('name')->get(),
            'stages' => Deal::STAGES,
            'sources' => Deal::SOURCES,
        ]);
    }

    public function update(Request $request, Deal $deal, DealService $dealService): RedirectResponse
    {
        $this->authorizeOwner($deal);
        $deal->update($dealService->normalizeStage($this->validated($request)));

        return redirect()->route('deals.show', $deal)->with('status', 'Deal updated.');
    }

    public function updateStage(Request $request, Deal $deal, DealService $dealService): RedirectResponse
    {
        $this->authorizeOwner($deal);

        $data = $request->validate([
            'stage' => ['required', Rule::in(array_keys(Deal::STAGES))],
        ]);

        $deal->update($dealService->normalizeStage([
            'stage' => $data['stage'],
            'probability' => $deal->probability,
        ]));

        return back()->with('status', 'Deal stage updated.');
    }

    public function markWon(Deal $deal): RedirectResponse
    {
        $this->authorizeOwner($deal);
        $deal->update(['stage' => 'won', 'status' => 'won', 'probability' => 100, 'won_at' => now(), 'lost_at' => null]);

        return back()->with('status', 'Deal marked won.');
    }

    public function markLost(Request $request, Deal $deal): RedirectResponse
    {
        $this->authorizeOwner($deal);
        $data = $request->validate([
            'lost_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $deal->update([
            'stage' => 'lost',
            'status' => 'lost',
            'probability' => 0,
            'lost_at' => now(),
            'won_at' => null,
            'lost_reason' => $data['lost_reason'] ?? null,
        ]);

        return back()->with('status', 'Deal marked lost.');
    }

    public function convertToProject(Deal $deal, DealService $dealService): RedirectResponse
    {
        $this->authorizeOwner($deal);
        $project = $dealService->convertToProject($deal->load('project'));

        return redirect()->route('projects.show', $project)->with('status', 'Deal converted to project.');
    }

    public function destroy(Deal $deal): RedirectResponse
    {
        $this->authorizeOwner($deal);
        $deal->delete();

        return redirect()->route('deals.index')->with('status', 'Deal deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', $this->ownedExists('clients')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'stage' => ['required', Rule::in(array_keys(Deal::STAGES))],
            'probability' => ['required', 'integer', 'min:0', 'max:100'],
            'source' => ['nullable', Rule::in(array_keys(Deal::SOURCES))],
            'expected_close_date' => ['nullable', 'date'],
            'next_follow_up_at' => ['nullable', 'date'],
            'lost_reason' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
