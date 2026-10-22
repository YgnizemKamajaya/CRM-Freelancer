<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    public const STAGES = [
        'new_inquiry' => 'New Inquiry',
        'qualified' => 'Qualified',
        'proposal_sent' => 'Proposal Sent',
        'negotiation' => 'Negotiation',
        'won' => 'Won',
        'lost' => 'Lost',
    ];

    public const SOURCES = [
        'referral' => 'Referral',
        'website' => 'Website',
        'linkedin' => 'LinkedIn',
        'instagram' => 'Instagram',
        'whatsapp' => 'WhatsApp',
        'marketplace' => 'Marketplace',
        'existing_client' => 'Existing Client',
        'other' => 'Other',
    ];

    protected $fillable = [
        'client_id',
        'title',
        'description',
        'value',
        'currency',
        'stage',
        'probability',
        'source',
        'expected_close_date',
        'next_follow_up_at',
        'status',
        'lost_reason',
        'won_at',
        'lost_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'expected_close_date' => 'date',
            'next_follow_up_at' => 'datetime',
            'won_at' => 'datetime',
            'lost_at' => 'datetime',
        ];
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhereHas('client', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"));
        });
    }

    public function getWeightedValueAttribute(): float
    {
        return (float) $this->value * ($this->probability / 100);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
