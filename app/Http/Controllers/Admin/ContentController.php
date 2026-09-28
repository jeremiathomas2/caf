<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentType;
use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use App\Models\ProgrammeSlot;
use App\Models\Sponsor;
use App\Services\AuditLogger;
use App\Support\SeasonContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Content and programme: the public pages, the running order, and the people
 * and partners the festival is built around.
 */
class ContentController extends Controller
{
    public function __construct(
        private readonly SeasonContext $seasons,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeAction('content.view');

        $season = $this->seasons->current();

        $slots = ProgrammeSlot::query()
            ->where('season_id', $season->getKey())
            ->with('registration')
            ->orderBy('event_date')
            ->orderBy('starts_at')
            ->get();

        $days = $slots
            ->map(fn (ProgrammeSlot $slot): string => $slot->event_date->toDateString())
            ->unique()
            ->sort()
            ->values();

        $requestedDay = $request->query('day');

        // Default to the first day that actually has slots, so an operator is
        // never shown a blank schedule while the programme is fully booked.
        $day = is_string($requestedDay) && $requestedDay !== ''
            ? Carbon::parse($requestedDay)->toDateString()
            : ($days->first() ?? $season->starts_on->toDateString());

        // event_date is cast to Carbon, so filtering this collection against a
        // plain date string never matches. Compare normalised date strings.
        $daySlots = $slots
            ->filter(fn (ProgrammeSlot $slot): bool => $slot->event_date->toDateString() === $day)
            ->groupBy('stage');

        $clashes = $this->clashes($slots);

        return view('admin.content.index', [
            'season' => $season,
            'pages' => ContentPage::query()
                ->orderByDesc('updated_at')
                ->limit(8)
                ->get(),
            'slots' => $slots,
            'days' => $days,
            'day' => $day,
            'daySlots' => $daySlots,
            'stages' => $daySlots->keys(),
            'clashes' => $clashes,
            'sponsors' => Sponsor::query()
                ->where(fn ($q) => $q->where('season_id', $season->getKey())->orWhereNull('season_id'))
                ->orderBy('sort_order')
                ->limit(6)
                ->get(),
            'judges' => $season->judges()->orderBy('name')->limit(6)->get(),
            'types' => ContentType::cases(),
            'statuses' => PublishStatus::cases(),
        ]);
    }

    /**
     * Create a page.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAction('content.manage');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('content_pages', 'slug')],
            'type' => ['required', Rule::enum(ContentType::class)],
            'status' => ['required', Rule::enum(PublishStatus::class)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:20000'],
            'is_in_footer' => ['nullable', 'boolean'],
        ]);

        // "slug" is nullable, so it is absent from $validated when the field is
        // left blank rather than present-and-empty.
        $slug = $validated['slug'] ?? null;

        $page = ContentPage::create([
            'title' => $validated['title'],
            'slug' => filled($slug) ? $slug : str($validated['title'])->slug()->toString(),
            'type' => $validated['type'],
            'status' => $validated['status'],
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'] ?? null,
            'is_in_footer' => (bool) ($validated['is_in_footer'] ?? false),
            'published_at' => $validated['status'] === PublishStatus::Published->value ? now() : null,
        ]);

        $this->audit->record(
            action: 'content.created',
            category: 'content',
            entityType: 'page',
            entityId: (string) $page->getKey(),
            entityLabel: $page->title,
        );

        return back()->with('status', "Page \"{$page->title}\" created.");
    }

    /**
     * Publish or unpublish a page.
     */
    public function publish(Request $request, ContentPage $page): RedirectResponse
    {
        $this->authorizeAction('content.manage');

        $validated = $request->validate([
            'status' => ['required', Rule::enum(PublishStatus::class)],
        ]);

        $page->update([
            'status' => $validated['status'],
            'published_at' => $validated['status'] === PublishStatus::Published->value
                ? ($page->published_at ?? now())
                : $page->published_at,
        ]);

        $this->audit->record(
            action: 'content.published',
            category: 'content',
            entityType: 'page',
            entityId: (string) $page->getKey(),
            entityLabel: $page->title,
            detail: 'Status set to '.$validated['status'].'.',
        );

        return back()->with('status', "\"{$page->title}\" is now {$validated['status']}.");
    }

    /**
     * Move a programme slot to a different stage or time.
     */
    public function updateSlot(Request $request, ProgrammeSlot $slot): RedirectResponse
    {
        $this->authorizeAction('content.manage');

        abort_unless($slot->season_id === $this->seasons->current()->getKey(), 404);

        $validated = $request->validate([
            'stage' => ['required', 'string', 'max:100'],
            'event_date' => ['required', 'date'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'string', 'max:40'],
        ]);

        DB::transaction(function () use ($slot, $validated): void {
            $slot->update([
                'stage' => $validated['stage'],
                'event_date' => Carbon::parse($validated['event_date'])->toDateString(),
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        $this->audit->record(
            action: 'programme.slot_updated',
            category: 'content',
            entityType: 'slot',
            entityId: (string) $slot->getKey(),
            entityLabel: $slot->title,
            detail: "Moved to {$validated['stage']} at {$validated['starts_at']}.",
        );

        return back()->with('status', "{$slot->title} rescheduled.");
    }

    /**
     * Registrations booked into two stages at the same time on the same day.
     *
     * @param  Collection<int, ProgrammeSlot>  $slots
     * @return list<array{registration: string, when: string, stages: list<string>}>
     */
    private function clashes(Collection $slots): array
    {
        $byDayAndStart = $slots
            ->whereNotNull('registration_id')
            ->groupBy(fn (ProgrammeSlot $slot): string => $slot->event_date->toDateString().'|'.$slot->starts_at);

        $found = [];

        foreach ($byDayAndStart as $group) {
            $registrations = $group->pluck('registration_id')->unique();

            if ($registrations->count() < 2) {
                continue;
            }

            $first = $group->first();

            foreach ($registrations as $registrationId) {
                $stages = $group
                    ->where('registration_id', $registrationId)
                    ->pluck('stage')
                    ->unique()
                    ->values()
                    ->all();

                if (count($stages) > 1) {
                    $found[] = [
                        'registration' => $first->registration?->group_name ?? 'Group #'.$registrationId,
                        'when' => $first->event_date->toDateString().' '.$first->starts_at,
                        'stages' => $stages,
                    ];
                }
            }
        }

        return $found;
    }

    private function authorizeAction(string $permission): void
    {
        abort_unless(auth()->user()?->canDo($permission), 403, "You do not have the {$permission} permission.");
    }
}
