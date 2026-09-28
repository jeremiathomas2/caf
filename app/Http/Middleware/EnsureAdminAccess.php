<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\SeasonContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function __construct(private readonly SeasonContext $seasons) {}

    /**
     * Only signed-in users holding the admin.access permission may enter /admin.
     *
     * A signed-in user without the permission keeps their session — group
     * leaders hold real accounts — and is simply refused.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return $this->toLogin($request);
        }

        abort_unless(
            $user->isAdmin() && $user->canDo('admin.access'),
            403,
            'This area is for festival staff.',
        );

        return $this->whenSeasonless($request, $next);
    }

    /**
     * Almost every screen needs a season to be active. On a brand new install
     * there is not one yet, so send the user to the seasons screen instead of
     * letting SeasonContext throw a 500. That screen enforces its own
     * permission, so anyone who may not manage seasons still gets a 403.
     */
    private function whenSeasonless(Request $request, Closure $next): Response
    {
        if ($this->seasons->tryCurrent() !== null || $request->is('admin/seasons*')) {
            return $next($request);
        }

        return redirect()
            ->route('admin.seasons.index')
            ->with('status', 'No season has been set up yet. Create one to get started.');
    }

    private function toLogin(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()
            ->guest(route('admin.login'))
            ->with('intended', $request->fullUrl());
    }
}
