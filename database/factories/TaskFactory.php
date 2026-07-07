<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(array_keys(Task::TYPES)),
            'status' => fake()->randomElement(array_keys(Task::STATUSES)),
            'priority' => fake()->randomElement(array_keys(Task::PRIORITIES)),
            'due_at' => fake()->dateTimeBetween('-1 week', '+2 weeks'),
            'completed_at' => null,
        ];
    }
}
