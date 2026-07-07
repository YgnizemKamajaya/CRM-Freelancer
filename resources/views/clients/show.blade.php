@php($money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'))
<x-app-layout>
    <x-slot:title>{{ $client->name }}</x-slot:title>

    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-semibold text-gray-900">{{ $client->name }}</h2>
                    <x-status-badge :status="$client->status" />
                </div>
                <p class="mt-1 text-sm text-gray-500">{{ $client->company_name ?? 'Independent client' }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($client->tags ?? [] as $tag)
                        <span class="rounded-full bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($client->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', $client->whatsapp) }}" class="crm-button-secondary">WhatsApp</a>
                @endif
                @if ($client->email)
                    <a href="mailto:{{ $client->email }}" class="crm-button-secondary">Email</a>
                @endif
                <a href="{{ route('clients.edit', $client) }}" class="crm-button-primary">Edit</a>
                <x-confirm-action
                    :action="route('clients.destroy', $client)"
                    label="Archive"
                    title="Archive client?"
                    body="This client will be archived and hidden from active lists."
                    confirm-label="Archive client"
                />
            </div>
        </div>

        <section class="grid gap-4 md:grid-cols-4">
            <x-stat-card label="Deals" :value="$client->deals->count()" icon="pipeline" />
            <x-stat-card label="Projects" :value="$client->projects->count()" icon="briefcase" tone="green" />
            <x-stat-card label="Invoiced" :value="$money($client->invoices->sum('total'))" icon="document" tone="amber" />
            <x-stat-card label="Outstanding" :value="$money($client->invoices->sum(fn($invoice) => $invoice->balance))" icon="document" tone="rose" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="space-y-6">
                <div class="crm-card p-5">
                    <h3 class="text-lg font-semibold text-gray-900">Contact</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div><dt class="text-gray-500">Email</dt><dd class="text-gray-900">{{ $client->email ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Phone</dt><dd class="text-gray-900">{{ $client->phone ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Website</dt><dd class="text-gray-900">{{ $client->website ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Last contacted</dt><dd class="text-gray-900">{{ $client->last_contacted_at?->format('d M Y H:i') ?? '-' }}</dd></div>
                    </dl>
                </div>
                @include('partials.activity-form', ['clientId' => $client->id, 'activityTypes' => $activityTypes])
            </div>

            <div class="space-y-6">
                @include('partials.activity-list', ['activities' => $client->activities])

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="crm-card overflow-hidden">
                        <div class="flex items-center justify-between border-b border-gray-200 p-4">
                            <h3 class="font-semibold text-gray-900">Deals</h3>
                            <a href="{{ route('deals.create', ['client_id' => $client->id]) }}" class="text-sm font-medium text-primary-600">Add</a>
                        </div>
                        @foreach ($client->deals as $deal)
                            <a href="{{ route('deals.show', $deal) }}" class="block border-b border-gray-100 p-4 last:border-0 hover:bg-gray-50">
                                <p class="font-medium text-gray-900">{{ $deal->title }}</p>
                                <p class="text-sm text-gray-500">{{ $money($deal->value) }} · {{ \App\Models\Deal::STAGES[$deal->stage] ?? $deal->stage }}</p>
                            </a>
                        @endforeach
                    </div>
                    <div class="crm-card overflow-hidden">
                        <div class="flex items-center justify-between border-b border-gray-200 p-4">
                            <h3 class="font-semibold text-gray-900">Projects</h3>
                            <a href="{{ route('projects.create', ['client_id' => $client->id]) }}" class="text-sm font-medium text-primary-600">Add</a>
                        </div>
                        @foreach ($client->projects as $project)
                            <a href="{{ route('projects.show', $project) }}" class="block border-b border-gray-100 p-4 last:border-0 hover:bg-gray-50">
                                <p class="font-medium text-gray-900">{{ $project->title }}</p>
                                <p class="text-sm text-gray-500">{{ \App\Models\Project::STATUSES[$project->status] ?? $project->status }} · {{ $project->progress }}%</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
