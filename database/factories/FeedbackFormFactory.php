<?php

namespace Database\Factories;

use App\Models\FeedbackForm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeedbackForm>
 */
class FeedbackFormFactory extends Factory
{
    protected $model = FeedbackForm::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'trigger_type' => fake()->randomElement(['positive', 'negative', 'always']),
            'fields' => [
                [
                    'name' => 'comment',
                    'type' => 'textarea',
                    'label' => 'Your feedback',
                    'required' => false,
                ],
            ],
            'is_active' => true,
        ];
    }

    public function forPositive(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type' => 'positive',
        ]);
    }

    public function forNegative(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type' => 'negative',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withDetailedFields(): static
    {
        return $this->state(fn (array $attributes) => [
            'fields' => [
                [
                    'name' => 'rating',
                    'type' => 'rating',
                    'label' => 'Rate this page',
                    'required' => true,
                ],
                [
                    'name' => 'comment',
                    'type' => 'textarea',
                    'label' => 'Tell us more',
                    'required' => false,
                ],
                [
                    'name' => 'email',
                    'type' => 'email',
                    'label' => 'Your email (optional)',
                    'required' => false,
                ],
            ],
        ]);
    }
}
