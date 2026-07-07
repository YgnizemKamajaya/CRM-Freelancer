<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(1_000_000, 20_000_000);

        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numberBetween(1000, 9999),
            'issue_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'currency' => 'IDR',
            'subtotal' => $total,
            'discount' => 0,
            'tax' => 0,
            'total' => $total,
            'amount_paid' => 0,
            'status' => fake()->randomElement(array_keys(Invoice::STATUSES)),
        ];
    }
}
