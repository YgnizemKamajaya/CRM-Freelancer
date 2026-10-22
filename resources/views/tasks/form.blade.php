@php($editing = $task->exists)
<x-app-layout>
    <x-slot:title>{{ $editing ? 'Edit Task' : 'Add Task' }}</x-slot:title>

    @if ($editing)
        <div class="mb-4 flex justify-end"><x-confirm-action :action="route('tasks.destroy', $task)" label="Delete task" title="Delete task?" body="This task will be removed." /></div>
    @endif
    <form method="POST" action="{{ $editing ? route('tasks.update', $task) : route('tasks.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="crm-card grid gap-4 p-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="crm-label">Title</label>
                <input id="title" name="title" value="{{ old('title', $task->title) }}" required class="crm-field">
            </div>
            <div>
                <label for="client_id" class="crm-label">Client</label>
                <select id="client_id" name="client_id" class="crm-field">
                    <option value="">None</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected((int) old('client_id', $task->client_id) === $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="deal_id" class="crm-label">Deal</label>
                <select id="deal_id" name="deal_id" class="crm-field">
                    <option value="">None</option>
                    @foreach ($deals as $deal)
                        <option value="{{ $deal->id }}" @selected((int) old('deal_id', $task->deal_id) === $deal->id)>{{ $deal->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="project_id" class="crm-label">Project</label>
                <select id="project_id" name="project_id" class="crm-field">
                    <option value="">None</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @selected((int) old('project_id', $task->project_id) === $project->id)>{{ $project->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="invoice_id" class="crm-label">Invoice</label>
                <select id="invoice_id" name="invoice_id" class="crm-field">
                    <option value="">None</option>
                    @foreach ($invoices as $invoice)
                        <option value="{{ $invoice->id }}" @selected((int) old('invoice_id', $task->invoice_id) === $invoice->id)>{{ $invoice->invoice_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="type" class="crm-label">Type</label>
                <select id="type" name="type" class="crm-field">
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $task->type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="priority" class="crm-label">Priority</label>
                <select id="priority" name="priority" class="crm-field">
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}" @selected(old('priority', $task->priority) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="crm-label">Status</label>
                <select id="status" name="status" class="crm-field">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $task->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="due_at" class="crm-label">Due at</label>
                <input id="due_at" name="due_at" type="datetime-local" value="{{ old('due_at', $task->due_at?->format('Y-m-d\TH:i')) }}" class="crm-field">
            </div>
            <div class="md:col-span-2">
                <label for="description" class="crm-label">Description</label>
                <textarea id="description" name="description" rows="5" class="crm-field">{{ old('description', $task->description) }}</textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('tasks.index') }}" class="crm-button-secondary">Cancel</a>
            <button class="crm-button-primary">{{ $editing ? 'Save Task' : 'Create Task' }}</button>
        </div>
    </form>
</x-app-layout>
