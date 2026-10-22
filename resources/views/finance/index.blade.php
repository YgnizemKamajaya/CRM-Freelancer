<?php $money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'); ?>
<x-app-layout>
    <x-slot:title>Finance</x-slot:title>

    @php
        $maxFlow = max(1, $monthly_cashflow->flatMap(fn ($m) => [$m['income'], $m['expense']])->max());
        $maxCat = max(1, $category_breakdown->max('total') ?? 1);
    @endphp

    <div class="personal-page finance-room">
        <div class="personal-hero">
            <div class="min-w-0">
                <p class="crm-eyebrow">Personal</p>
                <h2 class="text-2xl font-semibold" style="color: var(--crm-heading);">Finance</h2>
                <p class="mt-1 text-sm" style="color: var(--crm-muted);">Ringkasan keuangan bulan ini. Total saldo mencakup wallet aktif dan arsip; arsip tidak menghapus aset.</p>
            </div>
            <div class="personal-hero-side">
                <div class="personal-mascot is-finance" aria-hidden="true">
                    <span class="mascot-ear left"></span><span class="mascot-ear right"></span>
                    <span class="mascot-head"><span class="mascot-eye left"></span><span class="mascot-eye right"></span><span class="mascot-mouth"></span></span>
                    <span class="mascot-body"></span><span class="mascot-shadow"></span>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2">
                <a href="{{ route('wallets.index') }}" class="crm-button-secondary"><x-icon name="wallet" class="h-4 w-4" />Wallet</a>
                <a href="{{ route('budgets.index') }}" class="crm-button-secondary"><x-icon name="chart" class="h-4 w-4" />Budget</a>
                <a href="{{ route('transactions.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Transaksi</a>
                </div>
            </div>
        </div>

        <div class="personal-stat-grid">
            <x-stat-card label="Total saldo" :value="$money($total_balance)" icon="wallet" tone="blue" />
            <x-stat-card label="Pemasukan bulan ini" :value="$money($month_income)" icon="chart" tone="green" />
            <x-stat-card label="Pengeluaran bulan ini" :value="$money($month_expense)" icon="chart" tone="rose" />
            <x-stat-card label="Selisih bulan ini" :value="$money($month_income - $month_expense)" icon="pipeline" :tone="$month_income - $month_expense >= 0 ? 'green' : 'amber'" />
        </div>

        @if ($wallets->isEmpty())
            <div class="crm-card p-4 text-sm" style="color: var(--crm-muted);">
                Belum ada wallet. <a href="{{ route('wallets.create') }}" class="font-semibold" style="color: var(--crm-heading);">Buat wallet pertama</a> agar invoice yang dibayar bisa otomatis tercatat sebagai pemasukan.
            </div>
        @endif

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="crm-card personal-panel p-5 lg:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold" style="color: var(--crm-heading);">Cashflow 6 bulan</h3>
                    <div class="finance-chart-legend">
                        <span><span class="finance-chart-legend-dot" style="background: var(--crm-income);"></span>Masuk</span>
                        <span><span class="finance-chart-legend-dot" style="background: var(--crm-expense);"></span>Keluar</span>
                    </div>
                </div>
                <div class="finance-chart">
                    @foreach ($monthly_cashflow as $m)
                        <div class="finance-chart-col">
                            <div class="finance-chart-bars">
                                <span class="finance-bar is-income" style="height: {{ (int) round(($m['income'] / $maxFlow) * 100) }}%" title="Masuk: {{ $money($m['income']) }}"></span>
                                <span class="finance-bar is-expense" style="height: {{ (int) round(($m['expense'] / $maxFlow) * 100) }}%" title="Keluar: {{ $money($m['expense']) }}"></span>
                            </div>
                            <span class="finance-chart-label">{{ $m['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="crm-card personal-panel p-5">
                <h3 class="mb-4 text-sm font-semibold" style="color: var(--crm-heading);">Pengeluaran per kategori</h3>
                @forelse ($category_breakdown as $cat)
                    <div class="mb-3">
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span style="color: var(--crm-text);">{{ $cat['category'] }}</span>
                            <span style="color: var(--crm-muted);">{{ $money($cat['total']) }}</span>
                        </div>
                        <div class="crm-meter">
                            <div style="width: {{ (int) round(($cat['total'] / $maxCat) * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm" style="color: var(--crm-subtle);">Belum ada pengeluaran bulan ini.</p>
                @endforelse
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="crm-card personal-panel p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold" style="color: var(--crm-heading);">Budget bulan ini</h3>
                    <a href="{{ route('budgets.index') }}" class="text-xs font-semibold" style="color: var(--crm-muted);">Kelola</a>
                </div>
                @forelse ($budget_usage as $row)
                    <div class="mb-3">
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span style="color: var(--crm-text);">{{ $row['budget']->category }}</span>
                            <span style="color: var(--crm-muted);">{{ $money($row['spent']) }} / {{ $money($row['budget']->monthly_limit) }}</span>
                        </div>
                        <div class="crm-meter {{ $row['percent'] >= 100 ? 'is-over' : '' }}">
                            <div style="width: {{ $row['percent'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm" style="color: var(--crm-subtle);">Belum ada budget. <a href="{{ route('budgets.create') }}" class="font-semibold" style="color: var(--crm-heading);">Tambah budget</a>.</p>
                @endforelse
            </div>

            <div class="crm-card personal-panel p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold" style="color: var(--crm-heading);">Transaksi terbaru</h3>
                    <a href="{{ route('transactions.index') }}" class="text-xs font-semibold" style="color: var(--crm-muted);">Lihat semua</a>
                </div>
                @forelse ($recent_transactions as $tx)
                    <div class="personal-list-item">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium" style="color: var(--crm-text);">{{ $tx->category ?: ($tx->type === 'income' ? 'Pemasukan' : 'Pengeluaran') }}</p>
                            <p class="truncate text-xs" style="color: var(--crm-subtle);">{{ $tx->wallet?->name }} · {{ $tx->occurred_on->format('d M Y') }}</p>
                        </div>
                        <span class="text-sm font-semibold {{ $tx->type === 'income' ? 'text-income' : 'text-expense' }}">
                            {{ $tx->type === 'income' ? '+' : '−' }}{{ $money($tx->amount) }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm" style="color: var(--crm-subtle);">Belum ada transaksi.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
