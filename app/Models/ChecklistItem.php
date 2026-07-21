<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'is_done',
        'position',
        'for_date',
        'task_id',
        'notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
            'for_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('for_date', $date);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
