@php($money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'))
<x-app-layout>
    <x-slot:title>{{ $invoice->invoice_number }}</x-slot:title>

    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-semibold text-gray-900">{{ $invoice->invoice_number }}</h2>
                    <x-status-badge :status="$invoice->status" />
                </div>
                <p class="mt-1 text-sm text-gray-500">{{ $invoice->client->name }} · due {{ $invoice->due_date?->format('d M Y') ?? 'not set' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('invoices.print', $invoice) }}" class="crm-button-secondary" target="_blank">Print</a>
                <a href="{{ route('invoices.pdf', $invoice) }}" class="crm-button-secondary">PDF</a>
                <a href="{{ route('invoices.edit', $invoice) }}" class="crm-button-secondary">Edit</a>
                <form method="POST" action="{{ route('invoices.mark-sent', $invoice) }}">@csrf<button class="crm-button-secondary">Mark sent</button></form>
                <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}">@csrf<button class="crm-button-primary">Mark paid</button></form>
                <x-confirm-action
                    :action="route('invoices.destroy', $invoice)"
                    label="Delete"
                    title="Delete invoice?"
                    body="This invoice and its line items will be deleted."
                    confirm-label="Delete invoice"
                />
            </div>
        </div>

        <section class="grid gap-4 md:grid-cols-4">
            <x-stat-card label="Total" :value="$money($invoice->total)" icon="document" />
            <x-stat-card label="Paid" :value="$money($invoice->amount_paid)" icon="check" tone="green" />
            <x-stat-card label="Balance" :value="$money($invoice->balance)" icon="chart" tone="amber" />
            <x-stat-card label="Due date" :value="$invoice->due_date?->format('d M Y') ?? '-'" icon="calendar" tone="rose" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-6">
                <div class="crm-card overflow-hidden">
                    <div class="border-b border-gray-200 p-4"><h3 class="font-semibold text-gray-900">Items</h3></div>
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr><th class="px-4 py-3">Description</th><th class="px-4 py-3">Qty</th><th class="px-4 py-3">Unit</th><th class="px-4 py-3 text-right">Total</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td class="px-4 py-3">{{ $item->description }}</td>
                                    <td class="px-4 py-3">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3">{{ $money($item->unit_price) }}</td>
                                    <td class="px-4 py-3 text-right">{{ $money($item->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="border-t border-gray-200 p-4 text-sm">
                        <div class="ml-auto max-w-xs space-y-2">
                            <div class="flex justify-between"><span>Subtotal</span><span>{{ $money($invoice->subtotal) }}</span></div>
                            <div class="flex justify-between"><span>Discount</span><span>{{ $money($invoice->discount) }}</span></div>
                            <div class="flex justify-between"><span>Tax</span><span>{{ $money($invoice->tax) }}</span></div>
                            <div class="flex justify-between border-t border-gray-200 pt-2 font-semibold text-gray-900"><span>Total</span><span>{{ $money($invoice->total) }}</span></div>
                        </div>
                    </div>
                </div>
                @include('partials.activity-form', ['clientId' => $invoice->client_id, 'invoiceId' => $invoice->id, 'activityTypes' => $activityTypes])
            </div>
            <div class="space-y-6">
                <div class="crm-card p-5">
                    <h3 class="font-semibold text-gray-900">Billing context</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div><dt class="text-gray-500">Client</dt><dd><a class="font-medium text-primary-600" href="{{ route('clients.show', $invoice->client) }}">{{ $invoice->client->name }}</a></dd></div>
                        <div><dt class="text-gray-500">Project</dt><dd>{{ $invoice->project?->title ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Notes</dt><dd>{{ $invoice->notes ?? '-' }}</dd></div>
                    </dl>
                </div>
                @include('partials.activity-list', ['activities' => $invoice->activities])
            </div>
        </section>
    </div>
</x-app-layout>
