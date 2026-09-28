<?php

namespace App\Http\Middleware;

use App\Support\AdminNav;
use App\Support\SeasonContext;
use App\Support\SiteSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shares the data the management system shell needs: the active season, the
 * permission-filtered navigation, and the command palette.
 *
 * This only runs for /admin. The public marketing pages read the database
 * through their own components, so a visitor never pays for a season lookup.
 */
class ShareSiteContext
{
    public function __construct(
        private readonly SeasonContext $seasons,
        private readonly SiteSettings $settings,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin', 'admin/*')) {
            return $next($request);
        }

        $season = $this->seasons->tryCurrent();

        View::share('siteSettings', $this->settings);
        View::share('cafSeasons', $this->seasons->switchable());

        // The shell must render even on a brand new install with no season, so
        // the nav is always built. Badges stay null because they count rows
        // belonging to a season.
        View::share('adminSeason', $season);

        $nav = new AdminNav($season);

        View::share('adminNavGroups', $nav->groups($request->user()));
        View::share('adminCommands', $nav->palette($request->user()));

        return $next($request);
    }
}
