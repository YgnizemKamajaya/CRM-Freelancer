<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(array_keys(Project::STATUSES)),
            'priority' => fake()->randomElement(array_keys(Project::PRIORITIES)),
            'budget' => fake()->numberBetween(2_000_000, 25_000_000),
            'currency' => 'IDR',
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'deadline' => fake()->dateTimeBetween('now', '+2 months'),
            'progress' => fake()->numberBetween(0, 95),
        ];
    }
}
