<?php

namespace App\Services;

use App\Models\JournalEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

// TODO(audit D1): add month calendar view — planned, only list + mood chart exist. See AUDIT-TODO.md.
class JournalService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $entries = JournalEntry::query()->latest('entry_date')->latest()->limit(10)->get();
        $monthEntries = JournalEntry::query()
            ->whereBetween('entry_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->get(['mood', 'word_count', 'entry_date']);

        $moodValues = $monthEntries->pluck('mood')->filter();

        return [
            'recent_entries' => $entries,
            'total_entries' => JournalEntry::count(),
            'month_entries' => $monthEntries->count(),
            'month_words' => (int) $monthEntries->sum('word_count'),
            'avg_mood' => $moodValues->isNotEmpty() ? round($moodValues->avg(), 1) : null,
            'current_streak' => $this->currentStreak(),
            'mood_trend' => $this->moodTrend(),
            'top_tags' => $this->topTags(),
        ];
    }

    /**
     * Consecutive days (including today or yesterday) with at least one entry.
     */
    public function currentStreak(): int
    {
        $dates = JournalEntry::query()
            ->orderByDesc('entry_date')
            ->limit(400)
            ->pluck('entry_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $cursor = today();

        // Allow the streak to still count if the last entry was yesterday (today not written yet).
        if ($dates->first() === today()->toDateString()) {
            $cursor = today();
        } elseif ($dates->first() === today()->subDay()->toDateString()) {
            $cursor = today()->subDay();
        } else {
            return 0;
        }

        $streak = 0;
        $lookup = $dates->flip();

        while ($lookup->has($cursor->toDateString())) {
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    /**
     * @return Collection<int, array{label: string, mood: float|null}>
     */
    public function moodTrend(int $days = 14): Collection
    {
        $start = today()->subDays($days - 1);

        $byDate = JournalEntry::query()
            ->whereNotNull('mood')
            ->where('entry_date', '>=', $start)
            ->get(['entry_date', 'mood'])
            ->groupBy(fn (JournalEntry $entry) => $entry->entry_date->toDateString());

        return collect(range(0, $days - 1))->map(function (int $offset) use ($start, $byDate) {
            $date = $start->copy()->addDays($offset);
            $dayEntries = $byDate->get($date->toDateString());

            return [
                'label' => $date->format('d/m'),
                'mood' => $dayEntries ? round($dayEntries->avg('mood'), 1) : null,
            ];
        });
    }

    /**
     * @return Collection<int, array{tag: string, count: int}>
     */
    public function topTags(int $limit = 12): Collection
    {
        return JournalEntry::query()
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take($limit)
            ->map(fn (int $count, string $tag) => ['tag' => $tag, 'count' => $count])
            ->values();
    }
}
