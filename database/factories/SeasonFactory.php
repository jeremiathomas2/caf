<?php

namespace Database\Factories;

use App\Enums\SeasonState;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Season>
 */
class SeasonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 99);
        $name = 'Season '.$number;
        $starts = fake()->dateTimeBetween('+1 month', '+8 months');

        return [
            'number' => $number,
            'name' => $name,
            'slug' => Str::slug($name),
            'theme' => fake()->words(3, true),
            'tagline' => fake()->sentence(6),
            'state' => SeasonState::Draft->value,
            'is_current' => false,
            'venue' => 'Kigamboni Centre',
            'city' => 'Dar es Salaam',
            'country' => 'Tanzania',
            'starts_on' => $starts,
            'ends_on' => (clone $starts)->modify('+2 days'),
            'registration_opens_at' => now()->subMonth(),
            'registration_closes_at' => $starts,
            'early_bird_closes_at' => $starts->modify('-1 month'),
            'currency' => 'TZS',
            'secondary_currency' => 'USD',
            'per_head_fee' => 150000,
            'early_bird_fee' => 125000,
            'min_partial_payment_pct' => 50,
            'rounding_increment' => 100,
            'usd_fx_rate' => 2585.40,
            'summary' => fake()->paragraph(),
        ];
    }

    public function live(): static
    {
        return $this->state(fn (array $attributes): array => [
            'state' => SeasonState::Live->value,
            'is_current' => true,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'state' => SeasonState::Archived->value,
            'is_current' => false,
        ]);
    }
}
