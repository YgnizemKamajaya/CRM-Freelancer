<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Goal extends Model
{
    use HasFactory;

    public const TYPES = [
        'personal' => 'Pribadi',
        'financial' => 'Finansial',
    ];

    /** Preset gradient banners (CSS values live in app.css as .goal-banner--{key}). */
    public const GRADIENTS = [
        'sunset' => 'Sunset',
        'ocean' => 'Ocean',
        'forest' => 'Forest',
        'grape' => 'Grape',
        'gold' => 'Gold',
        'slate' => 'Slate',
    ];

    protected $fillable = [
        'title',
        'type',
        'metric_label',
        'banner_gradient',
        'banner_path',
        'target_amount',
        'current_amount',
        'wallet_id',
        'auto_track',
        'due_date',
        'is_archived',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'auto_track' => 'boolean',
            'is_archived' => 'boolean',
            'is_pinned' => 'boolean',
            'due_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Remove an uploaded banner image when the goal is deleted.
        static::deleting(function (Goal $goal) {
            if ($goal->banner_path && Storage::disk('local')->exists($goal->banner_path)) {
                Storage::disk('local')->delete($goal->banner_path);
            }
        });
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /** Gradient CSS-class suffix, falling back to a stable default. */
    public function gradientKey(): string
    {
        return array_key_exists((string) $this->banner_gradient, self::GRADIENTS)
            ? $this->banner_gradient
            : 'slate';
    }

    public function hasUploadedBanner(): bool
    {
        return (bool) $this->banner_path;
    }

    /** Progress percentage, capped 0..100. `current` may be overridden by live finance data. */
    public function percent(?float $current = null): int
    {
        $current ??= (float) $this->current_amount;
        $target = (float) $this->target_amount;

        if ($target <= 0) {
            return 0;
        }

        return (int) max(0, min(100, round(($current / $target) * 100)));
    }
}
