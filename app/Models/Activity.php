<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'general_note' => 'General note',
        'call' => 'Call',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
        'meeting' => 'Meeting',
        'proposal_sent' => 'Proposal sent',
        'invoice_sent' => 'Invoice sent',
        'payment_received' => 'Payment received',
        'status_update' => 'Status update',
    ];

    public const CONTACT_TYPES = ['general_note', 'call', 'whatsapp', 'email', 'meeting'];

    protected $fillable = [
        'user_id',
        'client_id',
        'deal_id',
        'project_id',
        'invoice_id',
        'type',
        'body',
        'activity_at',
    ];

    protected function casts(): array
    {
        return [
            'activity_at' => 'datetime',
        ];
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
