<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_name',
        'owner_email',
        'avatar_path',
        'business_name',
        'business_email',
        'business_phone',
        'business_whatsapp',
        'business_website',
        'logo_path',
        'signature_path',
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

}
