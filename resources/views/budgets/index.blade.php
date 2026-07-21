<?php $money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'); ?>
<x-app-layout>
    <x-slot:title>Budgets</x-slot:title>

    <div class="space-y-5">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <p class="crm-eyebrow">Finance</p>
                <h2 class="text-2xl font-semibold" style="color: var(--crm-heading);">Budget</h2>
                <p class="mt-1 text-sm" style="color: var(--crm-muted);">Batas pengeluaran per kategori tiap bulan.</p>
            </div>
            <a href="{{ route('budgets.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Budget baru</a>
        </div>

        @if ($budgets->count())
            <div class="crm-card overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase" style="background: var(--crm-surface-soft); color: var(--crm-subtle);">
                        <tr>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3 text-right">Limit bulanan</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($budgets as $budget)
                            <tr class="border-t" style="border-color: var(--crm-line);">
                                <td class="px-4 py-3 font-medium" style="color: var(--crm-text);">{{ $budget->category }}</td>
                                <td class="px-4 py-3 text-right" style="color: var(--crm-muted);">{{ $money($budget->monthly_limit) }}</td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('budgets.edit', $budget) }}" class="font-medium" style="color: var(--crm-heading);">Edit</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state title="Belum ada budget" text="Tetapkan batas pengeluaran untuk kategori yang sering kamu pakai." action="Budget baru" :href="route('budgets.create')" />
        @endif
    </div>
</x-app-layout>
