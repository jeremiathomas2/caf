<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['review_assignment_id', 'rubric_criterion_id', 'points', 'comment'])]
class ReviewScore extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<ReviewAssignment, $this>
     */
    public function reviewAssignment(): BelongsTo
    {
        return $this->belongsTo(ReviewAssignment::class);
    }

    /**
     * @return BelongsTo<RubricCriterion, $this>
     */
    public function criterion(): BelongsTo
    {
        return $this->belongsTo(RubricCriterion::class, 'rubric_criterion_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
        ];
    }
}
