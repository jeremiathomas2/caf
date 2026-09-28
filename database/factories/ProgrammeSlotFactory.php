<?php

namespace Database\Factories;

use App\Models\ProgrammeSlot;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgrammeSlot>
 */
class ProgrammeSlotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 month', '+8 months');

        return [
            'season_id' => Season::factory(),
            'event_date' => $start->format('Y-m-d'),
            'stage' => fake()->randomElement(['Main Stage', 'River Stage', 'Youth Stage']),
            'stage_location' => 'Kigamboni Centre',
            'starts_at' => $start->format('H:i'),
            'ends_at' => $start->modify('+45 minutes')->format('H:i'),
            'title' => fake()->sentence(3),
            'status' => 'confirmed',
            'is_public' => true,
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
