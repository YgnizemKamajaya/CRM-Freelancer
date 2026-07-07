@php
    $clientId = $clientId ?? null;
    $dealId = $dealId ?? null;
    $projectId = $projectId ?? null;
    $invoiceId = $invoiceId ?? null;
    $activityTypes = $activityTypes ?? [];
@endphp
<form method="POST" action="{{ route('activities.store') }}" class="crm-card space-y-4 p-4">
    @csrf
    <input type="hidden" name="client_id" value="{{ $clientId }}">
    <input type="hidden" name="deal_id" value="{{ $dealId }}">
    <input type="hidden" name="project_id" value="{{ $projectId }}">
    <input type="hidden" name="invoice_id" value="{{ $invoiceId }}">
    <div>
        <label for="activity_type" class="crm-label">Activity type</label>
        <select id="activity_type" name="type" class="crm-field">
            @foreach ($activityTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="activity_body" class="crm-label">Note</label>
        <textarea id="activity_body" name="body" rows="4" required class="crm-field" placeholder="Log call notes, WhatsApp context, proposal changes, or payment updates.">{{ old('body') }}</textarea>
        <x-input-error :messages="$errors->get('body')" class="mt-2" />
    </div>
    <div>
        <label for="activity_at" class="crm-label">Activity date</label>
        <input id="activity_at" type="datetime-local" name="activity_at" value="{{ old('activity_at', now()->format('Y-m-d\TH:i')) }}" class="crm-field">
    </div>
    <button class="crm-button-primary">Save activity</button>
</form>
