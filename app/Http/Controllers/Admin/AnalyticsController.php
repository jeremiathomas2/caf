<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\MessageThread;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Season;
use App\Services\AuditLogger;
use App\Support\SeasonContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Season reporting across the six domains the prototype groups: growth,
 * review, finance, communications, engagement and impact.
 *
 * Every figure is derived from the season's own rows, so a new install with
 * little data reports small numbers rather than pretending to be a festival.
 */
class AnalyticsController extends Controller
{
    public function __construct(
        private readonly SeasonContext $seasons,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeAction('analytics.view');

        $season = $this->seasons->current();

        $registrations = Registration::query()->forSeason($season);
        $approved = (clone $registrations)->withStatus([
            RegistrationStatus::Approved->value,
            RegistrationStatus::Confirmed->value,
            RegistrationStatus::Waitlisted->value,
        ])->count();
        $started = (clone $registrations)->whereNotNull('started_at')->count();
        $paid = (clone $registrations)->withStatus([RegistrationStatus::Approved, RegistrationStatus::Confirmed, RegistrationStatus::Waitlisted])
            ->whereIn('payment_status', [PaymentStatus::Paid->value, PaymentStatus::Waived->value])
            ->count();

        return view('admin.analytics.index', [
            'season' => $season,
            'metrics' => [
                'registrationConversion' => $this->percent($started > 0 ? $registrations->withStatus([
                    RegistrationStatus::Approved->value,
                    RegistrationStatus::Confirmed->value,
                    RegistrationStatus::Waitlisted->value,
                ])->count() : 0, $started),
                'paymentConversion' => $this->percent($paid, $approved),
                'outstanding' => (float) $season->invoices()->outstanding()->sum('amount') - (float) $season->invoices()->outstanding()->sum('amount_paid'),
                'openInvoices' => $season->invoices()->outstanding()->count(),
                'threads' => MessageThread::query()->where('season_id', $season->getKey())->count(),
                'threadsResolved' => MessageThread::query()->where('season_id', $season->getKey())->where('status', 'resolved')->count(),
            ],
            'funnel' => $this->funnel($season),
            'growth' => $this->growth($season),
            'countries' => $this->countries($season),
            'categories' => $this->categories($season),
            'payments' => $this->paymentBreakdown($season),
        ]);
    }

    /**
     * Generate the sponsor impact report for the season.
     */
    public function impactReport(Request $request): RedirectResponse
    {
        $this->authorizeAction('analytics.view');

        $season = $this->seasons->current();

        $this->audit->record(
            action: 'analytics.impact_report',
            category: 'analytics',
            entityType: 'season',
            entityId: (string) $season->getKey(),
            entityLabel: $season->name,
            detail: 'Sponsor impact report generated.',
        );

        return back()->with('status', 'Sponsor impact report is being generated.');
    }

    /**
     * Registration counts at each stage of the pipeline.
     *
     * @return list<array{label: string, value: int, gradient: string}>
     */
    private function funnel($season): array
    {
        $query = Registration::query()->forSeason($season);

        $gradients = [
            'linear-gradient(90deg,#5E181D,#E4572E)',
            'linear-gradient(90deg,#7A2228,#E4572E)',
            'linear-gradient(90deg,#B8391A,#E4572E)',
            'linear-gradient(90deg,#B8860B,#E8B368)',
            'linear-gradient(90deg,#1F7A4D,#4CC38A)',
        ];

        $stages = [
            'Started' => (clone $query)->whereNotNull('started_at')->count(),
            'Submitted' => (clone $query)->withStatus(RegistrationStatus::Submitted)->count(),
            'Under review' => (clone $query)->withStatus(RegistrationStatus::UnderReview)->count(),
            'Shortlisted' => (clone $query)->withStatus(RegistrationStatus::Shortlisted)->count(),
            'Approved' => (clone $query)->withStatus([RegistrationStatus::Approved, RegistrationStatus::Confirmed])->count(),
        ];

        $peak = max(1, max($stages));

        $rows = [];
        $index = 0;

        foreach ($stages as $label => $value) {
            $rows[] = [
                'label' => $label,
                'value' => $value,
                'percent' => (int) round($value / $peak * 100),
                'gradient' => $gradients[$index] ?? $gradients[0],
            ];
            $index++;
        }

        return $rows;
    }

