@php
    $editing = $invoice->exists;
    $lineItems = collect(old('items', $items->map(fn ($item) => $item->only(['description', 'quantity', 'unit_price']))->all()))->values()->all();
    if (! count($lineItems)) $lineItems = [['description' => '', 'quantity' => 1, 'unit_price' => 0]];
@endphp
<x-app-layout>
    <x-slot:title>{{ $editing ? 'Edit Invoice' : 'Add Invoice' }}</x-slot:title>

    <form x-data="invoiceEditor(@js($lineItems), @js(old('discount', $invoice->discount ?? 0)), @js(old('tax', $invoice->tax ?? 0)), @js(old('tax_percentage', $invoice->tax_percentage)))" method="POST" action="{{ $editing ? route('invoices.update', $invoice) : route('invoices.store') }}" class="mx-auto max-w-4xl space-y-6">
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
                <input id="discount" name="discount" x-model="discount" type="number" min="0" step="0.01" value="{{ old('discount', $invoice->discount ?? 0) }}" class="crm-field">
            </div>
            <div>
                <label for="tax" class="crm-label">Tax</label>
                <input id="tax" name="tax" x-model="tax" :readonly="taxPercentage !== null &amp;&amp; taxPercentage !== ''" type="number" min="0" step="0.01" value="{{ old('tax', $invoice->tax ?? 0) }}" class="crm-field">
            </div>
            <div>
                <label for="tax_percentage" class="crm-label">Pajak (%) — kosong untuk nominal manual</label>
                <input id="tax_percentage" name="tax_percentage" x-model="taxPercentage" type="number" min="0" max="100" step="0.01" value="{{ old('tax_percentage', $invoice->tax_percentage) }}" class="crm-field">
                <x-input-error :messages="$errors->get('tax_percentage')" class="mt-2" />
            </div>
            <div>
                <label for="amount_paid" class="crm-label">Amount paid</label>
                <p class="text-xs" style="color: var(--crm-muted);">Isi jumlah yang benar-benar diterima. Status Paid memerlukan pembayaran penuh.</p>
                <x-input-error :messages="$errors->get('amount_paid')" class="mt-2" />
                <input id="amount_paid" name="amount_paid" type="number" min="0" step="0.01" value="{{ old('amount_paid', $invoice->amount_paid ?? 0) }}" class="crm-field">
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
                <template x-for="(item, index) in items" :key="item.key">
                    <fieldset x-show="!item.removed" :disabled="item.removed" class="grid gap-3 p-4 md:grid-cols-[1fr_100px_150px_auto]">
                        <div>
                            <label :for="'item-'+item.key+'-description'" class="crm-label">Description</label>
                            <input :id="'item-'+item.key+'-description'" :name="'items['+index+'][description]'" x-model="item.description" class="crm-field">
                        </div>
                        <div>
                            <label :for="'item-'+item.key+'-quantity'" class="crm-label">Qty</label>
                            <input :id="'item-'+item.key+'-quantity'" :name="'items['+index+'][quantity]'" x-model="item.quantity" :required="String(item.description || '').trim().length > 0" type="number" min="0.01" step="0.01" class="crm-field">
                        </div>
                        <div>
                            <label :for="'item-'+item.key+'-price'" class="crm-label">Unit price</label>
                            <input :id="'item-'+item.key+'-price'" :name="'items['+index+'][unit_price]'" x-model="item.unit_price" type="number" min="0" step="0.01" class="crm-field">
                        </div>
                        <button type="button" @click="item.removed = true" class="crm-button-secondary self-end" :aria-label="'Hapus item '+(index+1)">Hapus</button>
                    </fieldset>
                </template>
                <noscript><p class="p-4">Aktifkan JavaScript untuk mengedit item invoice.</p></noscript>
                @foreach ($errors->get('items.*') as $messages)
                    <x-input-error :messages="$messages" class="px-4" />
                @endforeach
            </div>
        </div>

        <div class="crm-card flex flex-wrap items-center justify-between gap-3 p-4">
            <button type="button" @click="addItem()" class="crm-button-secondary">Tambah item</button>
            <p class="text-sm">Subtotal: <span x-text="money(subtotal)"></span> · Pajak: <span x-text="money(taxAmount)"></span> · Total: <strong x-text="money(total)"></strong></p>
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ $editing ? route('invoices.show', $invoice) : route('invoices.index') }}" class="crm-button-secondary">Cancel</a>
            <button class="crm-button-primary" x-bind:disabled="false" disabled>{{ $editing ? 'Save Invoice' : 'Create Invoice' }}</button>
        </div>
    </form>
    @push('scripts')
    <script>
        function invoiceEditor(rows, discount, tax, taxPercentage) {
            return {
                items: rows.map((row, index) => ({...row, key: index, removed: false})),
                nextKey: rows.length, discount, tax, taxPercentage,
                addItem() { this.items.push({key: this.nextKey++, description: '', quantity: 1, unit_price: 0, removed: false}); },
                get subtotal() { return this.items.filter(item => !item.removed && String(item.description || '').trim()).reduce((sum, item) => sum + Math.round(Number(item.quantity || 0) * Number(item.unit_price || 0) * 100) / 100, 0); },
                get taxAmount() { return this.taxPercentage !== null && this.taxPercentage !== '' ? Math.round(Math.max(0, this.subtotal - Number(this.discount || 0)) * Number(this.taxPercentage)) / 100 : Number(this.tax || 0); },
                get total() { return Math.max(0, this.subtotal - Number(this.discount || 0) + this.taxAmount); },
                money(value) { return Number(value).toLocaleString(undefined, {maximumFractionDigits: 2}); }
            };
        }
    </script>
    @endpush
</x-app-layout>
