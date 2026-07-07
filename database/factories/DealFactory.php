<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deal>
 */
class DealFactory extends Factory
{
    protected $model = Deal::class;

    public function definition(): array
    {
        $stage = fake()->randomElement(array_keys(Deal::STAGES));

        return [
            'client_id' => Client::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'value' => fake()->numberBetween(2_000_000, 30_000_000),
            'currency' => 'IDR',
            'stage' => $stage,
            'probability' => $stage === 'won' ? 100 : ($stage === 'lost' ? 0 : fake()->numberBetween(10, 80)),
            'source' => fake()->randomElement(array_keys(Deal::SOURCES)),
            'expected_close_date' => fake()->dateTimeBetween('now', '+2 months'),
            'next_follow_up_at' => fake()->dateTimeBetween('now', '+2 weeks'),
            'status' => $stage === 'won' ? 'won' : ($stage === 'lost' ? 'lost' : 'open'),
            'won_at' => $stage === 'won' ? now() : null,
            'lost_at' => $stage === 'lost' ? now() : null,
        ];
    }
}
