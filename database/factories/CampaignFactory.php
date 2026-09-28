<?php

namespace Database\Factories;

use App\Enums\CampaignStatus;
use App\Enums\Channel;
use App\Models\Campaign;
use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'created_by_id' => User::factory(),
            'name' => fake()->sentence(3),
            'channel' => Channel::Email->value,
            'subject' => fake()->sentence(4),
            'body' => fake()->paragraphs(2, true),
            'segment' => ['status' => ['approved', 'confirmed']],
            'status' => CampaignStatus::Draft->value,
            'recipient_count' => 0,
            'delivered_count' => 0,
            'cost' => 0,
            'currency' => 'TZS',
        ];
    }

    public function sent(int $recipients = 120, int $delivered = 118, float $cost = 42000): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CampaignStatus::Sent->value,
            'sent_at' => now()->subDays(3),
            'recipient_count' => $recipients,
            'delivered_count' => $delivered,
            'cost' => $cost,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CampaignStatus::Scheduled->value,
            'scheduled_at' => now()->addDays(2),
        ]);
    }
}
