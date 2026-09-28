<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegistrationStatusController extends Controller
{
    public function __construct(private readonly RegistrationService $registrations) {}

    public function __invoke(Request $request, Registration $registration): RedirectResponse
    {
        abort_unless(auth()->user()?->canDo('registrations.manage'), 403, 'You cannot change registration status.');

        $validated = $request->validate([
            'status' => ['required', Rule::enum(RegistrationStatus::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $target = RegistrationStatus::from($validated['status']);

        $this->registrations->transitionTo($registration, $target, $validated['note'] ?? null);

        return back()->with('status', "{$registration->code} moved to {$target->label()}.");
    }
}
