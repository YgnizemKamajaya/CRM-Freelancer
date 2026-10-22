@php($money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'))
<x-app-layout>
    <x-slot:title>Invoices</x-slot:title>

    {{-- ===== E-Signature Alpine Component (wraps button + modal together) ===== --}}
    <div x-data="esignModal()" x-init="init()">

        {{-- Trigger button lives here so it shares the same x-data scope --}}
        {{-- This div is a transparent passthrough, not a layout wrapper --}}

        <div class="space-y-5">
            <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Invoices</h2>
                    <p class="mt-1 text-sm text-gray-500">Simple billing status, due dates, and unpaid totals.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="crm-button-secondary"
                        aria-label="E-Signature settings"
                        @click="open = true"
                    >
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                        E-Signature
                    </button>
                    <a href="{{ route('invoices.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Add Invoice</a>
                </div>
            </div>

            <section class="grid gap-4 md:grid-cols-2">
                <x-stat-card label="Unpaid total" :value="$money($summary['unpaid'])" icon="document" tone="amber" />
                <x-stat-card label="Overdue total" :value="$money($summary['overdue'])" icon="document" tone="rose" />
            </section>

            <form method="GET" class="crm-card grid gap-3 p-4 md:grid-cols-[1fr_180px_220px_auto]">
                <input name="search" value="{{ request('search') }}" class="crm-field mt-0" placeholder="Search invoice number">
                <select name="status" class="crm-field mt-0">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="client_id" class="crm-field mt-0">
                    <option value="">All clients</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected((int) request('client_id') === $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
                <button class="crm-button-secondary">Filter</button>
            </form>

            @if ($invoices->count())
                <div class="crm-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[820px] text-left text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Invoice</th>
                                    <th scope="col" class="px-4 py-3">Client</th>
                                    <th scope="col" class="px-4 py-3">Issue</th>
                                    <th scope="col" class="px-4 py-3">Due</th>
                                    <th scope="col" class="px-4 py-3">Status</th>
                                    <th scope="col" class="px-4 py-3">Balance</th>
                                    <th scope="col" class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($invoices as $invoice)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                                        <td class="px-4 py-4">{{ $invoice->client->name }}</td>
                                        <td class="px-4 py-4">{{ $invoice->issue_date->format('d M Y') }}</td>
                                        <td class="px-4 py-4">{{ $invoice->due_date?->format('d M Y') ?? '-' }}</td>
                                        <td class="px-4 py-4"><x-status-badge :status="$invoice->status" /></td>
                                        <td class="px-4 py-4">{{ $money($invoice->balance) }}</td>
                                        <td class="px-4 py-4 text-right"><a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-primary-600">Open</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-gray-200 p-4">{{ $invoices->links() }}</div>
                </div>
            @else
                <x-empty-state title="No invoices found" text="Create an invoice with line items to track simple billing." action="Add Invoice" :href="route('invoices.create')" />
            @endif
        </div>

        {{-- ===== E-Signature Modal (custom, no x-modal component) ===== --}}
        {{--
            Architecture:
            - Outer shell: fixed inset-0, z-[60], flex centering container, pointer-events-none
              → covers viewport but does NOT intercept any click
            - Backdrop: separate fixed inset-0, z-[59], pointer-events-auto, @click closes modal
              → sits BEHIND content, handles backdrop click-to-close
            - Content panel: relative z-[1], pointer-events-auto
              → sits above backdrop, receives all clicks including file input
        --}}

        {{-- Backdrop (z-59, pointer-events-auto, closes on click) --}}
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/60"
            style="z-index: 59; display: none;"
            @click="open = false"
            aria-hidden="true"
        ></div>

        {{-- Centering shell: pointer-events-none so it never blocks content --}}
        <div
            x-show="open"
            class="fixed inset-0 flex items-center justify-center p-4"
            style="z-index: 60; display: none; pointer-events: none;"
            @keydown.escape.window="open = false"
        >
            {{-- Content panel: pointer-events-auto to receive all interactions --}}
            <div
                x-show="open"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="w-full rounded-xl bg-white shadow-2xl"
                style="pointer-events: auto; max-width: 32rem;"
                role="dialog"
                aria-modal="true"
                aria-labelledby="esign-modal-title"
                @click.stop
            >
                <div class="p-6">
                    {{-- Header --}}
                    <div class="mb-5 flex items-start justify-between">
                        <div>
                            <h3 id="esign-modal-title" class="text-lg font-semibold text-gray-900">E-Signature Settings</h3>
                            <p class="mt-0.5 text-sm text-gray-500">Upload tanda tangan digital untuk invoice PDF. PNG transparan direkomendasikan.</p>
                        </div>
                        <button
                            type="button"
                            @click="open = false; cancelCrop(); message = ''"
                            class="ml-4 flex-shrink-0 rounded p-1 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            aria-label="Tutup modal"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Current signature preview --}}
                    <div x-show="currentUrl && !showCropper" class="mb-5">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Tanda Tangan Tersimpan</p>
                        <div class="flex items-end gap-4">
                            <div class="rounded-lg border border-gray-200 p-3"
                                style="background-image: repeating-conic-gradient(#d1d5db 0% 25%, #f9fafb 0% 50%) 0 0 / 16px 16px; min-width:160px; min-height:80px; display:flex; align-items:center; justify-content:center;">
                                <img :src="currentUrl" alt="Tanda tangan tersimpan" class="max-h-20 max-w-[200px] object-contain">
                            </div>
                            <button type="button" @click="confirmDelete()" class="text-sm font-medium text-rose-600 hover:text-rose-800 focus:outline-none focus:underline">
                                Hapus Tanda Tangan
                            </button>
                        </div>
                    </div>

                    {{-- Upload zone (hidden when cropper active) --}}
                    <div x-show="!showCropper">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500" x-text="currentUrl ? 'Ganti Tanda Tangan' : 'Upload Tanda Tangan'"></p>
                        <label
                            for="esign-file-input"
                            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 px-6 py-8 text-center transition hover:border-primary-400 hover:bg-primary-50 focus-within:ring-2 focus-within:ring-primary-500"
                            @dragover.prevent
                            @drop.prevent="handleDrop($event)"
                        >
                            <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            <span class="text-sm text-gray-500">Drag &amp; drop atau <span class="font-medium text-primary-600">klik untuk pilih file</span></span>
                            <span class="text-xs text-gray-400">PNG (transparan), JPG, WEBP, GIF — maks. 4 MB</span>
                            <input id="esign-file-input" type="file" accept="image/*" class="sr-only" @change="handleFile($event)">
                        </label>
                    </div>

                    {{-- Cropper canvas --}}
                    <div x-show="showCropper" class="space-y-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Sesuaikan area crop</p>
                        <div class="overflow-hidden rounded-lg border border-gray-200 bg-gray-50" style="max-height: 340px;">
                            <img id="esign-crop-img" src="" alt="Preview crop" class="block max-w-full">
                        </div>
                        <div class="flex items-center justify-between gap-3 pt-1">
                            <button type="button" @click="cancelCrop()" class="crm-button-secondary text-sm">← Batal</button>
                            <button type="button" @click="cropAndSave()" :disabled="saving" class="crm-button-primary text-sm disabled:opacity-60">
                                <span x-show="!saving">Crop &amp; Simpan</span>
                                <span x-show="saving" class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Menyimpan...
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- Status message --}}
                    <div x-show="message" x-transition class="mt-4 rounded-lg px-4 py-3 text-sm"
                        :class="messageType === 'error' ? 'bg-rose-50 text-rose-700' : 'bg-green-50 text-green-700'"
                        role="alert" x-text="message">
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end x-data="esignModal()" --}}

    @push('scripts')

    <script>
    function esignModal() {
        return {
            open: false,
            currentUrl: @json(
                $setting->signature_path
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($setting->signature_path)
                    : null
            ),
            showCropper: false,
            saving: false,
            message: '',
            messageType: 'success',
            cropper: null,

            init() {
                // Destroy cropper when cropper panel hides
                this.$watch('showCropper', val => {
                    if (!val && this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }
                });
                // Lock body scroll when modal open
                this.$watch('open', val => {
                    document.body.style.overflow = val ? 'hidden' : '';
                });
            },

            handleFile(event) {
                const file = event.target.files[0];
                if (file) this.openCropper(file);
            },

            handleDrop(event) {
                const file = event.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) this.openCropper(file);
            },

            openCropper(file) {
                if (typeof window.Cropper !== 'function') {
                    this.message = 'Editor gambar belum siap. Muat ulang halaman lalu coba lagi.';
                    this.messageType = 'error';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.showCropper = true;
                    this.$nextTick(() => {
                        const img = document.getElementById('esign-crop-img');
                        img.src = e.target.result;
                        if (this.cropper) this.cropper.destroy();
                        this.cropper = new Cropper(img, {
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.8,
                            responsive: true,
                            background: true,
                            guides: true,
                            cropBoxResizable: true,
                        });
                    });
                };
                reader.readAsDataURL(file);
            },

            cancelCrop() {
                this.showCropper = false;
                const inp = document.getElementById('esign-file-input');
                if (inp) inp.value = '';
            },

            async cropAndSave() {
                if (!this.cropper) return;
                this.saving = true;
                this.message = '';

                try {
                    const canvas = this.cropper.getCroppedCanvas({ imageSmoothingEnabled: true, imageSmoothingQuality: 'high' });
                    if (!canvas) throw new Error('Canvas is null — cropper not ready');

                    const blob = await new Promise(res => canvas.toBlob(res, 'image/png'));
                    if (!blob) throw new Error('toBlob returned null');

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                    const form = new FormData();
                    form.append('signature', blob, 'signature.png');
                    form.append('_token', csrfToken);

                    const res = await fetch('{{ route('esignature.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: form,
                    });

                    let json;
                    const ct = res.headers.get('content-type') ?? '';
                    if (ct.includes('application/json')) {
                        json = await res.json();
                    } else {
                        const text = await res.text();
                        console.error('[esign] Non-JSON response:', res.status, text.slice(0, 300));
                        throw new Error('Server returned non-JSON (status ' + res.status + ')');
                    }

                    if (res.ok && json.ok) {
                        this.currentUrl = json.url + '?t=' + Date.now();
                        this.cancelCrop();
                        this.showStatus('Tanda tangan berhasil disimpan.', 'success');
                    } else {
                        this.showStatus(json.message ?? json.errors?.signature?.[0] ?? 'Gagal menyimpan.', 'error');
                    }
                } catch (err) {
                    console.error('[esign] cropAndSave error:', err);
                    this.showStatus('Terjadi kesalahan: ' + err.message, 'error');
                } finally {
                    this.saving = false;
                }
            },

            async confirmDelete() {
                if (!confirm('Hapus tanda tangan? Aksi ini tidak dapat dibatalkan.')) return;

                const res = await fetch('{{ route('esignature.destroy') }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const json = await res.json();

                if (res.ok && json.ok) {
                    this.currentUrl = null;
                    this.showStatus('Tanda tangan dihapus.', 'success');
                } else {
                    this.showStatus('Gagal menghapus.', 'error');
                }
            },

            showStatus(msg, type = 'success') {
                this.message = msg;
                this.messageType = type;
                setTimeout(() => { this.message = ''; }, 4000);
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
