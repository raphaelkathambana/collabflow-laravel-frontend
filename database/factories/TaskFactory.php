<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => \App\Models\Project::factory(),
            'name' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['ai', 'human', 'hitl']),
            'status' => fake()->randomElement(['generated', 'pending', 'in_progress', 'review', 'completed', 'cancelled', 'blocked']),
            'complexity' => fake()->randomElement(['LOW', 'MEDIUM', 'HIGH', 'VERY_HIGH']),
            'sequence' => fake()->numberBetween(1, 100),
            'estimated_hours' => fake()->numberBetween(1, 40),
            'ai_suitability_score' => fake()->randomFloat(2, 0, 1),
            'confidence_score' => fake()->randomFloat(2, 0, 1),
            'validation_score' => fake()->numberBetween(0, 100),
        ];
    }
}
