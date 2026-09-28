<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['review_round_id', 'registration_id', 'judge_id', 'status', 'completed_at'])]
class ReviewAssignment extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<ReviewRound, $this>
     */
    public function reviewRound(): BelongsTo
    {
        return $this->belongsTo(ReviewRound::class);
    }

    /**
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * @return BelongsTo<Judge, $this>
     */
    public function judge(): BelongsTo
    {
        return $this->belongsTo(Judge::class);
    }

    /**
     * @return HasMany<ReviewScore, $this>
     */
    public function scores(): HasMany
    {
        return $this->hasMany(ReviewScore::class);
    }

    public function isComplete(): bool
    {
        return $this->status === AssignmentStatus::Completed;
    }

    /**
     * Weighted total of the recorded criterion scores.
     */
    public function totalPoints(): float
    {
        return (float) $this->scores->sum(
            fn (ReviewScore $score): float => (float) $score->points * (float) $score->criterion->weight,
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AssignmentStatus::class,
            'completed_at' => 'datetime',
        ];
    }
}
