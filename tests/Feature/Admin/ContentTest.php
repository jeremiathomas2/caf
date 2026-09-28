<?php

namespace Tests\Feature\Admin;

use App\Enums\ContentType;
use App\Enums\PublishStatus;
use App\Enums\UserRole;
use App\Models\ContentPage;
use App\Models\Judge;
use App\Models\ProgrammeSlot;
use App\Models\Registration;
use App\Models\Season;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_content_screen_lists_pages(): void
    {
        $user = User::factory()->withRole(UserRole::ContentEditor)->create();
        Season::factory()->live()->create();

        ContentPage::factory()->create(['title' => 'Volunteer Handbook']);

        $this->actingAs($user)
            ->get(route('admin.content.index'))
            ->assertOk()
            ->assertSee('Volunteer Handbook');
    }

    #[Test]
    public function a_user_without_content_permission_is_forbidden(): void
    {
        $user = User::factory()->withRole(UserRole::FloorManager)->create();
        Season::factory()->live()->create();

        $this->actingAs($user)
            ->get(route('admin.content.index'))
            ->assertForbidden();
    }

    #[Test]
    public function creating_a_page_generates_a_slug_when_none_is_given(): void
    {
        $user = User::factory()->withRole(UserRole::ContentEditor)->create();
        Season::factory()->live()->create();

        $this->actingAs($user)
            ->from(route('admin.content.index'))
            ->post(route('admin.content.store'), [
                'title' => 'Transport & Accommodation',
                'type' => ContentType::Page->value,
                'status' => PublishStatus::Draft->value,
                'excerpt' => 'How to get to the venue.',
                'body' => 'Buses run every hour.',
            ])
            ->assertRedirect(route('admin.content.index'));

        $page = ContentPage::query()->where('title', 'Transport & Accommodation')->sole();

        $this->assertSame('transport-accommodation', $page->slug);
        $this->assertSame(PublishStatus::Draft, $page->status);
    }

    #[Test]
    public function creating_a_page_keeps_an_explicit_slug(): void
    {
        $user = User::factory()->withRole(UserRole::ContentEditor)->create();
        Season::factory()->live()->create();

        $this->actingAs($user)
            ->post(route('admin.content.store'), [
                'title' => 'About the Festival',
                'slug' => 'custom-about-path',
                'type' => ContentType::Page->value,
                'status' => PublishStatus::Draft->value,
            ]);

        $this->assertDatabaseHas('content_pages', ['slug' => 'custom-about-path']);
    }

    #[Test]
    public function creating_a_page_requires_a_title(): void
    {
        $user = User::factory()->withRole(UserRole::ContentEditor)->create();
        Season::factory()->live()->create();

        $this->actingAs($user)
            ->post(route('admin.content.store'), [
                'type' => ContentType::Page->value,
                'status' => PublishStatus::Draft->value,
            ])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('content_pages', 0);
    }

    #[Test]
    public function creating_a_page_requires_permission(): void
    {
        $user = User::factory()->withRole(UserRole::FloorManager)->create();
        Season::factory()->live()->create();

        $this->actingAs($user)
            ->post(route('admin.content.store'), [
                'title' => 'Sneaky Page',
                'type' => ContentType::Page->value,
                'status' => PublishStatus::Draft->value,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('content_pages', 0);
    }

    #[Test]
    public function publishing_a_draft_page_marks_it_published(): void
    {
        $user = User::factory()->withRole(UserRole::ContentEditor)->create();
        Season::factory()->live()->create();
        $page = ContentPage::factory()->create(['status' => PublishStatus::Draft->value]);

        $this->actingAs($user)
            ->from(route('admin.content.index'))
            ->post(route('admin.content.publish', $page), [
                'status' => PublishStatus::Published->value,
            ])
            ->assertRedirect(route('admin.content.index'))
            ->assertSessionHasNoErrors();

        $page->refresh();

        $this->assertSame(PublishStatus::Published, $page->status);
        $this->assertNotNull($page->published_at);
    }

    #[Test]
    public function publishing_a_live_page_does_not_stamp_a_second_publish_time(): void
    {
        $user = User::factory()->withRole(UserRole::ContentEditor)->create();
        Season::factory()->live()->create();
        $page = ContentPage::factory()->published()->create();

        $original = $page->published_at;

        $this->actingAs($user)
            ->post(route('admin.content.publish', $page));

        $this->assertTrue($original->equalTo($page->refresh()->published_at));
    }

    #[Test]
    public function publishing_requires_permission(): void
    {
        $user = User::factory()->withRole(UserRole::FloorManager)->create();
        Season::factory()->live()->create();
        $page = ContentPage::factory()->create(['status' => PublishStatus::Draft->value]);

        $this->actingAs($user)
            ->post(route('admin.content.publish', $page))
            ->assertForbidden();

        $this->assertSame(PublishStatus::Draft, $page->refresh()->status);
    }

    #[Test]
    public function the_programme_is_scoped_to_the_season(): void
    {
        $user = User::factory()->withRole(UserRole::ContentEditor)->create();
        $season = Season::factory()->live()->create();
        $other = Season::factory()->archived()->create();

        ProgrammeSlot::factory()->forSeason($season)->create(['stage' => 'Main Stage']);
        ProgrammeSlot::factory()->forSeason($other)->create(['stage' => 'Old Hall']);

        $this->actingAs($user)
            ->get(route('admin.content.index'))
            ->assertOk()
            ->assertSee('Main Stage')
            ->assertDontSee('Old Hall');
    }

    #[Test]
    public function rescheduling_a_slot_moves_it_to_the_new_day(): void
    {
        $user = User::factory()->withRole(UserRole::ContentEditor)->create();
        $season = Season::factory()->live()->create();
        $slot = ProgrammeSlot::factory()->forSeason($season)->create([
            'event_date' => now()->addDays(3)->toDateString(),
            'starts_at' => '18:00',
        ]);

        $newDay = now()->addDays(5)->toDateString();

        $this->actingAs($user)
            ->from(route('admin.content.index'))
            ->put(route('admin.content.slot', $slot), [
                'event_date' => $newDay,
                'starts_at' => '20:30',
                'ends_at' => '21:15',
                'stage' => $slot->stage,
                'status' => $slot->status,
            ])
            ->assertRedirect(route('admin.content.index'));

        $slot->refresh();

        $this->assertSame($newDay, $slot->event_date->toDateString());
        $this->assertSame('20:30', $slot->starts_at);
        $this->assertSame('21:15', $slot->ends_at);
    }

    #[Test]
    public function rescheduling_a_slot_requires_a_stage_date_and_start_time(): void
    {
        $user = User::factory()->withRole(UserRole::ContentEditor)->create();
        $season = Season::factory()->live()->create();
        $slot = ProgrammeSlot::factory()->forSeason($season)->create();

        $original = $slot->only(['event_date', 'starts_at', 'stage', 'status']);

        $this->actingAs($user)
            ->put(route('admin.content.slot', $slot), [])
            ->assertSessionHasErrors(['stage', 'event_date', 'starts_at', 'status']);

        // assertEquals, not assertSame: event_date is cast to Carbon, so the
        // reloaded attribute is a distinct instance holding the same value.
        $this->assertEquals(
            $original,
            $slot->refresh()->only(['event_date', 'starts_at', 'stage', 'status']),
        );
    }

    #[Test]
    public function a_slot_from_another_season_cannot_be_rescheduled(): void
    {
        $user = User::factory()->withRole(UserRole::ContentEditor)->create();
        Season::factory()->live()->create();
        $other = Season::factory()->archived()->create();
        $slot = ProgrammeSlot::factory()->forSeason($other)->create();

        $this->actingAs($user)
            ->put(route('admin.content.slot', $slot), [
                'event_date' => now()->addDay()->toDateString(),
                'starts_at' => '20:30',
                'stage' => $slot->stage,
                'status' => $slot->status,
            ])
            ->assertNotFound();
    }

    #[Test]
    public function rescheduling_requires_permission(): void
    {
        $user = User::factory()->withRole(UserRole::FloorManager)->create();
        $season = Season::factory()->live()->create();
        $slot = ProgrammeSlot::factory()->forSeason($season)->create();

        $this->actingAs($user)
            ->put(route('admin.content.slot', $slot), [
                'event_date' => now()->addDay()->toDateString(),
                'starts_at' => '20:30',
                'stage' => $slot->stage,
                'status' => $slot->status,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function the_screen_uses_the_day_from_the_query_string(): void
    {
        $user = User::factory()->withRole(UserRole::ContentEditor)->create();
        $season = Season::factory()->live()->create();

        $day = now()->addDays(4);
        $registration = Registration::factory()->forSeason($season)->create(['group_name' => 'Day Four Act']);

        ProgrammeSlot::factory()->forSeason($season)->create([
            'event_date' => $day->toDateString(),
            'registration_id' => $registration->getKey(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.content.index', ['day' => $day->toDateString()]))
            ->assertOk()
            ->assertSee('Day Four Act');
    }

    #[Test]
    public function sponsors_and_judges_come_from_the_database(): void
    {
        $user = User::factory()->withRole(UserRole::ContentEditor)->create();
        $season = Season::factory()->live()->create();

        // This card lists Sponsor and Judge records, not the TeamMember
        // directory that feeds the public site footer.
        Judge::factory()->forSeason($season)->create(['name' => 'Amina Yusuf']);
        Sponsor::factory()->create(['name' => 'M-Pesa Tanzania']);

        $this->actingAs($user)
            ->get(route('admin.content.index'))
            ->assertOk()
            ->assertSee('Amina Yusuf')
            ->assertSee('M-Pesa Tanzania');
    }
}
