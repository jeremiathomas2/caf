<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Season;
use Illuminate\Database\Seeder;

/**
 * Invoices for the live season, numbered from INV-2041, with payments behind
 * some of them so the finance screens show partial and settled states.
 */
class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::query()->where('number', 2)->firstOrFail();

        $plan = [
            // registration code, status, paid fraction, method
            ['CAF2-0001', InvoiceStatus::Paid->value, 1.0, 'bank_transfer'],
            ['CAF2-0002', InvoiceStatus::PartiallyPaid->value, 0.5, 'mpesa'],
            ['CAF2-0003', InvoiceStatus::Paid->value, 1.0, 'bank_transfer'],
            ['CAF2-0004', InvoiceStatus::Issued->value, 0.0, null],
            ['CAF2-0005', InvoiceStatus::PartiallyPaid->value, 0.6, 'mpesa'],
            ['CAF2-0006', InvoiceStatus::Issued->value, 0.0, null],
            ['CAF2-0007', InvoiceStatus::Issued->value, 0.0, null],
            ['CAF2-0008', InvoiceStatus::Overdue->value, 0.0, null],
            ['CAF2-0009', InvoiceStatus::Issued->value, 0.0, null],
            ['CAF2-0010', InvoiceStatus::Waived->value, 0.0, null],
            ['CAF2-0011', InvoiceStatus::Issued->value, 0.0, null],
            ['CAF2-0012', InvoiceStatus::Issued->value, 0.0, null],
            ['CAF2-0015', InvoiceStatus::Refunded->value, 1.0, 'bank_transfer'],
        ];

        foreach ($plan as $index => [$code, $status, $fraction, $method]) {
            $registration = Registration::query()->where('code', $code)->first();

            if ($registration === null) {
                continue;
            }

            $amount = $season->feeFor($registration->members_count);
            $waived = $status === InvoiceStatus::Waived->value ? $amount : 0;
            $paid = $waived > 0 ? 0.0 : round($amount * $fraction, 2);

            $invoice = Invoice::query()->updateOrCreate(
                ['number' => sprintf('INV-%d', 2041 + $index)],
                [
                    'season_id' => $season->getKey(),
                    'registration_id' => $registration->getKey(),
                    'currency' => $season->currency,
                    'amount' => $amount,
                    'amount_paid' => $paid,
                    'amount_waived' => $waived,
                    'status' => $status,
                    'method' => $method,                    'issued_at' => now()->subDays(30 - $index)->toDateString(),
                    'due_at' => now()->subDays(2 - $index)->toDateString(),
                    'paid_at' => $status === InvoiceStatus::Paid->value ? now()->subDays(18 - $index) : null,
                    'note' => $status === InvoiceStatus::Waived->value
                        ? 'Full bursary awarded by the festival committee.'
                        : null,
                ],
            );

            if ($paid > 0) {
                Payment::query()->updateOrCreate(
                    ['reference' => 'MPESA-'.sprintf('%05d', 7000 + $index)],
                    [
                        'invoice_id' => $invoice->getKey(),
                        'registration_id' => $registration->getKey(),
                        'method' => $method,
                        'currency' => $season->currency,
                        'amount' => $paid,
                        'status' => 'success',
                        'paid_at' => now()->subDays(18 - $index),
                    ],
                );
            }

            $this->syncPaymentStatus($registration, $status, $paid, $waived);
        }
    }

    /**
     * Keep the registration's denormalised payment status in step with its
     * newest invoice, the same way the payments module does.
     */
    private function syncPaymentStatus(
        Registration $registration,
        string $invoiceStatus,
        float $paid,
        float $waived,
    ): void {
        $status = match ($invoiceStatus) {
            InvoiceStatus::Paid->value => PaymentStatus::Paid->value,
            InvoiceStatus::PartiallyPaid->value => PaymentStatus::PartiallyPaid->value,
            InvoiceStatus::Overdue->value => PaymentStatus::Overdue->value,
            InvoiceStatus::Waived->value => PaymentStatus::Waived->value,
            InvoiceStatus::Refunded->value => PaymentStatus::Refunded->value,
            default => $paid > 0 ? PaymentStatus::PartiallyPaid->value : PaymentStatus::Unpaid->value,
        };

        $registration->update(['payment_status' => $status]);
    }
}
