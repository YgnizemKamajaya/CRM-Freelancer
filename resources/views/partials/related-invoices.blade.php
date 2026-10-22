<section class="crm-card overflow-hidden">
    <div class="flex items-center justify-between gap-2 border-b border-gray-200 p-4">
        <h3 class="font-semibold text-gray-900">Invoices</h3>
        <a href="{{ route('invoices.create', ['client_id' => $client->id]) }}" class="crm-button-secondary">Add invoice</a>
    </div>
    @forelse ($client->invoices as $relatedInvoice)
        <a href="{{ route('invoices.show', $relatedInvoice) }}" class="flex items-center justify-between gap-3 border-b border-gray-100 p-4 last:border-0 hover:bg-gray-50">
            <div><p class="font-medium text-gray-900">{{ $relatedInvoice->invoice_number }}</p><p class="text-xs text-gray-500">{{ $relatedInvoice->currency }} {{ number_format((float) $relatedInvoice->total, 2) }}</p></div>
            <x-status-badge :status="$relatedInvoice->status" />
        </a>
    @empty
        <p class="p-4 text-sm text-gray-500">No invoices yet.</p>
    @endforelse
</section>
