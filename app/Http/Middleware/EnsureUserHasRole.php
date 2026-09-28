<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Restrict a route to one or more roles.
     *
     * Usage: ->middleware('role:super_admin,season_manager')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->guest(route('admin.login'));
        }

        $allowed = array_map(
            fn (string $role): string => UserRole::tryFrom($role)?->value ?? $role,
            $roles,
        );

        abort_unless(in_array($user->role, $allowed, true), 403);

        return $next($request);
    }
}
