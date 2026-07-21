<div>
    <div class="mb-5 flex flex-col justify-between gap-3 md:flex-row md:items-center">
        <div>
            <a href="{{ route('workspace.index') }}" class="text-xs font-medium" style="color: var(--crm-subtle);">← Workspace</a>
            <h2 class="mt-1 text-2xl font-semibold" style="color: var(--crm-heading);">{{ $board->name }}</h2>
            @if ($board->description)
                <p class="mt-1 text-sm" style="color: var(--crm-muted);">{{ $board->description }}</p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <form wire:submit="addColumn" class="flex items-center gap-2">
                <input wire:model="newColumnName" class="crm-field mt-0" style="width: 11rem;" placeholder="Kolom baru...">
                <button class="crm-button-secondary"><x-icon name="plus" class="h-4 w-4" />Kolom</button>
            </form>
        </div>
    </div>
    @error('newColumnName') <p class="mb-3 text-sm text-expense">{{ $message }}</p> @enderror

    <div class="kanban-scroll flex gap-4 overflow-x-auto pb-4">
        @foreach ($columns as $column)
            <div class="kanban-column" wire:key="col-{{ $column->id }}">
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold" style="color: var(--crm-heading);">{{ $column->name }}</h3>
                        <span class="kanban-count">{{ $column->cards->count() }}@if($column->wip_limit)/{{ $column->wip_limit }}@endif</span>
                    </div>
                    <button wire:click="deleteColumn({{ $column->id }})" wire:confirm="Hapus kolom ini beserta semua kartunya?" class="crm-icon-button" style="min-height: 1.75rem; min-width: 1.75rem;" title="Hapus kolom">
                        <x-icon name="trash" class="h-3.5 w-3.5" />
                    </button>
                </div>

                <div
                    class="kanban-cards"
                    wire:sort="moveCard"
                    wire:sort:group="board-{{ $board->id }}"
                    wire:sort:group-id="{{ $column->id }}"
                >
                    @foreach ($column->cards as $card)
                        <div class="kanban-card" wire:key="card-{{ $card->id }}" wire:sort:item="{{ $card->id }}">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-medium" style="color: var(--crm-heading);">{{ $card->title }}</p>
                                <button wire:sort:ignore wire:click="editCard({{ $card->id }})" class="crm-icon-button" style="min-height: 1.5rem; min-width: 1.5rem;" title="Edit">
                                    <x-icon name="pencil" class="h-3 w-3" />
                                </button>
                            </div>
                            @if ($card->body)
                                <p class="mt-1 line-clamp-2 text-xs" style="color: var(--crm-muted);">{{ $card->body }}</p>
                            @endif
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                <span class="kanban-priority" data-priority="{{ $card->priority }}">{{ \App\Models\Card::PRIORITIES[$card->priority] ?? $card->priority }}</span>
                                @foreach (($card->tags ?? []) as $tag)
                                    <span class="journal-tag">#{{ $tag }}</span>
                                @endforeach
                                @if ($card->due_on)
                                    <span class="ml-auto text-xs" style="color: var(--crm-subtle);">{{ $card->due_on->format('d M') }}</span>
                                @endif
                            </div>
                            @if ($card->project_id)
                                <p class="mt-2 text-xs font-medium text-income">✓ Terhubung ke project</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <form wire:submit="addCard({{ $column->id }})" class="mt-2">
                    <input wire:model="newCardTitle.{{ $column->id }}" class="crm-field mt-0" placeholder="+ Tambah kartu" style="font-size: 0.8rem;">
                </form>
            </div>
        @endforeach

        @if ($columns->isEmpty())
            <div class="crm-card p-6 text-sm" style="color: var(--crm-muted);">Belum ada kolom. Tambahkan kolom pertama di atas.</div>
        @endif
    </div>

    {{-- Card editor modal --}}
    @if ($editingCard)
        <div class="fixed inset-0 z-[80] flex items-start justify-center px-4 pt-20" wire:key="card-editor">
            <div class="crm-mobile-backdrop absolute inset-0" wire:click="closeCard"></div>
            <div class="crm-card relative w-full max-w-lg p-5">
                <h3 class="mb-4 text-lg font-semibold" style="color: var(--crm-heading);">Edit kartu</h3>
                <div class="space-y-3">
                    <div>
                        <label class="crm-label">Judul</label>
                        <input wire:model="cardTitle" class="crm-field">
                        @error('cardTitle') <p class="mt-1 text-xs text-expense">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="crm-label">Catatan</label>
                        <textarea wire:model="cardBody" rows="5" class="crm-field"></textarea>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="crm-label">Prioritas</label>
                            <select wire:model="cardPriority" class="crm-field">
                                @foreach ($priorities as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="crm-label">Deadline</label>
                            <input wire:model="cardDueOn" type="date" class="crm-field">
                        </div>
                    </div>
                    <div>
                        <label class="crm-label">Tags</label>
                        <input wire:model="cardTags" class="crm-field" placeholder="riset, konten, urgent">
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <button wire:click="deleteCard({{ $editingCardId }})" wire:confirm="Hapus kartu ini?" class="crm-button-secondary text-expense">Hapus</button>
                        <button wire:click="convertToProject({{ $editingCardId }})" class="crm-button-secondary"><x-icon name="briefcase" class="h-4 w-4" />Jadikan project</button>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="closeCard" class="crm-button-secondary">Batal</button>
                        <button wire:click="saveCard" class="crm-button-primary">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
