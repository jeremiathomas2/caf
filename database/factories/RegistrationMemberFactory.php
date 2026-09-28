<?php

namespace Database\Factories;

use App\Models\Registration;
use App\Models\RegistrationMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationMember>
 */
class RegistrationMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('+2557## ### ###'),
            'part' => fake()->randomElement(['Soprano', 'Alto', 'Tenor', 'Bass', 'Accompanist']),
            'is_lead' => false,
            'sort_order' => 0,
        ];
    }

    public function lead(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_lead' => true,
            'sort_order' => 0,
        ]);
    }
}
