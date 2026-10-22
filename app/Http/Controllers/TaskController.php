<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use App\Services\ProjectProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    use ValidatesCrmRelations;

    public function index(Request $request): View
    {
        $filter = $request->input('filter', 'open');
        $tasks = Task::query()
            ->with(['client', 'deal', 'project', 'invoice'])
            ->when($filter === 'today', fn ($query) => $query->whereDate('due_at', today())->whereNotIn('status', ['done', 'cancelled']))
            ->when($filter === 'upcoming', fn ($query) => $query->where('due_at', '>', now()->endOfDay())->whereNotIn('status', ['done', 'cancelled']))
            ->when($filter === 'overdue', fn ($query) => $query->where('due_at', '<', now()->startOfDay())->whereNotIn('status', ['done', 'cancelled']))
            ->when($filter === 'completed', fn ($query) => $query->where('status', 'done'))
            ->when($filter === 'open', fn ($query) => $query->whereNotIn('status', ['done', 'cancelled']))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->priority))
            ->orderByRaw('due_at is null, due_at asc')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tasks.index', $this->formData($request) + [
            'tasks' => $tasks,
            'filter' => $filter,
        ]);
    }

    public function create(Request $request): View
    {
        return view('tasks.form', $this->formData($request) + [
            'task' => new Task([
                'client_id' => $request->integer('client_id') ?: null,
                'deal_id' => $request->integer('deal_id') ?: null,
                'project_id' => $request->integer('project_id') ?: null,
                'invoice_id' => $request->integer('invoice_id') ?: null,
                'type' => 'follow_up',
                'status' => 'pending',
                'priority' => 'medium',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $task = Task::create($this->normalizedData($request));

        $this->syncProjectProgress($task);

        if ($request->input('context') === 'project' && $task->project_id) {
            return redirect()->route('projects.show', $task->project_id)->with('status', 'Task created.');
        }

        return redirect()->route('tasks.index')->with('status', 'Task created.');
    }

    public function edit(Request $request, Task $task): View
    {
        $this->authorizeOwner($task);

        return view('tasks.form', $this->formData($request) + [
            'task' => $task,
        ]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeOwner($task);
        $oldProjectId = $task->project_id;
        $task->update($this->normalizedData($request, $task));

        $this->syncProjectProgress($task);
        if ($oldProjectId && $oldProjectId !== $task->project_id) {
            $this->syncProjectProgress(Project::find($oldProjectId));
        }

        return redirect()->route('tasks.index')->with('status', 'Task updated.');
    }

    public function complete(Task $task): RedirectResponse
    {
        $this->authorizeOwner($task);
        $task->update(['status' => 'done', 'completed_at' => now()]);
        $this->syncProjectProgress($task);

        return back()->with('status', 'Task completed.');
    }

    public function reopen(Task $task): RedirectResponse
    {
        $this->authorizeOwner($task);
        $task->update(['status' => 'pending', 'completed_at' => null]);
        $this->syncProjectProgress($task);

        return back()->with('status', 'Task reopened.');
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeOwner($task);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Task::STATUSES))],
        ]);

        $task->update([
            'status' => $data['status'],
            'completed_at' => $data['status'] === 'done' ? ($task->completed_at ?? now()) : null,
        ]);

        $this->syncProjectProgress($task);

        return back()->with('status', 'Task status updated.');
    }

    public function reschedule(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeOwner($task);

        $data = $request->validate([
            'preset' => ['required', Rule::in(['today', 'tomorrow', 'next_week'])],
        ]);

        $dueAt = match ($data['preset']) {
            'today' => now()->setHour(17)->setMinute(0),
            'tomorrow' => now()->addDay()->setHour(9)->setMinute(0),
            'next_week' => now()->addWeek()->startOfWeek()->setHour(9),
        };

        $task->update([
            'due_at' => $dueAt,
            'status' => $task->status === 'done' ? 'pending' : $task->status,
            'completed_at' => null,
        ]);
        $this->syncProjectProgress($task);

        return back()->with('status', 'Task rescheduled.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorizeOwner($task);
        $projectId = $task->project_id;
        $task->delete();
        $this->syncProjectProgress(Project::find($projectId));

        return redirect()->route('tasks.index')->with('status', 'Task deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['nullable', $this->ownedExists('clients')],
            'deal_id' => ['nullable', $this->ownedExists('deals')],
            'project_id' => ['nullable', $this->ownedExists('projects')],
            'invoice_id' => ['nullable', $this->ownedExists('invoices')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::in(array_keys(Task::TYPES))],
            'status' => ['required', Rule::in(array_keys(Task::STATUSES))],
            'priority' => ['required', Rule::in(array_keys(Task::PRIORITIES))],
            'due_at' => ['nullable', 'date'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizedData(Request $request, ?Task $task = null): array
    {
        $data = $this->validated($request);

        if ($data['status'] === 'done') {
            $data['completed_at'] = $task?->completed_at ?? now();
        } else {
            $data['completed_at'] = null;
        }

        return $data;
    }

    private function syncProjectProgress(Task|Project|null $source): void
    {
        $project = $source instanceof Task ? $source->project : $source;

        if ($project) {
            app(ProjectProgressService::class)->sync($project);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request): array
    {
        return [
            'clients' => $this->relationOptions(Client::class, 'client_id')->orderBy('name')->get(),
            'deals' => $this->relationOptions(Deal::class, 'deal_id')->orderBy('title')->get(),
            'projects' => $this->relationOptions(Project::class, 'project_id')->orderBy('title')->get(),
            'invoices' => $this->relationOptions(Invoice::class, 'invoice_id')->orderByDesc('issue_date')->get(),
            'types' => Task::TYPES,
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ];
    }
}
