@props(['status'])
@php
    $key = str($status)->snake()->toString();
    $classes = [
        'paid' => 'bg-green-50 text-green-700',
        'completed' => 'bg-green-50 text-green-700',
        'won' => 'bg-green-50 text-green-700',
        'sent' => 'bg-sky-50 text-sky-700',
        'in_progress' => 'bg-sky-50 text-sky-700',
        'active_client' => 'bg-sky-50 text-sky-700',
        'partially_paid' => 'bg-amber-50 text-amber-700',
        'pending' => 'bg-amber-50 text-amber-700',
        'waiting_client' => 'bg-amber-50 text-amber-700',
        'overdue' => 'bg-rose-50 text-rose-700',
        'lost' => 'bg-rose-50 text-rose-700',
        'cancelled' => 'bg-rose-50 text-rose-700',
        'urgent' => 'bg-rose-50 text-rose-700',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium '.($classes[$key] ?? 'bg-gray-100 text-gray-700')]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ str_replace('_', ' ', str($status)->title()) }}
</span>
