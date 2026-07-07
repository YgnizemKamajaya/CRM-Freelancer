<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Client;
use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    use ValidatesCrmRelations;

    public function index(Request $request): View
    {
        $clients = Client::query()
            ->withCount(['deals', 'projects', 'invoices'])
            ->search($request->string('search')->toString())
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('tag'), fn ($query) => $query->whereJsonContains('tags', $request->string('tag')->toString()))
            ->when($request->input('sort') === 'revenue', fn ($query) => $query->withSum('invoices as revenue_sum', 'total')->orderByDesc('revenue_sum'))
            ->when($request->input('sort') === 'activity', fn ($query) => $query->orderByDesc('last_contacted_at'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('clients.index', [
            'clients' => $clients,
            'statuses' => Client::STATUSES,
            'tags' => Client::query()
                ->get('tags')
                ->pluck('tags')
                ->flatten()
                ->filter()
                ->unique()
                ->sort()
                ->values(),
        ]);
    }

    public function create(): View
    {
        return view('clients.form', [
            'client' => new Client(['status' => 'lead']),
            'statuses' => Client::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $client = Client::create($this->validated($request));

        return redirect()->route('clients.show', $client)->with('status', 'Client created.');
    }

    public function show(Client $client): View
    {
        $this->authorizeOwner($client);

        $client->load([
            'deals' => fn ($query) => $query->latest()->limit(8),
            'projects' => fn ($query) => $query->latest()->limit(8),
            'tasks' => fn ($query) => $query->latest('due_at')->limit(8),
            'invoices' => fn ($query) => $query->latest()->limit(8),
            'activities' => fn ($query) => $query->latest('activity_at')->latest()->limit(12),
        ]);

        return view('clients.show', [
            'client' => $client,
            'activityTypes' => Activity::TYPES,
        ]);
    }

    public function edit(Client $client): View
    {
        $this->authorizeOwner($client);

        return view('clients.form', [
            'client' => $client,
            'statuses' => Client::STATUSES,
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeOwner($client);

        $client->update($this->validated($request));

        return redirect()->route('clients.show', $client)->with('status', 'Client updated.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorizeOwner($client);
        $client->forceFill(['archived_at' => now(), 'status' => 'archived'])->save();
        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Client archived.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'source' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(array_keys(Client::STATUSES))],
            'tags' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['tags'] = collect(explode(',', (string) ($data['tags'] ?? '')))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->values()
            ->all();

        return $data;
    }
}
