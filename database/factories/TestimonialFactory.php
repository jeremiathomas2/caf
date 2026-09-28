<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'season_id' => null,
            'author_name' => fake()->name(),
            'author_role' => fake()->randomElement(['Season 1 winner', 'Judge', 'Volunteer', 'Group leader']),
            'quote' => fake()->paragraph(),
            'is_public' => true,
            'sort_order' => 1,
        ];
    }
}
