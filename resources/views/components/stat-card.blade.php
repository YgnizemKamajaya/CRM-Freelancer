@props(['label', 'value', 'icon' => 'chart', 'tone' => 'blue'])
@php
    $tones = [
        'blue' => 'bg-primary-50 text-primary-700',
        'green' => 'bg-green-50 text-green-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'rose' => 'bg-rose-50 text-rose-700',
        'gray' => 'bg-gray-100 text-gray-700',
    ];
@endphp
<article class="crm-card p-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $value }}</p>
        </div>
        <span class="rounded-lg p-2 {{ $tones[$tone] ?? $tones['blue'] }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
    </div>
</article>
