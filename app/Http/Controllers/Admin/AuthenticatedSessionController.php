<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Season;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\OtpService;
use App\Support\SeasonContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Signs staff into the management system.
 *
 * A user with a confirmed second factor is parked in a short-lived session
 * reservation and must clear the code challenge before the session is
 * actually authenticated.
 */
class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly OtpService $otp,
        private readonly AuditLogger $audit,
        private readonly SeasonContext $seasons,
    ) {}

    public function create(): View
    {
        return view('admin.auth.login', [
            'season' => $this->seasons->tryCurrent(),
            'stats' => $this->stats(),
        ]);
    }

    /**
     * Headline counts shown on the sign-in art panel.
     *
     * @return array{seasons: int, registrations: int, countries: int}
     */
    private function stats(): array
    {
        return [
            'seasons' => Season::query()->count(),
            'registrations' => Registration::query()->count(),
            'countries' => Registration::query()->whereNotNull('country')->distinct()->count('country'),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $user = User::query()
            ->where('email', $credentials['email'])
            ->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => 'Those credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        if (! $user->isAdmin()) {
            throw ValidationException::withMessages([
                'email' => 'This account does not have access to the management system.',
            ]);
        }

        if ($user->hasConfirmedTwoFactor()) {
            return $this->startChallenge($user);
        }

        $this->signIn($user, (bool) ($credentials['remember'] ?? false));

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Second-factor step.
     */
    public function verifyForm(Request $request): View|RedirectResponse
    {
        $user = $this->challengedUser($request);

        if ($user === null) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.verify', ['user' => $user]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $this->challengedUser($request);

        if ($user === null) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate(['code' => ['required', 'string', 'digits:6']]);

        $key = (string) config('caf.auth.two_factor_reserve_key');
        $payload = (array) $request->session()->get($key, []);

        $result = $this->otp->verify($validated['code'], $payload);

        if (! $result['valid']) {
            $request->session()->put($key, [...$payload, 'attempts' => $result['attempts']]);

            throw ValidationException::withMessages([
                'code' => match ($result['reason']) {
                    'expired' => 'That code has expired. Sign in again to get a new one.',
                    'attempts' => 'Too many incorrect attempts. Sign in again.',
                    default => 'That code is not correct.',
                },
            ]);
        }

        $request->session()->forget($key);

        $this->signIn($user, false);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $user->forceFill(['last_login_at' => now()])->save();

            $this->audit->record(
                action: 'auth.logout',
                category: 'system',
                detail: $user->email,
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'You have been signed out.');
    }

    private function startChallenge(User $user): RedirectResponse
    {
        $code = $this->otp->generate(6);

        $request_key = (string) config('caf.auth.two_factor_reserve_key');
        session()->put($request_key, [
            'user_id' => $user->getKey(),
            'hash' => $this->otp->hash($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(OtpService::EXPIRY_MINUTES)->getTimestamp(),
        ]);

        $this->audit->record(
            action: 'auth.challenge',
            category: 'system',
            detail: $user->email,
        );

        // Without a mailer integration the code is surfaced once in the
        // session flash so the flow remains usable in development.
        return redirect()
            ->route('admin.login.verify')
            ->with('dev_code', app()->isLocal() ? $code : null);
    }

    private function challengedUser(Request $request): ?User
    {
        $key = (string) config('caf.auth.two_factor_reserve_key');
        $payload = (array) $request->session()->get($key, []);
        $userId = $payload['user_id'] ?? null;

        if ($userId === null) {
            return null;
        }

        $user = User::find($userId);

        return $user?->isAdmin() ? $user : null;
    }

    private function signIn(User $user, bool $remember): void
    {
        Auth::login($user, $remember);

        $request = request();
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        $this->audit->record(
            action: 'auth.login',
            category: 'system',
            detail: $user->email,
        );
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        $key = $this->throttleKey($request);

        if (! RateLimiter::tooManyAttempts($key, (int) config('caf.auth.throttle_attempts', 5))) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'email' => "Too many sign-in attempts. Please try again in {$seconds} seconds.",
        ])->after($seconds);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(
            Str::lower((string) $request->input('email')).'|'.$request->ip()
        );
    }
}
