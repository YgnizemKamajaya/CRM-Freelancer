<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'monthly_limit',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'monthly_limit' => 'decimal:2',
        ];
    }
}
