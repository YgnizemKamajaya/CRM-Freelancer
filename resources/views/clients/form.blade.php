@php($editing = $client->exists)
<x-app-layout>
    <x-slot:title>{{ $editing ? 'Edit Client' : 'Add Client' }}</x-slot:title>

    <form method="POST" action="{{ $editing ? route('clients.update', $client) : route('clients.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="crm-card p-5">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="name" class="crm-label">Name</label>
                    <input id="name" name="name" value="{{ old('name', $client->name) }}" required class="crm-field">
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <label for="company_name" class="crm-label">Company</label>
                    <input id="company_name" name="company_name" value="{{ old('company_name', $client->company_name) }}" class="crm-field">
                </div>
                <div>
                    <label for="email" class="crm-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $client->email) }}" class="crm-field">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <label for="whatsapp" class="crm-label">WhatsApp</label>
                    <input id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $client->whatsapp) }}" class="crm-field">
                </div>
                <div>
                    <label for="phone" class="crm-label">Phone</label>
                    <input id="phone" name="phone" value="{{ old('phone', $client->phone) }}" class="crm-field">
                </div>
                <div>
                    <label for="website" class="crm-label">Website</label>
                    <input id="website" name="website" type="url" value="{{ old('website', $client->website) }}" class="crm-field">
                    <x-input-error :messages="$errors->get('website')" class="mt-2" />
                </div>
                <div>
                    <label for="source" class="crm-label">Source</label>
                    <input id="source" name="source" value="{{ old('source', $client->source) }}" class="crm-field">
                </div>
                <div>
                    <label for="status" class="crm-label">Status</label>
                    <select id="status" name="status" class="crm-field">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $client->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="tags" class="crm-label">Tags</label>
                    <input id="tags" name="tags" value="{{ old('tags', collect($client->tags)->join(', ')) }}" class="crm-field" placeholder="web, retainer, ecommerce">
                </div>
                <div class="md:col-span-2">
                    <label for="address" class="crm-label">Address</label>
                    <textarea id="address" name="address" rows="2" class="crm-field">{{ old('address', $client->address) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="notes" class="crm-label">Notes</label>
                    <textarea id="notes" name="notes" rows="5" class="crm-field">{{ old('notes', $client->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ $editing ? route('clients.show', $client) : route('clients.index') }}" class="crm-button-secondary">Cancel</a>
            <button class="crm-button-primary">{{ $editing ? 'Save Client' : 'Create Client' }}</button>
        </div>
    </form>
</x-app-layout>
