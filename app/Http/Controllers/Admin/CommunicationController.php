<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ThreadStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Season;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\SeasonContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommunicationController extends Controller
{
    public function __construct(
        private readonly SeasonContext $seasons,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeAction('communications.view');

        $season = $this->seasons->current();
        $status = ThreadStatus::tryFrom((string) $request->query('status', ''));
        $search = trim((string) $request->query('q', ''));

        $threads = MessageThread::query()
            ->where('season_id', $season->getKey())
            ->with(['registration', 'assignedTo', 'messages'])
            ->when($status instanceof ThreadStatus, fn ($q) => $q->withStatus($status))
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($inner) use ($search): void {
                    $inner->where('subject', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('contact_email', 'like', "%{$search}%");
                });
            })
            ->latest('last_message_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.communications.index', [
            'season' => $season,
            'threads' => $threads,
            'status' => $status,
            'search' => $search,
            'statuses' => ThreadStatus::cases(),
            'needsReply' => MessageThread::query()->where('season_id', $season->getKey())->needsReply()->count(),
        ]);
    }

    public function show(MessageThread $thread): View
    {
        $this->authorizeAction('communications.view');

        $thread = $this->inCurrentSeason($thread);
        $thread->load(['messages.sender', 'registration', 'assignedTo', 'season']);

        $staff = User::query()
            ->whereIn('role', UserRole::adminRoles())
            ->orderBy('name')
            ->get();

        return view('admin.communications.show', [
            'thread' => $thread,
            'staff' => $staff,
            'recipients' => $thread->registration?->contact_email
                ? $thread->registration->contact_email
                : $thread->contact_email,
        ]);
    }

    /**
     * Post a reply into the thread.
     */
    public function reply(Request $request, MessageThread $thread): RedirectResponse
    {
        $this->authorizeAction('communications.manage');

        $thread = $this->inCurrentSeason($thread);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($thread, $validated, $user): void {
            Message::create([
                'message_thread_id' => $thread->getKey(),
                'sender_id' => $user?->getKey(),
                'sender_type' => 'staff',
                'sender_label' => $user?->name ?? 'CAF staff',
                'body' => $validated['body'],
                'channel' => $thread->channel,
                'sent_at' => now(),
            ]);

            $thread->update([
                'status' => ThreadStatus::Pending->value,
                'first_response_at' => $thread->first_response_at ?? now(),
                'last_message_at' => now(),
                'unread_count' => 0,
            ]);
        });

        $this->audit->record(
            action: 'message.replied',
            category: 'communication',
            entityType: 'thread',
            entityId: (string) $thread->getKey(),
            entityLabel: $thread->subject,
        );

        return back()->with('status', 'Reply sent.');
    }

    /**
     * Change who owns the conversation, or close it.
     */
    public function update(Request $request, MessageThread $thread): RedirectResponse
    {
        $this->authorizeAction('communications.manage');

        $thread = $this->inCurrentSeason($thread);

        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(ThreadStatus::class)],
            'assigned_to_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereIn('role', UserRole::adminRoles()),
            ],
        ]);

        $thread->update([
            'status' => $validated['status'] ?? $thread->status,
            'assigned_to_id' => $validated['assigned_to_id'] ?? $thread->assigned_to_id,
        ]);

        $this->audit->record(
            action: 'thread.updated',
            category: 'communication',
            entityType: 'thread',
            entityId: (string) $thread->getKey(),
            entityLabel: $thread->subject,
        );

        return back()->with('status', 'Conversation updated.');
    }

    /**
     * Conversations are managed per season, so a thread from another season is
     * treated as missing rather than forbidden.
     */
    private function inCurrentSeason(MessageThread $thread): MessageThread
    {
        abort_unless($thread->season_id === $this->seasons->current()->getKey(), 404);

        return $thread;
    }

    private function authorizeAction(string $permission): void
    {
        abort_unless(auth()->user()?->canDo($permission), 403, "You do not have the {$permission} permission.");
    }
}
