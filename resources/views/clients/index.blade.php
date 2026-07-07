<x-app-layout>
    <x-slot:title>Clients</x-slot:title>

    <div class="space-y-5">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Clients</h2>
                <p class="mt-1 text-sm text-gray-500">Search relationship context, revenue, and current work.</p>
            </div>
            <a href="{{ route('clients.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Add Client</a>
        </div>

        <form method="GET" class="crm-card grid gap-3 p-4 md:grid-cols-[1fr_170px_170px_160px_auto]">
            <label class="sr-only" for="search">Search clients</label>
            <input id="search" name="search" value="{{ request('search') }}" class="crm-field mt-0" placeholder="Search name, company, email, phone">
            <select name="status" class="crm-field mt-0">
                <option value="">All statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="tag" class="crm-field mt-0">
                <option value="">All tags</option>
                @foreach ($tags as $tag)
                    <option value="{{ $tag }}" @selected(request('tag') === $tag)>{{ $tag }}</option>
                @endforeach
            </select>
            <select name="sort" class="crm-field mt-0">
                <option value="">Newest</option>
                <option value="activity" @selected(request('sort') === 'activity')>Recent activity</option>
                <option value="revenue" @selected(request('sort') === 'revenue')>Total revenue</option>
            </select>
            <button class="crm-button-secondary">Filter</button>
        </form>

        @if ($clients->count())
            <div class="crm-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th scope="col" class="px-4 py-3">Client</th>
                                <th scope="col" class="px-4 py-3">Contact</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3">Work</th>
                                <th scope="col" class="px-4 py-3">Last contacted</th>
                                <th scope="col" class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($clients as $client)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4">
                                        <p class="font-medium text-gray-900">{{ $client->name }}</p>
                                        <p class="text-gray-500">{{ $client->company_name ?? 'Independent' }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p>{{ $client->email ?? 'No email' }}</p>
                                        <p class="text-gray-500">{{ $client->whatsapp ?? $client->phone ?? 'No phone' }}</p>
                                    </td>
                                    <td class="px-4 py-4"><x-status-badge :status="$client->status" /></td>
                                    <td class="px-4 py-4 text-gray-600">{{ $client->projects_count }} projects · {{ $client->invoices_count }} invoices</td>
                                    <td class="px-4 py-4 text-gray-600">{{ $client->last_contacted_at?->format('d M Y') ?? 'Never' }}</td>
                                    <td class="px-4 py-4 text-right">
                                        <a href="{{ route('clients.show', $client) }}" class="font-medium text-primary-600 hover:text-primary-700">Open</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 p-4">{{ $clients->links() }}</div>
            </div>
        @else
            <x-empty-state title="No clients found" text="Add your first client or change the current filters." action="Add Client" :href="route('clients.create')" />
        @endif
    </div>
</x-app-layout>
