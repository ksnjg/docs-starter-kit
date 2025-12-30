<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->paragraphs(5, true),
            'status' => 'published',
            'order' => fake()->numberBetween(0, 100),
            'parent_id' => null,
            'seo_title' => $title,
            'seo_description' => fake()->sentence(10),
            'source' => 'cms',
            'git_path' => null,
            'git_last_commit' => null,
            'git_last_author' => null,
            'updated_at_git' => null,
            'created_by' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    public function fromGit(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'git',
            'git_path' => 'docs/'.Str::slug($attributes['title']).'.md',
            'git_last_commit' => fake()->sha1(),
            'git_last_author' => fake()->name(),
            'updated_at_git' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function withAuthor(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user?->id ?? User::factory(),
        ]);
    }

    public function child(Page $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }
}
