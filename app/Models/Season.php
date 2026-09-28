<?php

namespace App\Models;

use App\Enums\SeasonState;
use Database\Factories\SeasonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'number', 'name', 'slug', 'theme', 'tagline', 'state', 'is_current',
    'venue', 'city', 'country', 'starts_on', 'ends_on',
    'registration_opens_at', 'registration_closes_at', 'early_bird_closes_at',
    'currency', 'secondary_currency', 'per_head_fee', 'early_bird_fee',
    'min_partial_payment_pct', 'rounding_increment', 'usd_fx_rate', 'summary',
])]
class Season extends Model
{
    /** @use HasFactory<SeasonFactory> */
    use HasFactory;

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * @return HasMany<ReviewRound, $this>
     */
    public function reviewRounds(): HasMany
    {
        return $this->hasMany(ReviewRound::class)->orderBy('sequence');
    }

    /**
     * @return HasMany<Judge, $this>
     */
    public function judges(): HasMany
    {
        return $this->hasMany(Judge::class);
    }

    /**
     * @return HasMany<RubricCriterion, $this>
     */
    public function rubricCriteria(): HasMany
    {
        return $this->hasMany(RubricCriterion::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<ProgrammeSlot, $this>
     */
    public function programmeSlots(): HasMany
    {
        return $this->hasMany(ProgrammeSlot::class);
    }

    /**
     * @return HasMany<Campaign, $this>
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * @return HasMany<FaqItem, $this>
     */
    public function faqItems(): HasMany
    {
        return $this->hasMany(FaqItem::class);
    }

    /**
     * Prefix used when generating registration codes for this season.
     */
    public function codePrefix(): string
    {
        return 'CAF'.$this->number;
    }

    /**
     * Fee owed by a group of the given size, honouring the early-bird window.
     */
    public function feeFor(int $members): int
    {
        $fee = $this->early_bird_fee !== null && $this->isEarlyBird()
            ? $this->early_bird_fee
            : $this->per_head_fee;

        return $this->roundToIncrement($fee * $members);
    }

    public function isEarlyBird(): bool
    {
        return $this->early_bird_closes_at !== null && now()->lessThan($this->early_bird_closes_at);
    }

    public function registrationIsOpen(): bool
    {
        if ($this->registration_closes_at !== null && now()->greaterThanOrEqualTo($this->registration_closes_at)) {
            return false;
        }

        return $this->registration_opens_at === null || now()->greaterThanOrEqualTo($this->registration_opens_at);
    }

    public function roundToIncrement(float $amount): int
    {
        $increment = max(1, (int) $this->rounding_increment);

        return (int) (round($amount / $increment) * $increment);
    }

    public function isLive(): bool
    {
        return $this->state === SeasonState::Live;
    }

    public function isArchived(): bool
    {
        return $this->state === SeasonState::Archived;
    }

    public function isDraft(): bool
    {
        return $this->state === SeasonState::Draft;
    }

    /**
     * @param  Builder<Season>  $query
     * @return Builder<Season>
     */
    #[Scope]
    protected function current(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    /**
     * @param  Builder<Season>  $query
     * @return Builder<Season>
     */
    #[Scope]
    protected function live(Builder $query): Builder
    {
        return $query->where('state', SeasonState::Live->value);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'is_current' => 'boolean',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
            'early_bird_closes_at' => 'datetime',
            'per_head_fee' => 'decimal:2',
            'early_bird_fee' => 'decimal:2',
            'usd_fx_rate' => 'decimal:4',
            'state' => SeasonState::class,
        ];
    }
}
