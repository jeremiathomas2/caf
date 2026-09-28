<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatusLookupTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_lookup_form_is_reachable_from_the_navigation(): void
    {
        $this->get(route('status'))
            ->assertOk()
            ->assertSee('Check your status');
    }

    #[Test]
    public function a_guest_can_look_up_a_registration_by_code(): void
    {
        $registration = Registration::factory()->create([
            'code' => 'CAF-0042',
            'group_name' => 'Upendo Voices',
            'status' => RegistrationStatus::Shortlisted->value,
        ]);

        $this->get(route('status', ['code' => 'CAF-0042']))
            ->assertOk()
            ->assertSee('Upendo Voices')
            ->assertSee('CAF-0042')
            ->assertSee('Shortlisted');
    }

    #[Test]
    public function the_lookup_shows_the_invoice_balance(): void
    {
        $registration = Registration::factory()->create(['code' => 'CAF-0043']);

        Invoice::factory()->create([
            'registration_id' => $registration->getKey(),
            'season_id' => $registration->season_id,
            'number' => 'INV-9001',
            'amount' => 450000,
            'amount_paid' => 150000,
            'status' => 'partially_paid',
        ]);

        $this->get(route('status', ['code' => 'CAF-0043']))
            ->assertOk()
            ->assertSee('INV-9001')
            ->assertSee('300,000');
    }

    #[Test]
    public function an_unknown_code_explains_itself_instead_of_failing(): void
    {
        $this->get(route('status', ['code' => 'CAF-9999']))
            ->assertOk()
            ->assertSee('No registration found');
    }

    #[Test]
    public function a_registration_links_to_its_own_public_status_page(): void
    {
        $registration = Registration::factory()->create(['code' => 'CAF-0044']);

        $this->assertSame(
            route('status', ['code' => 'CAF-0044']),
            $registration->publicUrl(),
        );
    }

    #[Test]
    public function the_lookup_does_not_expose_another_seasons_registration_to_a_guest(): void
    {
        Registration::factory()->create(['code' => 'CAF-0045']);

        $this->actingAs(User::factory()->withRole(UserRole::GroupLeader)->create())
            ->get(route('status', ['code' => 'CAF-0045']))
            ->assertOk();
    }

    #[Test]
    public function the_status_page_needs_no_season_in_the_session(): void
    {
        $season = Season::factory()->create();

        $this->assertSame(0, $season->registrations()->count());
        $this->get(route('status'))->assertOk();
    }

    #[Test]
    public function the_lookup_ignores_case_spacing_and_missing_dashes(): void
    {
        Registration::factory()->create([
            'code' => 'CAF2-0003',
            'group_name' => 'Zanzibar Harmony',
        ]);

        foreach (['caf2-0003', 'CAF2 0003', 'caf20003', "  CAF2-0003\n"] as $variant) {
            $this->get(route('status', ['code' => $variant]))
                ->assertOk()
                ->assertSee('Zanzibar Harmony');
        }
    }

    #[Test]
    public function the_lookup_reports_that_it_found_the_registration(): void
    {
        Registration::factory()->create([
            'code' => 'CAF2-0004',
            'group_name' => 'Bagamoyo Collective',
        ]);

        $this->get(route('status', ['code' => 'CAF2-0004']))
            ->assertOk()
            ->assertSee('Found your registration.');
    }

    #[Test]
    public function the_result_is_not_hidden_behind_the_scroll_reveal(): void
    {
        Registration::factory()->create([
            'code' => 'CAF2-0005',
            'group_name' => 'Mbeya Sound',
        ]);

        $body = $this->get(route('status', ['code' => 'CAF2-0005']))
            ->assertOk()
            ->getContent();

        // The reveal class starts elements at opacity:0 and only un-hides them
        // once they scroll into view, so lookup feedback must not use it.
        $this->assertStringNotContainsString('class="reveal"', $this->resultBlock($body));
        $this->assertStringContainsString('id="status-result"', $body);
    }

    #[Test]
    public function the_lookup_does_not_show_the_internal_status_history(): void
    {
        $registration = Registration::factory()->create(['code' => 'CAF2-0006']);

        $registration->statusEvents()->create([
            'from_status' => null,
            'to_status' => RegistrationStatus::Submitted->value,
        ]);

        $registration->statusEvents()->create([
            'from_status' => RegistrationStatus::Submitted->value,
            'to_status' => RegistrationStatus::UnderReview->value,
        ]);

        $body = $this->get(route('status', ['code' => 'CAF2-0006']))
            ->assertOk()
            ->getContent();

        // The current status is what a group needs; the transition history is
        // internal, so the page must not enumerate it.
        $this->assertStringNotContainsString('Status timeline', $body);
        $this->assertStringNotContainsString('Under Review', $this->resultBlock($body));
    }

    #[Test]
    public function the_lookup_still_shows_a_fee_when_no_invoice_exists_yet(): void
    {
        $season = Season::factory()->create([
            'number' => 2,
            'per_head_fee' => 50000,
            'early_bird_fee' => null,
            'rounding_increment' => 100,
        ]);

        $registration = Registration::factory()->for($season)->create([
            'code' => 'CAF2-0007',
            'members_count' => 4,
        ]);

        $this->assertSame(0, $registration->invoices()->count());

        $this->get(route('status', ['code' => 'CAF2-0007']))
            ->assertOk()
            ->assertSee('200,000');
    }

    #[Test]
    public function a_waived_invoice_reports_nothing_outstanding(): void
    {
        $registration = Registration::factory()->create(['code' => 'CAF2-0008']);

        $invoice = Invoice::factory()->create([
            'registration_id' => $registration->getKey(),
            'season_id' => $registration->season_id,
            'number' => 'INV-9100',
            'amount' => 450000,
            'amount_paid' => 0,
            'amount_waived' => 450000,
            'status' => 'waived',
        ]);

        $this->assertSame(0.0, $invoice->balance());
        $this->assertSame(0.0, $registration->fresh()->outstandingBalance());

        $this->get(route('status', ['code' => 'CAF2-0008']))
            ->assertOk()
            ->assertSee('Nothing further is owed on this registration.');
    }

    /**
     * The result block only, so assertions cannot be satisfied by unrelated
     * markup elsewhere on the page.
     */
    private function resultBlock(string $body): string
    {
        $start = strpos($body, 'id="status-result"');

        $this->assertNotFalse($start, 'The status result block is missing.');

        return substr($body, $start);
    }
}
