<?php $money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'); ?>
<x-app-layout>
    <x-slot:title>Transactions</x-slot:title>

    <div class="space-y-5">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <p class="crm-eyebrow">Finance</p>
                <h2 class="text-2xl font-semibold" style="color: var(--crm-heading);">Transaksi</h2>
                <p class="mt-1 text-sm" style="color: var(--crm-muted);">Semua pemasukan dan pengeluaran kamu.</p>
            </div>
            <a href="{{ route('transactions.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Transaksi baru</a>
        </div>

        <form method="GET" class="crm-card grid gap-3 p-4 md:grid-cols-[1fr_170px_160px_auto]">
            <input name="search" value="{{ request('search') }}" class="crm-field mt-0" placeholder="Cari kategori atau catatan">
            <select name="wallet_id" class="crm-field mt-0">
                <option value="">Semua wallet</option>
                @foreach ($wallets as $wallet)
                    <option value="{{ $wallet->id }}" @selected((string) request('wallet_id') === (string) $wallet->id)>{{ $wallet->name }}</option>
                @endforeach
            </select>
            <select name="type" class="crm-field mt-0">
                <option value="">Semua tipe</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="crm-button-secondary">Filter</button>
        </form>

        @if ($transactions->count())
            <div class="crm-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="text-xs uppercase" style="background: var(--crm-surface-soft); color: var(--crm-subtle);">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Kategori</th>
                                <th class="px-4 py-3">Wallet</th>
                                <th class="px-4 py-3 text-right">Jumlah</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $tx)
                                <tr class="border-t" style="border-color: var(--crm-line);">
                                    <td class="px-4 py-3" style="color: var(--crm-muted);">{{ $tx->occurred_on->format('d M Y') }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium" style="color: var(--crm-text);">{{ $tx->category ?: '—' }}</p>
                                        @if ($tx->note)<p class="text-xs" style="color: var(--crm-subtle);">{{ \Illuminate\Support\Str::limit($tx->note, 50) }}</p>@endif
                                    </td>
                                    <td class="px-4 py-3" style="color: var(--crm-muted);">{{ $tx->wallet?->name }}</td>
                                    <td class="px-4 py-3 text-right font-semibold {{ $tx->type === 'income' ? 'text-income' : 'text-expense' }}">
                                        {{ $tx->type === 'income' ? '+' : '−' }}{{ $money($tx->amount) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ $tx->invoice_payment_id ? route('invoices.edit', $tx->invoice_payment_id) : route('transactions.edit', $tx) }}" class="font-medium" style="color: var(--crm-heading);">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t p-4" style="border-color: var(--crm-line);">{{ $transactions->links() }}</div>
            </div>
        @else
            <x-empty-state title="Belum ada transaksi" text="Catat pemasukan atau pengeluaran pertamamu." action="Transaksi baru" :href="route('transactions.create')" />
        @endif
    </div>
</x-app-layout>
