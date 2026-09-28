<?php

namespace App\Support;

use App\Enums\SeasonState;
use App\Models\Season;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Resolves which season the management system is currently showing.
 *
 * The active season lives in the session. When it is missing or points at a
 * deleted season, the current season is used, and failing that the newest
 * live season.
 *
 * This is bound as a scoped singleton: built once per request, discarded after.
 * That is why the request is read per call rather than injected, and why the
 * lookups below can be memoised. A captured request would go stale and
 * silently break season switching.
 */
class SeasonContext
{
    public const SESSION_KEY = 'caf.season_id';

    /**
     * The request the cached lookups below were built for. A new request
     * invalidates them, so a long-lived worker can never serve one visitor's
     * season to the next.
     */
    private ?object $cachedFor = null;

    private ?Season $resolved = null;

    private ?Collection $switchable = null;

    public function current(): Season
    {
        $season = $this->tryCurrent();

        if ($season === null) {
            throw new \RuntimeException(
                'No season is available. Create one before using the management system.'
            );
        }

        $this->remember($season);

        return $season;
    }

    public function tryCurrent(): ?Season
    {
        return $this->cached()->resolved ??= $this->requested() ?? $this->fromSession() ?? $this->fallback();
    }

    public function select(Season $season): void
    {
        Session::put(self::SESSION_KEY, $season->getKey());

        $this->cached()->resolved = $season;
    }

    public function forget(): void
    {
        Session::forget(self::SESSION_KEY);

        $this->cached()->resolved = null;
    }

    /**
     * All seasons a user may switch between, newest first.
     *
     * @return Collection<int, Season>
     */
    public function switchable(): Collection
    {
        return $this->cached()->switchable ??= Season::query()->orderByDesc('number')->get();
    }

    /**
     * Drop the per-request lookups. Needed when seasons are written mid-request.
     */
    public function flush(): void
    {
        $this->cachedFor = null;
        $this->resolved = null;
        $this->switchable = null;
    }

    /**
     * Return the cache, discarding it if it belongs to an earlier request.
     */
    private function cached(): object
    {
        if ($this->cachedFor !== request()) {
            $this->cachedFor = request();
            $this->resolved = null;
            $this->switchable = null;
        }

        return $this;
    }

    private function requested(): ?Season
    {
        $id = request()->integer('season') ?: null;

        return $id === null ? null : Season::find($id);
    }

    private function fromSession(): ?Season
    {
        $id = Session::get(self::SESSION_KEY);

        return $id === null ? null : Season::find($id);
    }

    private function fallback(): ?Season
    {
        return Season::query()->current()->first()
            ?? Season::query()->where('state', SeasonState::Live->value)->orderByDesc('number')->first()
            ?? Season::query()->orderByDesc('number')->first();
    }

    private function remember(Season $season): void
    {
        if (Session::get(self::SESSION_KEY) !== $season->getKey()) {
            Session::put(self::SESSION_KEY, $season->getKey());
        }
    }
}
