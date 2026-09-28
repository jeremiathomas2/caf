<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * Apply a payment to an invoice and re-derive the invoice and
     * registration payment states from the resulting totals.
     *
     * @param  array<string, mixed>  $data
     */
    public function record(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data): Payment {
            $payment = Payment::create([
                ...$data,
                'invoice_id' => $invoice->getKey(),
                'registration_id' => $invoice->registration_id,
                'paid_at' => $data['paid_at'] ?? now(),
            ]);

            $this->recalculate($invoice->refresh());

            $this->audit->record(
                action: 'payment.recorded',
                category: 'payment',
                entityType: 'invoice',
                entityId: (string) $invoice->getKey(),
                entityLabel: $invoice->number,
                detail: sprintf('%s %s via %s', $payment->currency, $payment->amount, $payment->method->value),
            );

            return $payment;
        });
    }

    /**
     * Waive the balance on an invoice, recording the reason against it.
     */
    public function waive(Invoice $invoice, float $amount, ?string $reason = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $amount, $reason): Invoice {
            $invoice->adjustments()->create([
                'actor_id' => auth()->id(),
                'type' => 'waiver',
                'amount' => $amount,
                'reason' => $reason,
            ]);

            $invoice->amount_waived = (float) $invoice->amount_waived + $amount;
            $invoice->save();

            $this->recalculate($invoice->refresh());

            $this->audit->record(
                action: 'invoice.waived',
                category: 'payment',
                entityType: 'invoice',
                entityId: (string) $invoice->getKey(),
                entityLabel: $invoice->number,
                detail: $reason,
            );

            return $invoice;
        });
    }

    /**
     * Re-derive invoice and registration payment status from the totals.
     */
    public function recalculate(Invoice $invoice): Invoice
    {
        $covered = (float) $invoice->amount_paid + (float) $invoice->amount_waived;
        $balance = max(0, (float) $invoice->amount - $covered);

        $status = match (true) {
            $balance <= 0 && (float) $invoice->amount_waived > 0 => InvoiceStatus::Waived,
            $balance <= 0 => InvoiceStatus::Paid,
            $covered > 0 => InvoiceStatus::PartiallyPaid,
            $invoice->isOverdue() => InvoiceStatus::Overdue,
            default => InvoiceStatus::Issued,
        };

        $invoice->status = $status;
        $invoice->save();

        $invoice->registration()->update([
            'payment_status' => $this->toPaymentStatus($status)->value,
        ]);

        return $invoice;
    }

    public function toPaymentStatus(InvoiceStatus $status): PaymentStatus
    {
        return match ($status) {
            InvoiceStatus::Paid => PaymentStatus::Paid,
            InvoiceStatus::Waived => PaymentStatus::Waived,
            InvoiceStatus::PartiallyPaid => PaymentStatus::PartiallyPaid,
            InvoiceStatus::Overdue => PaymentStatus::Overdue,
            InvoiceStatus::Refunded => PaymentStatus::Refunded,
            InvoiceStatus::Void => PaymentStatus::Failed,
            default => PaymentStatus::Unpaid,
        };
    }

    /**
     * Guard a payment that would exceed the outstanding balance.
     *
     * @param  array<string, mixed>  $data
     */
    public function guardOverpayment(Invoice $invoice, array $data): void
    {
        $amount = (float) ($data['amount'] ?? 0);

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'The amount must be greater than zero.']);
        }

        if ($amount > $invoice->balance()) {
            throw ValidationException::withMessages([
                'amount' => sprintf(
                    'That exceeds the outstanding balance of %s %s.',
                    $invoice->currency,
                    number_format($invoice->balance(), 0),
                ),
            ]);
        }
    }
}
