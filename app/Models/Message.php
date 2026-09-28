<?php

namespace App\Models;

use App\Enums\Channel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'message_thread_id', 'sender_id', 'sender_type', 'sender_label',
    'body', 'channel', 'sent_at', 'read_at', 'delivered_at', 'meta',
])]
class Message extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<MessageThread, $this>
     */
    public function messageThread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isInbound(): bool
    {
        return $this->sender_type === 'contact';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => Channel::class,
            'meta' => 'array',
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }
}
