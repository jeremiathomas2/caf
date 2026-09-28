<?php

namespace Database\Factories;

use App\Enums\RoundStatus;
use App\Models\ReviewRound;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewRound>
 */
class ReviewRoundFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'name' => 'Preliminary Screening',
            'sequence' => 1,
            'status' => RoundStatus::Upcoming->value,
            'is_blind' => true,
            'aggregation' => 'Trimmed mean (drop high & low)',
            'opens_at' => now()->addWeek(),
            'closes_at' => now()->addWeeks(2),
        ];
    }

    public function forSeason(Season $season): static
    {
        return $this->state(fn (array $attributes): array => [
            'season_id' => $season->getKey(),
        ]);
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RoundStatus::Open->value,
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addWeek(),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RoundStatus::Published->value,
            'opens_at' => now()->subMonth(),
            'closes_at' => now()->subWeek(),
            'published_at' => now()->subDays(3),
        ]);
    }
}
