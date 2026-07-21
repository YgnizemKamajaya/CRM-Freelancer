<section class="crm-card home-card command-panel command-focus">
    <div class="home-card-head">
        <h2 class="home-card-title">Fokus hari ini</h2>
        <a href="{{ route('today.index') }}" wire:navigate class="home-card-link">Buka →</a>
    </div>

    @if ($total)
        <div class="crm-meter mb-3">
            <div style="width: {{ (int) round(($done / max(1, $total)) * 100) }}%"></div>
        </div>
    @endif

    <ul class="home-checklist">
        @foreach ($items as $item)
            <li wire:key="hf-{{ $item->id }}">
                <button wire:click="toggle({{ $item->id }})" class="home-check {{ $item->is_done ? 'is-done' : '' }}" aria-label="Tandai selesai">
                    @if ($item->is_done)<x-icon name="check" class="h-3 w-3" />@endif
                </button>
                <span class="{{ $item->is_done ? 'home-check-done' : '' }}">{{ $item->title }}</span>
            </li>
        @endforeach
    </ul>

    <form wire:submit="addItem" class="home-focus-add">
        <input wire:model="newTitle" class="crm-field mt-0" placeholder="+ Tambah tugas hari ini">
    </form>

    @if (! $total)
        <p class="home-empty" style="margin-top: 0.5rem;">Ketik tugas pertamamu di atas, atau <a href="{{ route('today.index') }}" wire:navigate class="home-inline-link">buka Hari ini →</a></p>
    @endif
</section>
