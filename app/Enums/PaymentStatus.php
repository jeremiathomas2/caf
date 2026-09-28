<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Pending = 'pending';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Waived = 'waived';
    case Refunded = 'refunded';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::Pending => 'Pending',
            self::PartiallyPaid => 'Partially Paid',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
            self::Waived => 'Waived',
            self::Refunded => 'Refunded',
            self::Failed => 'Failed',
        };
    }

    /**
     * Whether a balance is still expected against an invoice.
     */
    public function isOutstanding(): bool
    {
        return in_array($this, [self::Unpaid, self::Pending, self::PartiallyPaid, self::Overdue], true);
    }
}
