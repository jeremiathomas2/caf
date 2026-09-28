<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'registration_id' => fn (array $attributes): int => Invoice::query()
                ->findOrFail($attributes['invoice_id'])->registration_id,
            'reference' => strtoupper(fake()->bothify('MPESA-####-??##')),
            'method' => PaymentMethod::Mpesa->value,
            'currency' => 'TZS',
            'amount' => 450000,
            'status' => 'success',
            'paid_at' => now()->subDays(fake()->numberBetween(0, 10)),
            'meta' => ['phone' => fake()->numerify('+2557## ### ###')],
        ];
    }

    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn (array $attributes): array => [
            'invoice_id' => $invoice->id,
            'registration_id' => $invoice->registration_id,
            'currency' => $invoice->currency,
            'amount' => $invoice->amount,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'failed',
        ]);
    }
}
