<?php

namespace Database\Factories;

use App\Models\RubricCriterion;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RubricCriterion>
 */
class RubricCriterionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'name' => fake()->randomElement([
                'Vocal technique',
                'Harmony & musicality',
                'Stagecraft & stage presence',
                'Song choice & arrangement',
                'Audience impact & storytelling',
                'Originality & identity',
            ]),
            'max_points' => 10,
            'weight' => 20,
            'sort_order' => 1,
        ];
    }

    public function forSeason(Season $season): static
    {
        return $this->state(fn (array $attributes): array => [
            'season_id' => $season->getKey(),
        ]);
    }
}
