<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
    use HasFactory;

    public const PRIORITIES = Project::PRIORITIES;

    protected $fillable = [
        'board_column_id',
        'title',
        'body',
        'tags',
        'priority',
        'due_on',
        'position',
        'project_id',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'due_on' => 'date',
        ];
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(BoardColumn::class, 'board_column_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
