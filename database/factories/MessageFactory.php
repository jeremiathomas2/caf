<?php

namespace Database\Factories;

use App\Enums\Channel;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_thread_id' => MessageThread::factory(),
            'sender_id' => null,
            'sender_type' => 'contact',
            'sender_label' => 'Contact',
            'body' => fake()->paragraph(),
            'channel' => 'email',
            'sent_at' => now()->subHours(fake()->numberBetween(1, 96)),
            'read_at' => null,
            'delivered_at' => null,
        ];
    }

    public function onThread(MessageThread $thread): static
    {
        return $this->state(fn (array $attributes): array => [
            'message_thread_id' => $thread->id,
            'sender_label' => $thread->contact_name,
            'channel' => $thread->channel->value,
        ]);
    }

    public function outbound(?User $sender = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'sender_id' => $sender?->id,
            'sender_type' => 'staff',
            'sender_label' => $sender?->name ?? 'CAF Team',
            'delivered_at' => now(),
        ]);
    }

    public function onChannel(Channel $channel): static
    {
        return $this->state(fn (array $attributes): array => [
            'channel' => $channel->value,
        ]);
    }
}
