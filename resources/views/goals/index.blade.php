<?php $money = fn ($value) => number_format((float) $value, 0, ',', '.'); ?>
<x-app-layout>
    <x-slot:title>Goals</x-slot:title>

    @php
        $bannerStyle = fn ($goal) => $goal->hasUploadedBanner()
            ? 'background-image:url('.route('goals.banner', $goal).')'
            : '';
        $bannerClass = fn ($goal) => $goal->hasUploadedBanner() ? '' : 'goal-banner--'.$goal->gradientKey();
    @endphp

    <div class="personal-page goals-room">
        <div class="personal-hero">
            <div class="min-w-0">
                <p class="crm-eyebrow">Personal</p>
                <h2 class="text-2xl font-semibold" style="color: var(--crm-heading);">Goals</h2>
                <p class="mt-1 text-sm" style="color: var(--crm-muted);">Target pribadi, finansial, dan mimpi — dilacak dalam satu tempat.</p>
            </div>
            <div class="personal-hero-side">
                <div class="personal-mascot is-goals" aria-hidden="true">
                    <span class="mascot-ear left"></span><span class="mascot-ear right"></span>
                    <span class="mascot-head"><span class="mascot-eye left"></span><span class="mascot-eye right"></span><span class="mascot-mouth"></span></span>
                    <span class="mascot-body"></span><span class="mascot-shadow"></span>
                </div>
                <a href="{{ route('goals.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Goal baru</a>
            </div>
        </div>

        {{-- Pinned dream banners --}}
        @if ($pinnedGoals->count())
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($pinnedGoals as $goal)
                    @php $isFin = $goal->type === 'financial'; $prefix = $isFin ? ($goal->metric_label ?: 'IDR').' ' : ''; @endphp
                    <div class="goal-banner goal-banner--tall {{ $bannerClass($goal) }}" style="{{ $bannerStyle($goal) }}">
                        <div class="goal-banner-content">
                            <div class="flex items-start justify-between gap-2">
                                <span class="goal-banner-title text-lg">{{ $goal->title }}</span>
                                <form method="POST" action="{{ route('goals.pin', $goal) }}">
                                    @csrf
                                    <button class="goal-pin-btn is-pinned" title="Lepas pin"><x-icon name="pin" class="h-4 w-4" /></button>
                                </form>
                            </div>
                            <div class="mt-2">
                                <div class="mb-1 flex items-baseline justify-between text-sm">
                                    <span class="font-semibold">{{ $prefix }}{{ $money($goal->live_amount) }}</span>
                                    <span style="opacity: 0.85;">/ {{ $prefix }}{{ $money($goal->target_amount) }}</span>
                                </div>
                                <div class="crm-meter" style="background: oklch(100% 0 0 / 0.3);">
                                    <div style="width: {{ $goal->live_percent }}%; background: oklch(98% 0 0);"></div>
                                </div>
                                <p class="mt-1 text-xs" style="opacity: 0.9;">{{ $goal->live_percent }}%@if($goal->live_percent >= 100) · tercapai 🎉 @endif @if($goal->due_date) · target {{ $goal->due_date->format('d M Y') }}@endif</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($goals->count())
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($goals as $goal)
                    @php $isFin = $goal->type === 'financial'; $prefix = $isFin ? ($goal->metric_label ?: 'IDR').' ' : ''; @endphp
                    <div class="crm-card personal-goal-card overflow-hidden">
                        <div class="goal-banner {{ $bannerClass($goal) }}" style="{{ $bannerStyle($goal) }}">
                            <div class="goal-banner-content">
                                <div class="flex items-start justify-between">
                                    <span class="crm-phase-badge" style="background: oklch(100% 0 0 / 0.25); color: oklch(98% 0 0);">{{ \App\Models\Goal::TYPES[$goal->type] ?? $goal->type }}</span>
                                    <form method="POST" action="{{ route('goals.pin', $goal) }}">
                                        @csrf
                                        <button class="goal-pin-btn {{ $goal->is_pinned ? 'is-pinned' : '' }}" title="{{ $goal->is_pinned ? 'Lepas pin' : 'Pin' }}"><x-icon name="pin" class="h-4 w-4" /></button>
                                    </form>
                                </div>
                                <span class="goal-banner-title">{{ $goal->title }}</span>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="mb-1 flex items-baseline justify-between">
                                <span class="text-lg font-semibold" style="color: var(--crm-heading);">{{ $prefix }}{{ $money($goal->live_amount) }}</span>
                                <span class="text-sm" style="color: var(--crm-subtle);">/ {{ $prefix }}{{ $money($goal->target_amount) }} @if(!$isFin && $goal->metric_label) {{ $goal->metric_label }}@endif</span>
                            </div>
                            <div class="crm-meter {{ $goal->live_percent >= 100 ? 'is-over' : '' }}">
                                <div style="width: {{ $goal->live_percent }}%"></div>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs" style="color: var(--crm-subtle);">
                                <span>{{ $goal->live_percent }}%@if($goal->live_percent >= 100) · tercapai 🎉 @endif</span>
                                @if ($goal->due_date)<span>target {{ $goal->due_date->format('d M Y') }}</span>@endif
                            </div>

                            <div class="mt-4 flex items-center gap-2">
                                @if ($goal->auto_track && $isFin)
                                    <p class="text-xs text-income">⟲ Otomatis dari {{ $goal->wallet?->name ?? 'total saldo' }}</p>
                                @else
                                    <form method="POST" action="{{ route('goals.adjust', $goal) }}" class="flex items-center gap-2">
                                        @csrf
                                        <input name="delta" type="number" step="any" class="crm-field mt-0" style="width: 5.5rem;" placeholder="±" required>
                                        <button class="crm-button-secondary">Update</button>
                                    </form>
                                @endif
                                <a href="{{ route('goals.edit', $goal) }}" class="crm-icon-button ml-auto" style="min-height: 2rem; min-width: 2rem;" title="Edit"><x-icon name="pencil" class="h-4 w-4" /></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-empty-state title="Belum ada goal" text="Tetapkan target pertamamu — dana darurat, dana liburan, atau mimpi besarmu." action="Goal baru" :href="route('goals.create')" />
        @endif
    </div>
</x-app-layout>