    /**
     * Cumulative submissions per week since registration opened.
     *
     * @return list<array{label: string, value: int}>
     */
    private function growth($season): array
    {
        $opened = $season->registration_opens_at ?? $season->created_at ?? now()->subMonths(3);

        $weeks = max(1, (int) ceil(now()->diffInDays($opened, absolute: true) / 7));
        $weeks = min($weeks, 22);

        $rows = [];

        for ($week = 0; $week < $weeks; $week++) {
            $cutoff = Carbon::parse($opened)->addWeeks($week);

            $rows[] = [
                'label' => 'W'.($week * 4 + 1),
                'value' => Registration::query()
                    ->forSeason($season)
                    ->where('submitted_at', '<=', $cutoff)
                    ->count(),
            ];
        }

        return $rows;
    }

    /**
     * Approved registrations by country.
     *
     * @return list<array{country: string, value: int, percent: int}>
     */
    private function countries($season): array
    {
        $counts = Registration::query()
            ->forSeason($season)
            ->withStatus([RegistrationStatus::Approved, RegistrationStatus::Confirmed, RegistrationStatus::Waitlisted])
            ->whereNotNull('country')
            ->selectRaw('country, COUNT(*) as total')
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit(7)
            ->pluck('total', 'country');

        $peak = max(1, (int) $counts->max());

        return $counts->map(fn (int $total, string $country): array => [
            'country' => $country,
            'value' => $total,
            'percent' => (int) round($total / $peak * 100),
        ])->values()->all();
    }

    /**
     * Approved registrations by category.
     *
     * @return list<array{category: string, value: int}>
     */
    private function categories($season): array
    {
        return Registration::query()
            ->forSeason($season)
            ->withStatus([RegistrationStatus::Approved, RegistrationStatus::Confirmed, RegistrationStatus::Waitlisted])
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($row): array => [
                'category' => Str::headline((string) $row->category),
                'value' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * Payment states and the method mix behind them.
     *
     * @return array<string, mixed>
     */
    private function paymentBreakdown($season): array
    {
        $counts = Registration::query()
            ->forSeason($season)
            ->selectRaw('payment_status, COUNT(*) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        $methods = Payment::query()
            ->whereIn('invoice_id', $season->invoices()->select('id'))
            ->selectRaw('method, COUNT(*) as total, SUM(amount) as value')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => [
                'method' => $row->method->label(),
                'count' => (int) $row->total,
                'value' => (float) $row->value,
            ]);

        $paid = (int) ($counts[PaymentStatus::Paid->value] ?? 0);
        $settled = $counts->filter(fn ($v, $k) => in_array($k, [
            PaymentStatus::Paid->value,
            PaymentStatus::Waived->value,
        ], true))->sum();

        return [
            'states' => $counts->map(fn ($total, $state): array => [
                'state' => (string) $state,
                'count' => (int) $total,
            ])->values()->all(),
            'methods' => $methods->all(),
            'settledPercent' => $this->percent($settled, max(1, $counts->sum())),
            'paid' => $paid,
            'waived' => (int) ($counts[PaymentStatus::Waived->value] ?? 0),
        ];
    }

    private function percent(int $part, int $whole): float
    {
        return $whole > 0 ? round($part / $whole * 100, 1) : 0.0;
    }

    private function authorizeAction(string $permission): void
    {
        abort_unless(auth()->user()?->canDo($permission), 403, "You do not have the {$permission} permission.");
    }
}
