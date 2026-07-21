<x-app-layout>
    <x-slot:title>Vault</x-slot:title>

    <div x-data="{ uploading: false }" class="personal-page vault-room">
        <div class="personal-hero">
            <div class="min-w-0">
                <p class="crm-eyebrow">Personal</p>
                <h2 class="text-2xl font-semibold" style="color: var(--crm-heading);">Vault</h2>
                <p class="mt-1 text-sm" style="color: var(--crm-muted);">Simpan kontrak, aset, dan dokumen — privat, hanya kamu.</p>
            </div>
            <div class="personal-hero-side">
                <div class="personal-mascot is-vault" aria-hidden="true">
                    <span class="mascot-ear left"></span><span class="mascot-ear right"></span>
                    <span class="mascot-head"><span class="mascot-eye left"></span><span class="mascot-eye right"></span><span class="mascot-mouth"></span></span>
                    <span class="mascot-body"></span><span class="mascot-shadow"></span>
                </div>
                <button @click="uploading = !uploading" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Upload</button>
            </div>
        </div>

        <div x-show="uploading" x-cloak class="crm-card personal-panel p-5">
            <form method="POST" action="{{ route('vault.store') }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
                @csrf
                <div class="md:col-span-2">
                    <label for="files" class="crm-label">Pilih file (maks 25 MB/file, 20 file)</label>
                    <input id="files" name="files[]" type="file" multiple required class="crm-field">
                    <x-input-error :messages="$errors->get('files')" class="mt-2" />
                    <x-input-error :messages="$errors->get('files.0')" class="mt-2" />
                </div>
                <div>
                    <label for="folder" class="crm-label">Folder (opsional)</label>
                    <input id="folder" name="folder" list="folder-list" value="{{ old('folder') }}" class="crm-field" placeholder="Kontrak, Aset...">
                    <datalist id="folder-list">
                        @foreach ($folders as $folder)<option value="{{ $folder }}"></option>@endforeach
                    </datalist>
                </div>
                <div>
                    <label for="client_id" class="crm-label">Kaitkan client (opsional)</label>
                    <select id="client_id" name="client_id" class="crm-field">
                        <option value="">—</option>
                        @foreach ($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="project_id" class="crm-label">Kaitkan project (opsional)</label>
                    <select id="project_id" name="project_id" class="crm-field">
                        <option value="">—</option>
                        @foreach ($projects as $project)<option value="{{ $project->id }}">{{ $project->title }}</option>@endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex justify-end gap-3">
                    <button type="button" @click="uploading = false" class="crm-button-secondary">Batal</button>
                    <button class="crm-button-primary">Upload file</button>
                </div>
            </form>
        </div>

        <form method="GET" class="crm-card personal-filter grid gap-3 p-4 md:grid-cols-[1fr_180px_auto]">
            <input name="search" value="{{ request('search') }}" class="crm-field mt-0" placeholder="Cari nama file / folder">
            <select name="folder" class="crm-field mt-0">
                <option value="">Semua folder</option>
                @foreach ($folders as $folder)<option value="{{ $folder }}" @selected(request('folder') === $folder)>{{ $folder }}</option>@endforeach
            </select>
            <button class="crm-button-secondary">Filter</button>
        </form>

        @if ($files->count())
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($files as $file)
                    <div class="crm-card personal-file-card flex flex-col p-4">
                        <div class="mb-3 flex h-24 items-center justify-center rounded-lg" style="background: var(--crm-surface-soft); color: var(--crm-subtle);">
                            <x-icon name="{{ $file->isImage() ? 'board' : 'document' }}" class="h-8 w-8" />
                        </div>
                        <p class="truncate text-sm font-medium" style="color: var(--crm-heading);" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                        <p class="text-xs" style="color: var(--crm-subtle);">{{ $file->humanSize() }} · {{ $file->created_at->format('d M Y') }}</p>
                        @if ($file->folder)<p class="mt-1 text-xs" style="color: var(--crm-muted);">📁 {{ $file->folder }}</p>@endif
                        @if ($file->client)<p class="text-xs" style="color: var(--crm-muted);">{{ $file->client->name }}</p>@endif
                        <div class="mt-3 flex items-center gap-2 border-t pt-3" style="border-color: var(--crm-line);">
                            <a href="{{ route('vault.download', $file) }}" class="text-sm font-medium" style="color: var(--crm-heading);">Unduh</a>
                            <form method="POST" action="{{ route('vault.destroy', $file) }}" class="ml-auto" onsubmit="return confirm('Hapus file ini?')">
                                @csrf @method('DELETE')
                                <button class="crm-icon-button" style="min-height: 1.75rem; min-width: 1.75rem;" title="Hapus"><x-icon name="trash" class="h-3.5 w-3.5" /></button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <div>{{ $files->links() }}</div>
        @else
            <x-empty-state title="Vault masih kosong" text="Upload file pertamamu — kontrak, invoice PDF, aset desain, apa saja." />
        @endif
    </div>
</x-app-layout>
