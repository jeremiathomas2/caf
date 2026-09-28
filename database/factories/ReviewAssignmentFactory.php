<?php

namespace Database\Factories;

use App\Enums\AssignmentStatus;
use App\Models\Judge;
use App\Models\Registration;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewAssignment>
 */
class ReviewAssignmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'review_round_id' => ReviewRound::factory(),
            'registration_id' => Registration::factory(),
            'judge_id' => Judge::factory(),
            'status' => AssignmentStatus::Pending->value,
        ];
    }

    public function forRound(ReviewRound $round): static
    {
        return $this->state(fn (array $attributes): array => [
            'review_round_id' => $round->getKey(),
        ]);
    }

    public function forRegistration(Registration $registration): static
    {
        return $this->state(fn (array $attributes): array => [
            'registration_id' => $registration->getKey(),
        ]);
    }

    public function forJudge(Judge $judge): static
    {
        return $this->state(fn (array $attributes): array => [
            'judge_id' => $judge->getKey(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AssignmentStatus::Completed->value,
            'completed_at' => now()->subHours(fake()->numberBetween(1, 48)),
        ]);
    }
}
