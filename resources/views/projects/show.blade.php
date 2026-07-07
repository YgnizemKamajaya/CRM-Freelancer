@php
    $money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.');
    $progress = (int) $project->progress;
    $totalTasks = (int) $project->active_tasks_count;
    $doneTasks = (int) $project->completed_tasks_count;
    $kanbanMeta = [
        'pending' => ['title' => 'Next', 'tone' => 'amber'],
        'in_progress' => ['title' => 'Doing', 'tone' => 'blue'],
        'done' => ['title' => 'Done', 'tone' => 'green'],
        'cancelled' => ['title' => 'Parked', 'tone' => 'gray'],
    ];
@endphp
<x-app-layout>
    <x-slot:title>{{ $project->title }}</x-slot:title>

    <div class="space-y-7">
        <div class="project-hero">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge :status="$project->status" />
                    <x-status-badge :status="$project->priority" />
                    @if ($project->deadline)
                        <span class="inline-flex min-h-8 items-center gap-1 rounded-lg border border-gray-200 px-3 text-xs font-semibold text-gray-600">
                            <x-icon name="calendar" class="h-4 w-4" />
                            {{ $project->deadline->format('d M Y') }}
                        </span>
                    @endif
                </div>
                <h2 class="mt-4 text-3xl font-semibold text-gray-900">{{ $project->title }}</h2>
                <p class="mt-2 text-sm text-gray-500">{{ $project->client->name }} · {{ $money($project->budget) }}</p>
            </div>

            <div class="project-progress-card" style="--progress: {{ $progress }}">
                <div class="project-progress-ring" aria-label="Project progress {{ $progress }}%">
                    <span>{{ $progress }}%</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $doneTasks }} / {{ $totalTasks }} tasks</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $project->overdue_tasks_count }} overdue</p>
                </div>
            </div>
        </div>

        <section class="grid gap-4 md:grid-cols-4">
            <x-stat-card label="Budget" :value="$money($project->budget)" icon="briefcase" />
            <x-stat-card label="Progress" :value="$progress.'%'" icon="chart" tone="green" />
            <x-stat-card label="Deadline" :value="$project->deadline?->format('d M Y') ?? '-'" icon="calendar" tone="amber" />
            <x-stat-card label="Open tasks" :value="max(0, $totalTasks - $doneTasks)" icon="check" tone="rose" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
            <div class="space-y-6">
                <div class="crm-card p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Project brief</h3>
                            <p class="mt-3 text-sm leading-6 text-gray-600">{{ $project->description ?: 'No description yet.' }}</p>
                        </div>
                        <a href="{{ route('projects.edit', $project) }}" class="crm-button-secondary">Edit</a>
                    </div>

                    <div class="mt-5">
                        <div class="flex justify-between text-xs font-semibold text-gray-500">
                            <span>Task progress</span>
                            <span>{{ $progress }}%</span>
                        </div>
                        <div class="project-progress-track mt-2">
                            <div style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ route('invoices.create', ['project_id' => $project->id, 'client_id' => $project->client_id]) }}" class="crm-button-secondary">Add Invoice</a>
                        @if ($project->status !== 'completed')
                            <form method="POST" action="{{ route('projects.mark-completed', $project) }}">
                                @csrf
                                <button class="crm-button-primary">Mark completed</button>
                            </form>
                        @endif
                        <x-confirm-action
                            :action="route('projects.destroy', $project)"
                            label="Delete"
                            title="Delete project?"
                            body="This project will be removed, but linked client records stay."
                            confirm-label="Delete project"
                        />
                    </div>
                </div>

                <form method="POST" action="{{ route('tasks.store') }}" class="crm-card p-5">
                    @csrf
                    <input type="hidden" name="context" value="project">
                    <input type="hidden" name="client_id" value="{{ $project->client_id }}">
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <input type="hidden" name="type" value="project_task">
                    <input type="hidden" name="status" value="pending">

                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold text-gray-900">Quick task</h3>
                        <button class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Add</button>
                    </div>

                    <div class="mt-4 grid gap-3">
                        <label for="quick-task-title" class="sr-only">Task title</label>
                        <input id="quick-task-title" name="title" required maxlength="255" class="crm-field mt-0" placeholder="Next delivery step">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="quick-task-priority" class="crm-label">Priority</label>
                                <select id="quick-task-priority" name="priority" class="crm-field">
                                    @foreach ($taskPriorities as $value => $label)
                                        <option value="{{ $value }}" @selected($value === 'medium')>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="quick-task-due-at" class="crm-label">Due</label>
                                <input id="quick-task-due-at" name="due_at" type="datetime-local" class="crm-field">
                            </div>
                        </div>
                    </div>
                </form>

                @include('partials.activity-form', ['clientId' => $project->client_id, 'projectId' => $project->id, 'activityTypes' => $activityTypes])
            </div>

            <div class="space-y-6">
                <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="crm-card overflow-hidden">
                        <div class="border-b border-gray-200 p-4">
                            <h3 class="font-semibold text-gray-900">Timeline</h3>
                        </div>
                        <div class="project-timeline">
                            @forelse ($timeline as $event)
                                <article class="project-timeline-item" data-tone="{{ $event['tone'] }}">
                                    <div class="project-timeline-dot"></div>
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="font-semibold text-gray-900">{{ $event['title'] }}</p>
                                            <time class="text-xs font-semibold text-gray-500">{{ $event['date']?->format('d M Y') ?? '-' }}</time>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">{{ $event['meta'] }}</p>
                                    </div>
                                </article>
                            @empty
                                <div class="p-4 text-sm text-gray-500">No timeline yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="crm-card overflow-hidden">
                            <div class="border-b border-gray-200 p-4">
                                <h3 class="font-semibold text-gray-900">Invoices</h3>
                            </div>
                            @forelse ($project->invoices as $invoice)
                                <a href="{{ route('invoices.show', $invoice) }}" class="block border-b border-gray-100 p-4 last:border-0 hover:bg-gray-50">
                                    <p class="font-medium text-gray-900">{{ $invoice->invoice_number }}</p>
                                    <p class="mt-1 text-sm text-gray-500">{{ $money($invoice->total) }} · {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</p>
                                </a>
                            @empty
                                <div class="p-4 text-sm text-gray-500">No invoices yet.</div>
                            @endforelse
                        </div>

                        @include('partials.activity-list', ['activities' => $project->activities])
                    </div>
                </section>
            </div>
        </section>

        <section>
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Delivery board</h3>
                </div>
                <details class="project-board-settings">
                    <summary>Board rules</summary>
                    <div>
                        <p><strong>{{ $doneTasks }}</strong> done · <strong>{{ $totalTasks }}</strong> active</p>
                        <p><strong>{{ $project->overdue_tasks_count }}</strong> overdue</p>
                    </div>
                </details>
            </div>

            <div class="project-kanban">
                @foreach ($kanbanMeta as $status => $meta)
                    @php($columnTasks = $boardColumns->get($status, collect()))
                    <section class="project-kanban-column" data-tone="{{ $meta['tone'] }}">
                        <div class="project-kanban-head">
                            <span>{{ $meta['title'] }}</span>
                            <span>{{ $columnTasks->count() }}</span>
                        </div>

                        <div class="project-kanban-list">
                            @forelse ($columnTasks as $task)
                                <article class="project-task-card">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h4 class="font-semibold text-gray-900">{{ $task->title }}</h4>
                                            <p class="mt-1 text-xs text-gray-500">{{ $task->due_at?->format('d M Y H:i') ?? 'No due date' }}</p>
                                        </div>
                                        <x-status-badge :status="$task->priority" />
                                    </div>

                                    @if ($task->description)
                                        <p class="mt-3 line-clamp-2 text-sm text-gray-600">{{ $task->description }}</p>
                                    @endif

                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                        @if ($task->status === 'done')
                                            <form method="POST" action="{{ route('tasks.reopen', $task) }}">
                                                @csrf
                                                <button class="project-mini-button">Reopen</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                                @csrf
                                                <button class="project-mini-button is-primary"><x-icon name="check" class="h-4 w-4" />Done</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('tasks.update-status', $task) }}">
                                            @csrf
                                            <label for="task-status-{{ $task->id }}" class="sr-only">Status</label>
                                            <select id="task-status-{{ $task->id }}" name="status" class="project-status-select" onchange="this.form.submit()">
                                                @foreach ($taskStatuses as $value => $label)
                                                    <option value="{{ $value }}" @selected($task->status === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </div>
                                </article>
                            @empty
                                <div class="project-kanban-empty">No tasks</div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
