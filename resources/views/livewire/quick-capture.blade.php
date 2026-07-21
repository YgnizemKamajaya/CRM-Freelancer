<div
    class="crm-command-panel"
    x-data="{
        activeIndex: 0,
        items() {
            return this.$refs.list ? Array.from(this.$refs.list.querySelectorAll('[data-cmdk-item]')) : [];
        },
        move(delta) {
            const items = this.items();
            if (! items.length) return;
            this.activeIndex = (this.activeIndex + delta + items.length) % items.length;
            items[this.activeIndex]?.scrollIntoView({ block: 'nearest' });
        },
        select() {
            const items = this.items();
            items[this.activeIndex]?.click();
        },
    }"
    x-init="
        $watch('$wire.query', () => activeIndex = 0);
        $watch('commandOpen', (open) => {
            if (open) { $nextTick(() => $refs.commandSearchInput && $refs.commandSearchInput.focus()); }
        });
    "
    @keydown.down.prevent="move(1)"
    @keydown.up.prevent="move(-1)"
    @keydown.enter.prevent="select()"
>
    <div class="crm-command-input-wrap">
        <x-icon name="search" class="h-5 w-5" />
        <input
            type="text"
            wire:model.live.debounce.150ms="query"
            x-ref="commandSearchInput"
            class="crm-command-input"
            placeholder="Cari client, deal, project, invoice..."
            autocomplete="off"
        >
        <button type="button" class="crm-icon-button" style="min-height: 2.25rem; min-width: 2.25rem;"
            @click="commandOpen = false" aria-label="Tutup pencarian cepat">
            <x-icon name="x" class="h-4 w-4" />
        </button>
    </div>

    <div class="crm-command-list" x-ref="list">
        @forelse ($groups as $group)
            <p class="crm-command-group-label">{{ $group['label'] }}</p>
            @foreach ($group['items'] as $item)
                <a
                    href="{{ $item['href'] }}"
                    wire:navigate
                    data-cmdk-item
                    @click="commandOpen = false"
                    x-bind:class="items()[activeIndex] === $el ? 'is-active' : ''"
                    class="crm-command-item"
                >
                    <x-icon :name="$item['icon']" class="h-4 w-4" />
                    <span>{{ $item['label'] }}</span>
                    @if (!empty($item['soon']))
                        <span class="crm-nav-soon">Segera</span>
                    @endif
                </a>
            @endforeach
        @empty
            <p class="crm-command-empty">Tidak ada hasil untuk &quot;{{ $query }}&quot;.</p>
        @endforelse
    </div>

    <div class="crm-command-hint">
        <span><span class="crm-command-kbd">&uarr;</span> <span class="crm-command-kbd">&darr;</span> pilih &middot; <span class="crm-command-kbd">&crarr;</span> buka</span>
        <span><span class="crm-command-kbd">Esc</span> tutup</span>
    </div>
</div>
