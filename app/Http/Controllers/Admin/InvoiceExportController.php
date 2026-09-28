<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\AuditLogger;
use App\Support\SeasonContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams the invoice ledger for the active season as CSV, honouring the
 * status and search filters currently applied on screen.
 */
class InvoiceExportController extends Controller
{
    public function __construct(
        private readonly SeasonContext $seasons,
        private readonly AuditLogger $audit,
    ) {}

    public function __invoke(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()?->canDo('payments.manage'), 403, 'You cannot export invoices.');

        $season = $this->seasons->current();
        $status = InvoiceStatus::tryFrom((string) $request->query('status', ''));
        $search = trim((string) $request->query('q', ''));

        $query = Invoice::query()
            ->forSeason($season)
            ->with('registration')
            ->when($status instanceof InvoiceStatus, fn ($q) => $q->withStatus($status))
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($inner) use ($search): void {
                    $inner->where('number', 'like', "%{$search}%")
                        ->orWhereHas('registration', fn ($r) => $r->where('group_name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('issued_at')
            ->orderBy('id');

        $this->audit->record(
            action: 'invoice.exported',
            category: 'finance',
            entityType: 'season',
            entityId: (string) $season->getKey(),
            entityLabel: 'Season '.$season->number,
        );

        $filename = sprintf('caf-season-%d-invoices-%s.csv', $season->number, now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, [
                'Invoice', 'Group', 'Code', 'Method', 'Currency',
                'Amount', 'Paid', 'Waived', 'Balance', 'Status',
                'Issued', 'Due', 'Paid at', 'Days overdue', 'Note',
            ]);

            $query->chunk(200, function ($chunk) use ($handle): void {
                foreach ($chunk as $invoice) {
                    fputcsv($handle, [
                        $invoice->number,
                        $invoice->registration?->group_name,
                        $invoice->registration?->code,
                        $invoice->method,
                        $invoice->currency,
                        number_format((float) $invoice->amount, 2, '.', ''),
                        number_format((float) $invoice->amount_paid, 2, '.', ''),
                        number_format((float) $invoice->amount_waived, 2, '.', ''),
                        number_format($invoice->balance(), 2, '.', ''),
                        $invoice->status?->label(),
                        $invoice->issued_at?->toDateString(),
                        $invoice->due_at?->toDateString(),
                        $invoice->paid_at?->toDateTimeString(),
                        $invoice->daysOverdue(),
                        $invoice->note,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
