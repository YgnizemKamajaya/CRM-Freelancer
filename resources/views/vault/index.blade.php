<x-app-layout>
    <x-slot:title>Vault</x-slot:title>

    <div x-data="{ uploading: false, photoboxOpen: false, photoboxSrc: '', photoboxTitle: '', photoboxSize: '', photoboxDownloadUrl: '', photoboxZoom: 1, photoboxRotate: 0, openPhotobox(src, title, size, downloadUrl) { this.photoboxSrc = src; this.photoboxTitle = title; this.photoboxSize = size; this.photoboxDownloadUrl = downloadUrl; this.photoboxZoom = 1; this.photoboxRotate = 0; this.photoboxOpen = true; } }" class="personal-page vault-room">
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
                        @if ($file->isImage())
                            <div class="group relative mb-3 flex h-36 items-center justify-center overflow-hidden rounded-lg cursor-pointer" style="background: var(--crm-surface-soft);"
                                @click="openPhotobox('{{ route('vault.preview', $file) }}', '{{ e($file->original_name) }}', '{{ $file->humanSize() }}', '{{ route('vault.download', $file) }}')">
                                <img src="{{ route('vault.preview', $file) }}" alt="{{ $file->original_name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                                <div class="absolute inset-0 flex items-center justify-center gap-1.5 bg-black/40 text-xs font-semibold text-white opacity-0 backdrop-blur-[2px] transition-opacity group-hover:opacity-100">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    Prinjau Foto
                                </div>
                            </div>
                        @else
                            <div class="mb-3 flex h-24 items-center justify-center rounded-lg" style="background: var(--crm-surface-soft); color: var(--crm-subtle);">
                                <x-icon name="document" class="h-8 w-8" />
                            </div>
                        @endif

                        <p class="truncate text-sm font-medium" style="color: var(--crm-heading);" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                        <p class="text-xs" style="color: var(--crm-subtle);">{{ $file->humanSize() }} · {{ $file->created_at->format('d M Y') }}</p>
                        @if ($file->folder)<p class="mt-1 text-xs" style="color: var(--crm-muted);">📁 {{ $file->folder }}</p>@endif
                        @if ($file->client)<p class="text-xs" style="color: var(--crm-muted);">{{ $file->client->name }}</p>@endif
                        <div class="mt-3 flex items-center gap-3 border-t pt-3" style="border-color: var(--crm-line);">
                            @if ($file->isImage())
                                <button type="button" @click="openPhotobox('{{ route('vault.preview', $file) }}', '{{ e($file->original_name) }}', '{{ $file->humanSize() }}', '{{ route('vault.download', $file) }}')" class="text-sm font-medium transition hover:opacity-80" style="color: var(--crm-action);">Prinjau</button>
                            @endif
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

        <!-- PHOTOBOX / LIGHTBOX MODAL -->
        <div x-cloak x-show="photoboxOpen" x-transition.opacity
            @keydown.escape.window="photoboxOpen = false"
            class="fixed inset-0 z-50 flex flex-col items-center justify-between p-4 sm:p-6"
            style="background: rgba(0, 0, 0, 0.9); backdrop-filter: blur(16px);">
            
            <!-- Topbar Header -->
            <div class="w-full max-w-5xl flex items-center justify-between py-2.5 px-4 rounded-xl bg-white/10 backdrop-blur border border-white/15 text-white z-10 shadow-lg">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="p-2 rounded-lg bg-white/10 text-sky-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold truncate text-white" x-text="photoboxTitle"></h3>
                        <p class="text-xs text-gray-300" x-text="photoboxSize"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a :href="photoboxDownloadUrl" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold transition">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Unduh
                    </a>
                    <button type="button" @click="photoboxOpen = false" class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white transition" title="Tutup (Esc)">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            </div>

            <!-- Main Image Stage -->
            <div class="relative flex-1 w-full max-w-5xl flex items-center justify-center overflow-hidden my-4"
                @wheel.prevent="event.deltaY < 0 ? photoboxZoom = Math.min(photoboxZoom + 0.15, 3) : photoboxZoom = Math.max(photoboxZoom - 0.15, 0.5)">
                <img :src="photoboxSrc" :alt="photoboxTitle"
                    class="max-h-full max-w-full object-contain rounded-lg shadow-2xl transition-transform duration-150 select-none cursor-grab active:cursor-grabbing"
                    :style="`transform: scale(${photoboxZoom}) rotate(${photoboxRotate}deg);`"
                    @dblclick="photoboxZoom = photoboxZoom === 1 ? 1.8 : 1">
            </div>

            <!-- Toolbar Controls -->
            <div class="flex items-center gap-3 py-2 px-5 rounded-full bg-white/10 backdrop-blur border border-white/15 text-white z-10 shadow-lg">
                <button type="button" @click="photoboxZoom = Math.max(photoboxZoom - 0.25, 0.5)" class="p-1.5 hover:bg-white/10 rounded-full transition text-gray-200 hover:text-white" title="Zoom Out (-)">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                </button>
                <span class="text-xs font-mono text-gray-200 min-w-[3rem] text-center" x-text="`${Math.round(photoboxZoom * 100)}%`"></span>
                <button type="button" @click="photoboxZoom = Math.min(photoboxZoom + 0.25, 3)" class="p-1.5 hover:bg-white/10 rounded-full transition text-gray-200 hover:text-white" title="Zoom In (+)">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                </button>
                <div class="h-4 w-px bg-white/20"></div>
                <button type="button" @click="photoboxRotate = (photoboxRotate + 90) % 360" class="p-1.5 hover:bg-white/10 rounded-full transition text-gray-200 hover:text-white" title="Putar 90°">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path></svg>
                </button>
                <button type="button" @click="photoboxZoom = 1; photoboxRotate = 0" class="px-2.5 py-1 text-xs hover:bg-white/10 rounded-md transition font-medium text-gray-300 hover:text-white">
                    Reset
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
