@php($money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'))
<x-app-layout>
    <x-slot:title>{{ $deal->title }}</x-slot:title>

    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-semibold text-gray-900">{{ $deal->title }}</h2>
                    <x-status-badge :status="$deal->stage" />
                </div>
                <p class="mt-1 text-sm text-gray-500">{{ $deal->client->name }} · {{ $deal->probability }}% probability</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('deals.edit', $deal) }}" class="crm-button-secondary">Edit</a>
                <form method="POST" action="{{ route('deals.mark-won', $deal) }}">@csrf<button class="crm-button-primary">Mark won</button></form>
                <form method="POST" action="{{ route('deals.convert-to-project', $deal) }}">@csrf<button class="crm-button-secondary">Convert</button></form>
                <x-confirm-action
                    :action="route('deals.destroy', $deal)"
                    label="Delete"
                    title="Delete deal?"
                    body="This deal will be removed from the pipeline."
                    confirm-label="Delete deal"
                />
            </div>
        </div>

        <section class="grid gap-4 md:grid-cols-4">
            <x-stat-card label="Deal value" :value="$money($deal->value)" icon="pipeline" />
            <x-stat-card label="Weighted value" :value="$money($deal->weighted_value)" icon="chart" tone="green" />
            <x-stat-card label="Expected close" :value="$deal->expected_close_date?->format('d M Y') ?? '-'" icon="calendar" tone="amber" />
            <x-stat-card label="Next follow-up" :value="$deal->next_follow_up_at?->format('d M Y') ?? '-'" icon="check" tone="rose" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="space-y-6">
                <div class="crm-card p-5">
                    <h3 class="text-lg font-semibold text-gray-900">Deal summary</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">{{ $deal->description ?: 'No description yet.' }}</p>
                    <dl class="mt-4 grid gap-3 text-sm md:grid-cols-2">
                        <div><dt class="text-gray-500">Client</dt><dd><x-client-link :client="$deal->client" /></dd></div>
                        <div><dt class="text-gray-500">Status</dt><dd>{{ $deal->status }}</dd></div>
                        <div><dt class="text-gray-500">Won at</dt><dd>{{ $deal->won_at?->format('d M Y') ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Lost at</dt><dd>{{ $deal->lost_at?->format('d M Y') ?? '-' }}</dd></div>
                    </dl>
                </div>
                <form method="POST" action="{{ route('deals.mark-lost', $deal) }}" class="crm-card space-y-4 p-5">
                    @csrf
                    <div>
                        <label for="lost_reason" class="crm-label">Lost reason</label>
                        <textarea id="lost_reason" name="lost_reason" rows="3" class="crm-field" placeholder="Budget, timing, fit, or competitor.">{{ old('lost_reason', $deal->lost_reason) }}</textarea>
                        <x-input-error :messages="$errors->get('lost_reason')" class="mt-2" />
                    </div>
                    <button class="crm-button-danger">Mark lost</button>
                </form>
                @include('partials.activity-form', ['clientId' => $deal->client_id, 'dealId' => $deal->id, 'activityTypes' => $activityTypes])
            </div>
            <div class="space-y-6">
                @include('partials.activity-list', ['activities' => $deal->activities])
                <div class="crm-card p-5">
                    <h3 class="text-lg font-semibold text-gray-900">Related project</h3>
                    @if ($deal->project)
                        <a href="{{ route('projects.show', $deal->project) }}" class="mt-3 block rounded-lg border border-gray-200 p-4 hover:bg-gray-50">
                            <p class="font-medium text-gray-900">{{ $deal->project->title }}</p>
                            <p class="text-sm text-gray-500">{{ $deal->project->progress }}% complete</p>
                        </a>
                    @else
                        <p class="mt-3 text-sm text-gray-500">No project converted yet.</p>
                    @endif
                </div>
            </div>
        </section>
        @include('partials.related-tasks', ['relatedTasks' => $deal->tasks, 'taskContext' => ['deal_id' => $deal->id]])
    </div>
</x-app-layout>
