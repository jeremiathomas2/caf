<?php

namespace Database\Factories;

use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sponsor>
 */
class SponsorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'season_id' => null,
            'name' => fake()->company(),
            'tier' => fake()->randomElement(['Platinum', 'Gold', 'Silver', 'Partner', 'In-kind']),
            'description' => fake()->optional()->sentence(),
            'website' => 'https://'.fake()->domainName(),
            'is_public' => true,
            'sort_order' => 1,
        ];
    }
}
