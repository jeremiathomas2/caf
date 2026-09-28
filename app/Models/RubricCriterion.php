<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['season_id', 'name', 'max_points', 'weight', 'sort_order'])]
class RubricCriterion extends Model
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
     * @return HasMany<ReviewScore, $this>
     */
    public function scores(): HasMany
    {
        return $this->hasMany(ReviewScore::class);
    }

    public function label(): string
    {
        return sprintf('%d. %s', $this->sort_order, $this->name);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_points' => 'integer',
            'weight' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
