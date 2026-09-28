<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\SeasonContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(private readonly SeasonContext $seasons) {}

    public function index(Request $request): View
    {
        $this->authorizeAction('audit.view');

        $category = trim((string) $request->query('category', ''));
        $search = trim((string) $request->query('q', ''));

        $entries = AuditLog::query()
            ->with('actor')
            ->when($category !== '', fn ($q) => $q->where('category', $category))
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($inner) use ($search): void {
                    $inner->where('action', 'like', "%{$search}%")
                        ->orWhere('entity_label', 'like', "%{$search}%")
                        ->orWhere('entity_type', 'like', "%{$search}%")
                        ->orWhere('actor_label', 'like', "%{$search}%")
                        ->orWhere('detail', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(40)
            ->withQueryString();

        return view('admin.audit.index', [
            'entries' => $entries,
            'category' => $category,
            'search' => $search,
            'categories' => AuditLog::query()
                ->select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->all(),
            'total' => AuditLog::query()->count(),
        ]);
    }

    private function authorizeAction(string $permission): void
    {
        abort_unless(auth()->user()?->canDo($permission), 403, "You do not have the {$permission} permission.");
    }
}
