<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'business_email',
        'business_phone',
        'business_whatsapp',
        'business_website',
        'logo_path',
        'default_currency',
        'invoice_prefix',
        'invoice_next_number',
        'default_tax_percentage',
    ];

    protected function casts(): array
    {
        return [
            'default_tax_percentage' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
