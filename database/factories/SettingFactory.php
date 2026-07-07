<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'business_name' => fake()->company(),
            'business_email' => fake()->safeEmail(),
            'business_phone' => fake()->phoneNumber(),
            'business_whatsapp' => fake()->phoneNumber(),
            'business_website' => fake()->url(),
            'default_currency' => 'IDR',
            'invoice_prefix' => 'INV',
            'invoice_next_number' => fake()->numberBetween(1, 99),
            'default_tax_percentage' => 0,
        ];
    }
}
