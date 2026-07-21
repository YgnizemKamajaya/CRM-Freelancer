<?php

namespace App\Livewire;

use App\Models\ChecklistItem;
use App\Models\Task;
use Livewire\Component;

/**
 * Interactive "Focus today" card embedded on the Dashboard 2.0 home.
 * Shares the checklist_items table with the full Today page.
 */
class HomeFocusList extends Component
{
    // TODO(audit V8): verify this embedded Livewire card toggles/adds without a
    //   full page reload and stays in sync with the Today page. See AUDIT-TODO.md.

    public string $newTitle = '';

    public function render()
    {
        $date = today()->toDateString();

        $items = ChecklistItem::query()
            ->whereDate('for_date', $date)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return view('livewire.home-focus-list', [
            'items' => $items->take(6),
            'total' => $items->count(),
            'done' => $items->where('is_done', true)->count(),
        ]);
    }

    public function addItem(): void
    {
        $title = trim($this->newTitle);

        if ($title === '') {
            return;
        }

        $date = today()->toDateString();

        ChecklistItem::create([
            'title' => $title,
            'for_date' => $date,
            'position' => (int) ChecklistItem::whereDate('for_date', $date)->max('position') + 1,
        ]);

        $this->newTitle = '';
    }

    public function toggle(int $itemId): void
    {
        $item = ChecklistItem::findOrFail($itemId);
        $item->update([
            'is_done' => ! $item->is_done,
            'completed_at' => ! $item->is_done ? now() : null,
        ]);

        // Reflect completion onto a linked CRM task, matching the Today page.
        if ($item->task_id && $item->is_done) {
            Task::whereKey($item->task_id)
                ->whereNotIn('status', ['done', 'cancelled'])
                ->update(['status' => 'done', 'completed_at' => now()]);
        }
    }
}
