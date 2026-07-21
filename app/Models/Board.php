<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Board extends Model
{
    use HasFactory;

    public const TYPES = [
        'idea' => 'Ide & brainstorming',
        'project' => 'Rencana project',
        'custom' => 'Custom',
    ];

    protected $fillable = [
        'name',
        'type',
        'description',
        'position',
    ];

    public function columns(): HasMany
    {
        return $this->hasMany(BoardColumn::class)->orderBy('position');
    }

    public function cards(): HasManyThrough
    {
        return $this->hasManyThrough(Card::class, BoardColumn::class);
    }
}
