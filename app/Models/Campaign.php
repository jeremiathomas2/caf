<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Enums\Channel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'season_id', 'created_by_id', 'name', 'channel', 'subject', 'body', 'segment',
    'status', 'scheduled_at', 'sent_at', 'recipient_count', 'delivered_count', 'cost', 'currency',
])]
class Campaign extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Season, $this>
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function deliveryRate(): float
    {
        if ($this->recipient_count === 0) {
            return 0.0;
        }

        return round(($this->delivered_count / $this->recipient_count) * 100, 1);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [CampaignStatus::Draft, CampaignStatus::Scheduled], true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => Channel::class,
            'status' => CampaignStatus::class,
            'segment' => 'array',
            'recipient_count' => 'integer',
            'delivered_count' => 'integer',
            'cost' => 'decimal:2',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}
