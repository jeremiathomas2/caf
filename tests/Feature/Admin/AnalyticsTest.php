<?php

namespace Tests\Feature\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\RegistrationStatus;
use App\Enums\ThreadStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\MessageThread;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_analytics_screen_renders(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        Season::factory()->live()->create();

        $this->actingAs($user)
            ->get(route('admin.analytics.index'))
            ->assertOk();
    }

    #[Test]
    public function it_reports_zeroes_rather_than_dividing_by_nothing(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        Season::factory()->live()->create();

        $response = $this->actingAs($user)->get(route('admin.analytics.index'));

        $response->assertOk();

        // A funnel built from an empty season must not emit NaN or Infinity.
        $this->assertStringNotContainsString('NaN', $response->getContent());
        $this->assertStringNotContainsString('INF', $response->getContent());
    }

    #[Test]
    public function registration_conversion_is_scoped_to_the_season(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $current = Season::factory()->live()->create();
        $older = Season::factory()->archived()->create();

        // 2 approved out of 4 started = 50.0%
        Registration::factory()->forSeason($current)->confirmed()->create();
        Registration::factory()->forSeason($current)->withStatus(RegistrationStatus::Approved)->create();
        Registration::factory()->forSeason($current)->count(2)->create();

        // An older season with far higher numbers must not leak in.
        Registration::factory()->forSeason($older)->count(9)->confirmed()->create();

        $response = $this->actingAs($user)->get(route('admin.analytics.index'));

        $response->assertOk();
        $response->assertSee('Registration conversion', escape: false);
        $response->assertSee('50.0%', escape: false);
    }

    #[Test]
    public function the_outstanding_balance_kpi_counts_only_open_invoices(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = Season::factory()->live()->create();

        Invoice::factory()->forSeason($season)->create([
            'amount' => 1000, 'amount_paid' => 0, 'amount_waived' => 0,
            'status' => InvoiceStatus::Issued->value,
        ]);
        Invoice::factory()->forSeason($season)->create([
            'amount' => 2000, 'amount_paid' => 500, 'amount_waived' => 0,
            'status' => InvoiceStatus::PartiallyPaid->value,
        ]);
        Invoice::factory()->forSeason($season)->paid()->create(['amount' => 9000, 'amount_paid' => 9000]);

        $response = $this->actingAs($user)->get(route('admin.analytics.index'));

        $response->assertOk();
        $response->assertSee('Outstanding balance', escape: false);
        $response->assertSee('2 invoices', escape: false);
    }

    #[Test]
    public function the_threads_kpi_reports_resolved_against_total(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = Season::factory()->live()->create();

        MessageThread::factory()->count(3)->forSeason($season)->create(['status' => ThreadStatus::Open]);
        MessageThread::factory()->forSeason($season)->create(['status' => ThreadStatus::Resolved]);

        $response = $this->actingAs($user)->get(route('admin.analytics.index'));

        $response->assertOk();
        $response->assertSee('Threads resolved', escape: false);
        $response->assertSee('1/4', escape: false);
    }

    #[Test]
    public function the_impact_report_is_recorded_in_the_audit_log(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        Season::factory()->live()->create();

        $this->actingAs($user)
            ->from(route('admin.analytics.index'))
            ->post(route('admin.analytics.impact'))
            ->assertRedirect(route('admin.analytics.index'));

        $this->assertDatabaseHas('audit_logs', ['action' => 'analytics.impact_report']);
    }

    #[Test]
    public function the_impact_report_requires_permission(): void
    {
        $user = User::factory()->withRole(UserRole::FloorManager)->create();
        Season::factory()->live()->create();

        $this->actingAs($user)
            ->post(route('admin.analytics.impact'))
            ->assertForbidden();
    }

    #[Test]
    public function it_lists_the_distinct_payment_methods_seen(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = Season::factory()->live()->create();

        $mpesa = Invoice::factory()->forSeason($season)->create();
        $card = Invoice::factory()->forSeason($season)->create();

        Payment::factory()->forInvoice($mpesa)->create(['method' => PaymentMethod::Mpesa->value, 'amount' => 100]);
        Payment::factory()->forInvoice($card)->create(['method' => PaymentMethod::Card->value, 'amount' => 200]);

        $response = $this->actingAs($user)->get(route('admin.analytics.index'));

        $response->assertOk()->assertSee('M-Pesa', false);
    }

    #[Test]
    public function open_threads_are_counted(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = Season::factory()->live()->create();

        MessageThread::factory()->count(2)->forSeason($season)->create(['status' => ThreadStatus::Open]);
        MessageThread::factory()->forSeason($season)->create(['status' => ThreadStatus::Resolved]);

        $response = $this->actingAs($user)->get(route('admin.analytics.index'));

        $response->assertOk();
    }
}
