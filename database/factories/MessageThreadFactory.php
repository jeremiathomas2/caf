<?php

namespace Database\Factories;

use App\Enums\Channel;
use App\Enums\ThreadStatus;
use App\Models\MessageThread;
use App\Models\Registration;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageThread>
 */
class MessageThreadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $season = Season::factory()->create();

        return [
            'season_id' => $season->id,
            'registration_id' => null,
            'subject' => fake()->sentence(4),
            'channel' => Channel::Email->value,
            'status' => ThreadStatus::Open->value,
            'contact_name' => fake()->name(),
            'contact_email' => fake()->unique()->safeEmail(),
            'contact_phone' => fake()->numerify('+2557## ### ###'),
            'last_message_at' => now()->subHours(fake()->numberBetween(1, 72)),
            'unread_count' => fake()->numberBetween(0, 3),
        ];
    }

    public function forSeason(Season $season): static
    {
        return $this->state(fn (array $attributes): array => [
            'season_id' => $season->getKey(),
        ]);
    }

    public function forRegistration(Registration $registration): static
    {
        return $this->state(fn (array $attributes): array => [
            'registration_id' => $registration->id,
            'season_id' => $registration->season_id,
            'contact_name' => $registration->contact_name,
            'contact_email' => $registration->contact_email,
            'contact_phone' => $registration->contact_phone,
        ]);
    }

    public function withChannel(Channel $channel): static
    {
        return $this->state(fn (array $attributes): array => [
            'channel' => $channel->value,
        ]);
    }
}
