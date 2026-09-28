<?php

namespace Tests\Feature\Admin;

use App\Enums\Channel;
use App\Enums\ThreadStatus;
use App\Enums\UserRole;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Registration;
use App\Models\Season;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommunicationManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_inbox_lists_only_threads_from_the_active_season(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $current = Season::factory()->live()->create();
        $other = Season::factory()->archived()->create();

        MessageThread::factory()->forSeason($current)->create(['subject' => 'Invoice question']);
        MessageThread::factory()->forSeason($other)->create(['subject' => 'Old season enquiry']);

        $this->actingAs($user)
            ->get(route('admin.communications.index'))
            ->assertOk()
            ->assertSee('Invoice question')
            ->assertDontSee('Old season enquiry');
    }

    #[Test]
    public function the_inbox_can_be_filtered_by_status_and_searched(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = Season::factory()->live()->create();

        MessageThread::factory()->forSeason($season)->create([
            'subject' => 'Bursary application',
            'status' => ThreadStatus::Resolved->value,
        ]);
        MessageThread::factory()->forSeason($season)->create([
            'subject' => 'Transport arrangements',
            'status' => ThreadStatus::Open->value,
        ]);

        $this->actingAs($user)
            ->get(route('admin.communications.index', ['status' => 'resolved']))
            ->assertOk()
            ->assertSee('Bursary application')
            ->assertDontSee('Transport arrangements');

        $this->actingAs($user)
            ->get(route('admin.communications.index', ['q' => 'Transport']))
            ->assertOk()
            ->assertSee('Transport arrangements')
            ->assertDontSee('Bursary application');
    }

    #[Test]
    public function a_thread_from_another_season_is_not_reachable(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        Season::factory()->live()->create();
        $thread = MessageThread::factory()->forSeason(Season::factory()->archived()->create())->create();

        $this->actingAs($user)
            ->get(route('admin.communications.show', $thread))
            ->assertNotFound();
    }

    #[Test]
    public function the_thread_page_shows_the_conversation_with_its_group(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = Season::factory()->live()->create();
        $registration = Registration::factory()->forSeason($season)->create(['group_name' => 'Ibera Voices']);
        $thread = MessageThread::factory()->forRegistration($registration)->withChannel(Channel::Email)->create();

        Message::factory()->onThread($thread)->create([
            'body' => 'Could we confirm the stage time?',
            'sender_type' => 'contact',
            'sender_label' => $registration->contact_name,
            'sent_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.communications.show', $thread))
            ->assertOk()
            ->assertSee($thread->subject)
            ->assertSee('Could we confirm the stage time?')
            ->assertSee('Ibera Voices')
            ->assertSee(route('admin.registrations.show', $registration), escape: false);
    }

    #[Test]
    public function replying_marks_the_thread_pending_and_clears_its_unread_count(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = Season::factory()->live()->create();
        $thread = MessageThread::factory()->forSeason($season)->create([
            'status' => ThreadStatus::Open->value,
            'unread_count' => 3,
        ]);

        $this->actingAs($user)
            ->from(route('admin.communications.show', $thread))
            ->post(route('admin.communications.reply', $thread), ['body' => 'Noted, thank you.'])
            ->assertRedirect(route('admin.communications.show', $thread))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('messages', [
            'message_thread_id' => $thread->getKey(),
            'body' => 'Noted, thank you.',
            'sender_id' => $user->getKey(),
        ]);

        $thread->refresh();

        $this->assertSame(ThreadStatus::Pending, $thread->status);
        $this->assertSame(0, $thread->unread_count);
        $this->assertNotNull($thread->first_response_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'message.replied',
            'entity_id' => (string) $thread->getKey(),
        ]);
    }

    #[Test]
    public function a_reply_must_not_be_empty(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = Season::factory()->live()->create();
        $thread = MessageThread::factory()->forSeason($season)->create();

        $this->actingAs($user)
            ->from(route('admin.communications.show', $thread))
            ->post(route('admin.communications.reply', $thread), ['body' => ''])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('messages', 0);
    }

    #[Test]
    public function an_owner_and_a_status_can_be_changed(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $owner = User::factory()->withRole(UserRole::FinanceOfficer)->create();
        $season = Season::factory()->live()->create();
        $thread = MessageThread::factory()->forSeason($season)->create();

        $this->actingAs($user)
            ->from(route('admin.communications.show', $thread))
            ->post(route('admin.communications.update', $thread), [
                'status' => ThreadStatus::Resolved->value,
                'assigned_to_id' => $owner->getKey(),
            ])
            ->assertRedirect(route('admin.communications.show', $thread));

        $thread->refresh();

        $this->assertSame(ThreadStatus::Resolved, $thread->status);
        $this->assertSame($owner->getKey(), $thread->assigned_to_id);
    }

    #[Test]
    public function a_conversation_cannot_be_assigned_to_someone_who_is_not_staff(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $groupLeader = User::factory()->withRole(UserRole::GroupLeader)->create();
        $season = Season::factory()->live()->create();
        $thread = MessageThread::factory()->forSeason($season)->create();

        $this->actingAs($user)
            ->from(route('admin.communications.show', $thread))
            ->post(route('admin.communications.update', $thread), [
                'assigned_to_id' => $groupLeader->getKey(),
            ])
            ->assertSessionHasErrors('assigned_to_id');

        $this->assertNull($thread->refresh()->assigned_to_id);
    }

    #[Test]
    public function a_user_without_the_communications_permission_is_refused(): void
    {
        $judge = User::factory()->withRole(UserRole::Judge)->create();
        $season = Season::factory()->live()->create();
        $thread = MessageThread::factory()->forSeason($season)->create();

        $this->actingAs($judge)
            ->get(route('admin.communications.index'))
            ->assertForbidden();

        $this->actingAs($judge)
            ->get(route('admin.communications.show', $thread))
            ->assertForbidden();

        $this->actingAs($judge)
            ->from(route('admin.communications.show', $thread))
            ->post(route('admin.communications.reply', $thread), ['body' => 'Hello'])
            ->assertForbidden();

        $this->assertDatabaseCount('messages', 0);
    }

    #[Test]
    public function the_audit_trail_shows_who_changed_a_thread(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        Season::factory()->live()->create();
        $thread = MessageThread::factory()->forSeason(Season::query()->first())->create();

        app(AuditLogger::class)->record(
            action: 'thread.updated',
            category: 'communication',
            entityType: 'thread',
            entityId: (string) $thread->getKey(),
            entityLabel: $thread->subject,
            actor: $user,
        );

        $this->actingAs($user)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee($thread->subject)
            ->assertSee($user->name);
    }
}
