<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureModelStrictness();
        $this->configureRateLimiting();
        $this->configureUrls();
        $this->definePermissions();
        $this->registerBladeDirectives();
    }

    /**
     * Catch N+1 queries and silently dropped attributes during development
     * without also making missing attributes fatal, which would break the
     * admin views that read optional columns.
     */
    private function configureModelStrictness(): void
    {
        if ($this->app->isProduction()) {
            return;
        }

        Model::preventLazyLoading(true);
        Model::preventSilentlyDiscardingAttributes(true);
    }

    /**
     * Named limiters used by the sign-in routes.
     */
    private function configureRateLimiting(): void
    {
        $max = (int) config('caf.auth.throttle_attempts', 5);
        $decay = (int) config('caf.auth.throttle_decay_seconds', 60);

        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinutes(1, $max)
            ->by('login|'.mb_strtolower((string) $request->input('email')).'|'.$request->ip())
            ->response(fn (): array => [
                'message' => "Too many sign-in attempts. Please try again in {$decay} seconds.",
                'errors' => ['email' => ["Too many sign-in attempts. Please try again in {$decay} seconds."]],
            ]));

        RateLimiter::for('verify', fn (Request $request): Limit => Limit::perMinutes(5, 10)
            ->by('verify|'.$request->ip()));
    }

    private function configureUrls(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Expose the code-backed role map to the authorization layer, so
     * `can('registrations.manage')` mirrors UserRole::permissions().
     */
    private function definePermissions(): void
    {
        Gate::before(function (?User $user, string $ability): ?bool {
            if ($user === null) {
                return null;
            }

            return $user->canDo($ability) ? true : null;
        });
    }

    private function registerBladeDirectives(): void
    {
        Blade::if('role', function (string ...$roles): bool {
            $user = auth()->user();

            if ($user === null) {
                return false;
            }

            return in_array($user->role, array_map(
                fn (string $role): string => UserRole::tryFrom($role)?->value ?? $role,
                $roles,
            ), true);
        });

        Blade::if('can', function (string $permission): bool {
            $user = auth()->user();

            return $user !== null && method_exists($user, 'canDo') && $user->canDo($permission);
        });

        Blade::directive('icon', fn (string $expression): string => "<?php echo \\App\\Support\\Icon::svg({$expression}); ?>");

        Blade::directive('badge', fn (string $expression): string => "<?php echo \\App\\Support\\Badge::render({$expression}); ?>");

        Blade::directive('setting', fn (string $expression): string => "<?php echo app(\\App\\Support\\SiteSettings::class)->get({$expression}); ?>");
    }
}
