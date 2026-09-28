<?php

namespace Database\Factories;

use App\Enums\ContentType;
use App\Enums\PublishStatus;
use App\Models\ContentPage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ContentPage>
 */
class ContentPageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'type' => ContentType::Page->value,
            'status' => PublishStatus::Draft->value,
            'body' => fake()->paragraphs(3, true),
            'excerpt' => fake()->sentence(),
            'is_in_footer' => false,
            'meta' => ['path' => '/'.Str::slug($title)],
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PublishStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);
    }

    public function news(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => ContentType::News->value,
            'status' => PublishStatus::Published->value,
            'published_at' => now()->subDays(2),
        ]);
    }
}
