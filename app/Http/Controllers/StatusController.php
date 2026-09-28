<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Public status lookup. A group enters the code it received on submission and
 * sees where it is in the process, without needing an account.
 */
class StatusController extends Controller
{
    public function create(Request $request): View
    {
        $code = $this->normalise($request->string('code')->toString());

        $registration = $this->lookup($code);

        return view('pages.status', [
            'registration' => $registration,
            'code' => $request->string('code')->toString(),
            'searched' => $code !== '',
            'notFound' => $code !== '' && $registration === null,
        ]);
    }

    /**
     * Codes are issued as CAF2-0003, but people retype them in lower case, with
     * stray whitespace, or without the separating dash. Collapsing those away
     * makes every variant resolve to the same token.
     *
     * The stored column is folded the same way in lookup(), so both sides of
     * the comparison are normalised identically.
     */
    private function normalise(string $code): string
    {
        return Str::upper((string) preg_replace('/[\s\-]+/u', '', $code));
    }

    private function lookup(string $code): ?Registration
    {
        if ($code === '') {
            return null;
        }

        return Registration::query()
            ->whereRaw("UPPER(REPLACE(REPLACE(code, '-', ''), ' ', '')) = ?", [$code])
            ->with([
                'season',
                'invoices.payments',
                'members',
            ])
            ->first();
    }
}
