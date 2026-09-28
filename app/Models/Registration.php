<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationRole;
use App\Enums\RegistrationStatus;
use Database\Factories\RegistrationFactory;
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
    'season_id', 'code', 'group_name', 'category', 'role_type', 'country', 'city',
    'members_count', 'contact_name', 'contact_email', 'contact_phone',
    'performance_link', 'notes', 'status', 'payment_status', 'reviewer_id', 'source',
    'tags', 'score_total', 'score_votes', 'is_public', 'bio', 'image_url', 'art_gradient',
    'started_at', 'submitted_at', 'reviewed_at', 'approved_at', 'confirmed_at',
])]
class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory, SoftDeletes;

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
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * @return HasMany<RegistrationMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(RegistrationMember::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<RegistrationStatusEvent, $this>
     */
    public function statusEvents(): HasMany
    {
        return $this->hasMany(RegistrationStatusEvent::class)->latest();
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<MessageThread, $this>
     */
    public function threads(): HasMany
    {
        return $this->hasMany(MessageThread::class);
    }

    /**
     * @return HasMany<ProgrammeSlot, $this>
     */
    public function programmeSlots(): HasMany
    {
        return $this->hasMany(ProgrammeSlot::class);
    }

    /**
     * @return HasMany<ReviewAssignment, $this>
     */
    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    public function latestInvoice(): ?Invoice
    {
        if ($this->relationLoaded('invoices')) {
            return $this->invoices
                ->sortByDesc(fn (Invoice $invoice): int => ($invoice->issued_at?->getTimestamp() ?? 0) * 1_000_000 + $invoice->getKey())
                ->first();
        }

        return $this->invoices()->latest('issued_at')->latest('id')->first();
    }

    public function outstandingBalance(): float
    {
        $invoice = $this->latestInvoice();

        if ($invoice === null) {
            return (float) ($this->season?->feeFor($this->members_count) ?? 0);
        }

        return $invoice->balance();
    }

    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    public function publicUrl(): string
    {
        return route('status', ['code' => $this->code]);
    }

    /**
     * @param  Builder<Registration>  $query
     * @return Builder<Registration>
     */
    #[Scope]
    protected function forSeason(Builder $query, Season|int $season): Builder
    {
        return $query->where('season_id', $season instanceof Season ? $season->getKey() : $season);
    }

    /**
     * @param  Builder<Registration>  $query
     * @return Builder<Registration>
     */
    #[Scope]
    protected function withStatus(Builder $query, RegistrationStatus|array $status): Builder
    {
        $statuses = array_map(
            fn (RegistrationStatus|string $value): string => $value instanceof RegistrationStatus
                ? $value->value
                : $value,
            Arr::wrap($status),
        );

        return $query->whereIn('status', $statuses);
    }

    /**
     * @param  Builder<Registration>  $query
     * @return Builder<Registration>
     */
    #[Scope]
    protected function public(Builder $query): Builder
    {
        return $query->where('is_public', true)
            ->where('status', RegistrationStatus::Confirmed->value);
    }

    /**
     * @param  Builder<Registration>  $query
     * @return Builder<Registration>
     */
    #[Scope]
    protected function unpaid(Builder $query): Builder
    {
        return $query->whereIn('payment_status', array_map(
            fn (PaymentStatus $status): string => $status->value,
            [PaymentStatus::Unpaid, PaymentStatus::Pending, PaymentStatus::PartiallyPaid, PaymentStatus::Overdue],
        ));
    }

    /**
     * @param  Builder<Registration>  $query
     * @return Builder<Registration>
     */
    #[Scope]
    protected function needsAttention(Builder $query): Builder
    {
        return $query->unpaid()
            ->whereNotIn('status', array_map(
                fn (RegistrationStatus $status): string => $status->value,
                [RegistrationStatus::Confirmed, RegistrationStatus::NotSelected, RegistrationStatus::Rejected],
            ));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'payment_status' => PaymentStatus::class,
            'role_type' => RegistrationRole::class,
            'tags' => 'array',
            'members_count' => 'integer',
            'score_votes' => 'integer',
            'score_total' => 'decimal:2',
            'is_public' => 'boolean',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }
}
