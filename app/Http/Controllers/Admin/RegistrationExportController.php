<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\AuditLogger;
use App\Support\SeasonContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationExportController extends Controller
{
    public function __construct(
        private readonly SeasonContext $seasons,
        private readonly AuditLogger $audit,
    ) {}

    public function __invoke(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()?->canDo('registrations.export'), 403, 'You cannot export registrations.');

        $season = $this->seasons->current();
        $status = RegistrationStatus::tryFrom((string) $request->query('status', ''));
        $payment = PaymentStatus::tryFrom((string) $request->query('payment', ''));

        $query = Registration::query()
            ->forSeason($season)
            ->with(['reviewer', 'invoices'])
            ->when($status instanceof RegistrationStatus, fn ($q) => $q->withStatus($status))
            ->when($payment instanceof PaymentStatus, fn ($q) => $q->where('payment_status', $payment->value))
            ->orderBy('code');

        $this->audit->record(
            action: 'registration.exported',
            category: 'registration',
            entityType: 'season',
            entityId: (string) $season->getKey(),
            entityLabel: 'Season '.$season->number,
        );

        $filename = sprintf('caf-season-%d-registrations-%s.csv', $season->number, now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, [
                'Code', 'Group', 'Category', 'Country', 'City', 'Members',
                'Contact name', 'Contact email', 'Contact phone',
                'Status', 'Payment', 'Invoice', 'Invoiced', 'Paid', 'Balance',
                'Reviewer', 'Submitted', 'Tags',
            ]);

            $query->chunk(200, function ($chunk) use ($handle): void {
                foreach ($chunk as $registration) {
                    $invoice = $registration->latestInvoice();

                    fputcsv($handle, [
                        $registration->code,
                        $registration->group_name,
                        $registration->role_type?->label() ?? $registration->category,
                        $registration->country,
                        $registration->city,
                        $registration->members_count,
                        $registration->contact_name,
                        $registration->contact_email,
                        $registration->contact_phone,
                        $registration->status?->label(),
                        $registration->payment_status?->label(),
                        $invoice?->number,
                        $invoice === null ? '' : number_format((float) $invoice->amount, 2, '.', ''),
                        $invoice === null ? '' : number_format((float) $invoice->amount_paid, 2, '.', ''),
                        $invoice === null ? '' : number_format($invoice->balance(), 2, '.', ''),
                        $registration->reviewer?->name,
                        $registration->submitted_at?->toDateTimeString(),
                        implode('|', $registration->tags ?? []),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
