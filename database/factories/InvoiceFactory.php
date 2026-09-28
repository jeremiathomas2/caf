<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'season_id' => fn (array $attributes): int => Registration::query()
                ->findOrFail($attributes['registration_id'])->season_id,
            'number' => 'INV-'.fake()->unique()->numberBetween(1000, 99999),
            'currency' => 'TZS',
            'amount' => 450000,
            'amount_paid' => 0,
            'amount_waived' => 0,
            'status' => InvoiceStatus::Issued->value,
            'issued_at' => now()->subWeeks(2),
            'due_at' => now()->addWeeks(2),
        ];
    }

    public function forRegistration(Registration $registration): static
    {
        return $this->state(fn (array $attributes): array => [
            'registration_id' => $registration->id,
            'season_id' => $registration->season_id,
            'currency' => $registration->season->currency,
            'amount' => $registration->season->feeFor($registration->members_count),
        ]);
    }

    public function forSeason(Season $season): static
    {
        return $this->state(fn (array $attributes): array => [
            'season_id' => $season->id,
        ]);
    }

    public function paid(): static
    {
        return $this->afterCreating(function (Invoice $invoice): void {
            $invoice->forceFill([
                'amount_paid' => $invoice->amount,
                'status' => InvoiceStatus::Paid->value,
                'method' => PaymentMethod::Mpesa->value,
                'paid_at' => now()->subWeek(),
            ])->save();
        });
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::Overdue->value,
            'due_at' => now()->subWeeks(2),
        ]);
    }

    public function partiallyPaid(float $ratio = 0.5): static
    {
        return $this->afterCreating(function (Invoice $invoice) use ($ratio): void {
            $invoice->forceFill([
                'amount_paid' => round((float) $invoice->amount * $ratio, 2),
                'status' => InvoiceStatus::PartiallyPaid->value,
            ])->save();
        });
    }
}
