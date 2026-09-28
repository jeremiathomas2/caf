<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The organising team behind the festival: the public committee page entries
 * and the internal staff accounts, which are code-backed through UserRole.
 */
class TeamController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeAction('teams.view');

        return view('admin.teams.index', [
            'members' => TeamMember::query()->orderBy('sort_order')->get(),
            'roles' => UserRole::cases(),
            'publicCount' => TeamMember::query()->where('is_public', true)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAction('teams.manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role_title' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $member = TeamMember::create([
            'name' => $validated['name'],
            'role_title' => $validated['role_title'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'is_public' => (bool) ($validated['is_public'] ?? false),
            'sort_order' => (int) TeamMember::query()->max('sort_order') + 1,
        ]);

        $this->audit->record(
            action: 'team.member_created',
            category: 'team',
            entityType: 'member',
            entityId: (string) $member->getKey(),
            entityLabel: $member->name,
        );

        return back()->with('status', "{$member->name} added to the team.");
    }

    public function update(Request $request, TeamMember $teamMember): RedirectResponse
    {
        $this->authorizeAction('teams.manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role_title' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $teamMember->update([
            'name' => $validated['name'],
            'role_title' => $validated['role_title'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'is_public' => (bool) ($validated['is_public'] ?? false),
        ]);

        $this->audit->record(
            action: 'team.member_updated',
            category: 'team',
            entityType: 'member',
            entityId: (string) $teamMember->getKey(),
            entityLabel: $teamMember->name,
        );

        return back()->with('status', "{$teamMember->name} updated.");
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        $this->authorizeAction('teams.manage');

        $name = $teamMember->name;

        $teamMember->delete();

        $this->audit->record(
            action: 'team.member_removed',
            category: 'team',
            entityType: 'member',
            entityId: (string) $teamMember->getKey(),
            entityLabel: $name,
        );

        return back()->with('status', "{$name} removed from the team.");
    }

    private function authorizeAction(string $permission): void
    {
        abort_unless(auth()->user()?->canDo($permission), 403, "You do not have the {$permission} permission.");
    }
}
