<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'action' => fake()->randomElement([
                'registration.status',
                'payment.recorded',
                'content.updated',
                'registration.created',
                'campaign.sent',
                'invoice.waived',
            ]),
            'category' => fake()->randomElement(['registration', 'payment', 'content', 'communication', 'general']),
            'entity_type' => 'registration',
            'entity_id' => (string) fake()->numberBetween(1, 100),
            'entity_label' => fake()->words(2, true),
            'detail' => fake()->sentence(),
            'ip_address' => fake()->ipv4(),
        ];
    }
}
