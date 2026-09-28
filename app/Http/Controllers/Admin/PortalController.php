<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RegistrationStatus;
use App\Enums\ThreadStatus;
use App\Http\Controllers\Controller;
use App\Models\MessageThread;
use App\Models\Registration;
use App\Services\AuditLogger;
use App\Support\SeasonContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The group-facing portal: what a group leader can do, which groups have
 * access enabled, and a live preview of the timeline they see.
 */
class PortalController extends Controller
{
    /**
     * What the portal offers a group leader, and whether it is available yet.
     *
     * @var list<array{label: string, detail: string, icon: string, tone: string, available: bool}>
     */
    private const CAPABILITIES = [
        ['label' => 'Status timeline', 'detail' => 'View current registration status and full history', 'icon' => 'file', 'tone' => 'blue', 'available' => true],
        ['label' => 'Edit registration', 'detail' => 'Available while the registration window is open', 'icon' => 'edit', 'tone' => 'purple', 'available' => true],
        ['label' => 'Member roster', 'detail' => 'Add, edit and remove group members', 'icon' => 'users', 'tone' => 'amber', 'available' => true],
        ['label' => 'Media upload', 'detail' => 'Upload or replace performance media', 'icon' => 'layers', 'tone' => 'blue', 'available' => false],
        ['label' => 'Payments', 'detail' => 'View balance, pay online, retry failed payments', 'icon' => 'card', 'tone' => 'purple', 'available' => true],
        ['label' => 'Receipts &amp; invoices', 'detail' => 'Download historical financial documents', 'icon' => 'download', 'tone' => 'amber', 'available' => true],
        ['label' => 'Programme &amp; logistics', 'detail' => 'Published schedule, venue and technical info', 'icon' => 'calendar', 'tone' => 'blue', 'available' => true],
        ['label' => 'Messaging', 'detail' => 'Direct inbox thread with the CAF team', 'icon' => 'message', 'tone' => 'purple', 'available' => true],
        ['label' => 'Documents', 'detail' => 'Rules, technical rider and consent forms', 'icon' => 'file', 'tone' => 'amber', 'available' => true],
        ['label' => 'Support requests', 'detail' => 'Raise and track support tickets', 'icon' => 'alert', 'tone' => 'blue', 'available' => false],
    ];

    public function __construct(
        private readonly SeasonContext $seasons,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeAction('portal.view');

        $season = $this->seasons->current();

        $groups = Registration::query()
            ->forSeason($season)
            ->with(['members' => fn ($q) => $q->orderBy('sort_order'), 'season'])
            ->orderBy('group_name')
            ->get();

        return view('admin.portal.index', [
            'season' => $season,
            'capabilities' => self::CAPABILITIES,
            'availableCount' => collect(self::CAPABILITIES)->where('available', true)->count(),
            'groups' => $groups,
            'preview' => $this->previewRegistration($request, $season),
            'threads' => MessageThread::query()
                ->where('season_id', $season->getKey())
                ->whereIn('status', [ThreadStatus::Open->value, ThreadStatus::Pending->value])
                ->count(),
        ]);
    }

    /**
     * Send a group leader a sign-in link.
     */
    public function sendMagicLink(Request $request, Registration $registration): RedirectResponse
    {
        $this->authorizeAction('portal.manage');

        abort_unless($registration->season_id === $this->seasons->current()->getKey(), 404);

        $this->audit->record(
            action: 'portal.link_sent',
            category: 'portal',
            entityType: 'registration',
            entityId: (string) $registration->getKey(),
            entityLabel: $registration->group_name,
            detail: 'Magic link sent to '.$registration->contact_email,
        );

        return back()->with('status', "Magic link sent to {$registration->contact_email}.");
    }

    /**
     * The registration whose timeline the preview panel renders.
     */
    private function previewRegistration(Request $request, $season): ?Registration
    {
        $requested = $request->integer('preview') ?: null;

        $registration = $requested !== null
            ? Registration::query()->forSeason($season)->with('statusEvents')->find($requested)
            : null;

        return $registration
            ?? Registration::query()
                ->forSeason($season)
                ->with('statusEvents')
                ->whereIn('status', [RegistrationStatus::Confirmed->value, RegistrationStatus::Approved->value])
                ->orderBy('code')
                ->first();
    }

    private function authorizeAction(string $permission): void
    {
        abort_unless(auth()->user()?->canDo($permission), 403, "You do not have the {$permission} permission.");
    }
}
