@php($editing = $wallet->exists)
<x-app-layout>
    <x-slot:title>{{ $editing ? 'Edit Wallet' : 'Wallet baru' }}</x-slot:title>

    <div x-data="{}">
        @if ($editing)
            <x-confirm-action :action="route('wallets.destroy', $wallet)" label="Hapus" title="Hapus wallet?" body="Jika wallet punya transaksi, wallet akan diarsipkan, bukan dihapus." />
        @endif

        <form method="POST" action="{{ $editing ? route('wallets.update', $wallet) : route('wallets.store') }}" class="mx-auto max-w-2xl space-y-6">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="crm-card p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="name" class="crm-label">Nama wallet</label>
                        <input id="name" name="name" value="{{ old('name', $wallet->name) }}" required class="crm-field" placeholder="BCA, Cash, GoPay...">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <label for="type" class="crm-label">Tipe</label>
                        <select id="type" name="type" class="crm-field">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', $wallet->type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="currency" class="crm-label">Mata uang</label>
                        <input id="currency" name="currency" value="{{ old('currency', $wallet->currency ?: 'IDR') }}" maxlength="3" required class="crm-field">
                        <x-input-error :messages="$errors->get('currency')" class="mt-2" />
                    </div>
                    <div class="md:col-span-2">
                        <label for="opening_balance" class="crm-label">Saldo awal</label>
                        <input id="opening_balance" name="opening_balance" type="number" step="0.01" value="{{ old('opening_balance', $wallet->opening_balance) }}" class="crm-field">
                        <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                    </div>
                    @if ($editing)
                        <div class="md:col-span-2 flex items-center gap-2">
                            <input id="is_archived" name="is_archived" type="checkbox" value="1" @checked(old('is_archived', $wallet->is_archived)) class="h-4 w-4">
                            <label for="is_archived" class="text-sm" style="color: var(--crm-text);">Arsipkan wallet ini</label>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <div>
                    @if ($editing)
                        <button type="button" class="crm-button-danger"
                            x-on:click.prevent="$dispatch('open-modal', '{{ 'confirm-action-'.md5(route('wallets.destroy', $wallet).'Hapus'.'Hapus wallet?') }}')">
                            Hapus
                        </button>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('wallets.index') }}" class="crm-button-secondary">Batal</a>
                    <button class="crm-button-primary">{{ $editing ? 'Simpan' : 'Buat wallet' }}</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
