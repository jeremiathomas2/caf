<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'season_id', 'registration_id', 'event_date', 'stage', 'stage_location',
    'starts_at', 'ends_at', 'title', 'description', 'status', 'is_public', 'sort_order',
])]
class ProgrammeSlot extends Model
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
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function timeRange(): string
    {
        return $this->ends_at === null
            ? $this->starts_at
            : sprintf('%s – %s', $this->starts_at, $this->ends_at);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'is_public' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
