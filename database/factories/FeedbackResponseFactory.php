<?php

namespace Database\Factories;

use App\Models\FeedbackForm;
use App\Models\FeedbackResponse;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeedbackResponse>
 */
class FeedbackResponseFactory extends Factory
{
    protected $model = FeedbackResponse::class;

    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'feedback_form_id' => null,
            'is_helpful' => fake()->boolean(70),
            'form_data' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    public function helpful(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_helpful' => true,
        ]);
    }

    public function notHelpful(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_helpful' => false,
        ]);
    }

    public function forPage(Page $page): static
    {
        return $this->state(fn (array $attributes) => [
            'page_id' => $page->id,
        ]);
    }

    public function withForm(FeedbackForm $form): static
    {
        return $this->state(fn (array $attributes) => [
            'feedback_form_id' => $form->id,
            'form_data' => [
                'comment' => fake()->paragraph(),
            ],
        ]);
    }
}
