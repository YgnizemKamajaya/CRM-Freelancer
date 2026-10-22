<div class="personal-page today-room">
    <div class="personal-hero">
        <div class="min-w-0">
            <p class="crm-eyebrow">Personal</p>
            <h2 class="text-2xl font-semibold" style="color: var(--crm-heading);">Hari ini</h2>
            <p class="mt-1 text-sm" style="color: var(--crm-muted);">{{ $prettyDate }}</p>
        </div>
        <div class="personal-hero-side">
            <div class="personal-mascot is-today" aria-hidden="true">
                <span class="mascot-ear left"></span><span class="mascot-ear right"></span>
                <span class="mascot-head"><span class="mascot-eye left"></span><span class="mascot-eye right"></span><span class="mascot-mouth"></span></span>
                <span class="mascot-body"></span><span class="mascot-shadow"></span>
            </div>
            <div class="text-right">
                <p class="text-2xl font-semibold" style="color: var(--crm-heading);">{{ $doneCount }}/{{ $items->count() }}</p>
                <p class="text-xs" style="color: var(--crm-subtle);">selesai</p>
            </div>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-2">
        <button wire:click="changeDate(-1)" class="crm-button-secondary" aria-label="Hari sebelumnya"><x-icon name="chevron-left" /></button>
        <input type="date" wire:model.live="date" class="crm-field mt-0" style="width:auto" aria-label="Tanggal checklist">
        <button wire:click="changeDate(1)" class="crm-button-secondary" aria-label="Hari berikutnya"><x-icon name="chevron-right" /></button>
        <a href="{{ route('today.index') }}" wire:navigate class="crm-button-secondary">Hari ini</a>
    </div>
    <p class="mb-4 text-xs" style="color:var(--crm-muted)">Mencentang item menyelesaikan task CRM terkait. Untuk membuka task lagi, gunakan Buka kembali di Tasks.</p>
    @if ($items->count())
        <div class="crm-meter mb-5">
            <div style="width: {{ $items->count() ? (int) round(($doneCount / $items->count()) * 100) : 0 }}%"></div>
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-[1fr_18rem]">
        <div class="crm-card personal-panel p-5">
            <form wire:submit="addItem" class="mb-4 flex items-center gap-2">
                <input wire:model="newTitle" class="crm-field mt-0" placeholder="Tambah tugas hari ini...">
                <button class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" /></button>
            </form>

            @if ($items->count())
                <div class="flex flex-col gap-1.5" wire:sort="reorder">
                    @foreach ($items as $item)
                        <div class="checklist-row" wire:key="ci-{{ $item->id }}" wire:sort:item="{{ $item->id }}">
                            <button wire:sort:ignore wire:click="toggle({{ $item->id }})" class="checklist-check {{ $item->is_done ? 'is-done' : '' }}" aria-label="Toggle">
                                @if ($item->is_done)<x-icon name="check" class="h-3.5 w-3.5" />@endif
                            </button>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm {{ $item->is_done ? 'checklist-done-text' : '' }}" style="color: var(--crm-text);">{{ $item->title }}</p>
                                @if ($item->task_id)
                                    <span class="text-xs" style="color: var(--crm-subtle);">↳ dari CRM task</span>
                                @endif
                            </div>
                            <button wire:sort:ignore wire:click="deleteItem({{ $item->id }})" class="crm-icon-button" style="min-height: 1.75rem; min-width: 1.75rem;" title="Hapus">
                                <x-icon name="trash" class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="py-8 text-center text-sm" style="color: var(--crm-subtle);">Belum ada tugas. Tambahkan yang mau kamu selesaikan hari ini.</p>
            @endif
        </div>

        <div class="space-y-4">
            @if ($isToday)
                <div class="crm-card personal-panel p-4">
                    <h3 class="mb-2 text-sm font-semibold" style="color: var(--crm-heading);">Belum selesai kemarin</h3>
                    <p class="mb-3 text-xs" style="color: var(--crm-subtle);">Pindahkan sisa tugas ke hari ini.</p>
                    <button wire:click="rollOver" class="crm-button-secondary w-full justify-center">Tarik ke hari ini</button>
                </div>

                @if ($pullableTasks->count())
                    <div class="crm-card personal-panel p-4">
                        <h3 class="mb-2 text-sm font-semibold" style="color: var(--crm-heading);">Task CRM jatuh tempo hari ini</h3>
                        <div class="space-y-2">
                            @foreach ($pullableTasks as $task)
                                <div class="flex items-center justify-between gap-2">
                                    <span class="min-w-0 truncate text-sm" style="color: var(--crm-text);">{{ $task->title }}</span>
                                    <button wire:click="pullTask({{ $task->id }})" class="crm-icon-button" style="min-height: 1.75rem; min-width: 1.75rem;" title="Tambahkan"><x-icon name="plus" class="h-3.5 w-3.5" /></button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
