@php($activities = $activities ?? collect())
<div class="crm-card overflow-hidden">
    <div class="border-b border-gray-200 p-4">
        <h2 class="text-lg font-semibold text-gray-900">Activity timeline</h2>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse ($activities as $activity)
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <x-status-badge :status="$activity->type" />
                        <p class="mt-2 text-sm text-gray-700">{{ $activity->body }}</p>
                        <p class="mt-2 text-xs text-gray-500">{{ $activity->activity_at?->format('d M Y H:i') }}</p>
                    </div>
                    <form method="POST" action="{{ route('activities.destroy', $activity) }}">
                        @csrf
                        @method('DELETE')
                        <button class="text-xs font-medium text-rose-600 hover:text-rose-700">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-4 text-sm text-gray-500">No activity recorded yet.</div>
        @endforelse
    </div>
</div>
