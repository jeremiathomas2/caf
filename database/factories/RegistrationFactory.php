<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationRole;
use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'code' => 'CAF-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'group_name' => fake()->unique()->company(),
            'category' => fake()->randomElement(RegistrationRole::Singers->categories()),
            'role_type' => RegistrationRole::Singers->value,
            'country' => 'Tanzania',
            'city' => fake()->randomElement(['Dar es Salaam', 'Arusha', 'Mwanza', 'Dodoma', 'Tanga']),
            'members_count' => fake()->numberBetween(3, 12),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->unique()->safeEmail(),
            'contact_phone' => fake()->numerify('+2557## ### ###'),
            'performance_link' => 'https://youtube.com/watch?v='.fake()->bothify('???????????'),
            'notes' => fake()->optional()->sentence(),
            'status' => RegistrationStatus::Submitted->value,
            'payment_status' => PaymentStatus::Unpaid->value,
            'source' => 'web',
            'tags' => [],
            'is_public' => false,
            'started_at' => now()->subWeek(),
            'submitted_at' => now()->subDays(3),
        ];
    }

    public function forSeason(Season $season): static
    {
        return $this->state(fn (array $attributes): array => [
            'season_id' => $season->id,
            'code' => $season->codePrefix().'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function withStatus(RegistrationStatus $status): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => $status->value,
        ]);
    }

    public function withPaymentStatus(PaymentStatus $status): static
    {
        return $this->state(fn (array $attributes): array => [
            'payment_status' => $status->value,
        ]);
    }

    public function confirmed(): static
    {
        return $this->withStatus(RegistrationStatus::Confirmed)
            ->withPaymentStatus(PaymentStatus::Paid)
            ->state(fn (array $attributes): array => [
                'is_public' => true,
                'approved_at' => now()->subWeek(),
                'confirmed_at' => now()->subDays(4),
            ]);
    }
}
