<?php

namespace Database\Factories;

use App\Models\TermsClause;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TermsClause>
 */
class TermsClauseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position' => fake()->unique()->numberBetween(1, 99),
            'title' => fake()->sentence(3),
            'body' => fake()->paragraph(),
        ];
    }
}
