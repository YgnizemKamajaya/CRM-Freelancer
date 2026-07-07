@php($editing = $invoice->exists)
<x-app-layout>
    <x-slot:title>{{ $editing ? 'Edit Invoice' : 'Add Invoice' }}</x-slot:title>

    <form method="POST" action="{{ $editing ? route('invoices.update', $invoice) : route('invoices.store') }}" class="mx-auto max-w-4xl space-y-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="crm-card grid gap-4 p-5 md:grid-cols-3">
            <div>
                <label for="invoice_number" class="crm-label">Invoice number</label>
                <input id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" required class="crm-field">
                <x-input-error :messages="$errors->get('invoice_number')" class="mt-2" />
            </div>
            <div>
                <label for="client_id" class="crm-label">Client</label>
                <select id="client_id" name="client_id" required class="crm-field">
                    <option value="">Choose client</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected((int) old('client_id', $invoice->client_id) === $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="project_id" class="crm-label">Project</label>
                <select id="project_id" name="project_id" class="crm-field">
                    <option value="">None</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @selected((int) old('project_id', $invoice->project_id) === $project->id)>{{ $project->title }} · {{ $project->client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="issue_date" class="crm-label">Issue date</label>
                <input id="issue_date" name="issue_date" type="date" value="{{ old('issue_date', $invoice->issue_date?->format('Y-m-d')) }}" class="crm-field">
            </div>
            <div>
                <label for="due_date" class="crm-label">Due date</label>
                <input id="due_date" name="due_date" type="date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}" class="crm-field">
            </div>
            <div>
                <label for="status" class="crm-label">Status</label>
                <select id="status" name="status" class="crm-field">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $invoice->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="currency" class="crm-label">Currency</label>
                <input id="currency" name="currency" maxlength="3" value="{{ old('currency', $invoice->currency ?? 'IDR') }}" class="crm-field">
            </div>
            <div>
                <label for="discount" class="crm-label">Discount</label>
                <input id="discount" name="discount" type="number" min="0" step="1000" value="{{ old('discount', $invoice->discount ?? 0) }}" class="crm-field">
            </div>
            <div>
                <label for="tax" class="crm-label">Tax</label>
                <input id="tax" name="tax" type="number" min="0" step="1000" value="{{ old('tax', $invoice->tax ?? 0) }}" class="crm-field">
            </div>
            <div>
                <label for="amount_paid" class="crm-label">Amount paid</label>
                <input id="amount_paid" name="amount_paid" type="number" min="0" step="1000" value="{{ old('amount_paid', $invoice->amount_paid ?? 0) }}" class="crm-field">
            </div>
            <div class="md:col-span-3">
                <label for="notes" class="crm-label">Notes</label>
                <textarea id="notes" name="notes" rows="3" class="crm-field">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
        </div>

        <div class="crm-card overflow-hidden">
            <div class="border-b border-gray-200 p-4">
                <h3 class="font-semibold text-gray-900">Line items</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @for ($i = 0; $i < 5; $i++)
                    @php($item = $items[$i] ?? null)
                    <div class="grid gap-3 p-4 md:grid-cols-[1fr_120px_180px]">
                        <div>
                            <label for="item_{{ $i }}_description" class="crm-label">Description</label>
                            <input id="item_{{ $i }}_description" name="items[{{ $i }}][description]" value="{{ old("items.$i.description", $item->description ?? '') }}" class="crm-field">
                        </div>
                        <div>
                            <label for="item_{{ $i }}_quantity" class="crm-label">Qty</label>
                            <input id="item_{{ $i }}_quantity" name="items[{{ $i }}][quantity]" type="number" min="0.01" step="0.01" value="{{ old("items.$i.quantity", $item->quantity ?? 1) }}" class="crm-field">
                        </div>
                        <div>
                            <label for="item_{{ $i }}_unit_price" class="crm-label">Unit price</label>
                            <input id="item_{{ $i }}_unit_price" name="items[{{ $i }}][unit_price]" type="number" min="0" step="1000" value="{{ old("items.$i.unit_price", $item->unit_price ?? 0) }}" class="crm-field">
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ $editing ? route('invoices.show', $invoice) : route('invoices.index') }}" class="crm-button-secondary">Cancel</a>
            <button class="crm-button-primary">{{ $editing ? 'Save Invoice' : 'Create Invoice' }}</button>
        </div>
    </form>
</x-app-layout>
