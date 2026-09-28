<?php

namespace Database\Factories;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role_title' => fake()->randomElement([
                'Festival Director', 'Artistic Director', 'Head of Finance',
                'Registration Lead', 'Communications Lead', 'Volunteer Coordinator',
            ]),
            'phone' => fake()->numerify('+2557## ### ###'),
            'email' => fake()->unique()->companyEmail(),
            'bio' => fake()->optional()->paragraph(),
            'is_public' => true,
            'sort_order' => 1,
        ];
    }
}
