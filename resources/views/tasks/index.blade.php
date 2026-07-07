<x-app-layout>
    <x-slot:title>Tasks</x-slot:title>

    <div class="space-y-5">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Tasks & follow-ups</h2>
                <p class="mt-1 text-sm text-gray-500">Daily work queue for clients, deals, projects, and invoices.</p>
            </div>
            <a href="{{ route('tasks.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Add Task</a>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach (['open' => 'Open', 'today' => 'Today', 'upcoming' => 'Upcoming', 'overdue' => 'Overdue', 'completed' => 'Completed'] as $value => $label)
                <a href="{{ route('tasks.index', ['filter' => $value]) }}" @class([
                    'rounded-lg px-4 py-2 text-sm font-medium',
                    'bg-primary-600 text-white' => $filter === $value,
                    'bg-white text-gray-700 border border-gray-200' => $filter !== $value,
                ])>{{ $label }}</a>
            @endforeach
        </div>

        <form method="GET" class="crm-card grid gap-3 p-4 md:grid-cols-[1fr_180px_180px_auto]">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div></div>
            <select name="type" class="crm-field mt-0">
                <option value="">All types</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="priority" class="crm-field mt-0">
                <option value="">All priorities</option>
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="crm-button-secondary">Filter</button>
        </form>

        @if ($tasks->count())
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($tasks as $task)
                    <article class="crm-card p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $task->title }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $task->client?->name ?? 'No client' }}</p>
                            </div>
                            <x-status-badge :status="$task->priority" />
                        </div>
                        <p class="mt-4 text-sm text-gray-600">{{ $task->description ?: 'No description.' }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <x-status-badge :status="$task->status" />
                            <x-status-badge :status="$task->type" />
                        </div>
                        <p class="mt-4 text-sm text-gray-500">Due {{ $task->due_at?->format('d M Y H:i') ?? 'not set' }}</p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <a href="{{ route('tasks.edit', $task) }}" class="crm-button-secondary">Edit</a>
                            @if ($task->status === 'done')
                                <form method="POST" action="{{ route('tasks.reopen', $task) }}">@csrf<button class="crm-button-secondary">Reopen</button></form>
                            @else
                                <form method="POST" action="{{ route('tasks.complete', $task) }}">@csrf<button class="crm-button-primary">Done</button></form>
                            @endif
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2 border-t border-gray-100 pt-3">
                            @foreach (['today' => 'Today 17:00', 'tomorrow' => 'Tomorrow', 'next_week' => 'Next week'] as $preset => $label)
                                <form method="POST" action="{{ route('tasks.reschedule', $task) }}">
                                    @csrf
                                    <input type="hidden" name="preset" value="{{ $preset }}">
                                    <button class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50">{{ $label }}</button>
                                </form>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
            {{ $tasks->links() }}
        @else
            <x-empty-state title="No tasks found" text="Create a follow-up or loosen filters." action="Add Task" :href="route('tasks.create')" />
        @endif
    </div>
</x-app-layout>
