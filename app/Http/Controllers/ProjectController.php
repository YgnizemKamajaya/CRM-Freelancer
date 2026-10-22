<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Project;
use App\Models\Task;
use App\Services\ProjectProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    use ValidatesCrmRelations;

    public function index(Request $request): View
    {
        $projects = Project::query()
            ->with(['client', 'deal'])
            ->withCount([
                'tasks as active_tasks_count' => fn ($query) => $query->where('status', '!=', 'cancelled'),
                'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
            ])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->client_id))
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->search.'%'))
            ->orderByRaw('deadline is null, deadline asc')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $projects->getCollection()->each(function (Project $project): void {
            $totalTasks = (int) $project->active_tasks_count;
            $doneTasks = (int) $project->completed_tasks_count;

            $project->setAttribute(
                'computed_progress',
                $totalTasks > 0 ? (int) round(($doneTasks / $totalTasks) * 100) : (int) $project->progress,
            );
        });

        return view('projects.index', [
            'projects' => $projects,
            'clients' => $this->relationOptions(Client::class, 'client_id')->orderBy('name')->get(),
            'statuses' => Project::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        return view('projects.form', [
            'project' => new Project([
                'client_id' => $request->integer('client_id') ?: null,
                'deal_id' => $request->integer('deal_id') ?: null,
                'status' => 'planned',
                'priority' => 'medium',
                'currency' => 'IDR',
                'progress' => 0,
            ]),
            'clients' => $this->relationOptions(Client::class, 'client_id')->orderBy('name')->get(),
            'deals' => $this->relationOptions(Deal::class, 'deal_id')->with('client')->orderBy('title')->get(),
            'statuses' => Project::STATUSES,
            'priorities' => Project::PRIORITIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $project = Project::create($this->validated($request));

        return redirect()->route('projects.show', $project)->with('status', 'Project created.');
    }

    public function show(Project $project): View
    {
        $this->authorizeOwner($project);
        $project->load([
            'client',
            'deal',
            'tasks' => fn ($query) => $query->orderByRaw("case status when 'in_progress' then 0 when 'pending' then 1 when 'done' then 2 else 3 end")->orderByRaw('due_at is null, due_at asc')->latest(),
            'invoices' => fn ($query) => $query->orderByRaw('due_date is null, due_date asc')->latest(),
            'activities' => fn ($query) => $query->latest('activity_at')->latest(),
        ])->loadCount([
            'tasks as active_tasks_count' => fn ($query) => $query->where('status', '!=', 'cancelled'),
            'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
            'tasks as overdue_tasks_count' => fn ($query) => $query->whereNotIn('status', ['done', 'cancelled'])->where('due_at', '<', now()->startOfDay()),
        ]);

        $timeline = $project->tasks->map(fn (Task $task) => [
            'kind' => 'task',
            'tone' => $task->status === 'done' ? 'green' : ($task->due_at?->lt(now()) && $task->status !== 'done' ? 'rose' : 'amber'),
            'title' => $task->title,
            'meta' => Task::STATUSES[$task->status].' task',
            'date' => $task->completed_at ?? $task->due_at ?? $task->created_at,
            'status' => $task->status,
        ])->concat($project->activities->map(fn (Activity $activity) => [
            'kind' => 'activity',
            'tone' => 'blue',
            'title' => $activity->body,
            'meta' => Activity::TYPES[$activity->type],
            'date' => $activity->activity_at ?? $activity->created_at,
            'status' => $activity->type,
        ]))->concat($project->invoices->map(fn ($invoice) => [
            'kind' => 'invoice',
            'tone' => in_array($invoice->status, ['paid'], true) ? 'green' : ($invoice->due_date?->lt(today()) && $invoice->status !== 'paid' ? 'rose' : 'amber'),
            'title' => $invoice->invoice_number,
            'meta' => ucfirst(str_replace('_', ' ', $invoice->status)).' invoice',
            'date' => $invoice->paid_at ?? $invoice->due_date ?? $invoice->issue_date,
            'status' => $invoice->status,
        ]))->sortByDesc('date')->values()->take(12);

        return view('projects.show', [
            'project' => $project,
            'activityTypes' => Activity::TYPES,
            'taskStatuses' => Task::STATUSES,
            'taskPriorities' => Task::PRIORITIES,
            'boardColumns' => $project->tasks->groupBy('status'),
            'timeline' => $timeline,
        ]);
    }

    public function edit(Request $request, Project $project): View
    {
        $this->authorizeOwner($project);

        return view('projects.form', [
            'project' => $project,
            'clients' => $this->relationOptions(Client::class, 'client_id')->orderBy('name')->get(),
            'deals' => $this->relationOptions(Deal::class, 'deal_id')->with('client')->orderBy('title')->get(),
            'statuses' => Project::STATUSES,
            'priorities' => Project::PRIORITIES,
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeOwner($project);
        $project->update($this->validated($request));

        return redirect()->route('projects.show', $project)->with('status', 'Project updated.');
    }

    public function markCompleted(Project $project): RedirectResponse
    {
        $this->authorizeOwner($project);
        $project->tasks()
            ->where('status', '!=', 'cancelled')
            ->update(['status' => 'done', 'completed_at' => now()]);
        app(ProjectProgressService::class)->sync($project);

        return back()->with('status', 'Project completed.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorizeOwner($project);
        $project->delete();

        return redirect()->route('projects.index')->with('status', 'Project deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', $this->ownedExists('clients')],
            'deal_id' => ['nullable', $this->ownedExists('deals')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(array_keys(Project::STATUSES))],
            'priority' => ['required', Rule::in(array_keys(Project::PRIORITIES))],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'start_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
        ]);
    }
}
