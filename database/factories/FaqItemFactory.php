<?php

namespace Database\Factories;

use App\Models\FaqItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FaqItem>
 */
class FaqItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'season_id' => null,
            'question' => fake()->sentence(6).'?',
            'answer' => fake()->paragraph(),
            'is_public' => true,
            'sort_order' => 1,
        ];
    }
}
