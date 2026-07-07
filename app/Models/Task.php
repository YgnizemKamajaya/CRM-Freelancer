<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'follow_up' => 'Follow-up',
        'call' => 'Call',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
        'meeting' => 'Meeting',
        'project_task' => 'Project Task',
        'invoice_reminder' => 'Invoice Reminder',
        'admin' => 'Admin',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'done' => 'Done',
        'cancelled' => 'Cancelled',
    ];

    public const PRIORITIES = Project::PRIORITIES;

    protected $fillable = [
        'user_id',
        'client_id',
        'deal_id',
        'project_id',
        'invoice_id',
        'title',
        'description',
        'type',
        'status',
        'priority',
        'due_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
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
