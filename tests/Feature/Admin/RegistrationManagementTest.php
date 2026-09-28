<?php

namespace Tests\Feature\Admin;

use App\Enums\RegistrationRole;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_guest_is_sent_to_the_login_screen(): void
    {
        $this->get(route('admin.registrations.index'))->assertRedirect(route('admin.login'));
    }

    #[Test]
    public function a_non_admin_role_cannot_reach_the_management_system(): void
    {
        $this->actingAs(User::factory()->withRole(UserRole::GroupLeader)->create())
            ->get(route('admin.registrations.index'))
            ->assertForbidden();
    }

    #[Test]
    public function a_season_manager_lists_only_the_active_season(): void
    {
        $active = Season::factory()->live()->create();
        Registration::factory()->forSeason($active)->create(['group_name' => 'Upendo Voices']);
        Registration::factory()->create(['group_name' => 'Other Season Group']);

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->get(route('admin.registrations.index'))
            ->assertOk()
            ->assertSee('Upendo Voices')
            ->assertDontSee('Other Season Group');
    }

    #[Test]
    public function a_staff_member_without_registration_rights_cannot_open_the_create_form(): void
    {
        Season::factory()->live()->create();

        $this->actingAs(User::factory()->withRole(UserRole::ContentEditor)->create())
            ->get(route('admin.registrations.create'))
            ->assertForbidden();
    }

    #[Test]
    public function an_auditor_may_read_registrations_but_not_change_them(): void
    {
        $season = Season::factory()->live()->create();
        Registration::factory()->forSeason($season)->create(['group_name' => 'Upendo Voices']);
        $user = User::factory()->withRole(UserRole::Auditor)->create();

        $this->actingAs($user)->get(route('admin.registrations.index'))->assertOk()->assertSee('Upendo Voices');
        $this->actingAs($user)->get(route('admin.registrations.create'))->assertForbidden();
    }

    #[Test]
    public function the_create_form_shows_the_next_code_for_the_season(): void
    {
        Season::factory()->live()->create();

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->get(route('admin.registrations.create'))
            ->assertOk()
            ->assertSee('New registration');
    }

    #[Test]
    public function a_registration_is_created_with_its_members_invoice_and_audit_trail(): void
    {
        $season = Season::factory()->live()->create();
        $user = User::factory()->withRole(UserRole::SeasonManager)->create();

        $response = $this->actingAs($user)->post(route('admin.registrations.store'), $this->payload($season));

        $registration = Registration::sole();

        $response->assertRedirect(route('admin.registrations.show', $registration));
        $response->assertSessionHas('status');

        $this->assertSame('Upendo Voices', $registration->group_name);
        $this->assertSame('manual', $registration->source);
        $this->assertSame(3, $registration->members_count);
        $this->assertCount(3, $registration->members);
        $this->assertTrue($registration->members->firstWhere('sort_order', 0)->is_lead);

        $invoice = Invoice::sole();
        $this->assertSame($registration->getKey(), $invoice->registration_id);
        $this->assertSame($season->feeFor(3), (int) $invoice->amount);
        $this->assertSame(0, (int) $invoice->amount_paid);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'registration.created',
            'entity_id' => (string) $registration->getKey(),
            'actor_id' => $user->getKey(),
        ]);
    }

    #[Test]
    public function the_performer_count_must_match_the_member_list(): void
    {
        $season = Season::factory()->live()->create();

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->post(route('admin.registrations.store'), $this->payload($season, ['members_count' => 9]))
            ->assertSessionHasErrors('members_count');

        $this->assertSame(0, Registration::count());
    }

    #[Test]
    public function a_group_must_name_a_lead(): void
    {
        $season = Season::factory()->live()->create();

        $payload = $this->payload($season);
        unset($payload['members'][0]['is_lead']);

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->post(route('admin.registrations.store'), $payload)
            ->assertSessionHasErrors('members.0.is_lead');

        $this->assertSame(0, Registration::count());
    }

    #[Test]
    public function every_member_needs_a_name(): void
    {
        $season = Season::factory()->live()->create();

        $payload = $this->payload($season);
        $payload['members'][1]['name'] = '';

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->post(route('admin.registrations.store'), $payload)
            ->assertSessionHasErrors('members.1.name');
    }

    #[Test]
    public function a_submitted_registration_can_be_edited(): void
    {
        $registration = Registration::factory()
            ->withStatus(RegistrationStatus::Submitted)
            ->create(['group_name' => 'Old Name']);

        $payload = $this->payload($registration->season, ['group_name' => 'New Name']);

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->put(route('admin.registrations.update', $registration), $payload)
            ->assertRedirect(route('admin.registrations.show', $registration));

        $this->assertSame('New Name', $registration->refresh()->group_name);
        $this->assertCount(3, $registration->members);
    }

    #[Test]
    public function a_confirmed_registration_can_no_longer_be_edited(): void
    {
        $registration = Registration::factory()->confirmed()->create(['group_name' => 'Locked']);

        $payload = $this->payload($registration->season, ['group_name' => 'Changed']);

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->put(route('admin.registrations.update', $registration), $payload)
            ->assertRedirect(route('admin.registrations.show', $registration))
            ->assertSessionHas('error');

        $this->assertSame('Locked', $registration->refresh()->group_name);
    }

    #[Test]
    public function the_edit_screen_sends_a_confirmed_registration_back_to_its_page(): void
    {
        $registration = Registration::factory()->confirmed()->create();

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->get(route('admin.registrations.edit', $registration))
            ->assertRedirect(route('admin.registrations.show', $registration));
    }

    #[Test]
    public function a_status_transition_is_recorded_in_the_timeline(): void
    {
        $registration = Registration::factory()->withStatus(RegistrationStatus::Submitted)->create();

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->post(route('admin.registrations.status', $registration), [
                'status' => RegistrationStatus::UnderReview->value,
                'note' => 'Picked up for review.',
            ])
            ->assertSessionHasNoErrors();

        $registration->refresh();

        $this->assertSame(RegistrationStatus::UnderReview, $registration->status);
        $this->assertDatabaseHas('registration_status_events', [
            'registration_id' => $registration->getKey(),
            'to_status' => RegistrationStatus::UnderReview->value,
            'note' => 'Picked up for review.',
        ]);
    }

    #[Test]
    public function an_illegal_status_transition_is_refused(): void
    {
        $registration = Registration::factory()->withStatus(RegistrationStatus::Rejected)->create();

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->from(route('admin.registrations.show', $registration))
            ->post(route('admin.registrations.status', $registration), [
                'status' => RegistrationStatus::Confirmed->value,
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame(RegistrationStatus::Rejected, $registration->refresh()->status);
    }

    #[Test]
    public function tags_are_trimmed_deduplicated_and_capped(): void
    {
        $registration = Registration::factory()->create();

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->from(route('admin.registrations.show', $registration))
            ->post(route('admin.registrations.tag', $registration), [
                'tags' => ' gospel , choir, gospel , , choir ',
            ]);

        $this->assertSame(['gospel', 'choir'], $registration->refresh()->tags);
    }

    #[Test]
    public function a_reviewer_can_be_assigned_and_cleared(): void
    {
        $registration = Registration::factory()->create();
        $reviewer = User::factory()->withRole(UserRole::Judge)->create();
        $user = User::factory()->withRole(UserRole::SeasonManager)->create();

        $this->actingAs($user)
            ->from(route('admin.registrations.show', $registration))
            ->post(route('admin.registrations.assign', $registration), ['reviewer_id' => $reviewer->getKey()]);

        $this->assertSame($reviewer->getKey(), $registration->refresh()->reviewer_id);

        $this->actingAs($user)
            ->from(route('admin.registrations.show', $registration))
            ->post(route('admin.registrations.assign', $registration), ['reviewer_id' => null]);

        $this->assertNull($registration->refresh()->reviewer_id);
    }

    #[Test]
    public function deleting_a_registration_soft_deletes_it_and_leaves_an_audit_record(): void
    {
        $registration = Registration::factory()->create(['code' => 'CAF-7777']);
        $id = $registration->getKey();

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->delete(route('admin.registrations.destroy', $registration))
            ->assertRedirect(route('admin.registrations.index'));

        $this->assertSoftDeleted('registrations', ['id' => $id]);
        $this->assertNull(Registration::find($id));

        $audit = AuditLog::query()->where('action', 'registration.deleted')->sole();
        $this->assertSame('CAF-7777', $audit->detail);
    }

    #[Test]
    public function the_search_filter_narrows_the_list(): void
    {
        $season = Season::factory()->live()->create();
        Registration::factory()->forSeason($season)->create(['group_name' => 'Upendo Voices']);
        Registration::factory()->forSeason($season)->create(['group_name' => 'Harmony Saints']);

        $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->get(route('admin.registrations.index', ['q' => 'upendo']))
            ->assertOk()
            ->assertSee('Upendo Voices')
            ->assertDontSee('Harmony Saints');
    }

    #[Test]
    public function the_export_is_csv_and_respects_the_season(): void
    {
        $season = Season::factory()->live()->create();
        Registration::factory()->forSeason($season)->create(['group_name' => 'Upendo Voices']);
        Registration::factory()->create(['group_name' => 'Other Season Group']);

        $response = $this->actingAs(User::factory()->withRole(UserRole::SeasonManager)->create())
            ->get(route('admin.registrations.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('Upendo Voices', $response->streamedContent());
        $this->assertStringNotContainsString('Other Season Group', $response->streamedContent());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(Season $season, array $overrides = []): array
    {
        return array_merge([
            'season_id' => $season->getKey(),
            'group_name' => 'Upendo Voices',
            'role_type' => RegistrationRole::Singers->value,
            'category' => 'Gospel group',
            'country' => 'Tanzania',
            'city' => 'Arusha',
            'members_count' => 3,
            'contact_name' => 'Amina Njeri',
            'contact_email' => 'amina@example.com',
            'contact_phone' => '+255700000001',
            'performance_link' => 'https://youtube.com/watch?v=abcdefghijk',
            'bio' => 'A gospel group from Arusha.',
            'notes' => '',
            'is_public' => false,
            'members' => [
                ['name' => 'Amina Njeri', 'part' => 'Alto', 'is_lead' => '1'],
                ['name' => 'Joseph Mwangi', 'part' => 'Tenor', 'is_lead' => '0'],
                ['name' => 'Grace Kimaro', 'part' => 'Soprano', 'is_lead' => '0'],
            ],
        ], $overrides);
    }
}
