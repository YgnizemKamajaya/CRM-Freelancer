<?php $money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'); ?>
<x-app-layout>
    <x-slot:title>Wallets</x-slot:title>

    <div class="space-y-5">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <p class="crm-eyebrow">Finance</p>
                <h2 class="text-2xl font-semibold" style="color: var(--crm-heading);">Wallets</h2>
                <p class="mt-1 text-sm" style="color: var(--crm-muted);">Cash, bank, e-wallet — saldo dihitung dari saldo awal + transaksi.</p>
            </div>
            <a href="{{ route('wallets.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Wallet baru</a>
        </div>

        @if ($wallets->count())
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($wallets as $wallet)
                    <div class="crm-card p-5 {{ $wallet->is_archived ? 'opacity-60' : '' }}">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide" style="color: var(--crm-subtle);">{{ \App\Models\Wallet::TYPES[$wallet->type] ?? $wallet->type }}</p>
                                <p class="mt-1 text-base font-semibold" style="color: var(--crm-heading);">{{ $wallet->name }}</p>
                            </div>
                            <span class="rounded-lg p-2" style="background: var(--crm-sidebar-soft); color: var(--crm-heading);"><x-icon name="wallet" class="h-5 w-5" /></span>
                        </div>
                        <p class="mt-4 text-2xl font-semibold" style="color: var(--crm-heading);">{{ $money($wallet->balance) }}</p>
                        <p class="text-xs" style="color: var(--crm-subtle);">Saldo awal {{ $money($wallet->opening_balance) }}</p>
                        <div class="mt-4 flex items-center gap-3 text-sm">
                            <a href="{{ route('wallets.edit', $wallet) }}" class="font-medium" style="color: var(--crm-heading);">Edit</a>
                            <a href="{{ route('transactions.index', ['wallet_id' => $wallet->id]) }}" class="font-medium" style="color: var(--crm-muted);">Transaksi</a>
                            @if ($wallet->is_archived)
                                <span class="ml-auto text-xs" style="color: var(--crm-subtle);">Arsip</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-empty-state title="Belum ada wallet" text="Buat wallet pertama untuk mulai mencatat keuangan." action="Wallet baru" :href="route('wallets.create')" />
        @endif
    </div>
</x-app-layout>
