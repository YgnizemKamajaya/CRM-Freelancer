<x-app-layout>
    <x-slot:title>Journal</x-slot:title>

    @php $maxTag = max(1, $top_tags->max('count') ?? 1); @endphp

    <div class="personal-page journal-room">
        <div class="personal-hero">
            <div class="min-w-0">
                <p class="crm-eyebrow">Personal</p>
                <h2 class="text-2xl font-semibold" style="color: var(--crm-heading);">Journal</h2>
                <p class="mt-1 text-sm" style="color: var(--crm-muted);">Ruang privat untuk mencatat & merefleksikan hari-harimu.</p>
            </div>
            <div class="personal-hero-side">
                <div class="personal-mascot is-journal" aria-hidden="true">
                    <span class="mascot-ear left"></span><span class="mascot-ear right"></span>
                    <span class="mascot-head"><span class="mascot-eye left"></span><span class="mascot-eye right"></span><span class="mascot-mouth"></span></span>
                    <span class="mascot-body"></span><span class="mascot-shadow"></span>
                </div>
                <a href="{{ route('journal.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Entri baru</a>
            </div>
        </div>

        <div class="personal-stat-grid">
            <x-stat-card label="Total entri" :value="$total_entries" icon="book" tone="blue" />
            <x-stat-card label="Entri bulan ini" :value="$month_entries" icon="calendar" tone="green" />
            <x-stat-card label="Streak menulis" :value="$current_streak.' hari'" icon="check" tone="amber" />
            <x-stat-card label="Mood rata-rata" :value="$avg_mood ? ($moodEmoji[(int) round($avg_mood)] ?? '—').' '.$avg_mood : '—'" icon="chart" tone="rose" />
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="crm-card personal-panel p-5 lg:col-span-2">
                <h3 class="mb-4 text-sm font-semibold" style="color: var(--crm-heading);">Tren mood 14 hari</h3>
                <div class="mood-trend">
                    @foreach ($mood_trend as $point)
                        <div class="mood-trend-col">
                            <div class="mood-trend-bar" @if($point['mood']) data-mood="{{ (int) round($point['mood']) }}" @endif title="{{ $point['mood'] ? 'Mood '.$point['mood'] : 'Tidak ada entri' }}">
                                <span style="height: {{ $point['mood'] ? (int) round(($point['mood'] / 5) * 100) : 4 }}%"></span>
                            </div>
                            <span class="mood-trend-label">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="crm-card personal-panel p-5">
                <h3 class="mb-4 text-sm font-semibold" style="color: var(--crm-heading);">Tag sering dipakai</h3>
                @if ($top_tags->count())
                    <div class="flex flex-wrap gap-2">
                        @foreach ($top_tags as $t)
                            <a href="{{ route('journal.index', ['tag' => $t['tag']]) }}" class="journal-tag" style="font-size: {{ 0.7 + ($t['count'] / $maxTag) * 0.35 }}rem;">
                                #{{ $t['tag'] }} <span style="color: var(--crm-subtle);">{{ $t['count'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm" style="color: var(--crm-subtle);">Belum ada tag. Tambahkan tag saat menulis entri.</p>
                @endif
            </div>
        </div>

        <form method="GET" class="crm-card personal-filter grid gap-3 p-4 md:grid-cols-[1fr_160px_auto]">
            <input name="search" value="{{ request('search') }}" class="crm-field mt-0" placeholder="Cari judul atau isi">
            <select name="mood" class="crm-field mt-0">
                <option value="">Semua mood</option>
                @foreach ($moods as $value => $label)
                    <option value="{{ $value }}" @selected((string) request('mood') === (string) $value)>{{ $moodEmoji[$value] }} {{ $label }}</option>
                @endforeach
            </select>
            <button class="crm-button-secondary">Filter</button>
        </form>

        @if ($entries->count())
            <div class="space-y-3">
                @foreach ($entries as $entry)
                    <a href="{{ route('journal.show', $entry) }}" class="crm-card personal-list-item personal-journal-entry" style="text-decoration: none;">
                        <div class="flex flex-col items-center justify-center rounded-lg px-3 py-2" style="background: var(--crm-sidebar-soft); min-width: 3.5rem;">
                            <span class="text-lg font-bold" style="color: var(--crm-heading);">{{ $entry->entry_date->format('d') }}</span>
                            <span class="text-xs" style="color: var(--crm-subtle);">{{ $entry->entry_date->format('M') }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                @if ($entry->mood)<span title="{{ $entry->moodLabel() }}">{{ $entry->moodEmoji() }}</span>@endif
                                <p class="truncate font-semibold" style="color: var(--crm-heading);">{{ $entry->title ?: 'Tanpa judul' }}</p>
                            </div>
                            <p class="mt-1 line-clamp-2 text-sm" style="color: var(--crm-muted);">{{ \Illuminate\Support\Str::limit(strip_tags($entry->body), 160) }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                @foreach (($entry->tags ?? []) as $tag)
                                    <span class="journal-tag">#{{ $tag }}</span>
                                @endforeach
                                <span class="ml-auto text-xs" style="color: var(--crm-subtle);">{{ $entry->word_count }} kata</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div>{{ $entries->links() }}</div>
        @else
            <x-empty-state title="Belum ada entri" text="Mulai tulis catatan atau refleksi pertamamu." action="Entri baru" :href="route('journal.create')" />
        @endif
    </div>
</x-app-layout>
