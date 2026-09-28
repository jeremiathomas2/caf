<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RegistrationStatus;
use App\Enums\SeasonState;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SeasonRequest;
use App\Models\Season;
use App\Services\AuditLogger;
use App\Support\SeasonContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SeasonController extends Controller
{
    public function __construct(
        private readonly SeasonContext $seasons,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeAction('seasons.view');

        $state = SeasonState::tryFrom((string) $request->query('state', ''));
        $search = trim((string) $request->query('q', ''));

        $seasons = Season::query()
            ->withCount('registrations')
            ->when($state instanceof SeasonState, fn ($query) => $query->where('state', $state->value))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('theme', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('number')
            ->paginate(15)
            ->withQueryString();

        return view('admin.seasons.index', [
            'seasons' => $seasons,
            'state' => $state,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $this->authorizeAction('seasons.manage');

        return view('admin.seasons.create', ['season' => new Season]);
    }

    public function store(SeasonRequest $request): RedirectResponse
    {
        $season = Season::create([
            ...$request->validated(),
            'slug' => $request->validated()['slug'] ?? Str::slug($request->validated()['name']),
        ]);

        $this->seasons->flush();

        $this->audit->record(
            action: 'season.created',
            category: 'season',
            entityType: 'season',
            entityId: (string) $season->getKey(),
            entityLabel: $season->name,
        );

        return redirect()
            ->route('admin.seasons.show', $season)
            ->with('status', "Season {$season->number} created.");
    }

    public function show(Season $season): View
    {
        $this->authorizeAction('seasons.view');

        $this->seasons->select($season);

        return view('admin.seasons.show', [
            'season' => $season->loadCount('registrations'),
            'stats' => [
                'registrations' => $season->registrations()->count(),
                'confirmed' => $season->registrations()->where('status', RegistrationStatus::Confirmed->value)->count(),
                'invoices' => $season->invoices()->count(),
                'rounds' => $season->reviewRounds()->count(),
            ],
            'fees' => [
                'standard' => $season->feeFor(1),
                'early_bird' => $season->early_bird_fee,
                'sizes' => collect([3, 6, 10, 14, 18])->mapWithKeys(fn (int $size): array => [
                    $size => $season->feeFor($size),
                ]),
            ],
        ]);
    }

    public function edit(Season $season): View
    {
        $this->authorizeAction('seasons.manage');

        return view('admin.seasons.edit', ['season' => $season]);
    }

    public function update(SeasonRequest $request, Season $season): RedirectResponse
    {
        $season->update($request->validated());

        $this->audit->record(
            action: 'season.updated',
            category: 'season',
            entityType: 'season',
            entityId: (string) $season->getKey(),
            entityLabel: $season->name,
        );

        return redirect()
            ->route('admin.seasons.show', $season)
            ->with('status', "Season {$season->number} updated.");
    }

    /**
     * Make one season current, clearing the flag on the others.
     */
    public function makeCurrent(Season $season): RedirectResponse
    {
        $this->authorizeAction('seasons.manage');

        DB::transaction(function () use ($season): void {
            Season::query()->where('is_current', true)->update(['is_current' => false]);
            $season->update(['is_current' => true, 'state' => SeasonState::Live->value]);
        });

        $this->seasons->flush();
        $this->seasons->select($season);

        $this->audit->record(
            action: 'season.current',
            category: 'season',
            entityType: 'season',
            entityId: (string) $season->getKey(),
            entityLabel: $season->name,
        );

        return back()->with('status', "{$season->name} is now the current season.");
    }

    private function authorizeAction(string $permission): void
    {
        abort_unless(auth()->user()?->canDo($permission), 403, "You do not have the {$permission} permission.");
    }
}
