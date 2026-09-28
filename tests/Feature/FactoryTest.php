<?php

namespace Tests\Feature;

use App\Enums\CampaignStatus;
use App\Enums\Channel;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PublishStatus;
use App\Enums\RegistrationStatus;
use App\Enums\RoundStatus;
use App\Enums\SeasonState;
use App\Enums\ThreadStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\ContentPage;
use App\Models\FaqItem;
use App\Models\Invoice;
use App\Models\Judge;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\NavItem;
use App\Models\Payment;
use App\Models\ProgrammeSlot;
use App\Models\Registration;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\ReviewScore;
use App\Models\RubricCriterion;
use App\Models\Season;
use App\Models\Sponsor;
use App\Models\TeamMember;
use App\Models\TermsClause;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{class-string}>
     */
    public static function factoryProvider(): array
    {
        return [
            'User' => [User::class],
            'Season' => [Season::class],
            'Registration' => [Registration::class],
            'Invoice' => [Invoice::class],
            'Payment' => [Payment::class],
            'MessageThread' => [MessageThread::class],
            'Message' => [Message::class],
            'Campaign' => [Campaign::class],
            'ContentPage' => [ContentPage::class],
            'ProgrammeSlot' => [ProgrammeSlot::class],
            'Sponsor' => [Sponsor::class],
            'Testimonial' => [Testimonial::class],
            'FaqItem' => [FaqItem::class],
            'TermsClause' => [TermsClause::class],
            'TeamMember' => [TeamMember::class],
            'NavItem' => [NavItem::class],
            'ReviewRound' => [ReviewRound::class],
            'RubricCriterion' => [RubricCriterion::class],
            'Judge' => [Judge::class],
            'ReviewAssignment' => [ReviewAssignment::class],
            'ReviewScore' => [ReviewScore::class],
        ];
    }

    /**
     * @param  class-string  $model
     */
    #[Test]
    #[DataProvider('factoryProvider')]
    public function factory_creates_a_persisted_model_without_overriding_input(string $model): void
    {
        $created = $model::factory()->create();

        $this->assertTrue($created->exists);
        $this->assertTrue($created->getKey() !== null);
        $this->assertSame(1, $model::query()->count());
    }

    #[Test]
    public function season_factory_states_set_state_and_currency(): void
    {
        $this->assertSame(SeasonState::Live, Season::factory()->live()->create()->state);
        $this->assertTrue(Season::factory()->live()->create()->is_current);
        $this->assertSame(SeasonState::Archived, Season::factory()->archived()->create()->state);
        $this->assertFalse(Season::factory()->archived()->create()->is_current);
    }

    #[Test]
    public function registration_factory_states(): void
    {
        $season = Season::factory()->create();

        $confirmed = Registration::factory()->forSeason($season)->confirmed()->create();

        $this->assertSame($season->id, $confirmed->season_id);
        $this->assertSame(RegistrationStatus::Confirmed, $confirmed->status);
        $this->assertTrue($confirmed->is_public);
        $this->assertStringStartsWith($season->codePrefix().'-', $confirmed->code);
    }

    #[Test]
    public function invoice_factory_states(): void
    {
        $registration = Registration::factory()->create(['members_count' => 4]);

        $invoice = Invoice::factory()->forRegistration($registration)->create();

        $this->assertSame($registration->season_id, $invoice->season_id);
        $this->assertEquals($registration->season->feeFor(4), (float) $invoice->amount);
        $this->assertSame(InvoiceStatus::Issued, $invoice->status);

        $this->assertSame(InvoiceStatus::Paid, Invoice::factory()->paid()->create()->status);
        $this->assertSame(InvoiceStatus::Overdue, Invoice::factory()->overdue()->create()->status);

        $partial = Invoice::factory()->partiallyPaid(0.25)->create(['amount' => 1000]);
        $this->assertSame(InvoiceStatus::PartiallyPaid, $partial->status);
        $this->assertEquals(250, (float) $partial->amount_paid);
    }

    #[Test]
    public function payment_factory_copies_invoice_totals(): void
    {
        $invoice = Invoice::factory()->create(['amount' => 250000]);
        $payment = Payment::factory()->forInvoice($invoice)->create();

        $this->assertSame($invoice->registration_id, $payment->registration_id);
        $this->assertEquals(250000, (float) $payment->amount);
        $this->assertInstanceOf(PaymentMethod::class, $payment->method);
        $this->assertTrue($payment->isSuccessful());
        $this->assertFalse(Payment::factory()->failed()->create()->isSuccessful());
    }

    #[Test]
    public function campaign_and_review_factory_states(): void
    {
        $sent = Campaign::factory()->sent()->create();

        $this->assertSame(CampaignStatus::Sent, $sent->status);
        $this->assertSame(98.3, $sent->deliveryRate());
        $this->assertSame(CampaignStatus::Scheduled, Campaign::factory()->scheduled()->create()->status);
        $this->assertSame(CampaignStatus::Draft, Campaign::factory()->create()->status);

        $this->assertSame(RoundStatus::Open, ReviewRound::factory()->open()->create()->status);
        $this->assertSame(RoundStatus::Published, ReviewRound::factory()->published()->create()->status);
        $this->assertTrue(ReviewAssignment::factory()->completed()->create()->isComplete());
    }

    #[Test]
    public function user_factory_states(): void
    {
        $this->assertNull(User::factory()->unverified()->create()->email_verified_at);
        $this->assertTrue(User::factory()->withRole(UserRole::Judge)->create()->hasRole(UserRole::Judge));
        $this->assertTrue(User::factory()->withTwoFactor()->create()->hasConfirmedTwoFactor());
    }

    #[Test]
    public function content_and_platform_factories(): void
    {
        $this->assertSame(PublishStatus::Published, ContentPage::factory()->published()->create()->status);
        $this->assertTrue(ContentPage::factory()->news()->create()->isLive());
        $this->assertFalse(ContentPage::factory()->create()->isLive());

        $slot = ProgrammeSlot::factory()->create();
        $this->assertNotEmpty($slot->timeRange());
        $this->assertStringContainsString('–', $slot->timeRange());

        $this->assertNotEmpty(TermsClause::factory()->create()->title);
        $this->assertSame('#', NavItem::factory()->create(['route_name' => 'missing.route'])->href());

        $this->assertInstanceOf(
            Channel::class,
            MessageThread::factory()->withChannel(Channel::Whatsapp)->create()->channel,
        );
        $this->assertSame(ThreadStatus::Open, MessageThread::factory()->create()->status);
    }
}
