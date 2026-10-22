<?php

namespace App\Livewire;

use App\Models\ChecklistItem;
use App\Models\Task;
use App\Services\ProjectProgressService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class TodayChecklist extends Component
{
    // TODO(audit V5): verify wire:sort reorder persists and Livewire auto-discovers
    //   this component. Requires npm run build. See AUDIT-TODO.md.

    #[Url]
    public string $date = '';

    public string $newTitle = '';

    public function mount(?string $date = null): void
    {
        $this->date = $date ?: ($this->date ?: today()->toDateString());
        $this->validate(['date' => ['required', 'date_format:Y-m-d']]);
    }

    public function updatedDate(): void
    {
        $this->validate(['date' => ['required', 'date_format:Y-m-d']]);
    }

    public function changeDate(int $offset): void
    {
        $this->date = Carbon::parse($this->date)->addDays($offset)->toDateString();
    }

    public function render()
    {
        $current = Carbon::parse($this->date);

        $items = ChecklistItem::query()
            ->with('task')
            ->forDate($this->date)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return view('livewire.today-checklist', [
            'items' => $items,
            'doneCount' => $items->where('is_done', true)->count(),
            'isToday' => $current->isToday(),
            'prettyDate' => $current->translatedFormat('l, d F Y'),
            'pullableTasks' => $this->pullableTasks(),
        ]);
    }

    public function addItem(): void
    {
        $title = trim($this->newTitle);

        if ($title === '') {
            return;
        }

        ChecklistItem::create([
            'title' => $title,
            'for_date' => $this->date,
            'position' => (int) ChecklistItem::forDate($this->date)->max('position') + 1,
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

        // Optionally reflect completion back onto the linked CRM task.
        if ($item->task_id && $item->is_done) {
            Task::whereKey($item->task_id)
                ->whereNotIn('status', ['done', 'cancelled'])
                ->update(['status' => 'done', 'completed_at' => now()]);
            $project = Task::find($item->task_id)?->project;
            if ($project) {
                app(ProjectProgressService::class)->sync($project);
            }
        }
    }

    public function deleteItem(int $itemId): void
    {
        ChecklistItem::whereKey($itemId)->delete();
    }

    /**
     * wire:sort handler — persist the new order within today's list.
     */
    public function reorder(int $itemId, int $position): void
    {
        DB::transaction(function () use ($itemId, $position) {
            $ordered = ChecklistItem::forDate($this->date)
                ->orderBy('position')
                ->pluck('id')
                ->reject(fn ($id) => $id === $itemId)
                ->values();

            $ordered->splice(max(0, min($position, $ordered->count())), 0, [$itemId]);

            $ordered->each(fn ($id, $index) => ChecklistItem::whereKey($id)->update(['position' => $index]));
        });
    }

    /**
     * Move yesterday's (and earlier) unfinished items into today.
     */
    public function rollOver(): void
    {
        if (! Carbon::parse($this->date)->isToday()) {
            return;
        }

        $base = (int) ChecklistItem::forDate($this->date)->max('position');

        ChecklistItem::query()
            ->whereDate('for_date', '<', $this->date)
            ->where('is_done', false)
            ->orderBy('for_date')
            ->orderBy('position')
            ->get()
            ->each(function (ChecklistItem $item) use (&$base) {
                $item->update(['for_date' => $this->date, 'position' => ++$base]);
            });
    }

    /**
     * Pull a CRM task due today into the checklist (once).
     */
    public function pullTask(int $taskId): void
    {
        $task = Task::whereKey($taskId)->firstOrFail();

        $exists = ChecklistItem::forDate($this->date)->where('task_id', $taskId)->exists();

        if ($exists) {
            return;
        }

        ChecklistItem::create([
            'title' => $task->title,
            'for_date' => $this->date,
            'task_id' => $task->id,
            'position' => (int) ChecklistItem::forDate($this->date)->max('position') + 1,
        ]);
    }

    /**
     * @return Collection<int, Task>
     */
    protected function pullableTasks()
    {
        if (! Carbon::parse($this->date)->isToday()) {
            return collect();
        }

        $alreadyPulled = ChecklistItem::forDate($this->date)
            ->whereNotNull('task_id')
            ->pluck('task_id');

        return Task::query()
            ->whereDate('due_at', today())
            ->whereNotIn('status', ['done', 'cancelled'])
            ->whereNotIn('id', $alreadyPulled)
            ->orderBy('due_at')
            ->limit(20)
            ->get();
    }
}
