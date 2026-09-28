<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Judge;
use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Judge>
 */
class JudgeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'user_id' => User::factory()->withRole(UserRole::Judge),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role_title' => 'Judge',
            'bio' => fake()->optional()->paragraph(),
            'is_public' => true,
            'is_active' => true,
        ];
    }

    public function forSeason(Season $season): static
    {
        return $this->state(fn (array $attributes): array => [
            'season_id' => $season->getKey(),
        ]);
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => null,
            'role_title' => 'Guest judge',
        ]);
    }
}
