@php
    $money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.');
    $moneyLabels = ['Revenue in range', 'Unpaid invoice total', 'Open pipeline value'];
@endphp
<x-app-layout>
    <x-slot:title>Reports</x-slot:title>

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Reports</h2>
            <p class="mt-1 text-sm text-gray-500">Compact business health readout for portfolio-ready MVP.</p>
        </div>

        <form method="GET" class="crm-card grid gap-3 p-4 md:grid-cols-[1fr_1fr_auto]">
            <div>
                <label for="from" class="crm-label">From</label>
                <input id="from" name="from" type="date" value="{{ request('from', $from->format('Y-m-d')) }}" class="crm-field">
            </div>
            <div>
                <label for="to" class="crm-label">To</label>
                <input id="to" name="to" type="date" value="{{ request('to', $to->format('Y-m-d')) }}" class="crm-field">
            </div>
            <div class="flex items-end">
                <button class="crm-button-secondary w-full">Apply</button>
            </div>
        </form>

        <section class="grid gap-4 md:grid-cols-4">
            @foreach ($cards as $label => $value)
                <x-stat-card :label="$label" :value="in_array($label, $moneyLabels, true) ? $money($value) : $value" icon="chart" />
            @endforeach
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            @foreach ([
                'Deal stages' => $dealStageTotals,
                'Invoice statuses' => $invoiceStatusTotals,
                'Project statuses' => $projectStatusTotals,
                'Task statuses' => $taskTotals,
            ] as $title => $items)
                <div class="crm-card p-5">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($items as $label => $count)
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium text-gray-700">{{ str_replace('_', ' ', str($label)->title()) }}</span>
                                    <span class="text-gray-500">{{ $count }}</span>
                                </div>
                                <div class="mt-1 h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-primary-600" style="width: {{ min(100, $count * 12) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No data yet.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </section>

        <section class="crm-card overflow-hidden">
            <div class="border-b border-gray-200 p-4"><h3 class="font-semibold text-gray-900">Top clients by paid revenue</h3></div>
            <div class="divide-y divide-gray-100">
                @forelse ($topClients as $client)
                    <a href="{{ route('clients.show', $client) }}" class="flex items-center justify-between p-4 hover:bg-gray-50">
                        <div>
                            <p class="font-medium text-gray-900">{{ $client->name }}</p>
                            <p class="text-sm text-gray-500">{{ $client->company_name }}</p>
                        </div>
                        <p class="font-semibold text-gray-900">{{ $money($client->paid_total) }}</p>
                    </a>
                @empty
                    <p class="p-4 text-sm text-gray-500">No paid invoices yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
