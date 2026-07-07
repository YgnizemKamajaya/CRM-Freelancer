@php($editing = $deal->exists)
<x-app-layout>
    <x-slot:title>{{ $editing ? 'Edit Deal' : 'Add Deal' }}</x-slot:title>

    <form method="POST" action="{{ $editing ? route('deals.update', $deal) : route('deals.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="crm-card grid gap-4 p-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="crm-label">Title</label>
                <input id="title" name="title" value="{{ old('title', $deal->title) }}" required class="crm-field">
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>
            <div>
                <label for="client_id" class="crm-label">Client</label>
                <select id="client_id" name="client_id" required class="crm-field">
                    <option value="">Choose client</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected((int) old('client_id', $deal->client_id) === $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
            </div>
            <div>
                <label for="source" class="crm-label">Source</label>
                <select id="source" name="source" class="crm-field">
                    <option value="">Not set</option>
                    @foreach ($sources as $value => $label)
                        <option value="{{ $value }}" @selected(old('source', $deal->source) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="value" class="crm-label">Value</label>
                <input id="value" name="value" type="number" min="0" step="1000" value="{{ old('value', $deal->value ?? 0) }}" class="crm-field">
            </div>
            <div>
                <label for="currency" class="crm-label">Currency</label>
                <input id="currency" name="currency" maxlength="3" value="{{ old('currency', $deal->currency ?? 'IDR') }}" required class="crm-field">
            </div>
            <div>
                <label for="stage" class="crm-label">Stage</label>
                <select id="stage" name="stage" class="crm-field">
                    @foreach ($stages as $value => $label)
                        <option value="{{ $value }}" @selected(old('stage', $deal->stage) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="probability" class="crm-label">Probability</label>
                <input id="probability" name="probability" type="number" min="0" max="100" value="{{ old('probability', $deal->probability ?? 0) }}" class="crm-field">
            </div>
            <div>
                <label for="expected_close_date" class="crm-label">Expected close</label>
                <input id="expected_close_date" name="expected_close_date" type="date" value="{{ old('expected_close_date', $deal->expected_close_date?->format('Y-m-d')) }}" class="crm-field">
            </div>
            <div>
                <label for="next_follow_up_at" class="crm-label">Next follow-up</label>
                <input id="next_follow_up_at" name="next_follow_up_at" type="datetime-local" value="{{ old('next_follow_up_at', $deal->next_follow_up_at?->format('Y-m-d\TH:i')) }}" class="crm-field">
            </div>
            <div class="md:col-span-2">
                <label for="description" class="crm-label">Description</label>
                <textarea id="description" name="description" rows="5" class="crm-field">{{ old('description', $deal->description) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label for="lost_reason" class="crm-label">Lost reason</label>
                <textarea id="lost_reason" name="lost_reason" rows="2" class="crm-field">{{ old('lost_reason', $deal->lost_reason) }}</textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ $editing ? route('deals.show', $deal) : route('deals.index') }}" class="crm-button-secondary">Cancel</a>
            <button class="crm-button-primary">{{ $editing ? 'Save Deal' : 'Create Deal' }}</button>
        </div>
    </form>
</x-app-layout>
