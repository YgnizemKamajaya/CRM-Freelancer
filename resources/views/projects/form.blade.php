@php($editing = $project->exists)
<x-app-layout>
    <x-slot:title>{{ $editing ? 'Edit Project' : 'Add Project' }}</x-slot:title>

    <form method="POST" action="{{ $editing ? route('projects.update', $project) : route('projects.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="crm-card grid gap-4 p-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="crm-label">Title</label>
                <input id="title" name="title" value="{{ old('title', $project->title) }}" required class="crm-field">
            </div>
            <div>
                <label for="client_id" class="crm-label">Client</label>
                <select id="client_id" name="client_id" required class="crm-field">
                    <option value="">Choose client</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected((int) old('client_id', $project->client_id) === $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="deal_id" class="crm-label">Related deal</label>
                <select id="deal_id" name="deal_id" class="crm-field">
                    <option value="">None</option>
                    @foreach ($deals as $deal)
                        <option value="{{ $deal->id }}" @selected((int) old('deal_id', $project->deal_id) === $deal->id)>{{ $deal->title }} · {{ $deal->client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="crm-label">Status</label>
                <select id="status" name="status" class="crm-field">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $project->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="priority" class="crm-label">Priority</label>
                <select id="priority" name="priority" class="crm-field">
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}" @selected(old('priority', $project->priority) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="budget" class="crm-label">Budget</label>
                <input id="budget" name="budget" type="number" min="0" step="1000" value="{{ old('budget', $project->budget) }}" class="crm-field">
            </div>
            <div>
                <label for="currency" class="crm-label">Currency</label>
                <input id="currency" name="currency" maxlength="3" value="{{ old('currency', $project->currency ?? 'IDR') }}" class="crm-field">
            </div>
            <div>
                <label for="start_date" class="crm-label">Start date</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="crm-field">
            </div>
            <div>
                <label for="deadline" class="crm-label">Deadline</label>
                <input id="deadline" name="deadline" type="date" value="{{ old('deadline', $project->deadline?->format('Y-m-d')) }}" class="crm-field">
            </div>
            <div class="md:col-span-2">
                <label for="description" class="crm-label">Description</label>
                <textarea id="description" name="description" rows="5" class="crm-field">{{ old('description', $project->description) }}</textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ $editing ? route('projects.show', $project) : route('projects.index') }}" class="crm-button-secondary">Cancel</a>
            <button class="crm-button-primary">{{ $editing ? 'Save Project' : 'Create Project' }}</button>
        </div>
    </form>
</x-app-layout>
