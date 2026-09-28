<?php

namespace App\Models;

use App\Enums\RoundStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'season_id', 'name', 'sequence', 'status', 'is_blind', 'aggregation',
    'opens_at', 'closes_at', 'published_at',
])]
class ReviewRound extends Model
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
     * @return HasMany<ReviewAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    public function isOpen(): bool
    {
        return $this->status === RoundStatus::Open;
    }

    public function completionPercent(): int
    {
        $total = $this->assignments()->count();

        if ($total === 0) {
            return 0;
        }

        return (int) round(($this->assignments()->where('status', 'completed')->count() / $total) * 100);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'is_blind' => 'boolean',
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'published_at' => 'datetime',
            'status' => RoundStatus::class,
        ];
    }
}
