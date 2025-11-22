<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Core\Models\Task>
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
        $statuses = ['Not Started', 'In Progress', 'Completed', 'Deferred', 'Waiting For Someone'];
        $priorities = ['Low', 'Medium', 'High', 'Urgent'];
        $tags = ['urgent', 'client', 'internal', 'bug-fix', 'feature', 'documentation', 'testing', 'review'];

        $startDate = fake()->optional(0.7)->dateTimeBetween('-3 months', '+1 month');
        $dueDate = $startDate ? fake()->optional(0.8)->dateTimeBetween($startDate, '+6 months') : null;

        // Get a random category ID if categories exist
        $categoryId = null;
        if (class_exists('\App\Core\Models\TaskCategory') && \App\Core\Models\TaskCategory::count() > 0) {
            $categoryId = \App\Core\Models\TaskCategory::inRandomOrder()->first()->id;
        }

        return [
            'name' => fake()->sentence(fake()->numberBetween(2, 8)),
            'description' => fake()->optional(0.8)->paragraphs(fake()->numberBetween(1, 3), true),
            'category_id' => $categoryId,
            'tags' => fake()->optional(0.6)->randomElements($tags, fake()->numberBetween(1, 3)),
            'status' => fake()->randomElement($statuses),
            'priority' => fake()->randomElement($priorities),
            'hourly_rate' => fake()->optional(0.4)->randomFloat(2, 25, 150),
            'estimated_hours' => fake()->optional(0.7)->numberBetween(1, 80),
            'progress' => fake()->numberBetween(0, 100),
            'is_billable' => fake()->boolean(30), // 30% chance of being billable
            'start_date' => $startDate?->format('Y-m-d'),
            'due_date' => $dueDate?->format('Y-m-d'),
            'is_active' => fake()->boolean(95), // 95% chance of being active
        ];
    }

    /**
     * Indicate that the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Completed',
            'progress' => 100,
        ]);
    }

    /**
     * Indicate that the task is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-2 months', '-1 day')->format('Y-m-d'),
            'status' => fake()->randomElement(['Not Started', 'In Progress', 'Waiting For Someone']),
        ]);
    }

    /**
     * Indicate that the task is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => fake()->randomElement(['High', 'Urgent']),
        ]);
    }

    /**
     * Indicate that the task is billable.
     */
    public function billable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_billable' => true,
            'hourly_rate' => fake()->randomFloat(2, 50, 200),
        ]);
    }

    /**
     * Indicate that the task is a subtask.
     */
    public function subtask(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_task_id' => \App\Core\Models\Task::factory(),
        ]);
    }
}
