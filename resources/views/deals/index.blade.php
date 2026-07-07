@php($money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'))
<x-app-layout>
    <x-slot:title>Deals</x-slot:title>

    <div class="space-y-5">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Deals pipeline</h2>
                <p class="mt-1 text-sm text-gray-500">Track value, probability, and next follow-up.</p>
            </div>
            <a href="{{ route('deals.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Add Deal</a>
        </div>

        <div class="grid gap-3 lg:grid-cols-6">
            @foreach ($stages as $stage => $label)
                <div class="crm-card p-3">
                    <p class="text-xs font-medium text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-xl font-semibold text-gray-900">{{ ($pipeline[$stage] ?? collect())->count() }}</p>
                    <p class="text-xs text-gray-500">{{ $money(($pipeline[$stage] ?? collect())->sum('value')) }}</p>
                </div>
            @endforeach
        </div>

        <form method="GET" class="crm-card grid gap-3 p-4 md:grid-cols-[1fr_180px_180px_auto]">
            <input name="search" value="{{ request('search') }}" class="crm-field mt-0" placeholder="Search deal or client">
            <select name="stage" class="crm-field mt-0">
                <option value="">All stages</option>
                @foreach ($stages as $value => $label)
                    <option value="{{ $value }}" @selected(request('stage') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="source" class="crm-field mt-0">
                <option value="">All sources</option>
                @foreach ($sources as $value => $label)
                    <option value="{{ $value }}" @selected(request('source') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="crm-button-secondary">Filter</button>
        </form>

        @if ($deals->count())
            <div class="crm-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th scope="col" class="px-4 py-3">Deal</th>
                                <th scope="col" class="px-4 py-3">Client</th>
                                <th scope="col" class="px-4 py-3">Stage</th>
                                <th scope="col" class="px-4 py-3">Value</th>
                                <th scope="col" class="px-4 py-3">Expected</th>
                                <th scope="col" class="px-4 py-3">Follow-up</th>
                                <th scope="col" class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($deals as $deal)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4">
                                        <p class="font-medium text-gray-900">{{ $deal->title }}</p>
                                        <p class="text-gray-500">{{ $deal->source ? ($sources[$deal->source] ?? $deal->source) : 'No source' }}</p>
                                    </td>
                                    <td class="px-4 py-4">{{ $deal->client->name }}</td>
                                    <td class="px-4 py-4">
                                        <form method="POST" action="{{ route('deals.update-stage', $deal) }}">
                                            @csrf
                                            <label class="sr-only" for="deal_stage_{{ $deal->id }}">Stage</label>
                                            <select id="deal_stage_{{ $deal->id }}" name="stage" class="rounded-lg border-gray-300 text-xs focus:border-primary-500 focus:ring-primary-200" onchange="this.form.submit()">
                                                @foreach ($stages as $value => $label)
                                                    <option value="{{ $value }}" @selected($deal->stage === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-gray-900">{{ $money($deal->value) }}</td>
                                    <td class="px-4 py-4">{{ $money($deal->weighted_value) }} <span class="text-xs text-gray-500">({{ $deal->probability }}%)</span></td>
                                    <td class="px-4 py-4">{{ $deal->next_follow_up_at?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-4 text-right"><a href="{{ route('deals.show', $deal) }}" class="font-medium text-primary-600">Open</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 p-4">{{ $deals->links() }}</div>
            </div>
        @else
            <x-empty-state title="No deals found" text="Add a deal to start tracking pipeline value." action="Add Deal" :href="route('deals.create')" />
        @endif
    </div>
</x-app-layout>
