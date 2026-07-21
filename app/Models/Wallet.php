<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    public const TYPES = [
        'cash' => 'Cash',
        'bank' => 'Bank',
        'ewallet' => 'E-wallet',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name',
        'type',
        'currency',
        'opening_balance',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_archived' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
