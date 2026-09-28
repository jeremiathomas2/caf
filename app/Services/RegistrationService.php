<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\RegistrationStatusEvent;
use App\Models\Season;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * Allocate the next registration code for a season.
     */
    public function nextCode(Season $season): string
    {
        $padding = (int) config('caf.registration.code_padding', 4);

        $last = Registration::query()
            ->where('season_id', $season->getKey())
            ->where('code', 'like', $season->codePrefix().'-%')
            ->orderByDesc('code')
            ->value('code');

        $sequence = $last === null
            ? 1
            : ((int) substr((string) $last, strlen($season->codePrefix()) + 1)) + 1;

        return sprintf('%s-%0'.$padding.'d', $season->codePrefix(), $sequence);
    }

    /**
     * Record a manual or public registration, its members, and its invoice.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(Season $season, array $data, string $source = 'web'): Registration
    {
        /** @var list<array<string, mixed>> $members */
        $members = $data['members'] ?? [];
        unset($data['members']);

        return DB::transaction(function () use ($season, $data, $members, $source): Registration {
            $registration = Registration::create([
                ...$data,
                'season_id' => $season->getKey(),
                'code' => $this->nextCode($season),
                'status' => RegistrationStatus::Submitted->value,
                'payment_status' => PaymentStatus::Unpaid->value,
                'source' => $source,
                'started_at' => now(),
                'submitted_at' => now(),
            ]);

            if ($members !== []) {
                $registration->members()->createMany($members);
            }

            $registration->statusEvents()->create([
                'from_status' => null,
                'to_status' => RegistrationStatus::Submitted->value,
                'actor_label' => $source === 'web' ? 'Public registration form' : 'Manual entry',
                'note' => 'Registration received',
            ]);

            $this->openInvoice($registration);

            return $registration->refresh();
        });
    }

    /**
     * Issue the invoice for a registration, sized to its member count.
     */
    public function openInvoice(Registration $registration): Invoice
    {
        $season = $registration->season;
        $amount = $season->feeFor($registration->members_count);

        return Invoice::create([
            'season_id' => $season->getKey(),
            'registration_id' => $registration->getKey(),
            'number' => $this->nextInvoiceNumber(),
            'currency' => $season->currency,
            'amount' => $amount,
            'status' => InvoiceStatus::Issued->value,
            'issued_at' => now(),
            'due_at' => now()->addDays(14),
        ]);
    }

    public function nextInvoiceNumber(): string
    {
        $last = Invoice::query()->orderByDesc('number')->value('number');
        $sequence = $last === null ? 2041 : ((int) substr((string) $last, 4)) + 1;

        return 'INV-'.$sequence;
    }

    /**
     * Move a registration to a new status, enforcing the transition graph.
     */
    public function transitionTo(Registration $registration, RegistrationStatus $target, ?string $note = null): Registration
    {
        $current = $registration->status;

        if ($current === $target) {
            return $registration;
        }

        if (! $current->canTransitionTo($target)) {
            throw ValidationException::withMessages([
                'status' => sprintf(
                    'A registration that is %s cannot move to %s.',
                    $current->label(),
                    $target->label(),
                ),
            ]);
        }

        DB::transaction(function () use ($registration, $current, $target, $note): void {
            $registration->status = $target;

            $timestamps = match ($target) {
                RegistrationStatus::UnderReview, RegistrationStatus::Shortlisted => ['reviewed_at' => now()],
                RegistrationStatus::Approved, RegistrationStatus::Waitlisted, RegistrationStatus::NotSelected => ['reviewed_at' => now()],
                RegistrationStatus::Confirmed => ['reviewed_at' => now(), 'confirmed_at' => now()],
                default => [],
            };

            if ($target === RegistrationStatus::Approved) {
                $timestamps['approved_at'] = now();
            }

            $registration->fill($timestamps)->save();

            $event = new RegistrationStatusEvent([
                'from_status' => $current->value,
                'to_status' => $target->value,
                'actor_id' => auth()->id(),
                'note' => $note,
            ]);
            $event->registration()->associate($registration);
            $event->save();

            $this->audit->registrationStatusChanged($registration, $current->value, $target->value, $note);
        });

        return $registration->refresh();
    }

    /**
     * Statuses the current one may move to, for populating action menus.
     *
     * @return list<RegistrationStatus>
     */
    public function nextStatuses(Registration $registration): array
    {
        return $registration->status->allowedTransitions();
    }
}
