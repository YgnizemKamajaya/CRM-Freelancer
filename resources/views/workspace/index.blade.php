<x-app-layout>
    <x-slot:title>Workspace</x-slot:title>

    <div x-data="{ creating: false }" class="personal-page workspace-room">
        <div class="personal-hero">
            <div class="min-w-0">
                <p class="crm-eyebrow">Personal</p>
                <h2 class="text-2xl font-semibold" style="color: var(--crm-heading);">Workspace</h2>
                <p class="mt-1 text-sm" style="color: var(--crm-muted);">Board kanban untuk ide, brainstorming, dan rencana project.</p>
            </div>
            <div class="personal-hero-side">
                <div class="personal-mascot is-workspace" aria-hidden="true">
                    <span class="mascot-ear left"></span><span class="mascot-ear right"></span>
                    <span class="mascot-head"><span class="mascot-eye left"></span><span class="mascot-eye right"></span><span class="mascot-mouth"></span></span>
                    <span class="mascot-body"></span><span class="mascot-shadow"></span>
                </div>
                <button @click="creating = !creating" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Board baru</button>
            </div>
        </div>

        <div x-show="creating" x-cloak class="crm-card personal-panel p-5">
            <form method="POST" action="{{ route('workspace.store') }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                <div>
                    <label for="name" class="crm-label">Nama board</label>
                    <input id="name" name="name" value="{{ old('name') }}" required class="crm-field" placeholder="Ide konten, Project sampingan...">
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <label for="type" class="crm-label">Tipe</label>
                    <select id="type" name="type" class="crm-field">
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs" style="color: var(--crm-subtle);">Kolom default akan dibuat otomatis sesuai tipe.</p>
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="crm-label">Deskripsi (opsional)</label>
                    <textarea id="description" name="description" rows="2" class="crm-field">{{ old('description') }}</textarea>
                </div>
                <div class="md:col-span-2 flex justify-end gap-3">
                    <button type="button" @click="creating = false" class="crm-button-secondary">Batal</button>
                    <button class="crm-button-primary">Buat board</button>
                </div>
            </form>
        </div>

        @if ($boards->count())
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($boards as $board)
                    <a href="{{ route('workspace.show', $board) }}" class="crm-card personal-board-card block p-5 transition hover:-translate-y-0.5" style="text-decoration: none;">
                        <div class="flex items-start justify-between">
                            <span class="rounded-lg p-2" style="background: var(--crm-sidebar-soft); color: var(--crm-heading);"><x-icon name="board" class="h-5 w-5" /></span>
                            <span class="crm-phase-badge">{{ \App\Models\Board::TYPES[$board->type] ?? $board->type }}</span>
                        </div>
                        <p class="mt-4 text-base font-semibold" style="color: var(--crm-heading);">{{ $board->name }}</p>
                        @if ($board->description)
                            <p class="mt-1 line-clamp-2 text-sm" style="color: var(--crm-muted);">{{ $board->description }}</p>
                        @endif
                        <p class="mt-3 text-xs" style="color: var(--crm-subtle);">{{ $board->cards_count }} kartu</p>
                    </a>
                @endforeach
            </div>
        @else
            <x-empty-state title="Belum ada board" text="Buat board pertama untuk menampung ide atau rencana projectmu." />
        @endif
    </div>
</x-app-layout>
