<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    public const TYPES = [
        'income' => 'Income',
        'expense' => 'Expense',
    ];

    public const SUGGESTED_CATEGORIES = [
        'Client work',
        'Retainer',
        'Bonus',
        'Refund',
        'Other income',
        'Software & tools',
        'Hosting & domain',
        'Equipment',
        'Rent',
        'Utilities',
        'Transport',
        'Food',
        'Marketing',
        'Taxes',
        'Other expense',
    ];

    protected $fillable = [
        'wallet_id',
        'type',
        'category',
        'amount',
        'currency',
        'note',
        'occurred_on',
        'invoice_id',
        'project_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'occurred_on' => 'date',
        ];
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query->where('category', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%");
        });
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
