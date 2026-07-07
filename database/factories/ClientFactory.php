<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company_name' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'whatsapp' => fake()->phoneNumber(),
            'website' => fake()->url(),
            'source' => fake()->randomElement(['Referral', 'Website', 'LinkedIn', 'Instagram']),
            'status' => fake()->randomElement(array_keys(Client::STATUSES)),
            'tags' => fake()->randomElements(['web', 'retainer', 'ecommerce', 'crm'], 2),
            'notes' => fake()->sentence(),
            'last_contacted_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
