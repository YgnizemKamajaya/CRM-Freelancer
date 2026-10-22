@php
    $money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.');
@endphp
<x-app-layout>
    <x-slot:title>Dashboard</x-slot:title>

    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <p class="crm-eyebrow">CRM</p>
                <h1 class="text-2xl font-semibold" style="color: var(--crm-heading);">Ringkasan CRM</h1>
                <p class="mt-1 text-sm" style="color: var(--crm-muted);">Metrik lengkap penjualan, proyek, dan invoice.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="crm-button-secondary">← Beranda</a>
        </div>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Total clients" :value="$metrics['total_clients']" icon="users" />
            <x-stat-card label="Active deals" :value="$metrics['active_deals']" icon="pipeline" tone="amber" />
            <x-stat-card label="Active projects" :value="$metrics['active_projects']" icon="briefcase" tone="green" />
            <x-stat-card label="Due today" :value="$metrics['tasks_due_today']" icon="check" tone="rose" />
            <x-stat-card label="Pipeline value" :value="$money($metrics['pipeline_value'])" icon="chart" />
            <x-stat-card label="Expected revenue" :value="$money($metrics['expected_revenue'])" icon="chart" tone="green" />
            <x-stat-card label="Unpaid invoices" :value="$money($metrics['unpaid_invoice_amount'])" icon="document" tone="amber" />
            <x-stat-card label="Overdue invoices" :value="$money($metrics['overdue_invoice_amount'])" icon="document" tone="rose" />
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <div class="crm-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-200 p-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Active deals by stage</h2>
                        <p class="text-sm text-gray-500">Pipeline scan, no drag required.</p>
                    </div>
                    <a href="{{ route('deals.create') }}" class="crm-button-primary">Add Deal</a>
                </div>
                <div class="grid gap-3 p-4 md:grid-cols-3 xl:grid-cols-6">
                    @foreach (\App\Models\Deal::STAGES as $stage => $label)
                        @php($stageData = $active_deals_by_stage->get($stage))
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <p class="text-xs font-medium text-gray-500">{{ $label }}</p>
                            <p class="mt-2 text-xl font-semibold text-gray-900">{{ $stageData->total ?? 0 }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $money($stageData->value ?? 0) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="crm-card p-4">
                <h2 class="text-lg font-semibold text-gray-900">Quick actions</h2>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <a href="{{ route('clients.create') }}" class="crm-button-secondary">Client</a>
                    <a href="{{ route('deals.create') }}" class="crm-button-primary">Deal</a>
                    <a href="{{ route('projects.create') }}" class="crm-button-secondary">Project</a>
                    <a href="{{ route('tasks.create') }}" class="crm-button-secondary">Task</a>
                    <a href="{{ route('invoices.create') }}" class="crm-button-secondary col-span-2">Invoice</a>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="crm-card overflow-hidden">
                <div class="border-b border-gray-200 p-4">
                    <h2 class="text-lg font-semibold text-gray-900">Today follow-ups</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($today_tasks as $task)
                        <div class="flex items-start justify-between gap-4 p-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ $task->title }}</p>
                                <p class="text-sm text-gray-500">{{ $task->client?->name ?? 'No client' }} · {{ $task->due_at?->format('H:i') }}</p>
                            </div>
                            <x-status-badge :status="$task->priority" />
                        </div>
                    @empty
                        <div class="p-4 text-sm text-gray-500">No follow-ups due today.</div>
                    @endforelse
                </div>
            </div>

            <div class="crm-card overflow-hidden">
                <div class="border-b border-gray-200 p-4">
                    <h2 class="text-lg font-semibold text-gray-900">Unpaid invoices</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($unpaid_invoices as $invoice)
                        <a href="{{ route('invoices.show', $invoice) }}" class="flex items-start justify-between gap-4 p-4 hover:bg-gray-50">
                            <div>
                                <p class="font-medium text-gray-900">{{ $invoice->invoice_number }}</p>
                                <p class="text-sm text-gray-500">{{ $invoice->client->name }} · due {{ $invoice->due_date?->format('d M Y') ?? 'not set' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ $money($invoice->balance) }}</p>
                                <x-status-badge :status="$invoice->status" />
                            </div>
                        </a>
                    @empty
                        <div class="p-4 text-sm text-gray-500">No unpaid invoices.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="crm-card overflow-hidden">
                <div class="border-b border-gray-200 p-4">
                    <h2 class="text-lg font-semibold text-gray-900">Recent clients</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($recent_clients as $client)
                        <a href="{{ route('clients.show', $client) }}" class="flex items-center justify-between p-4 hover:bg-gray-50">
                            <div>
                                <p class="font-medium text-gray-900">{{ $client->name }}</p>
                                <p class="text-sm text-gray-500">{{ $client->company_name ?? $client->email }}</p>
                            </div>
                            <x-status-badge :status="$client->status" />
                        </a>
                    @empty
                        <div class="p-4 text-sm text-gray-500">No clients yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="crm-card overflow-hidden">
                <div class="border-b border-gray-200 p-4">
                    <h2 class="text-lg font-semibold text-gray-900">Recent activity</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($recent_activities as $activity)
                        <div class="p-4">
                            <p class="text-sm font-medium text-gray-900">{{ $activity->client?->name ?? 'General' }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ $activity->body }}</p>
                            <p class="mt-2 text-xs text-gray-500">{{ $activity->activity_at?->diffForHumans() }}</p>
                        </div>
                    @empty
                        <div class="p-4 text-sm text-gray-500">No activity yet.</div>
                    @endforelse
                </div>
            </div>
        </section>
        <section class="grid gap-6 lg:grid-cols-2">
            @include('partials.related-tasks', ['relatedTasks' => $overdue_tasks, 'taskContext' => [], 'tasksHeading' => 'Overdue tasks'])
            <div class="crm-card p-5">
                <h3 class="font-semibold text-gray-900">Pendapatan invoice lunas per bulan</h3>
                @forelse ($monthly_revenue as $month => $revenue)
                    <div class="mt-3 flex justify-between gap-3 text-sm"><span>{{ $month }}</span><span>{{ $money($revenue) }}</span></div>
                @empty
                    <p class="mt-3 text-sm text-gray-500">Belum ada invoice lunas.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
