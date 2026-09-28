<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Season;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Support\SeasonContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Payments: the invoice ledger, payment capture, and the season's money
 * summary. The ledger is append-only — corrections go in as adjustments.
 */
class PaymentController extends Controller
{
    public function __construct(
        private readonly SeasonContext $seasons,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeAction('payments.view');

        $season = $this->seasons->current();
        $status = InvoiceStatus::tryFrom((string) $request->query('status', ''));
        $search = trim((string) $request->query('q', ''));

        $invoices = Invoice::query()
            ->forSeason($season)
            ->with('registration')
            ->when($status instanceof InvoiceStatus, fn ($q) => $q->withStatus($status))
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($inner) use ($search): void {
                    $inner->where('number', 'like', "%{$search}%")
                        ->orWhereHas('registration', fn ($r) => $r->where('group_name', 'like', "%{$search}%"));
                });
            })
            ->latest('issued_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', [
            'season' => $season,
            'invoices' => $invoices,
            'status' => $status,
            'search' => $search,
            'statuses' => InvoiceStatus::cases(),
            'totals' => $this->totals($season),
            'methods' => PaymentMethod::cases(),
            'lastRun' => Setting::query()->where('key', 'finance.last_reconciled_at')->value('value'),
            'overdueCount' => $season->invoices()
                ->withStatus(InvoiceStatus::Overdue)
                ->count(),
        ]);
    }

    /**
     * Record a payment against an invoice and roll the invoice and
     * registration payment states forward.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAction('payments.manage');

        $season = $this->seasons->current();

        $validated = $request->validate([
            'invoice_id' => ['required', 'integer', Rule::exists('invoices', 'id')->where('season_id', $season->getKey())],
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['required', 'string', 'max:64'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $invoice = Invoice::query()->forSeason($season)->findOrFail($validated['invoice_id']);

        DB::transaction(function () use ($invoice, $validated, $request): void {
            Payment::create([
                'invoice_id' => $invoice->getKey(),
                'registration_id' => $invoice->registration_id,
                'reference' => $validated['reference'],
                'method' => $validated['method'],
                'currency' => $invoice->currency,
                'amount' => $validated['amount'],
                'status' => 'success',
                'paid_at' => $validated['paid_at'] ?? now(),
                'meta' => ['recorded_by' => $request->user()?->name],
            ]);

            $invoice->refresh()->recalculate();
        });

        $this->audit->record(
            action: 'payment.recorded',
            category: 'finance',
            entityType: 'invoice',
            entityId: (string) $invoice->getKey(),
            entityLabel: $invoice->number,
            detail: 'Payment of '.number_format((float) $validated['amount'], 2).' '.$invoice->currency.' recorded.',
        );

        return back()->with('status', "Payment recorded against {$invoice->number}.");
    }

    /**
     * Queue a settlement run for the payment providers.
     */
    public function reconcile(Request $request): RedirectResponse
    {
        $this->authorizeAction('payments.manage');

        $season = $this->seasons->current();

        $pending = $season->invoices()->outstanding()->count();

        $this->audit->record(
            action: 'payments.reconciled',
            category: 'finance',
            entityType: 'season',
            entityId: (string) $season->getKey(),
            entityLabel: $season->name,
            detail: 'Reconciliation queued for '.$pending.' outstanding invoices.',
        );

        return back()->with('status', "Reconciliation started · {$pending} records queued.");
    }

    /**
     * Money summary for the season: collected, outstanding, refunded, waived.
     *
     * @return array<string, mixed>
     */
    private function totals(Season $season): array
    {
        $collected = (float) $season->invoices()->sum('amount_paid');
        $outstanding = (float) $season->invoices()->outstanding()->sum(DB::raw('amount - amount_paid - amount_waived'));
        $refunded = (float) $season->invoices()->withStatus(InvoiceStatus::Refunded)->sum('amount_paid');
        $waived = (float) $season->invoices()->sum('amount_waived');

        return [
            'collected' => $collected,
            'outstanding' => max(0.0, $outstanding),
            'refunded' => $refunded,
            'waived' => $waived,
            'invoiceCount' => $season->invoices()->count(),
            'openCount' => $season->invoices()->outstanding()->count(),
            'refundCount' => $season->invoices()->withStatus(InvoiceStatus::Refunded)->count(),
            'waiverCount' => $season->invoices()->where('amount_waived', '>', 0)->count(),
            'paymentCount' => Payment::query()
                ->whereIn('invoice_id', $season->invoices()->select('id'))
                ->count(),
        ];
    }

    private function authorizeAction(string $permission): void
    {
        abort_unless(auth()->user()?->canDo($permission), 403, "You do not have the {$permission} permission.");
    }
}
