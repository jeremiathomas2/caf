<?php

namespace Tests\Feature\Admin;

use App\Enums\AssignmentStatus;
use App\Enums\RoundStatus;
use App\Enums\UserRole;
use App\Models\Judge;
use App\Models\Registration;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\ReviewScore;
use App\Models\RubricCriterion;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JudgingTest extends TestCase
{
    use RefreshDatabase;

    private function season(): Season
    {
        return Season::factory()->live()->create();
    }

    /**
     * An assignment whose judge actually belongs to the given season, which is
     * what the progress panel reads.
     */
    private function assign(Judge $judge, ReviewRound $round, Registration $registration): ReviewAssignment
    {
        return ReviewAssignment::factory()
            ->forJudge($judge)
            ->forRound($round)
            ->forRegistration($registration)
            ->create();
    }

    #[Test]
    public function the_judging_screen_ranks_applications_by_total_score(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = $this->season();

        $judge = Judge::factory()->forSeason($season)->create();
        $criterion = RubricCriterion::factory()->forSeason($season)->create(['name' => 'Vocal technique']);
        $round = ReviewRound::factory()->open()->forSeason($season)->create(['is_blind' => false]);

        $strong = Registration::factory()->forSeason($season)->create(['group_name' => 'Strong Act']);
        $weak = Registration::factory()->forSeason($season)->create(['group_name' => 'Weak Act']);

        $strongAssignment = $this->assign($judge, $round, $strong);
        $weakAssignment = $this->assign($judge, $round, $weak);

        $strongAssignment->update(['status' => AssignmentStatus::Completed]);
        $weakAssignment->update(['status' => AssignmentStatus::InProgress]);

        ReviewScore::factory()->create(['review_assignment_id' => $strongAssignment->getKey(), 'rubric_criterion_id' => $criterion->getKey(), 'points' => 9]);
        ReviewScore::factory()->create(['review_assignment_id' => $weakAssignment->getKey(), 'rubric_criterion_id' => $criterion->getKey(), 'points' => 3]);

        $this->actingAs($user)
            ->get(route('admin.judging.index'))
            ->assertOk()
            ->assertSee('Strong Act')
            ->assertSee('Weak Act')
            ->assertSeeInOrder(['Strong Act', 'Weak Act'], escape: false);
    }

    #[Test]
    public function blind_mode_hides_group_names_but_keeps_the_code(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = $this->season();
        $judge = Judge::factory()->forSeason($season)->create();
        $criterion = RubricCriterion::factory()->forSeason($season)->create();
        $round = ReviewRound::factory()->open()->forSeason($season)->create(['is_blind' => true]);

        $registration = Registration::factory()->forSeason($season)->create([
            'group_name' => 'Secretive Act',
            'code' => 'CAF2-0042',
        ]);

        $assignment = $this->assign($judge, $round, $registration);
        $assignment->update(['status' => AssignmentStatus::InProgress]);

        ReviewScore::factory()->create([
            'review_assignment_id' => $assignment->getKey(),
            'rubric_criterion_id' => $criterion->getKey(),
            'points' => 7,
        ]);

        $this->actingAs($user)
            ->get(route('admin.judging.index'))
            ->assertOk()
            ->assertSee('Entry CAF2-0042', escape: false)
            ->assertDontSee('Secretive Act');
    }

    #[Test]
    public function a_round_without_scores_still_renders(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = $this->season();

        ReviewRound::factory()->open()->forSeason($season)->create();

        $this->actingAs($user)
            ->get(route('admin.judging.index'))
            ->assertOk()
            ->assertSee('No scores yet');
    }

    #[Test]
    public function the_screen_is_scoped_to_the_season_in_the_query_string(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $current = Season::factory()->live()->create();
        $older = Season::factory()->archived()->create();

        ReviewRound::factory()->forSeason($current)->create(['name' => 'Current Round']);
        ReviewRound::factory()->forSeason($older)->create(['name' => 'Older Round']);

        $this->actingAs($user)
            ->get(route('admin.judging.index', ['season' => $older->getKey()]))
            ->assertOk()
            ->assertSee('Older Round')
            ->assertDontSee('Current Round');
    }

    #[Test]
    public function a_user_without_the_judging_permission_is_forbidden(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $this->season();

        $this->actingAs($user)
            ->get(route('admin.judging.index'))
            ->assertForbidden();
    }

    #[Test]
    public function nudging_judges_is_audited(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $this->season();

        $this->actingAs($user)
            ->from(route('admin.judging.index'))
            ->post(route('admin.judging.nudge'))
            ->assertRedirect(route('admin.judging.index'));

        $this->assertDatabaseHas('audit_logs', ['action' => 'judging.nudged']);
    }

    #[Test]
    public function nudging_judges_requires_permission(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $this->season();

        $this->actingAs($user)
            ->post(route('admin.judging.nudge'))
            ->assertForbidden();
    }

    #[Test]
    public function publishing_a_round_stamps_the_published_time(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = $this->season();
        $round = ReviewRound::factory()->open()->forSeason($season)->create();

        $this->actingAs($user)
            ->from(route('admin.judging.index'))
            ->post(route('admin.judging.publish', $round))
            ->assertRedirect(route('admin.judging.index'));

        $round->refresh();

        $this->assertSame(RoundStatus::Published, $round->status);
        $this->assertNotNull($round->published_at);
    }

    #[Test]
    public function a_published_round_cannot_be_published_again(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = $this->season();
        $round = ReviewRound::factory()->published()->forSeason($season)->create();

        $this->actingAs($user)
            ->post(route('admin.judging.publish', $round))
            ->assertForbidden();
    }

    #[Test]
    public function a_round_from_another_season_cannot_be_published(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $this->season();
        $other = Season::factory()->archived()->create();
        $round = ReviewRound::factory()->open()->forSeason($other)->create();

        $this->actingAs($user)
            ->post(route('admin.judging.publish', $round))
            ->assertNotFound();
    }

    #[Test]
    public function the_judge_panel_shows_completed_against_assigned(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = $this->season();
        $judge = Judge::factory()->forSeason($season)->create(['name' => 'Wanjiku Kamau']);
        $round = ReviewRound::factory()->open()->forSeason($season)->create();

        ReviewAssignment::factory()->count(3)->forJudge($judge)->forRound($round)->create();

        ReviewAssignment::query()->orderBy('id')->take(2)->get()
            ->each(fn (ReviewAssignment $a) => $a->update(['status' => AssignmentStatus::Completed]));

        $this->actingAs($user)
            ->get(route('admin.judging.index'))
            ->assertOk()
            ->assertSee('Wanjiku Kamau')
            ->assertSee('2/3', escape: false)
            ->assertSee('3 applications assigned', escape: false);
    }
}
