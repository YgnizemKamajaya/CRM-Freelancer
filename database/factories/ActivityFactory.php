<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(array_keys(Activity::TYPES)),
            'body' => fake()->paragraph(),
            'activity_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
