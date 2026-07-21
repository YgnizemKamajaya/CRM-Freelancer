<?php $money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'); ?>
<x-app-layout>
    <x-slot:title>Command Center</x-slot:title>

    @php
        $maxFlow = max(1, $cashflow->flatMap(fn ($m) => [$m['income'], $m['expense']])->max());
        $alertTone = ['red' => 'home-alert--red', 'orange' => 'home-alert--orange', 'yellow' => 'home-alert--yellow'];
        $focusPercent = $checklist_total ? (int) round(($checklist_done / $checklist_total) * 100) : 0;
    @endphp

    <div class="command-center">
        <header class="command-hero">
            <div class="command-hero-bg" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span>
            </div>

            <div class="command-hero-main">
                <div class="personal-mascot is-command" aria-hidden="true">
                    <span class="mascot-ear left"></span>
                    <span class="mascot-ear right"></span>
                    <span class="mascot-head">
                        <span class="mascot-eye left"></span>
                        <span class="mascot-eye right"></span>
                        <span class="mascot-mouth"></span>
                    </span>
                    <span class="mascot-body"></span>
                    <span class="mascot-shadow"></span>
                </div>

                <div class="min-w-0">
                    <p class="command-date">{{ $today_label }}</p>
                    <h1>{{ $greeting }}.</h1>
                    <p class="command-copy">Satu pusat untuk kerja client, uang, fokus, catatan, dan file pribadi.</p>
                </div>
            </div>

            <button type="button" class="command-search" @click="commandOpen = true">
                <x-icon name="search" class="h-5 w-5" />
                <span>Search or command</span>
                <kbd>Ctrl K</kbd>
            </button>

            <div class="command-actions" aria-label="Quick actions">
                <a wire:navigate href="{{ route('today.index') }}"><x-icon name="check" class="h-4 w-4" /><span>Hari ini</span></a>
                <a wire:navigate href="{{ route('transactions.create') }}"><x-icon name="wallet" class="h-4 w-4" /><span>Transaksi</span></a>
                <a wire:navigate href="{{ route('goals.create') }}"><x-icon name="chart" class="h-4 w-4" /><span>Goal</span></a>
                <a wire:navigate href="{{ route('journal.create') }}"><x-icon name="book" class="h-4 w-4" /><span>Journal</span></a>
                <a wire:navigate href="{{ route('vault.index') }}"><x-icon name="document" class="h-4 w-4" /><span>Vault</span></a>
                <a wire:navigate href="{{ route('workspace.index') }}"><x-icon name="board" class="h-4 w-4" /><span>Workspace</span></a>
            </div>
        </header>

        <div class="command-grid">
            <section class="command-panel command-now">
                <div>
                    <p class="command-panel-kicker">Now</p>
                    <p class="command-clock">{{ now()->format('H:i') }}</p>
                    <p class="command-muted">{{ $checklist_done }}/{{ $checklist_total }} fokus selesai</p>
                </div>
                <div class="command-ring" style="--value: {{ $focusPercent }};">
                    <span>{{ $focusPercent }}%</span>
                </div>
            </section>

            <livewire:home-focus-list />

            <section class="command-panel command-finance">
                <div class="command-panel-head">
                    <div>
                        <p class="command-panel-kicker">Finance</p>
                        <h2>{{ $money($total_balance) }}</h2>
                    </div>
                    <a wire:navigate href="{{ route('finance.index') }}">Open</a>
                </div>
                <div class="command-split">
                    <span><strong>{{ $money($month_income) }}</strong> masuk</span>
                    <span><strong>{{ $money($month_expense) }}</strong> keluar</span>
                </div>
                <div class="home-cashflow">
                    @foreach ($cashflow as $m)
                        <div class="home-cashflow-col">
                            <div class="home-cashflow-bars">
                                <span class="finance-bar is-income" style="height: {{ (int) round(($m['income'] / $maxFlow) * 100) }}%"></span>
                                <span class="finance-bar is-expense" style="height: {{ (int) round(($m['expense'] / $maxFlow) * 100) }}%"></span>
                            </div>
                            <span class="home-cashflow-label">{{ $m['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="command-panel command-attention">
                <div class="command-panel-head">
                    <div>
                        <p class="command-panel-kicker">Attention</p>
                        <h2>{{ $alerts->count() }} item</h2>
                    </div>
                </div>
                @if ($alerts->count())
                    <ul class="home-alerts">
                        @foreach ($alerts->take(4) as $alert)
                            <li>
                                <a wire:navigate href="{{ $alert['href'] }}" class="home-alert {{ $alertTone[$alert['level']] }}">
                                    <span class="home-alert-dot"></span>
                                    <x-icon :name="$alert['icon']" class="h-4 w-4" />
                                    <span>{{ $alert['text'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="home-clear">
                        <span class="home-clear-mark"><x-icon name="check" class="h-5 w-5" /></span>
                        <p>Semua beres hari ini.</p>
                    </div>
                @endif
            </section>

            <section class="command-panel command-goal">
                @if ($featured_goal)
                    @php
                        $g = $featured_goal;
                        $isFin = $g->type === 'financial';
                        $prefix = $isFin ? ($g->metric_label ?: 'IDR').' ' : '';
                        $bg = $g->hasUploadedBanner() ? 'background-image:url('.route('goals.banner', $g).')' : '';
                        $bgClass = $g->hasUploadedBanner() ? '' : 'goal-banner--'.$g->gradientKey();
                    @endphp
                    <div class="goal-banner goal-banner--tall {{ $bgClass }}" style="{{ $bg }}">
                        <div class="goal-banner-content">
                            <div class="flex items-center justify-between">
                                <span class="home-dream-eyebrow">{{ $g->is_pinned ? 'Mimpi utama' : 'Goal teratas' }}</span>
                                <a wire:navigate href="{{ route('goals.index') }}" style="color: oklch(98% 0 0); opacity: 0.9;"><x-icon name="chart" class="h-4 w-4" /></a>
                            </div>
                            <span class="goal-banner-title text-lg">{{ $g->title }}</span>
                            <div class="mt-2">
                                <div class="mb-1 flex items-baseline justify-between text-sm">
                                    <span class="font-semibold">{{ $prefix }}{{ number_format($g->live_amount, 0, ',', '.') }}</span>
                                    <span style="opacity: 0.85;">/ {{ $prefix }}{{ number_format($g->target_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="crm-meter" style="background: oklch(100% 0 0 / 0.3);">
                                    <div style="width: {{ $g->live_percent }}%; background: oklch(98% 0 0);"></div>
                                </div>
                                <p class="mt-1 text-xs" style="opacity: 0.9;">{{ $g->live_percent }}% complete</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="command-empty-card">
                        <p class="command-panel-kicker">Goals</p>
                        <h2>Belum ada goal</h2>
                        <a wire:navigate href="{{ route('goals.create') }}">Buat goal</a>
                    </div>
                @endif
            </section>

            <section class="command-panel">
                <div class="command-panel-head">
                    <div>
                        <p class="command-panel-kicker">Workspace</p>
                        <h2>Boards</h2>
                    </div>
                    <a wire:navigate href="{{ route('workspace.index') }}">Open</a>
                </div>
                <div class="command-list">
                    @forelse ($workspace_boards as $board)
                        <a wire:navigate href="{{ route('workspace.show', $board) }}" class="command-list-item">
                            <span class="command-list-icon"><x-icon name="board" class="h-4 w-4" /></span>
                            <span class="min-w-0">
                                <strong>{{ $board->name }}</strong>
                                <small>{{ $board->cards_count }} kartu</small>
                            </span>
                        </a>
                    @empty
                        <p class="command-muted">Belum ada board.</p>
                    @endforelse
                </div>
            </section>

            <section class="command-panel">
                <div class="command-panel-head">
                    <div>
                        <p class="command-panel-kicker">Vault</p>
                        <h2>Recent files</h2>
                    </div>
                    <a wire:navigate href="{{ route('vault.index') }}">Open</a>
                </div>
                <div class="command-list">
                    @forelse ($recent_vault_files as $file)
                        <a href="{{ route('vault.download', $file) }}" class="command-list-item">
                            <span class="command-list-icon"><x-icon name="{{ $file->isImage() ? 'board' : 'document' }}" class="h-4 w-4" /></span>
                            <span class="min-w-0">
                                <strong>{{ $file->original_name }}</strong>
                                <small>{{ $file->humanSize() }} @if($file->folder) / {{ $file->folder }} @endif</small>
                            </span>
                        </a>
                    @empty
                        <p class="command-muted">Vault kosong.</p>
                    @endforelse
                </div>
            </section>

            <section class="command-panel command-journal">
                <div class="command-panel-head">
                    <div>
                        <p class="command-panel-kicker">Journal</p>
                        <h2>{{ $journal_streak }} day streak</h2>
                    </div>
                    <a wire:navigate href="{{ route('journal.create') }}">New</a>
                </div>
                <div class="home-mood">
                    @foreach ($mood_trend->take(7) as $point)
                        <div class="home-mood-dot" @if($point['mood']) data-mood="{{ (int) round($point['mood']) }}" @endif title="{{ $point['label'] }}"></div>
                    @endforeach
                </div>
                <div class="command-list mt-4">
                    @forelse ($recent_journal_entries as $entry)
                        <a wire:navigate href="{{ route('journal.show', $entry) }}" class="command-list-item">
                            <span class="command-date-tile">{{ $entry->entry_date->format('d') }}</span>
                            <span class="min-w-0">
                                <strong>{{ $entry->title ?: 'Tanpa judul' }}</strong>
                                <small>{{ $entry->word_count }} kata</small>
                            </span>
                        </a>
                    @empty
                        <p class="command-muted">Belum menulis bulan ini.</p>
                    @endforelse
                </div>
            </section>

            <section class="command-panel command-pipeline">
                <div class="command-panel-head">
                    <div>
                        <p class="command-panel-kicker">CRM Pipeline</p>
                        <h2>{{ $pipeline['count'] }} open deal</h2>
                    </div>
                    <a wire:navigate href="{{ route('dashboard.crm') }}">CRM</a>
                </div>
                @if ($pipeline['count'])
                    <div class="home-pipeline">
                        @foreach (\App\Models\Deal::STAGES as $stage => $label)
                            <div class="home-pipeline-stage">
                                <span class="home-pipeline-num">{{ $pipeline['by_stage'][$stage] ?? 0 }}</span>
                                <span class="home-pipeline-label">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="home-pipeline-weighted">Nilai tertimbang <strong>{{ $money($pipeline['weighted']) }}</strong></p>
                @else
                    <p class="command-muted">Belum ada deal aktif.</p>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
