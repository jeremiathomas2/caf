<?php

namespace Database\Seeders;

use App\Enums\AssignmentStatus;
use App\Enums\RegistrationStatus;
use App\Enums\RoundStatus;
use App\Models\Judge;
use App\Models\Registration;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\ReviewScore;
use App\Models\RubricCriterion;
use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * A live judging round for the current season: six weighted criteria, a judge
 * panel, assignments across the shortlisted groups, and scores for the panel
 * members who have already finished.
 */
class ReviewSeeder extends Seeder
{
    /**
     * @var list<array{name: string, max: int, weight: int}>
     */
    private const CRITERIA = [
        ['name' => 'Vocal technique', 'max' => 10, 'weight' => 25],
        ['name' => 'Harmony & intonation', 'max' => 10, 'weight' => 25],
        ['name' => 'Rhythm & precision', 'max' => 10, 'weight' => 15],
        ['name' => 'Arrangement & originality', 'max' => 10, 'weight' => 20],
        ['name' => 'Stage presence', 'max' => 10, 'weight' => 10],
        ['name' => 'Audience impact', 'max' => 10, 'weight' => 5],
    ];

    public function run(): void
    {
        $season = Season::query()->where('number', 2)->firstOrFail();

        $criteria = collect(self::CRITERIA)->map(fn (array $criterion, int $order) => RubricCriterion::query()->updateOrCreate(
            ['season_id' => $season->getKey(), 'name' => $criterion['name']],
            [
                'max_points' => $criterion['max'],
                'weight' => $criterion['weight'],
                'sort_order' => $order,
            ],
        ));

        $judges = $this->judges($season);

        $round = ReviewRound::query()->updateOrCreate(
            ['season_id' => $season->getKey(), 'sequence' => 1],
            [
                'name' => 'Preliminary judging',
                'status' => RoundStatus::Open->value,
                'is_blind' => true,
                'aggregation' => 'Trimmed mean (drop high & low)',
                'opens_at' => now()->subDays(10),
                'closes_at' => now()->addDays(12),
            ],
        );

        $shortlisted = Registration::query()
            ->where('season_id', $season->getKey())
            ->whereIn('status', [
                RegistrationStatus::Shortlisted->value,
                RegistrationStatus::Approved->value,
                RegistrationStatus::Confirmed->value,
            ])
            ->orderBy('code')
            ->get();

        foreach ($shortlisted as $index => $registration) {
            foreach ($judges as $judgeIndex => $judge) {
                $completed = ($index + $judgeIndex) % 3 !== 2;

                $assignment = ReviewAssignment::query()->updateOrCreate(
                    [
                        'review_round_id' => $round->getKey(),
                        'registration_id' => $registration->getKey(),
                        'judge_id' => $judge->getKey(),
                    ],
                    [
                        'status' => $completed
                            ? AssignmentStatus::Completed->value
                            : AssignmentStatus::InProgress->value,
                        'completed_at' => $completed ? now()->subDays(3) : null,
                    ],
                );

                if ($completed) {
                    $this->score($assignment, $criteria, $index, $judgeIndex);
                }
            }

            $this->tally($registration);
        }
    }

    /**
     * @return Collection<int, Judge>
     */
    private function judges(Season $season)
    {
        $panel = [
            ['name' => 'Prof. Amina Ramadhani', 'role_title' => 'Chair of judges', 'email' => 'amina.ramadhani@example.com'],
            ['name' => 'Jonas Mwakalinga', 'role_title' => 'Senior judge', 'email' => 'jonas.mwakalinga@example.com'],
            ['name' => 'Rehema Ally', 'role_title' => 'Judge', 'email' => 'rehema.ally@example.com'],
            ['name' => 'Dr. Peter Komba', 'role_title' => 'Judge', 'email' => 'peter.komba@example.com'],
        ];

        return collect($panel)->map(function (array $judge, int $index) use ($season) {
            $user = User::query()->where('email', $judge['email'])->first();

            return Judge::query()->updateOrCreate(
                ['season_id' => $season->getKey(), 'name' => $judge['name']],
                [
                    'user_id' => $user?->getKey(),
                    'email' => $judge['email'],
                    'role_title' => $judge['role_title'],
                    'bio' => 'Panel member for the '.$season->name.' season.',
                    'is_public' => $index === 0,
                    'is_active' => true,
                ],
            );
        });
    }

    /**
     * @param  Collection<int, RubricCriterion>  $criteria
     */
    private function score(ReviewAssignment $assignment, $criteria, int $index, int $judgeIndex): void
    {
        foreach ($criteria as $position => $criterion) {
            $points = min(
                $criterion->max_points,
                6 + (($index * 2 + $judgeIndex + $position) % 4),
            );

            ReviewScore::query()->updateOrCreate(
                [
                    'review_assignment_id' => $assignment->getKey(),
                    'rubric_criterion_id' => $criterion->getKey(),
                ],
                [
                    'points' => $points,
                    'comment' => $position === 0
                        ? 'Strong onset and a clear blend through the lower voices.'
                        : null,
                ],
            );
        }
    }

    /**
     * Roll the completed scores up onto the registration so lists can sort by
     * them without a join.
     */
    private function tally(Registration $registration): void
    {
        $completed = ReviewAssignment::query()
            ->where('registration_id', $registration->getKey())
            ->where('status', AssignmentStatus::Completed->value);

        $points = ReviewScore::query()
            ->whereIn('review_assignment_id', $completed->select('id'))
            ->pluck('points');

        if ($points->isEmpty()) {
            return;
        }

        $registration->update([
            'score_total' => round((float) $points->avg(), 2),
            'score_votes' => $completed->count(),
        ]);
    }
}
