<section class="crm-card overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 p-4">
        <h3 class="font-semibold text-gray-900">{{ $tasksHeading ?? 'Tasks' }}</h3>
        <a href="{{ route('tasks.create', $taskContext) }}" class="crm-button-secondary">Add follow-up task</a>
    </div>
    @forelse ($relatedTasks as $relatedTask)
        <a href="{{ route('tasks.edit', $relatedTask) }}" class="flex items-center justify-between gap-3 border-b border-gray-100 p-4 last:border-0 hover:bg-gray-50">
            <div class="min-w-0"><p class="truncate font-medium text-gray-900">{{ $relatedTask->title }}</p><p class="text-xs text-gray-500">{{ $relatedTask->due_at?->format('d M Y H:i') ?? 'No due date' }}</p></div>
            <x-status-badge :status="$relatedTask->status" />
        </a>
    @empty
        <p class="p-4 text-sm text-gray-500">No related tasks yet.</p>
    @endforelse
</section>
