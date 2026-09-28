<?php

namespace App\Models;

use App\Enums\Channel;
use App\Enums\ThreadStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

#[Fillable([
    'season_id', 'registration_id', 'assigned_to_id', 'subject', 'channel', 'status',
    'contact_name', 'contact_email', 'contact_phone', 'first_response_at',
    'last_message_at', 'unread_count',
])]
class MessageThread extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsTo<Season, $this>
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /**
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->oldest('sent_at');
    }

    public function lastMessage(): ?Message
    {
        if ($this->relationLoaded('messages')) {
            return $this->messages->last();
        }

        return $this->messages()->latest('sent_at')->first();
    }

    public function preview(): string
    {
        return str($this->lastMessage()?->body ?? 'No messages yet.')
            ->squish()
            ->limit(140)
            ->toString();
    }

    public function responseTimeHours(): ?float
    {
        if ($this->first_response_at === null || $this->messages()->min('sent_at') === null) {
            return null;
        }

        return round(
            $this->first_response_at->diffInMinutes($this->messages()->min('sent_at'), false) / 60,
            1,
        );
    }

    /**
     * @param  Builder<MessageThread>  $query
     * @return Builder<MessageThread>
     */
    #[Scope]
    protected function withChannel(Builder $query, Channel|array $channel): Builder
    {
        return $query->whereIn('channel', array_map(
            fn (Channel|string $value): string => $value instanceof Channel ? $value->value : $value,
            Arr::wrap($channel),
        ));
    }

    /**
     * @param  Builder<MessageThread>  $query
     * @return Builder<MessageThread>
     */
    #[Scope]
    protected function needsReply(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ThreadStatus::Open->value,
            ThreadStatus::Pending->value,
        ]);
    }

    /**
     * @param  Builder<MessageThread>  $query
     * @return Builder<MessageThread>
     */
    #[Scope]
    protected function withStatus(Builder $query, ThreadStatus|array $status): Builder
    {
        return $query->whereIn('status', array_map(
            fn (ThreadStatus|string $value): string => $value instanceof ThreadStatus ? $value->value : $value,
            Arr::wrap($status),
        ));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => Channel::class,
            'status' => ThreadStatus::class,
            'unread_count' => 'integer',
            'first_response_at' => 'datetime',
            'last_message_at' => 'datetime',
        ];
    }
}
