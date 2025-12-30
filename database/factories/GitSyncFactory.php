<?php

namespace Database\Factories;

use App\Models\GitSync;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GitSync>
 */
class GitSyncFactory extends Factory
{
    protected $model = GitSync::class;

    public function definition(): array
    {
        return [
            'commit_hash' => fake()->sha1(),
            'commit_message' => fake()->sentence(),
            'commit_author' => fake()->name(),
            'commit_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'sync_status' => 'success',
            'files_changed' => fake()->numberBetween(1, 50),
            'error_message' => null,
        ];
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'sync_status' => 'success',
            'error_message' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'sync_status' => 'failed',
            'error_message' => fake()->sentence(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'sync_status' => 'in_progress',
            'files_changed' => 0,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'sync_status' => 'pending',
            'files_changed' => 0,
        ]);
    }
}
