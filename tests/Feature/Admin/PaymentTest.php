<?php

namespace Tests\Feature\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_payments_screen_lists_the_season_invoices(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $season = Season::factory()->live()->create();
        $registration = Registration::factory()->forSeason($season)->create();

        Invoice::factory()->forSeason($season)->forRegistration($registration)->create(['number' => 'INV-9001']);

        $this->actingAs($user)
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('INV-9001');
    }

    #[Test]
    public function the_screen_is_scoped_to_the_season_in_the_query_string(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $current = Season::factory()->live()->create();
        $older = Season::factory()->archived()->create();

        Invoice::factory()->forSeason($current)->create(['number' => 'INV-CURRENT']);
        Invoice::factory()->forSeason($older)->create(['number' => 'INV-OLDER']);

        $this->actingAs($user)
            ->get(route('admin.payments.index', ['season' => $older->getKey()]))
            ->assertOk()
            ->assertSee('INV-OLDER')
            ->assertDontSee('INV-CURRENT');
    }

    #[Test]
    public function the_status_filter_narrows_the_list(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $season = Season::factory()->live()->create();

        Invoice::factory()->forSeason($season)->overdue()->create(['number' => 'INV-LATE']);
        Invoice::factory()->forSeason($season)->paid()->create(['number' => 'INV-SETTLED']);

        $this->actingAs($user)
            ->get(route('admin.payments.index', ['status' => InvoiceStatus::Overdue->value]))
            ->assertOk()
            ->assertSee('INV-LATE')
            ->assertDontSee('INV-SETTLED');
    }

    #[Test]
    public function a_user_without_payment_permission_is_forbidden(): void
    {
        $user = User::factory()->withRole(UserRole::FloorManager)->create();
        Season::factory()->live()->create();

        $this->actingAs($user)
            ->get(route('admin.payments.index'))
            ->assertForbidden();
    }

    #[Test]
    public function recording_a_full_payment_marks_the_invoice_and_registration_paid(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $season = Season::factory()->live()->create();
        $registration = Registration::factory()->forSeason($season)->create();
        $invoice = Invoice::factory()->forSeason($season)->forRegistration($registration)->create([
            'amount' => 5000,
            'amount_paid' => 0,
        ]);

        $this->actingAs($user)
            ->from(route('admin.payments.index'))
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->getKey(),
                'amount' => 5000,
                'method' => PaymentMethod::Mpesa->value,
                'reference' => 'MPESA-XYZ-001',
            ])
            ->assertRedirect(route('admin.payments.index'));

        $invoice->refresh();

        $this->assertSame(InvoiceStatus::Paid, $invoice->status);
        $this->assertEquals(5000, (float) $invoice->amount_paid);
        $this->assertSame(PaymentStatus::Paid, $registration->refresh()->payment_status);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->getKey(),
            'reference' => 'MPESA-XYZ-001',
            'status' => 'success',
        ]);
    }

    #[Test]
    public function recording_a_partial_payment_leaves_a_balance(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $season = Season::factory()->live()->create();
        $registration = Registration::factory()->forSeason($season)->create();
        $invoice = Invoice::factory()->forSeason($season)->forRegistration($registration)->create([
            'amount' => 5000,
            'amount_paid' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->getKey(),
                'amount' => 2000,
                'method' => PaymentMethod::Mpesa->value,
                'reference' => 'MPESA-XYZ-002',
            ]);

        $invoice->refresh();

        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->status);
        $this->assertSame(PaymentStatus::PartiallyPaid, $registration->refresh()->payment_status);
        $this->assertEquals(3000.0, $invoice->balance());
    }

    #[Test]
    public function a_payment_cannot_be_recorded_against_another_seasons_invoice(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        Season::factory()->live()->create();
        $other = Season::factory()->archived()->create();
        $invoice = Invoice::factory()->forSeason($other)->create();

        $this->actingAs($user)
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->getKey(),
                'amount' => 100,
                'method' => PaymentMethod::Mpesa->value,
                'reference' => 'MPESA-XYZ-003',
            ])
            ->assertSessionHasErrors('invoice_id');

        $this->assertDatabaseCount('payments', 0);
    }

    #[Test]
    public function recording_a_payment_requires_permission(): void
    {
        $user = User::factory()->withRole(UserRole::FloorManager)->create();
        $season = Season::factory()->live()->create();
        $invoice = Invoice::factory()->forSeason($season)->create();

        $this->actingAs($user)
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->getKey(),
                'amount' => 100,
                'method' => PaymentMethod::Mpesa->value,
                'reference' => 'MPESA-XYZ-004',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function a_negative_payment_is_rejected(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $season = Season::factory()->live()->create();
        $invoice = Invoice::factory()->forSeason($season)->create();

        $this->actingAs($user)
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->getKey(),
                'amount' => -50,
                'method' => PaymentMethod::Mpesa->value,
                'reference' => 'MPESA-XYZ-005',
            ])
            ->assertSessionHasErrors('amount');
    }

    #[Test]
    public function an_unknown_payment_method_is_rejected(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $season = Season::factory()->live()->create();
        $invoice = Invoice::factory()->forSeason($season)->create();

        $this->actingAs($user)
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->getKey(),
                'amount' => 100,
                'method' => 'carrier_pigeon',
                'reference' => 'MPESA-XYZ-006',
            ])
            ->assertSessionHasErrors('method');
    }

    #[Test]
    public function the_payment_is_recorded_in_the_audit_log(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $season = Season::factory()->live()->create();
        $invoice = Invoice::factory()->forSeason($season)->create(['number' => 'INV-9002']);

        $this->actingAs($user)
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->getKey(),
                'amount' => 100,
                'method' => PaymentMethod::Mpesa->value,
                'reference' => 'MPESA-XYZ-007',
            ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.recorded']);
    }

    #[Test]
    public function the_invoice_export_streams_a_csv_of_the_season(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $season = Season::factory()->live()->create();
        $registration = Registration::factory()->forSeason($season)->create(['group_name' => 'River Band']);

        Invoice::factory()->forSeason($season)->forRegistration($registration)->create(['number' => 'INV-EXPORT-1']);

        $response = $this->actingAs($user)->get(route('admin.payments.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Invoice,Group,Code', $csv);
        $this->assertStringContainsString('INV-EXPORT-1', $csv);
        $this->assertStringContainsString('River Band', $csv);
    }

    #[Test]
    public function the_export_honours_the_status_filter(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $season = Season::factory()->live()->create();

        Invoice::factory()->forSeason($season)->overdue()->create(['number' => 'INV-LATE-EXPORT']);
        Invoice::factory()->forSeason($season)->paid()->create(['number' => 'INV-PAID-EXPORT']);

        $csv = $this->actingAs($user)
            ->get(route('admin.payments.export', ['status' => InvoiceStatus::Overdue->value]))
            ->streamedContent();

        $this->assertStringContainsString('INV-LATE-EXPORT', $csv);
        $this->assertStringNotContainsString('INV-PAID-EXPORT', $csv);
    }

    #[Test]
    public function the_export_requires_permission(): void
    {
        $user = User::factory()->withRole(UserRole::FloorManager)->create();
        Season::factory()->live()->create();

        $this->actingAs($user)
            ->get(route('admin.payments.export'))
            ->assertForbidden();
    }

    #[Test]
    public function failed_payments_do_not_count_towards_the_paid_total(): void
    {
        $invoice = Invoice::factory()->create(['amount' => 1000, 'amount_paid' => 0]);

        Payment::factory()->forInvoice($invoice)->failed()->create(['amount' => 1000]);

        $invoice->recalculate();

        $this->assertSame(0.0, (float) $invoice->refresh()->amount_paid);
        $this->assertNotSame(InvoiceStatus::Paid, $invoice->status);
    }
}
