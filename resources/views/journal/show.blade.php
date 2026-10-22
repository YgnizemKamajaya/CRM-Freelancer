<x-app-layout>
    <x-slot:title>{{ $entry->title ?: 'Entri jurnal' }}</x-slot:title>

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="crm-eyebrow">{{ $entry->entry_date->format('l, d F Y') }}</p>
                <h2 class="mt-1 text-2xl font-semibold" style="color: var(--crm-heading);">
                    @if ($entry->mood)<span title="{{ $entry->moodLabel() }}">{{ $entry->moodEmoji() }}</span> @endif
                    {{ $entry->title ?: 'Tanpa judul' }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('journal.export', $entry) }}" class="crm-button-secondary"><x-icon name="document" class="h-4 w-4" />Export</a>
                <a href="{{ route('journal.edit', $entry) }}" class="crm-button-primary">Edit</a>
            </div>
        </div>

        @if ($entry->tags)
            <div class="flex flex-wrap gap-2">
                @foreach ($entry->tags as $tag)
                    <a href="{{ route('journal.index', ['tag' => $tag]) }}" class="journal-tag">#{{ $tag }}</a>
                @endforeach
            </div>
        @endif

        <div class="crm-card p-6">
            @if (filled($entry->body))
                <div class="journal-prose">{{ $entry->body }}</div>
            @else
                <p class="text-sm" style="color: var(--crm-subtle);">Entri ini tidak punya isi.</p>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-4 text-sm" style="color: var(--crm-subtle);">
            <span>{{ $entry->word_count }} kata</span>
            @if ($entry->client)<span>· Client: <x-client-link :client="$entry->client" /></span>@endif
            @if ($entry->project)<span>· Project: {{ $entry->project->title }}</span>@endif
        </div>

        <a href="{{ route('journal.index') }}" class="inline-block text-sm font-medium" style="color: var(--crm-muted);">← Kembali ke jurnal</a>
    </div>
</x-app-layout>
