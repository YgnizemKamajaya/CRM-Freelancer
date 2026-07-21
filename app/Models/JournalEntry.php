<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;

    /** 1 (terburuk) .. 5 (terbaik) */
    public const MOODS = [
        1 => 'Buruk',
        2 => 'Kurang',
        3 => 'Biasa',
        4 => 'Baik',
        5 => 'Bagus',
    ];

    public const MOOD_EMOJI = [
        1 => '😞',
        2 => '😕',
        3 => '😐',
        4 => '🙂',
        5 => '😄',
    ];

    protected $fillable = [
        'entry_date',
        'mood',
        'title',
        'body',
        'tags',
        'word_count',
        'client_id',
        'project_id',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'mood' => 'integer',
            'tags' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (JournalEntry $entry) {
            $entry->word_count = Str::of((string) $entry->body)
                ->stripTags()
                ->squish()
                ->explode(' ')
                ->filter()
                ->count();
        });
    }

    public function moodLabel(): ?string
    {
        return $this->mood ? (self::MOODS[$this->mood] ?? null) : null;
    }

    public function moodEmoji(): ?string
    {
        return $this->mood ? (self::MOOD_EMOJI[$this->mood] ?? null) : null;
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%");
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
