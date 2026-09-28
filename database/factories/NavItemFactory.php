<?php

namespace Database\Factories;

use App\Models\NavItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NavItem>
 */
class NavItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->word(),
            'route_name' => 'home',
            'url' => null,
            'is_visible' => true,
            'sort_order' => 1,
        ];
    }
}
