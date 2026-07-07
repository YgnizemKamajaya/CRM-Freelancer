@props(['title', 'text', 'action' => null, 'href' => null])
<div class="crm-card flex flex-col items-center justify-center px-6 py-12 text-center">
    <div class="mb-4 rounded-lg bg-gray-100 p-4 text-gray-400">
        <x-icon name="document" class="h-8 w-8" />
    </div>
    <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
    <p class="mt-2 max-w-md text-sm text-gray-500">{{ $text }}</p>
    @if ($action && $href)
        <a href="{{ $href }}" class="crm-button-primary mt-5">{{ $action }}</a>
    @endif
</div>
