<?php

namespace App\Livewire;

use App\Models\Board;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Project;
use App\Models\Task;
use App\Models\VaultFile;
use Livewire\Component;

class QuickCapture extends Component
{
    public string $query = '';

    public function updatedQuery(): void
    {
        // Trim stray leading whitespace so obvious matches aren't hidden.
        $this->query = ltrim($this->query);
    }

    public function render()
    {
        return view('livewire.quick-capture', [
            'groups' => $this->resultGroups(),
        ]);
    }

    /**
     * @return array<int, array{label: string, items: array<int, array<string, mixed>>}>
     */
    protected function resultGroups(): array
    {
        $term = trim($this->query);

        if ($term === '') {
            $groups = [
                ['label' => 'Buat baru', 'items' => $this->createShortcuts()],
                ['label' => 'Navigasi', 'items' => $this->navigationShortcuts()],
            ];
        } else {
            $groups = [
                ['label' => 'Clients', 'items' => $this->searchClients($term)],
                ['label' => 'Deals', 'items' => $this->searchDeals($term)],
                ['label' => 'Projects', 'items' => $this->searchProjects($term)],
                ['label' => 'Invoices', 'items' => $this->searchInvoices($term)],
                ['label' => 'Tasks', 'items' => $this->searchTasks($term)],
                ['label' => 'Journal', 'items' => $this->searchJournal($term)],
                ['label' => 'Boards', 'items' => $this->searchWorkspace($term, Board::class, 'name', 'workspace.show', 'board')],
                ['label' => 'Goals', 'items' => $this->searchWorkspace($term, Goal::class, 'title', 'goals.edit', 'chart')],
                ['label' => 'Vault', 'items' => $this->searchWorkspace($term, VaultFile::class, 'original_name', 'vault.download', 'document')],
                ['label' => 'Navigasi', 'items' => $this->matchingShortcuts($term)],
            ];
        }

        return array_values(array_filter($groups, fn (array $group) => $group['items'] !== []));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function createShortcuts(): array
    {
        return [
            ['label' => 'Client baru', 'href' => route('clients.create'), 'icon' => 'users'],
            ['label' => 'Deal baru', 'href' => route('deals.create'), 'icon' => 'pipeline'],
            ['label' => 'Project baru', 'href' => route('projects.create'), 'icon' => 'briefcase'],
            ['label' => 'Task baru', 'href' => route('tasks.create'), 'icon' => 'check'],
            ['label' => 'Invoice baru', 'href' => route('invoices.create'), 'icon' => 'document'],
            ['label' => 'Catat transaksi', 'href' => route('transactions.create'), 'icon' => 'wallet'],
            ['label' => 'Entri jurnal', 'href' => route('journal.create'), 'icon' => 'book'],
            ['label' => 'Board workspace', 'href' => route('workspace.index'), 'icon' => 'board'],
            ['label' => 'Buka hari ini', 'href' => route('today.index'), 'icon' => 'check'],
            ['label' => 'Goal baru', 'href' => route('goals.create'), 'icon' => 'chart'],
            ['label' => 'Upload file', 'href' => route('vault.index'), 'icon' => 'document'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function navigationShortcuts(): array
    {
        return $this->shortcutMap();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function matchingShortcuts(string $term): array
    {
        return array_values(array_filter(
            $this->shortcutMap(),
            fn (array $item) => str_contains(strtolower($item['label']), strtolower($term))
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function shortcutMap(): array
    {
        return [
            ['label' => 'Ringkasan CRM', 'href' => route('dashboard.crm'), 'icon' => 'chart'],
            ['label' => 'Dashboard', 'href' => route('dashboard'), 'icon' => 'home'],
            ['label' => 'Hari ini', 'href' => route('today.index'), 'icon' => 'check'],
            ['label' => 'Focus', 'href' => route('focus.index'), 'icon' => 'clock'],
            ['label' => 'Goals', 'href' => route('goals.index'), 'icon' => 'chart'],
            ['label' => 'Clients', 'href' => route('clients.index'), 'icon' => 'users'],
            ['label' => 'Deals', 'href' => route('deals.index'), 'icon' => 'pipeline'],
            ['label' => 'Projects', 'href' => route('projects.index'), 'icon' => 'briefcase'],
            ['label' => 'Tasks', 'href' => route('tasks.index'), 'icon' => 'check'],
            ['label' => 'Invoices', 'href' => route('invoices.index'), 'icon' => 'document'],
            ['label' => 'Reports', 'href' => route('reports.index'), 'icon' => 'chart'],
            ['label' => 'Finance', 'href' => route('finance.index'), 'icon' => 'wallet'],
            ['label' => 'Workspace', 'href' => route('workspace.index'), 'icon' => 'board'],
            ['label' => 'Journal', 'href' => route('journal.index'), 'icon' => 'book'],
            ['label' => 'Vault', 'href' => route('vault.index'), 'icon' => 'document'],
            ['label' => 'Settings', 'href' => route('settings.edit'), 'icon' => 'cog'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function searchClients(string $term): array
    {
        return Client::query()
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('company_name', 'like', "%{$term}%"))
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name'])
            ->map(fn (Client $client) => [
                'label' => $client->name,
                'href' => route('clients.show', $client),
                'icon' => 'users',
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function searchDeals(string $term): array
    {
        return Deal::query()
            ->where('title', 'like', "%{$term}%")
            ->orderBy('title')
            ->limit(5)
            ->get(['id', 'title'])
            ->map(fn (Deal $deal) => [
                'label' => $deal->title,
                'href' => route('deals.show', $deal),
                'icon' => 'pipeline',
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function searchProjects(string $term): array
    {
        return Project::query()
            ->where('title', 'like', "%{$term}%")
            ->orderBy('title')
            ->limit(5)
            ->get(['id', 'title'])
            ->map(fn (Project $project) => [
                'label' => $project->title,
                'href' => route('projects.show', $project),
                'icon' => 'briefcase',
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function searchInvoices(string $term): array
    {
        return Invoice::query()
            ->where('invoice_number', 'like', "%{$term}%")
            ->orderBy('invoice_number')
            ->limit(5)
            ->get(['id', 'invoice_number'])
            ->map(fn (Invoice $invoice) => [
                'label' => $invoice->invoice_number,
                'href' => route('invoices.show', $invoice),
                'icon' => 'document',
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function searchTasks(string $term): array
    {
        return Task::query()
            ->where('title', 'like', "%{$term}%")
            ->orderBy('title')
            ->limit(5)
            ->get(['id', 'title'])
            ->map(fn (Task $task) => [
                'label' => $task->title,
                'href' => route('tasks.edit', $task),
                'icon' => 'check',
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function searchJournal(string $term): array
    {
        return JournalEntry::query()
            ->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('body', 'like', "%{$term}%"))
            ->orderByDesc('entry_date')
            ->limit(5)
            ->get(['id', 'title', 'entry_date'])
            ->map(fn (JournalEntry $entry) => [
                'label' => $entry->title ?: $entry->entry_date->format('d M Y'),
                'href' => route('journal.show', $entry),
                'icon' => 'book',
            ])
            ->all();
    }

    protected function searchWorkspace(string $term, string $model, string $column, string $route, string $icon): array
    {
        return $model::query()->where($column, 'like', '%'.$term.'%')->orderBy($column)->limit(5)->get()
            ->map(fn ($record) => ['label' => $record->{$column}, 'href' => route($route, $record), 'icon' => $icon])->all();
    }
}
