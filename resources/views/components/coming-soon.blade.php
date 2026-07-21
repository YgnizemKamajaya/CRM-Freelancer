@props([
    'icon' => 'document',
    'title',
    'phase',
    'description',
    'features' => [],
])

<div class="crm-card p-6 md:p-10">
    <div class="mx-auto flex max-w-2xl flex-col items-center gap-4 text-center">
        <div class="rounded-xl p-3" style="background: var(--crm-sidebar-soft); color: var(--crm-heading);">
            <x-icon :name="$icon" class="h-7 w-7" />
        </div>

        <span class="crm-phase-badge">
            <x-icon name="clock" class="h-3.5 w-3.5" />
            {{ $phase }}
        </span>

        <h2 class="text-2xl font-semibold" style="color: var(--crm-heading);">{{ $title }}</h2>
        <p class="text-sm" style="color: var(--crm-muted);">{{ $description }}</p>

        @if (count($features))
            <ul class="mt-2 grid w-full gap-2 text-left sm:grid-cols-2">
                @foreach ($features as $feature)
                    <li class="crm-card flex items-start gap-2 p-3 text-sm" style="color: var(--crm-text);">
                        <span style="color: var(--crm-subtle);"><x-icon name="check" class="mt-0.5 h-4 w-4 flex-none" /></span>
                        <span>{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
