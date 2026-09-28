<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

#[Fillable([
    'season_id', 'registration_id', 'number', 'currency', 'amount', 'amount_paid',
    'amount_waived', 'status', 'method', 'issued_at', 'due_at', 'paid_at', 'note',
])]
class Invoice extends Model
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

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('paid_at');
    }

    /**
     * @return HasMany<InvoiceAdjustment, $this>
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(InvoiceAdjustment::class);
    }

    /**
     * What is still owed. A waiver counts as covered, so a fully waived
     * invoice settles at zero rather than still asking for money.
     */
    public function balance(): float
    {
        $covered = (float) $this->amount_paid + (float) $this->amount_waived;

        return max(0, (float) $this->amount - $covered);
    }

    public function progressPercent(): int
    {
        if ((float) $this->amount <= 0) {
            return 0;
        }

        $covered = (float) $this->amount_paid + (float) $this->amount_waived;

        return (int) min(100, round(($covered / (float) $this->amount) * 100));
    }

    public function isOverdue(): bool
    {
        return $this->status === InvoiceStatus::Overdue
            || ($this->due_at !== null && $this->balance() > 0 && now()->greaterThan($this->due_at));
    }

    public function daysOverdue(): int
    {
        if (! $this->isOverdue() || $this->due_at === null) {
            return 0;
        }

        return (int) $this->due_at->startOfDay()->diffInDays(now()->startOfDay());
    }

    /**
     * Re-derive the paid total, the invoice status and the registration's
     * denormalised payment state from the payment rows.
     *
     * The ledger is append-only, so this is the only thing that should ever
     * write `amount_paid` or `status`.
     */
    public function recalculate(): self
    {
        $this->amount_paid = (float) $this->payments()->where('status', 'success')->sum('amount');

        $this->status = match (true) {
            $this->status === InvoiceStatus::Waived => InvoiceStatus::Waived,
            $this->status === InvoiceStatus::Refunded => InvoiceStatus::Refunded,
            $this->status === InvoiceStatus::Void => InvoiceStatus::Void,
            (float) $this->amount_paid >= (float) $this->amount => InvoiceStatus::Paid,
            (float) $this->amount_paid > 0 => InvoiceStatus::PartiallyPaid,
            $this->isPastDue() => InvoiceStatus::Overdue,
            default => InvoiceStatus::Issued,
        };

        $this->paid_at = $this->status === InvoiceStatus::Paid ? ($this->paid_at ?? now()) : null;

        $this->save();

        $this->syncRegistration();

        return $this;
    }

    /**
     * Keep the registration's payment badge in step with its newest invoice.
     */
    public function syncRegistration(): void
    {
        $registration = $this->registration;

        if ($registration === null) {
            return;
        }

        $paymentStatus = match ($this->status) {
            InvoiceStatus::Paid => PaymentStatus::Paid,
            InvoiceStatus::PartiallyPaid => PaymentStatus::PartiallyPaid,
            InvoiceStatus::Overdue => PaymentStatus::Overdue,
            InvoiceStatus::Waived => PaymentStatus::Waived,
            InvoiceStatus::Refunded => PaymentStatus::Refunded,
            default => PaymentStatus::Unpaid,
        };

        $registration->update(['payment_status' => $paymentStatus->value]);
    }

    /**
     * Whether the due date has passed while a balance remains.
     */
    public function isPastDue(): bool
    {
        return $this->due_at !== null
            && $this->balance() > 0
            && now()->greaterThan($this->due_at);
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    #[Scope]
    protected function outstanding(Builder $query): Builder
    {
        return $query->whereColumn('amount', '>', 'amount_paid')
            ->whereIn('status', [
                InvoiceStatus::Issued->value,
                InvoiceStatus::PartiallyPaid->value,
                InvoiceStatus::Overdue->value,
            ]);
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    #[Scope]
    protected function withStatus(Builder $query, InvoiceStatus|array $status): Builder
    {
        return $query->whereIn('status', array_map(
            fn (InvoiceStatus|string $value): string => $value instanceof InvoiceStatus ? $value->value : $value,
            Arr::wrap($status),
        ));
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    #[Scope]
    protected function forSeason(Builder $query, Season|int $season): Builder
    {
        return $query->where('season_id', $season instanceof Season ? $season->getKey() : $season);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'amount_waived' => 'decimal:2',
            'issued_at' => 'date',
            'due_at' => 'date',
            'paid_at' => 'datetime',
        ];
    }
}
