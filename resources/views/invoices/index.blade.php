@php($money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'))
<x-app-layout>
    <x-slot:title>Invoices</x-slot:title>

    <div class="space-y-5">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Invoices</h2>
                <p class="mt-1 text-sm text-gray-500">Simple billing status, due dates, and unpaid totals.</p>
            </div>
            <a href="{{ route('invoices.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Add Invoice</a>
        </div>

        <section class="grid gap-4 md:grid-cols-2">
            <x-stat-card label="Unpaid total" :value="$money($summary['unpaid'])" icon="document" tone="amber" />
            <x-stat-card label="Overdue total" :value="$money($summary['overdue'])" icon="document" tone="rose" />
        </section>

        <form method="GET" class="crm-card grid gap-3 p-4 md:grid-cols-[1fr_180px_220px_auto]">
            <input name="search" value="{{ request('search') }}" class="crm-field mt-0" placeholder="Search invoice number">
            <select name="status" class="crm-field mt-0">
                <option value="">All statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="client_id" class="crm-field mt-0">
                <option value="">All clients</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected((int) request('client_id') === $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
            <button class="crm-button-secondary">Filter</button>
        </form>

        @if ($invoices->count())
            <div class="crm-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th scope="col" class="px-4 py-3">Invoice</th>
                                <th scope="col" class="px-4 py-3">Client</th>
                                <th scope="col" class="px-4 py-3">Issue</th>
                                <th scope="col" class="px-4 py-3">Due</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3">Balance</th>
                                <th scope="col" class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($invoices as $invoice)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                                    <td class="px-4 py-4">{{ $invoice->client->name }}</td>
                                    <td class="px-4 py-4">{{ $invoice->issue_date->format('d M Y') }}</td>
                                    <td class="px-4 py-4">{{ $invoice->due_date?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-4"><x-status-badge :status="$invoice->status" /></td>
                                    <td class="px-4 py-4">{{ $money($invoice->balance) }}</td>
                                    <td class="px-4 py-4 text-right"><a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-primary-600">Open</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 p-4">{{ $invoices->links() }}</div>
            </div>
        @else
            <x-empty-state title="No invoices found" text="Create an invoice with line items to track simple billing." action="Add Invoice" :href="route('invoices.create')" />
        @endif
    </div>
</x-app-layout>
