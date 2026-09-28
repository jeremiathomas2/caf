<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Password reset for staff accounts.
 *
 * Reset tokens are created directly rather than relying on the framework's
 * notification, so the flow works without a mail transport configured. The
 * link is surfaced in the session flash outside production.
 */
class PasswordResetController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function requestForm(): View
    {
        return view('admin.auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'email']]);

        $user = User::query()->where('email', $validated['email'])->first();

        if ($user !== null) {
            $token = Password::createToken($user);

            $link = route('admin.password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);

            $this->audit->record(
                action: 'auth.password_requested',
                category: 'system',
                detail: $user->email,
            );

            if (! app()->isProduction()) {
                return redirect()
                    ->route('admin.password.request')
                    ->with('dev_link', $link);
            }

            // A production deployment should hand the link to the mailer here.
        }

        return back()->with('status', 'If that address belongs to an account, a reset link is on its way.');
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->numbers()],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        $this->audit->record(
            action: 'auth.password_reset',
            category: 'system',
            detail: $validated['email'],
        );

        return redirect()
            ->route('admin.login')
            ->with('status', 'Your password has been reset. Sign in with the new password.');
    }
}
