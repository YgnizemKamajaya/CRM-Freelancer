@props([
    'action',
    'method' => 'DELETE',
    'label' => 'Delete',
    'title' => 'Confirm action',
    'body' => 'This action cannot be undone.',
    'confirmLabel' => 'Confirm',
    'buttonClass' => 'crm-button-danger',
    'hideTrigger' => false,
])

@php($modalName = 'confirm-action-'.md5($action.$label.$title))

<div>
    @unless ($hideTrigger)
    <button type="button" class="{{ $buttonClass }}" x-data x-on:click.prevent="$dispatch('open-modal', '{{ $modalName }}')">
        {{ $label }}
    </button>
    @endunless

    <x-modal :name="$modalName" focusable>
        <form method="POST" action="{{ $action }}" class="p-6">
            @csrf
            @method($method)

            <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ $body }}</p>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="crm-button-secondary" x-on:click="$dispatch('close')">Cancel</button>
                <button class="crm-button-danger">{{ $confirmLabel }}</button>
            </div>
        </form>
    </x-modal>
</div>
