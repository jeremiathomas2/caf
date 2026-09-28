<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RegistrationRequest;
use App\Models\Registration;
use App\Models\Season;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\RegistrationService;
use App\Support\SeasonContext;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly SeasonContext $seasons,
        private readonly RegistrationService $registrations,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeAction('registrations.view');

        $season = $this->seasons->current();
        $status = $this->statusFilter($request);
        $payment = $this->paymentFilter($request);
        $search = trim((string) $request->query('q', ''));

        $query = Registration::query()
            ->forSeason($season)
            ->with('reviewer')
            ->when($status instanceof RegistrationStatus, fn ($q) => $q->withStatus($status))
            ->when($payment instanceof PaymentStatus, fn ($q) => $q->where('payment_status', $payment->value))
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($inner) use ($search): void {
                    $inner->where('group_name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('contact_email', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->latest('submitted_at')
            ->latest('id');

        // The staff table is short enough to filter in the database, but the
        // prototype's status chips map onto two different columns, so the
        // server owns the filtering and JavaScript only narrows the page.
        $registrations = $query->paginate(20)->withQueryString();

        return view('admin.registrations.index', [
            'season' => $season,
            'registrations' => $registrations,
            'status' => $status,
            'payment' => $payment,
            'search' => $search,
            'total' => Registration::query()->forSeason($season)->count(),
            'statuses' => RegistrationStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAction('registrations.manage');

        $season = $request->integer('season')
            ? Season::findOrFail($request->integer('season'))
            : $this->seasons->current();

        $this->seasons->select($season);

        return view('admin.registrations.create', [
            'season' => $season,
            'registration' => new Registration([
                'season_id' => $season->getKey(),
                'members_count' => 3,
                'is_public' => false,
            ]),
            'nextCode' => $this->registrations->nextCode($season),
        ]);
    }

    public function store(RegistrationRequest $request): RedirectResponse
    {
        $attributes = $request->registrationAttributes();
        $members = $request->memberRows();

        $registration = $this->registrations->create(
            Season::findOrFail($attributes['season_id']),
            [...$attributes, 'members' => $members],
            source: 'manual',
        );

        $this->audit->record(
            action: 'registration.created',
            category: 'registration',
            entityType: 'registration',
            entityId: (string) $registration->getKey(),
            entityLabel: $registration->group_name,
            detail: $registration->code,
        );

        return redirect()
            ->route('admin.registrations.show', $registration)
            ->with('status', "{$registration->code} created for {$registration->group_name}.");
    }

    public function show(Registration $registration): View
    {
        $this->authorizeAction('registrations.view');

        $this->seasons->select($registration->season);

        $registration->load([
            'season',
            'reviewer',
            'members',
            'statusEvents.actor',
            'invoices.payments',
            'reviewAssignments.judge',
        ]);

        return view('admin.registrations.show', [
            'registration' => $registration,
            'invoice' => $registration->latestInvoice(),
            'nextStatuses' => $this->registrations->nextStatuses($registration),
            'reviewers' => $this->reviewers(),
        ]);
    }

    public function edit(Registration $registration): View|RedirectResponse
    {
        $this->authorizeAction('registrations.manage');

        if (! $registration->isEditable()) {
            return redirect()
                ->route('admin.registrations.show', $registration)
                ->with('error', "A {$registration->status->label()} registration can no longer be edited.");
        }

        $registration->load('members');

        return view('admin.registrations.edit', [
            'season' => $registration->season,
            'registration' => $registration,
            'members' => $registration->members->all(),
        ]);
    }

    public function update(RegistrationRequest $request, Registration $registration): RedirectResponse
    {
        if (! $registration->isEditable()) {
            return redirect()
                ->route('admin.registrations.show', $registration)
                ->with('error', "A {$registration->status->label()} registration can no longer be edited.");
        }

        $attributes = $request->registrationAttributes();
        $members = $request->memberRows();

        // Season is fixed once a registration exists — the invoice and any
        // recorded payments belong to the original edition.
        unset($attributes['season_id']);

        DB::transaction(function () use ($registration, $attributes, $members): void {
            $registration->update($attributes);
            $registration->members()->delete();
            $registration->members()->createMany($members);
        });

        $this->audit->record(
            action: 'registration.updated',
            category: 'registration',
            entityType: 'registration',
            entityId: (string) $registration->getKey(),
            entityLabel: $registration->group_name,
        );

        return redirect()
            ->route('admin.registrations.show', $registration)
            ->with('status', "{$registration->code} updated.");
    }

    public function destroy(Registration $registration): RedirectResponse
    {
        $this->authorizeAction('registrations.manage');

        $code = $registration->code;
        $name = $registration->group_name;

        $registration->delete();

        $this->audit->record(
            action: 'registration.deleted',
            category: 'registration',
            entityType: 'registration',
            entityLabel: $name,
            detail: $code,
        );

        return redirect()
            ->route('admin.registrations.index')
            ->with('status', "{$code} ({$name}) removed.");
    }

    /**
     * Assign a reviewer to a registration.
     */
    public function assign(Registration $registration, Request $request): RedirectResponse
    {
        $this->authorizeAction('registrations.manage');

        $validated = $request->validate([
            'reviewer_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $registration->update(['reviewer_id' => $validated['reviewer_id'] ?? null]);

        $reviewer = $validated['reviewer_id'] ? User::find($validated['reviewer_id']) : null;

        $this->audit->record(
            action: 'registration.assigned',
            category: 'registration',
            entityType: 'registration',
            entityId: (string) $registration->getKey(),
            entityLabel: $registration->group_name,
            detail: $reviewer?->name ?? 'Unassigned',
        );

        return back()->with('status', $reviewer === null
            ? 'Reviewer cleared.'
            : "Assigned to {$reviewer->name}.");
    }

    /**
     * Replace the tags on a registration.
     */
    public function tag(Registration $registration, Request $request): RedirectResponse
    {
        $this->authorizeAction('registrations.manage');

        $validated = $request->validate([
            'tags' => ['present', 'string', 'max:400'],
        ]);

        $tags = collect(explode(',', $validated['tags']))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique()
            ->take(10)
            ->values()
            ->all();

        $registration->update(['tags' => $tags]);

        $this->audit->record(
            action: 'registration.tagged',
            category: 'registration',
            entityType: 'registration',
            entityId: (string) $registration->getKey(),
            entityLabel: $registration->group_name,
            detail: $tags === [] ? 'Tags cleared' : implode(', ', $tags),
        );

        return back()->with('status', $tags === [] ? 'Tags cleared.' : 'Tags updated.');
    }

    /**
     * @return Collection<int, User>
     */
    private function reviewers(): Collection
    {
        return User::query()
            ->whereIn('role', UserRole::reviewerRoles())
            ->orderBy('name')
            ->get();
    }

    private function statusFilter(Request $request): ?RegistrationStatus
    {
        return RegistrationStatus::tryFrom((string) $request->query('status', ''));
    }

    private function paymentFilter(Request $request): ?PaymentStatus
    {
        return PaymentStatus::tryFrom((string) $request->query('payment', ''));
    }

    private function authorizeAction(string $permission): void
    {
        abort_unless(auth()->user()?->canDo($permission), 403, "You do not have the {$permission} permission.");
    }
}
