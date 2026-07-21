<?php

namespace App\Livewire;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class BoardShow extends Component
{
    // TODO(audit V1): verify Livewire 4 auto-discovers this class + the
    //   `livewire.board-show` view on first render (no "Unable to find component").
    // TODO(audit V4): full-page render injects into layouts.app {{ $slot }};
    //   $title is unset on the Livewire path (falls back to 'Freelance CRM').
    //   Add #[Title(...)] if a precise tab title is needed. See AUDIT-TODO.md.
    public Board $board;

    // Inline "add card" state, keyed per column.
    // TODO(audit V3): verify dynamic array-key binding
    //   (wire:model="newCardTitle.{columnId}") gives each column an independent
    //   input and Enter creates the card in the correct column. See AUDIT-TODO.md.
    public array $newCardTitle = [];

    #[Validate('required|string|max:255')]
    public string $newColumnName = '';

    // Card editor modal state.
    public bool $editingCard = false;
    public ?int $editingCardId = null;
    public string $cardTitle = '';
    public string $cardBody = '';
    public string $cardTags = '';
    public string $cardPriority = 'medium';
    public ?string $cardDueOn = null;

    public function mount(Board $board): void
    {
        $this->board = $board;
    }

    public function render()
    {
        $columns = $this->board->columns()
            ->with(['cards' => fn ($query) => $query->orderBy('position')])
            ->orderBy('position')
            ->get();

        return view('livewire.board-show', [
            'columns' => $columns,
            'priorities' => Card::PRIORITIES,
        ]);
    }

    /**
     * wire:sort handler. Fires on the destination column only.
     * Persists the card's new column + position, and compacts sibling positions.
     *
     * TODO(audit V2): needs `npm run build`; verify drop actually hits this method
     *   and the new column+position survive a refresh. See AUDIT-TODO.md.
     */
    public function moveCard(int $cardId, int $position, int $columnId): void
    {
        $card = Card::findOrFail($cardId);
        $column = BoardColumn::whereHas('board', fn ($q) => $q->whereKey($this->board->id))
            ->findOrFail($columnId);

        DB::transaction(function () use ($card, $column, $position) {
            $card->board_column_id = $column->id;
            $card->save();

            $ordered = $column->cards()->orderBy('position')->pluck('id')
                ->reject(fn ($id) => $id === $card->id)
                ->values();

            $ordered->splice(max(0, min($position, $ordered->count())), 0, [$card->id]);

            $ordered->each(function ($id, $index) {
                Card::whereKey($id)->update(['position' => $index]);
            });
        });
    }

    public function addCard(int $columnId): void
    {
        $title = trim($this->newCardTitle[$columnId] ?? '');

        if ($title === '') {
            return;
        }

        $column = $this->ownedColumn($columnId);

        $column->cards()->create([
            'title' => $title,
            'priority' => 'medium',
            'position' => (int) $column->cards()->max('position') + 1,
        ]);

        $this->newCardTitle[$columnId] = '';
    }

    public function editCard(int $cardId): void
    {
        $card = $this->ownedCard($cardId);

        $this->editingCardId = $card->id;
        $this->cardTitle = $card->title;
        $this->cardBody = (string) $card->body;
        $this->cardTags = collect($card->tags)->join(', ');
        $this->cardPriority = $card->priority;
        $this->cardDueOn = $card->due_on?->format('Y-m-d');
        $this->editingCard = true;
    }

    public function saveCard(): void
    {
        $this->validate([
            'cardTitle' => 'required|string|max:255',
            'cardBody' => 'nullable|string|max:20000',
            'cardPriority' => 'required|in:'.implode(',', array_keys(Card::PRIORITIES)),
            'cardDueOn' => 'nullable|date',
        ]);

        $card = $this->ownedCard($this->editingCardId);

        $card->update([
            'title' => $this->cardTitle,
            'body' => $this->cardBody ?: null,
            'tags' => collect(explode(',', $this->cardTags))->map(fn ($t) => trim($t))->filter()->values()->all(),
            'priority' => $this->cardPriority,
            'due_on' => $this->cardDueOn ?: null,
        ]);

        $this->closeCard();
    }

    public function deleteCard(int $cardId): void
    {
        $this->ownedCard($cardId)->delete();
        $this->closeCard();
    }

    /**
     * Promote an idea card into a real CRM Project, mirroring the Deal→Project flow.
     */
    public function convertToProject(int $cardId): void
    {
        $card = $this->ownedCard($cardId);

        if ($card->project_id) {
            $this->redirectRoute('projects.show', $card->project_id, navigate: true);

            return;
        }

        $project = Project::create([
            'title' => $card->title,
            'description' => $card->body,
            'status' => 'planned',
            'priority' => $card->priority,
        ]);

        $card->update(['project_id' => $project->id]);

        $this->redirectRoute('projects.edit', $project, navigate: true);
    }

    public function closeCard(): void
    {
        $this->reset(['editingCard', 'editingCardId', 'cardTitle', 'cardBody', 'cardTags', 'cardPriority', 'cardDueOn']);
        $this->cardPriority = 'medium';
    }

    public function addColumn(): void
    {
        $this->validateOnly('newColumnName');

        $this->board->columns()->create([
            'name' => $this->newColumnName,
            'position' => (int) $this->board->columns()->max('position') + 1,
        ]);

        $this->newColumnName = '';
    }

    public function deleteColumn(int $columnId): void
    {
        $this->ownedColumn($columnId)->delete();
    }

    private function ownedColumn(int $columnId): BoardColumn
    {
        return BoardColumn::where('board_id', $this->board->id)->findOrFail($columnId);
    }

    private function ownedCard(?int $cardId): Card
    {
        return Card::whereHas('column', fn ($q) => $q->where('board_id', $this->board->id))
            ->findOrFail($cardId);
    }
}
