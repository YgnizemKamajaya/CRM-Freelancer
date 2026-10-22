@php($editing = $goal->exists)
<x-app-layout>
    <x-slot:title>{{ $editing ? 'Edit Goal' : 'Goal baru' }}</x-slot:title>

    {{--
        Outer wrapper holds BOTH the main form AND the confirm-action modal.
        BUG FIX: x-confirm-action renders its own <form> internally.
        Placing it INSIDE the edit <form> created illegal nested forms — HTML
        spec says nested forms are invalid, and browsers would unpredictably
        submit the wrong form/action (causing goals to be deleted instead of saved).
    --}}
    <div x-data="{}">
        @if ($editing)
            {{-- Delete confirm modal: MUST live OUTSIDE the edit <form> --}}
            <x-confirm-action :hide-trigger="true"
                :action="route('goals.destroy', $goal)"
                label="Hapus"
                title="Hapus goal?"
                body="Tindakan ini tidak bisa dibatalkan." />
        @endif

        <form method="POST"
              action="{{ $editing ? route('goals.update', $goal) : route('goals.store') }}"
              enctype="multipart/form-data"
              class="mx-auto max-w-xl space-y-6"
              x-data="{ type: '{{ old('type', $goal->type) }}', auto: {{ old('auto_track', $goal->auto_track) ? 'true' : 'false' }} }">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="crm-card p-5 space-y-4">
                <div>
                    <label for="title" class="crm-label">Judul goal</label>
                    <input id="title" name="title" value="{{ old('title', $goal->title) }}" required class="crm-field" placeholder="Dana darurat, Baca 12 buku...">
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="type" class="crm-label">Tipe</label>
                        <select id="type" name="type" x-model="type" class="crm-field">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="metric_label" class="crm-label">Satuan</label>
                        <input id="metric_label" name="metric_label" value="{{ old('metric_label', $goal->metric_label) }}" class="crm-field" x-bind:placeholder="type === 'financial' ? 'IDR' : 'buku, km, ...'">
                    </div>
                    <div>
                        <label for="target_amount" class="crm-label">Target</label>
                        <input id="target_amount" name="target_amount" type="number" step="any" min="0" value="{{ old('target_amount', $goal->target_amount) }}" required class="crm-field">
                        <x-input-error :messages="$errors->get('target_amount')" class="mt-2" />
                    </div>
                    <div>
                        <label for="due_date" class="crm-label">Target tanggal (opsional)</label>
                        <input id="due_date" name="due_date" type="date" value="{{ old('due_date', optional($goal->due_date)->format('Y-m-d')) }}" class="crm-field">
                    </div>
                </div>

                {{-- Banner --}}
                <div class="space-y-3 rounded-lg p-3" style="background: var(--crm-surface-soft);">
                    <p class="crm-label" style="margin-bottom: 0;">Banner</p>

                    @if ($goal->hasUploadedBanner())
                        <div class="goal-banner goal-banner--tall" style="background-image:url({{ route('goals.banner', $goal) }})">
                            <div class="goal-banner-content"><span class="goal-banner-title">{{ $goal->title ?: 'Preview' }}</span></div>
                        </div>
                        <label class="flex items-center gap-2 text-sm" style="color: var(--crm-text);">
                            <input type="checkbox" name="remove_banner" value="1" class="h-4 w-4">
                            Hapus gambar, kembali ke gradient
                        </label>
                    @endif

                    <div>
                        <p class="text-xs" style="color: var(--crm-muted);">Pilih gradient</p>
                        <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-6">
                            @foreach ($gradients as $key => $label)
                                <label>
                                    <input type="radio" name="banner_gradient" value="{{ $key }}" class="goal-radio sr-only" @checked(old('banner_gradient', $goal->banner_gradient ?: 'slate') === $key)>
                                    <span class="goal-swatch goal-banner--{{ $key }} block" title="{{ $label }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="banner_image" class="text-xs" style="color: var(--crm-muted);">Atau upload gambar sendiri (maks 8 MB) — menimpa gradient</label>
                        <input id="banner_image" name="banner_image" type="file" accept="image/*" class="crm-field">
                        <x-input-error :messages="$errors->get('banner_image')" class="mt-2" />
                    </div>

                    <label class="flex items-center gap-2 text-sm" style="color: var(--crm-text);">
                        <input type="hidden" name="is_pinned" value="0">
                        <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $goal->is_pinned)) class="h-4 w-4">
                        Pin sebagai mimpi utama (tampil besar di atas)
                    </label>
                </div>

                {{-- Financial-only options --}}
                <div x-show="type === 'financial'" x-cloak class="space-y-3 rounded-lg p-3" style="background: var(--crm-surface-soft);">
                    <label class="flex items-center gap-2 text-sm" style="color: var(--crm-text);">
                        <input type="hidden" name="auto_track" value="0">
                        <input type="checkbox" name="auto_track" value="1" x-model="auto" @checked(old('auto_track', $goal->auto_track)) class="h-4 w-4">
                        Lacak otomatis dari saldo wallet
                    </label>
                    <div x-show="auto">
                        <label for="wallet_id" class="crm-label">Sumber saldo</label>
                        <select id="wallet_id" name="wallet_id" class="crm-field">
                            <option value="">Total semua wallet</option>
                            @foreach ($wallets as $wallet)
                                <option value="{{ $wallet->id }}" @selected((string) old('wallet_id', $goal->wallet_id) === (string) $wallet->id)>{{ $wallet->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div x-show="!(type === 'financial' && auto)">
                    <label for="current_amount" class="crm-label">Progress saat ini</label>
                    <input id="current_amount" name="current_amount" type="number" step="any" min="0" value="{{ old('current_amount', $goal->current_amount) }}" class="crm-field">
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <div>
                    @if ($editing)
                        {{--
                            Trigger-only button: dispatches Alpine event to open the modal
                            whose <form> is rendered OUTSIDE this <form> (above).
                            Hash must match confirm-action.blade.php's modalName formula:
                            md5($action . $label . $title)
                        --}}
                        <button type="button" class="crm-button-danger"
                            x-on:click.prevent="$dispatch('open-modal', '{{ 'confirm-action-'.md5(route('goals.destroy', $goal).'Hapus'.'Hapus goal?') }}')">
                            Hapus
                        </button>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('goals.index') }}" class="crm-button-secondary">Batal</a>
                    <button class="crm-button-primary">{{ $editing ? 'Simpan' : 'Buat goal' }}</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
