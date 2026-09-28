<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AssignmentStatus;
use App\Enums\RoundStatus;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\RubricCriterion;
use App\Models\Season;
use App\Services\AuditLogger;
use App\Support\SeasonContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Judging: rounds, the rubric, and the live standings derived from the scores
 * judges have already submitted.
 */
class JudgingController extends Controller
{
    public function __construct(
        private readonly SeasonContext $seasons,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeAction('judging.view');

        $season = $this->seasons->current();

        $round = $this->selectedRound($season, $request);

        $criteria = RubricCriterion::query()
            ->where('season_id', $season->getKey())
            ->orderBy('sort_order')
            ->get();

        $progress = $this->judgeProgress($season, $round);

        $standings = $this->standings($season, $round, $criteria);

        return view('admin.judging.index', [
            'season' => $season,
            'rounds' => ReviewRound::query()
                ->where('season_id', $season->getKey())
                ->orderBy('sequence')
                ->get(),
            'round' => $round,
            'criteria' => $criteria,
            'progress' => $progress,
            'standings' => $standings,
            'pendingScores' => (int) $progress->sum('pending'),
            'assignedCount' => (int) $progress->sum('total'),
            'openRounds' => ReviewRound::query()
                ->where('season_id', $season->getKey())
                ->where('status', RoundStatus::Open->value)
                ->count(),
        ]);
    }

    /**
     * Nudge every judge with scores still outstanding on the open rounds.
     */
    public function nudge(Request $request): RedirectResponse
    {
        $this->authorizeAction('judging.manage');

        $season = $this->seasons->current();

        $judges = $season->judges()
            ->whereHas('assignments', fn ($q) => $q->whereIn('status', [
                AssignmentStatus::Pending->value,
                AssignmentStatus::InProgress->value,
            ]))
            ->get();

        $this->audit->record(
            action: 'judging.nudged',
            category: 'judging',
            entityType: 'season',
            entityId: (string) $season->getKey(),
            entityLabel: $season->name,
            detail: 'Reminder queued for '.$judges->count().' judges with outstanding scores.',
        );

        return back()->with('status', $judges->isEmpty()
            ? 'No judges have outstanding scores.'
            : 'Reminder queued for '.$judges->count().' judges with outstanding scores.');
    }

    /**
     * Close the round and write the aggregate score back onto each
     * registration, then release the standings.
     */
    public function publish(Request $request, ReviewRound $round): RedirectResponse
    {
        $this->authorizeAction('judging.manage');

        abort_unless($round->season_id === $this->seasons->current()->getKey(), 404);

        // Republishing would overwrite the scores already released to groups, so
        // only a round that has not gone out can be published.
        abort_if($round->status === RoundStatus::Published, 403, 'This round has already been published.');

        $round->load('assignments.scores');

        DB::transaction(function () use ($round): void {
            foreach ($round->assignments->groupBy('registration_id') as $registrationId => $assignments) {
                $points = $assignments
                    ->where('status', AssignmentStatus::Completed->value)
                    ->flatMap(fn (ReviewAssignment $assignment) => $assignment->scores)
                    ->pluck('points');

                Registration::query()
                    ->whereKey($registrationId)
                    ->update([
                        'score_total' => $points->isEmpty() ? null : round((float) $points->avg(), 2),
                        'score_votes' => $assignments->where('status', AssignmentStatus::Completed->value)->count(),
                        'reviewed_at' => now(),
                    ]);
            }

            $round->update([
                'status' => RoundStatus::Published->value,
                'published_at' => now(),
            ]);
        });

        $this->audit->record(
            action: 'judging.published',
            category: 'judging',
            entityType: 'round',
            entityId: (string) $round->getKey(),
            entityLabel: $round->name,
            detail: 'Results published for '.$round->assignments->groupBy('registration_id')->count().' registrations.',
        );

        return back()->with('status', "Results published for {$round->name}.");
    }

    /**
     * The requested round, falling back to the open one and then the newest.
     */
    private function selectedRound(Season $season, Request $request): ?ReviewRound
    {
        $rounds = ReviewRound::query()
            ->where('season_id', $season->getKey())
            ->orderBy('sequence')
            ->get();

        $requested = $request->integer('round') ?: null;

        if ($requested !== null && ($match = $rounds->firstWhere('id', $requested))) {
            return $match;
        }

        return $rounds->firstWhere('status', RoundStatus::Open->value) ?? $rounds->last();
    }

    /**
     * Per-judge completion across the given round, or the whole season when no
     * round is selected.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function judgeProgress(Season $season, ?ReviewRound $round): Collection
    {
        return $season->judges()
            ->with(['assignments' => fn ($q) => $q->when(
                $round !== null,
                fn ($inner) => $inner->where('review_round_id', $round->getKey()),
            )])
            ->orderBy('name')
            ->get()
            ->map(fn ($judge) => [
                'judge' => $judge,
                'done' => $judge->assignments->where('status', AssignmentStatus::Completed->value)->count(),
                'total' => $judge->assignments->count(),
                'pending' => $judge->assignments->where('status', '!=', AssignmentStatus::Completed->value)->count(),
            ])
            ->sortByDesc('done');
    }

    /**
     * @param  Collection<int, RubricCriterion>  $criteria
     * @return Collection<int, array<string, mixed>>
     */
    private function standings(Season $season, ?ReviewRound $round, Collection $criteria): Collection
    {
        if ($round === null) {
            return collect();
        }

        $assignments = ReviewAssignment::query()
            ->where('review_round_id', $round->getKey())
            ->with(['registration', 'scores' => fn ($q) => $q->whereIn('rubric_criterion_id', $criteria->pluck('id'))])
            ->whereIn('status', [AssignmentStatus::Completed->value, AssignmentStatus::InProgress->value])
            ->get();

        $rows = $assignments
            ->groupBy('registration_id')
            ->map(function ($group) use ($criteria) {
                $registration = $group->first()->registration;
                $scores = $group->flatMap(fn (ReviewAssignment $assignment) => $assignment->scores);

                $perCriterion = $criteria->mapWithKeys(function (RubricCriterion $criterion) use ($scores) {
                    $points = $scores->where('rubric_criterion_id', $criterion->getKey())->pluck('points');

                    return [$criterion->getKey() => $points->isEmpty() ? null : round((float) $points->avg(), 1)];
                });

                $total = 0.0;
                $weightCovered = 0;

                foreach ($criteria as $criterion) {
                    $scored = $perCriterion[$criterion->getKey()];

                    if ($scored === null) {
                        continue;
                    }

                    $weight = max(1, $criterion->weight);
                    $total += $scored / max(1, $criterion->max_points) * $weight;
                    $weightCovered += $weight;
                }

                return [
                    'registration' => $registration,
                    'scores' => $perCriterion,
                    'weighted' => $weightCovered > 0 ? round($total, 1) : null,
                    'votes' => $group->where('status', AssignmentStatus::Completed->value)->count(),
                ];
            })
            ->filter(fn (array $row) => $row['registration'] !== null && $row['weighted'] !== null)
            ->sortByDesc('weighted')
            ->values();

        return $rows;
    }

    private function authorizeAction(string $permission): void
    {
        abort_unless(auth()->user()?->canDo($permission), 403, "You do not have the {$permission} permission.");
    }
}
