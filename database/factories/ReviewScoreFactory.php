<?php

namespace Database\Factories;

use App\Models\ReviewAssignment;
use App\Models\ReviewScore;
use App\Models\RubricCriterion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewScore>
 */
class ReviewScoreFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'review_assignment_id' => ReviewAssignment::factory(),
            'rubric_criterion_id' => RubricCriterion::factory(),
            'points' => fake()->numberBetween(5, 10),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
