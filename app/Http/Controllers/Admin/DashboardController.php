<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Judge;
use App\Models\MessageThread;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Season;
use App\Support\SeasonContext;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly SeasonContext $seasons) {}

    public function __invoke(Request $request): View
    {
        $season = $this->seasons->current();

        return view('admin.dashboard', [
            'season' => $season,
            'kpis' => $this->kpis($season),
            'statusBreakdown' => $this->statusBreakdown($season),
            'revenue' => $this->revenueSeries($season),
            'recentRegistrations' => Registration::query()
                ->forSeason($season)
                ->with('season')
                ->latest('submitted_at')
                ->limit(6)
                ->get(),
            'attention' => Registration::query()
                ->forSeason($season)
                ->needsAttention()
                ->with('season')
                ->limit(5)
                ->get(),
            'openThreads' => MessageThread::query()
                ->where('season_id', $season->getKey())
                ->needsReply()
                ->with('registration')
                ->latest('last_message_at')
                ->limit(5)
                ->get(),
            'judgeLoad' => Judge::query()
                ->where('season_id', $season->getKey())
                ->withCount('assignments')
                ->limit(5)
                ->get(),
            'recentAudit' => AuditLog::query()->with('actor')->latest()->limit(6)->get(),
        ]);
    }

    /**
     * @return array<string, array{value: int|string|float, foot: string, tone: string}>
     */
    private function kpis(Season $season): array
    {
        $registrations = Registration::query()->forSeason($season);
        $invoices = Invoice::query()->forSeason($season);

        $total = (clone $registrations)->count();
        $confirmed = (clone $registrations)->withStatus(RegistrationStatus::Confirmed)->count();
        $members = (int) (clone $registrations)->sum('members_count');
        $expected = (float) (clone $invoices)->sum('amount');
        $collected = (float) (clone $invoices)->sum('amount_paid');
        $outstanding = (int) (clone $invoices)->outstanding()->count();
        $awaitingPayment = (clone $registrations)->unpaid()->count();

        return [
            'registrations' => [
                'value' => $total,
                'foot' => sprintf('%d confirmed · %d performers', $confirmed, $members),
                'tone' => 'primary',
            ],
            'collected' => [
                'value' => $expected > 0 ? round($collected / $expected * 100) : 0,
                'foot' => sprintf('%s of %s expected', $this->money($collected), $this->money($expected)),
                'tone' => 'ok',
            ],
            'awaiting_payment' => [
                'value' => $awaitingPayment,
                'foot' => sprintf('%d invoices outstanding', $outstanding),
                'tone' => $awaitingPayment > 0 ? 'warn' : 'ok',
            ],
            'open_threads' => [
                'value' => MessageThread::query()->needsReply()->count(),
                'foot' => 'Conversations needing a reply',
                'tone' => 'info',
            ],
        ];
    }

    /**
     * @return list<array{status: RegistrationStatus, count: int, pct: int}>
     */
    private function statusBreakdown(Season $season): array
    {
        $total = Registration::query()->forSeason($season)->count();

        $rows = Registration::query()
            ->forSeason($season)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(RegistrationStatus::cases())
            ->map(fn (RegistrationStatus $status): array => [
                'status' => $status,
                'count' => (int) ($rows[$status->value] ?? 0),
                'pct' => $total > 0 ? (int) round(((int) ($rows[$status->value] ?? 0)) / $total * 100) : 0,
            ])
            ->reject(fn (array $row): bool => $row['count'] === 0)
            ->values()
            ->all();
    }

    /**
     * Collected vs invoiced for the last six months.
     *
     * Collected comes from successful payments, expected from the invoices
     * issued in the same window. Bucketing happens in PHP so the same code
     * runs on MySQL and on the SQLite test database.
     *
     * @return array{labels: list<string>, collected: list<float>, expected: list<float>}
     */
    private function revenueSeries(Season $season): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $back): CarbonImmutable => CarbonImmutable::now()->subMonths($back)->startOfMonth());

        $from = $months->first();

        $payments = Payment::query()
            ->where('status', 'success')
            ->where('paid_at', '>=', $from)
            ->whereHas('registration', fn (Builder $q) => $q->where('season_id', $season->getKey()))
            ->get(['paid_at', 'amount'])
            ->groupBy(fn (Payment $payment): string => $payment->paid_at->format('Y-m'));

        $invoices = Invoice::query()
            ->forSeason($season)
            ->whereNotNull('issued_at')
            ->where('issued_at', '>=', $from)
            ->get(['issued_at', 'amount'])
            ->groupBy(fn (Invoice $invoice): string => $invoice->issued_at->format('Y-m'));

        $collected = $months
            ->map(fn (CarbonImmutable $month): float => (float) ($payments[$month->format('Y-m')] ?? collect())->sum('amount'))
            ->all();

        $expected = $months
            ->map(fn (CarbonImmutable $month): float => (float) ($invoices[$month->format('Y-m')] ?? collect())->sum('amount'))
            ->all();

        return [
            'labels' => $months->map(fn (CarbonImmutable $month): string => $month->format('M'))->all(),
            'collected' => $collected,
            'expected' => $expected,
        ];
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0);
    }
}
