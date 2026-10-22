@php($editing = $entry->exists)
<x-app-layout>
    <x-slot:title>{{ $editing ? 'Edit Entri' : 'Entri baru' }}</x-slot:title>

    <div x-data="{}">
        @if ($editing)
            <x-confirm-action :hide-trigger="true" :action="route('journal.destroy', $entry)" label="Hapus" title="Hapus entri?" body="Tindakan ini tidak bisa dibatalkan." />
        @endif

        <form method="POST" action="{{ $editing ? route('journal.update', $entry) : route('journal.store') }}" class="mx-auto max-w-3xl space-y-6">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="crm-card p-5 space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="entry_date" class="crm-label">Tanggal</label>
                        <input id="entry_date" name="entry_date" type="date" value="{{ old('entry_date', optional($entry->entry_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="crm-field">
                        <x-input-error :messages="$errors->get('entry_date')" class="mt-2" />
                    </div>
                    <div>
                        <label class="crm-label">Mood</label>
                        <div class="flex gap-2">
                            @foreach ($moods as $value => $label)
                                <label>
                                    <input type="radio" name="mood" value="{{ $value }}" class="journal-mood-radio sr-only" @checked((string) old('mood', $entry->mood) === (string) $value)>
                                    <span class="journal-mood-pick" title="{{ $label }}">{{ $moodEmoji[$value] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <label for="title" class="crm-label">Judul</label>
                    <input id="title" name="title" value="{{ old('title', $entry->title) }}" class="crm-field" placeholder="Opsional">
                </div>

                <div>
                    <label for="body" class="crm-label">Isi</label>
                    <textarea id="body" name="body" rows="14" class="crm-field" placeholder="Tulis apa saja...">{{ old('body', $entry->body) }}</textarea>
                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                </div>

                <div>
                    <label for="tags" class="crm-label">Tags</label>
                    <input id="tags" name="tags" value="{{ old('tags', collect($entry->tags)->join(', ')) }}" class="crm-field" placeholder="refleksi, kerja, ide">
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="client_id" class="crm-label">Kaitkan ke client (opsional)</label>
                        <select id="client_id" name="client_id" class="crm-field">
                            <option value="">— Tidak ada —</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected((string) old('client_id', $entry->client_id) === (string) $client->id)>{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="project_id" class="crm-label">Kaitkan ke project (opsional)</label>
                        <select id="project_id" name="project_id" class="crm-field">
                            <option value="">— Tidak ada —</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected((string) old('project_id', $entry->project_id) === (string) $project->id)>{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <div>
                    @if ($editing)
                        <button type="button" class="crm-button-danger"
                            x-on:click.prevent="$dispatch('open-modal', '{{ 'confirm-action-'.md5(route('journal.destroy', $entry).'Hapus'.'Hapus entri?') }}')">
                            Hapus
                        </button>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ $editing ? route('journal.show', $entry) : route('journal.index') }}" class="crm-button-secondary">Batal</a>
                    <button class="crm-button-primary">{{ $editing ? 'Simpan' : 'Simpan entri' }}</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
