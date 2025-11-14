<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'domain' => fake()->randomElement(['software_development', 'healthcare', 'finance', 'education', 'retail', 'manufacturing', 'technology', 'other']),
            'status' => fake()->randomElement(['draft', 'planning', 'active', 'in_progress', 'on_hold', 'completed', 'cancelled']),
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'goals' => [
                ['id' => 1, 'title' => 'Goal 1', 'description' => 'First goal', 'priority' => 'high'],
            ],
        ];
    }
}
