<?php $money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'); ?>
<x-app-layout>
    <x-slot:title>Personal Dashboard</x-slot:title>

    @php
        $alertTone = ['red' => 'home-alert--red', 'orange' => 'home-alert--orange', 'yellow' => 'home-alert--yellow'];
        $focusPercent = $checklist_total ? (int) round(($checklist_done / $checklist_total) * 100) : 0;
    @endphp

    <div class="command-center">

        {{-- ═══ ROW 0: PINNED GOAL BANNER (full-width) ════════════════════════ --}}
        @if ($featured_goal)
            @php
                $g = $featured_goal;
                $isFin = $g->type === 'financial';
                $prefix = $isFin ? ($g->metric_label ?: 'IDR').' ' : '';
                $bg = $g->hasUploadedBanner() ? 'background-image:url('.route('goals.banner', $g).')' : '';
                $bgClass = $g->hasUploadedBanner() ? '' : 'goal-banner--'.$g->gradientKey();
            @endphp
            <section class="overflow-hidden rounded-2xl shadow-md">
                <div class="goal-banner goal-banner--tall {{ $bgClass }}" style="{{ $bg }}">
                    <div class="goal-banner-content">
                        <div class="flex items-center justify-between">
                            <span class="home-dream-eyebrow text-xs uppercase tracking-widest font-bold">
                                🎯 {{ $g->is_pinned ? 'Mimpi Utama' : 'Goal Teratas' }}
                            </span>
                            <a wire:navigate href="{{ route('goals.index') }}"
                               class="p-1.5 rounded-full transition hover:bg-white/20"
                               style="color: oklch(98% 0 0);" title="Kelola Goals">
                                <x-icon name="chart" class="h-5 w-5" />
                            </a>
                        </div>
                        <h2 class="goal-banner-title text-xl sm:text-2xl font-bold mt-1">{{ $g->title }}</h2>
                        <div class="mt-3 max-w-sm">
                            <div class="mb-1.5 flex items-baseline justify-between text-sm font-medium">
                                <span>{{ $prefix }}{{ number_format($g->live_amount, 0, ',', '.') }}</span>
                                <span class="opacity-90">/ {{ $prefix }}{{ number_format($g->target_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="crm-meter" style="height: 0.55rem; background: oklch(100% 0 0 / 0.25);">
                                <div style="width: {{ min(100, $g->live_percent) }}%; background: oklch(98% 0 0);"></div>
                            </div>
                            <div class="mt-1.5 flex items-center justify-between text-xs opacity-90">
                                <span>Target tercapai</span>
                                <span class="font-bold">{{ $g->live_percent }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ═══ ROW 1: HERO GREETING (centered) ══════════════════════════════ --}}
        <header class="command-hero flex flex-col items-center text-center py-8">
            <div class="command-hero-bg" aria-hidden="true"></div>

            <div class="relative z-10 max-w-2xl mx-auto w-full space-y-3">
                <p class="text-xs uppercase tracking-widest font-semibold text-gray-500 dark:text-gray-400">{{ $today_label }}</p>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight leading-tight">
                    {{ $greeting }}, {{ $owner_name }}! 👋
                </h1>
                @if ($quote)
                    <div class="home-quote-box mx-auto max-w-xl justify-center">
                        <span class="text-amber-500 font-serif text-xl leading-none flex-shrink-0">"</span>
                        <div class="min-w-0 text-left">
                            <p class="home-quote-text">{{ $quote['text'] }}</p>
                            <p class="home-quote-author">— {{ $quote['author'] }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <button type="button" class="command-search relative z-10 mt-6" @click="commandOpen = true">
                <x-icon name="search" class="h-5 w-5 flex-shrink-0" />
                <span>Cari klien, deal, task, atau perintah</span>
                <kbd>Ctrl K</kbd>
            </button>

            <div class="command-actions relative z-10 justify-center flex-wrap mt-4">
                <a wire:navigate href="{{ route('today.index') }}"><x-icon name="check" class="h-4 w-4" /><span>Hari ini</span></a>
                <a wire:navigate href="{{ route('projects.index') }}"><x-icon name="briefcase" class="h-4 w-4" /><span>Proyek</span></a>
                <a wire:navigate href="{{ route('transactions.create') }}"><x-icon name="wallet" class="h-4 w-4" /><span>Transaksi</span></a>
                <a wire:navigate href="{{ route('goals.create') }}"><x-icon name="chart" class="h-4 w-4" /><span>Goal</span></a>
                <a wire:navigate href="{{ route('journal.create') }}"><x-icon name="book" class="h-4 w-4" /><span>Jurnal</span></a>
                <a wire:navigate href="{{ route('vault.index') }}"><x-icon name="document" class="h-4 w-4" /><span>Vault</span></a>
            </div>
        </header>

        {{-- ═══ ROW 2: STATS BAR (finance | alerts) ══════════════════════════ --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,20rem),1fr)); gap:1.5rem; align-items:stretch;">
            {{-- Finance --}}
            <section class="command-panel">
                <div class="command-panel-head">
                    <div>
                        <p class="command-panel-kicker">Keuangan Bulan Ini</p>
                        <h2 style="font-size:1.25rem; font-weight:800;">{{ $money($total_balance) }}</h2>
                    </div>
                    <a wire:navigate href="{{ route('finance.index') }}">Buka</a>
                </div>
                <div class="command-split">
                    <span><strong style="color:var(--crm-income);">+{{ $money($month_income) }}</strong> masuk</span>
                    <span><strong style="color:var(--crm-expense);">-{{ $money($month_expense) }}</strong> keluar</span>
                </div>
            </section>

            {{-- Alerts / Reminders --}}
            <section class="command-panel">
                <div class="command-panel-head">
                    <div>
                        <p class="command-panel-kicker">Perhatian &amp; Reminders</p>
                        <h2>{{ $alerts->count() }} item perlu tindakan</h2>
                    </div>
                </div>
                @if ($alerts->count())
                    <ul class="home-alerts">
                        @foreach ($alerts->take(3) as $alert)
                            <li>
                                <a wire:navigate href="{{ $alert['href'] }}" class="home-alert {{ $alertTone[$alert['level']] }}">
                                    <span class="home-alert-dot"></span>
                                    <x-icon :name="$alert['icon']" class="h-4 w-4" />
                                    <span>{{ $alert['text'] }}</span>
                                    <span class="home-alert-arrow"><x-icon name="chevron-right" class="h-4 w-4" /></span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="home-clear">
                        <span class="home-clear-mark"><x-icon name="check" class="h-5 w-5" /></span>
                        <p>Semua beres! Tidak ada invoice menunggak atau task mendesak.</p>
                    </div>
                @endif
            </section>
        </div>

        {{-- ═══ ROW 3: MAIN GRID (focus | calendar | projects+invoices) ════════ --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,22rem),1fr)); gap:1.5rem; align-items:start;">

            {{-- Left: Focus + Pipeline + Upcoming Invoices --}}
            <div style="display:flex; flex-direction:column; gap:1.5rem;">

                {{-- Today Focus --}}
                <livewire:home-focus-list />

                {{-- CRM Pipeline --}}
                <section class="command-panel">
                    <div class="command-panel-head">
                        <div>
                            <p class="command-panel-kicker">CRM Sales Pipeline</p>
                            <h2>{{ $pipeline['count'] }} Deal Aktif</h2>
                        </div>
                        <a wire:navigate href="{{ route('deals.index') }}">Pipeline</a>
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
                        <p class="home-pipeline-weighted">Nilai potensi: <strong>{{ $money($pipeline['weighted']) }}</strong></p>
                    @else
                        <p class="command-muted">Belum ada deal aktif di pipeline.</p>
                    @endif
                </section>

                {{-- Upcoming Invoices --}}
                <section class="command-panel">
                    <div class="command-panel-head">
                        <div>
                            <p class="command-panel-kicker">Invoice Jatuh Tempo</p>
                            <h2>Perlu Ditagih</h2>
                        </div>
                        <a wire:navigate href="{{ route('invoices.index') }}">Semua</a>
                    </div>
                    <div style="display:flex;flex-direction:column;">
                        @forelse ($upcoming_invoices as $invoice)
                            <a wire:navigate href="{{ route('invoices.show', $invoice) }}"
                               style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;padding:0.65rem 0.35rem;border-bottom:1px solid var(--crm-line);text-decoration:none;transition:background 0.15s;border-radius:0.5rem;">
                                <div style="min-width:0;">
                                    <p style="font-size:0.875rem;font-weight:600;color:var(--crm-heading);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $invoice->invoice_number }}</p>
                                    <p style="font-size:0.75rem;color:var(--crm-subtle);">{{ $invoice->client?->name }} · {{ $invoice->due_date?->format('d M') }}</p>
                                </div>
                                <div style="text-align:right;flex-shrink:0;">
                                    <p style="font-size:0.8rem;font-weight:700;color:var(--crm-heading);">{{ $money($invoice->balance) }}</p>
                                    <x-status-badge :status="$invoice->status" />
                                </div>
                            </a>
                        @empty
                            <p class="command-muted">Tidak ada invoice jatuh tempo mendatang.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- Middle: Calendar + Agenda --}}
            <div style="display:flex; flex-direction:column; gap:1.5rem;">
                <section class="command-panel">
                    <div class="command-panel-head">
                        <div>
                            <p class="command-panel-kicker">Kalender &amp; Agenda</p>
                            <h2>{{ $calendar_data['month_name'] }}</h2>
                        </div>
                        <a wire:navigate href="{{ route('journal.index') }}">Jurnal</a>
                    </div>

                    {{-- Calendar grid --}}
                    <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:0.2rem; text-align:center; margin-bottom:1rem;">
                        @foreach (['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $dh)
                            <div style="font-size:0.65rem; font-weight:800; color:var(--crm-subtle); padding:0.25rem 0; text-transform:uppercase;">{{ $dh }}</div>
                        @endforeach

                        @for ($i = 0; $i < $calendar_data['first_day_of_week']; $i++)
                            <div style="height:2rem;"></div>
                        @endfor

                        @for ($d = 1; $d <= $calendar_data['days_in_month']; $d++)
                            @php
                                $isToday  = ($d === $calendar_data['today_day']);
                                $hasJ     = in_array($d, $calendar_data['journal_days']);
                                $hasT     = in_array($d, $calendar_data['task_days']);
                                $hasP     = in_array($d, $calendar_data['project_days']);
                                $hasI     = in_array($d, $calendar_data['invoice_days']);
                                $tDate    = sprintf('%04d-%02d-%02d', $calendar_data['year'], $calendar_data['month'], $d);
                                $dayBg    = $isToday ? 'background:var(--crm-action);color:var(--crm-on-action);font-weight:800;box-shadow:var(--crm-shadow-soft);' : '';
                            @endphp
                            <a wire:navigate href="{{ route('journal.index', ['date' => $tDate]) }}"
                               style="position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;height:2.2rem;border-radius:0.45rem;font-size:0.78rem;font-weight:650;color:var(--crm-text);text-decoration:none;transition:background 0.15s;{{ $dayBg }}"
                               title="{{ $d }} {{ $calendar_data['month_name'] }}">
                                <span>{{ $d }}</span>
                                @if ($hasJ || $hasT || $hasP || $hasI)
                                    <div style="display:flex;gap:2px;position:absolute;bottom:2px;">
                                        @if($hasJ)<span class="cal-dot cal-dot--journal"></span>@endif
                                        @if($hasT)<span class="cal-dot cal-dot--task"></span>@endif
                                        @if($hasP)<span class="cal-dot cal-dot--project"></span>@endif
                                        @if($hasI)<span class="cal-dot cal-dot--invoice"></span>@endif
                                    </div>
                                @endif
                            </a>
                        @endfor
                    </div>

                    {{-- Legend + Agenda --}}
                    <div style="border-top:1px solid var(--crm-line);padding-top:0.75rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;">
                            <span style="font-size:0.75rem;font-weight:700;color:var(--crm-muted);">Agenda Bulan Ini</span>
                            <div style="display:flex;gap:0.5rem;font-size:0.65rem;color:var(--crm-subtle);">
                                <span style="display:flex;align-items:center;gap:3px;"><span class="cal-dot cal-dot--journal"></span>Jurnal</span>
                                <span style="display:flex;align-items:center;gap:3px;"><span class="cal-dot cal-dot--task"></span>Task</span>
                                <span style="display:flex;align-items:center;gap:3px;"><span class="cal-dot cal-dot--project"></span>Project</span>
                            </div>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:0.5rem;">
                            @forelse ($calendar_data['agendas'] as $agenda)
                                <a wire:navigate href="{{ $agenda['href'] }}" class="home-agenda-item">
                                    <div style="min-width:0;flex:1;">
                                        <p style="font-size:0.8rem;font-weight:600;color:var(--crm-heading);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $agenda['title'] }}</p>
                                        <p style="font-size:0.72rem;color:var(--crm-subtle);">{{ $agenda['date']?->format('d M Y') }}</p>
                                    </div>
                                    <span style="font-size:0.65rem;font-weight:700;padding:0.15rem 0.55rem;border-radius:999px;background:var(--crm-surface-soft);color:var(--crm-muted);">{{ $agenda['badge'] }}</span>
                                </a>
                            @empty
                                <p class="command-muted">Tidak ada agenda mendatang bulan ini.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            {{-- Right: Active Projects + Upcoming Invoices --}}
            <div style="display:flex; flex-direction:column; gap:1.5rem;">

                {{-- Active Projects --}}
                <section class="command-panel">
                    <div class="command-panel-head">
                        <div>
                            <p class="command-panel-kicker">Proyek Berjalan</p>
                            <h2>{{ $active_projects->count() }} Proyek Aktif</h2>
                        </div>
                        <a wire:navigate href="{{ route('projects.index') }}">Semua</a>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:0.75rem;">
                        @forelse ($active_projects as $project)
                            <a wire:navigate href="{{ route('projects.show', $project) }}" class="home-project-card">
                                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem;">
                                    <div style="min-width:0;">
                                        <h3 style="font-size:0.875rem;font-weight:700;color:var(--crm-heading);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $project->title }}</h3>
                                        <p style="font-size:0.75rem;color:var(--crm-subtle);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $project->client?->name ?? 'Klien internal' }}</p>
                                    </div>
                                    <x-status-badge :status="$project->status" />
                                </div>
                                <div>
                                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:0.72rem;color:var(--crm-subtle);margin-bottom:0.3rem;">
                                        <span>Progress ({{ $project->done_tasks_count }}/{{ $project->tasks_count }} task)</span>
                                        <span>{{ (int) $project->progress }}%</span>
                                    </div>
                                    <div class="crm-meter">
                                        <div style="width: {{ (int) $project->progress }}%;"></div>
                                    </div>
                                </div>
                                @if ($project->deadline)
                                    <p style="font-size:0.7rem;color:var(--crm-subtle);">
                                        Deadline: {{ $project->deadline->format('d M Y') }} ({{ $project->deadline->diffForHumans() }})
                                    </p>
                                @endif
                            </a>
                        @empty
                            <p class="command-muted">Belum ada proyek yang sedang berjalan.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>

        {{-- ═══ ROW 4: BOTTOM (journal | workspace | vault) ══════════════════ --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,22rem),1fr)); gap:1.5rem; align-items:start;">

            {{-- Journal & Mood --}}
            <section class="command-panel">
                <div class="command-panel-head">
                    <div>
                        <p class="command-panel-kicker">Catatan &amp; Mood</p>
                        <h2>{{ $journal_streak }} Hari Beruntun</h2>
                    </div>
                    <a wire:navigate href="{{ route('journal.create') }}">Tulis Baru</a>
                </div>
                <div class="home-mood">
                    @foreach ($mood_trend->take(7) as $point)
                        <div class="home-mood-dot" @if($point['mood']) data-mood="{{ (int) round($point['mood']) }}" @endif title="{{ $point['label'] }}"></div>
                    @endforeach
                </div>
                <div class="command-list" style="margin-top:0.75rem;">
                    @forelse ($recent_journal_entries as $entry)
                        <a wire:navigate href="{{ route('journal.show', $entry) }}" class="command-list-item">
                            <span class="command-date-tile">{{ $entry->entry_date->format('d') }}</span>
                            <span style="min-width:0;">
                                <strong>{{ $entry->title ?: 'Tanpa judul' }}</strong>
                                <small>{{ $entry->word_count }} kata</small>
                            </span>
                        </a>
                    @empty
                        <p class="command-muted">Belum ada catatan jurnal bulan ini.</p>
                    @endforelse
                </div>
            </section>

            {{-- Workspace --}}
            <section class="command-panel">
                <div class="command-panel-head">
                    <div>
                        <p class="command-panel-kicker">Workspace</p>
                        <h2>Papan Kanban</h2>
                    </div>
                    <a wire:navigate href="{{ route('workspace.index') }}">Buka</a>
                </div>
                <div class="command-list">
                    @forelse ($workspace_boards as $board)
                        <a wire:navigate href="{{ route('workspace.show', $board) }}" class="command-list-item">
                            <span class="command-list-icon"><x-icon name="board" class="h-4 w-4" /></span>
                            <span style="min-width:0;">
                                <strong>{{ $board->name }}</strong>
                                <small>{{ $board->cards_count }} kartu</small>
                            </span>
                        </a>
                    @empty
                        <p class="command-muted">Belum ada papan workspace.</p>
                    @endforelse
                </div>
            </section>

            {{-- Vault --}}
            <section class="command-panel">
                <div class="command-panel-head">
                    <div>
                        <p class="command-panel-kicker">Vault File</p>
                        <h2>Berkas Terbaru</h2>
                    </div>
                    <a wire:navigate href="{{ route('vault.index') }}">Buka</a>
                </div>
                <div class="command-list">
                    @forelse ($recent_vault_files as $file)
                        <a href="{{ $file->isImage() ? route('vault.index') : route('vault.download', $file) }}" class="command-list-item">
                            @if ($file->isImage())
                                <span class="command-list-icon overflow-hidden rounded flex items-center justify-center border border-gray-200 dark:border-gray-700" style="width: 2rem; height: 2rem;">
                                    <img src="{{ route('vault.preview', $file) }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                </span>
                            @else
                                <span class="command-list-icon"><x-icon name="document" class="h-4 w-4" /></span>
                            @endif
                            <span style="min-width:0;">
                                <strong>{{ $file->original_name }}</strong>
                                <small>{{ $file->humanSize() }}@if($file->folder) / {{ $file->folder }}@endif</small>
                            </span>
                        </a>
                    @empty
                        <p class="command-muted">Vault masih kosong.</p>
                    @endforelse
                </div>
            </section>
        </div>

    </div>
</x-app-layout>
