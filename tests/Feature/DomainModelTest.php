<?php

namespace Tests\Feature;

use App\Enums\AssignmentStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Enums\SeasonState;
use App\Enums\ThreadStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Registration;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\RubricCriterion;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DomainModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_roles_expose_permissions_and_admin_access(): void
    {
        $admin = User::factory()->withRole(UserRole::SuperAdmin)->create(['name' => 'Amina Juma']);
        $judge = User::factory()->withRole(UserRole::Judge)->create();
        $leader = User::factory()->withRole(UserRole::GroupLeader)->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->canDo('settings.manage'));
        $this->assertTrue($admin->hasRole(UserRole::SuperAdmin));

        $this->assertFalse($judge->canDo('registrations.manage'));
        $this->assertTrue($judge->canDo('judging.score'));

        $this->assertFalse($leader->isAdmin());
        $this->assertFalse($leader->canDo('admin.access'));

        $this->assertSame('AJ', $admin->initials());
        $this->assertFalse($admin->requiresTwoFactorChallenge());
    }

    #[Test]
    public function two_factor_users_are_detected(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->assertTrue($user->requiresTwoFactorChallenge());
        $this->assertTrue($user->hasConfirmedTwoFactor());
    }

    #[Test]
    public function season_calculates_fees_rounding_and_open_window(): void
    {
        $earlyBird = Season::factory()->create([
            'per_head_fee' => 150000,
            'early_bird_fee' => 125000,
            'early_bird_closes_at' => now()->addMonth(),
            'rounding_increment' => 500,
        ]);

        $this->assertInstanceOf(SeasonState::class, $earlyBird->state);
        $this->assertSame(750000, $earlyBird->feeFor(6));
        $this->assertSame('CAF'.$earlyBird->number, $earlyBird->codePrefix());
        $this->assertTrue($earlyBird->registrationIsOpen());
        $this->assertTrue($earlyBird->isEarlyBird());
        $this->assertFalse($earlyBird->isLive());

        $standard = Season::factory()->create([
            'per_head_fee' => 150000,
            'early_bird_fee' => 125000,
            'early_bird_closes_at' => now()->subDay(),
            'rounding_increment' => 500,
        ]);

        $this->assertFalse($standard->isEarlyBird());
        $this->assertSame(900000, $standard->feeFor(6));

        $closed = Season::factory()->create(['registration_closes_at' => now()->subDay()]);
        $this->assertFalse($closed->registrationIsOpen());
    }

    #[Test]
    public function season_rounds_fees_to_the_configured_increment(): void
    {
        $season = Season::factory()->create([
            'per_head_fee' => 150000,
            'early_bird_fee' => null,
            'rounding_increment' => 1000,
        ]);

        $this->assertSame(2000, $season->roundToIncrement(2100));
        $this->assertSame(2000, $season->roundToIncrement(1500));
    }

    #[Test]
    public function registration_casts_status_and_scopes_correctly(): void
    {
        $season = Season::factory()->create();

        $confirmed = Registration::factory()->forSeason($season)->confirmed()->create();
        $submitted = Registration::factory()->forSeason($season)
            ->withStatus(RegistrationStatus::Submitted)
            ->withPaymentStatus(PaymentStatus::Unpaid)
            ->create();

        $this->assertInstanceOf(RegistrationStatus::class, $confirmed->status);
        $this->assertSame(RegistrationStatus::Confirmed, $confirmed->status);
        $this->assertInstanceOf(PaymentStatus::class, $confirmed->payment_status);
        $this->assertTrue($confirmed->is_public);

        $this->assertSame(1, Registration::forSeason($season)->public()->count());
        $this->assertSame(1, Registration::forSeason($season)->needsAttention()->count());
        $this->assertSame(1, Registration::forSeason($season)->unpaid()->count());
        $this->assertSame(1, Registration::forSeason($season)->withStatus(RegistrationStatus::Submitted)->count());
        $this->assertSame(2, Registration::forSeason($season)->count());

        $this->assertTrue($submitted->isEditable());
        $this->assertFalse($confirmed->isEditable());
    }

    #[Test]
    public function registration_members_and_status_events_relate_correctly(): void
    {
        $registration = Registration::factory()->create();

        $registration->members()->createMany([
            ['name' => 'Lead One', 'is_lead' => true, 'sort_order' => 0],
            ['name' => 'Second', 'sort_order' => 1],
        ]);

        $registration->statusEvents()->create(['to_status' => 'under_review', 'actor_label' => 'Officer']);

        $this->assertSame(2, $registration->members()->count());
        $this->assertSame('Lead One', $registration->members()->first()->name);
        $this->assertTrue($registration->members()->first()->is_lead);

        $event = $registration->statusEvents()->first();
        $this->assertInstanceOf(RegistrationStatus::class, $event->to_status);
        $this->assertSame('Officer', $event->actorName());
    }

    #[Test]
    public function invoice_tracks_balance_progress_and_overdue(): void
    {
        $half = Invoice::factory()->partiallyPaid(0.5)->create();

        $this->assertInstanceOf(InvoiceStatus::class, $half->status);
        $this->assertSame(InvoiceStatus::PartiallyPaid, $half->status);
        $this->assertEquals((float) $half->amount / 2, $half->balance());
        $this->assertSame(50, $half->progressPercent());

        $invoice = Invoice::factory()->partiallyPaid()->create([
            'amount' => 300000,
            'amount_paid' => 150000,
        ]);

        $this->assertEquals(150000, $invoice->balance());
        $this->assertSame(50, $invoice->progressPercent());
        $this->assertTrue($invoice->balance() > 0);

        $overdue = Invoice::factory()->overdue()->create([
            'amount' => 100000,
            'due_at' => now()->subDays(3),
        ]);

        $this->assertTrue($overdue->isOverdue());
        $this->assertSame(3, $overdue->daysOverdue());

        $paid = Invoice::factory()->paid()->create(['amount' => 100000]);
        $this->assertFalse($paid->isOverdue());
        $this->assertSame(0, $paid->daysOverdue());
        $this->assertSame(100, $paid->progressPercent());
        $this->assertEquals(0.0, $paid->balance());
    }

    #[Test]
    public function thread_exposes_channel_status_and_message_history(): void
    {
        $thread = MessageThread::factory()->create();

        Message::factory()->onThread($thread)->create([
            'sent_at' => now()->subHour(),
            'body' => 'Please confirm our slot.',
        ]);
        Message::factory()->onThread($thread)->outbound()->create([
            'sent_at' => now(),
        ]);

        $thread->refresh()->load('messages');

        $this->assertInstanceOf(ThreadStatus::class, $thread->status);
        $this->assertSame(2, $thread->messages()->count());
        $this->assertCount(2, $thread->messages);
        $this->assertTrue($thread->messages->first()->isInbound());
        $this->assertFalse($thread->messages->last()->isInbound());
        $this->assertNotEmpty($thread->preview());

        $this->assertSame(1, MessageThread::query()->count());
        $this->assertSame(1, MessageThread::withStatus(ThreadStatus::Open)->count());
        $this->assertSame(1, MessageThread::needsReply()->count());
    }

    #[Test]
    public function review_assignment_aggregates_weighted_scores(): void
    {
        $round = ReviewRound::factory()->create();
        $assignment = ReviewAssignment::factory()->create(['review_round_id' => $round->id]);

        $criterion = RubricCriterion::factory()->create([
            'season_id' => $round->season_id,
            'weight' => 50,
            'max_points' => 10,
        ]);

        $assignment->scores()->create([
            'rubric_criterion_id' => $criterion->id,
            'points' => 8,
        ]);

        $this->assertInstanceOf(AssignmentStatus::class, $assignment->status);
        $this->assertSame(AssignmentStatus::Pending, $assignment->status);
        $this->assertFalse($assignment->isComplete());
        $this->assertEquals(400, $assignment->fresh()->totalPoints());
        $this->assertSame(0, $round->fresh()->completionPercent());
    }
}
