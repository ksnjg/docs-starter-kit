<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'value' => fake()->word(),
            'group' => 'general',
        ];
    }

    public function theme(): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => 'theme',
        ]);
    }

    public function typography(): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => 'typography',
        ]);
    }
}
