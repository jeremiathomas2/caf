<div class="dd" id="bellDD" role="menu" data-align="right" style="right:14px;min-width:330px;max-width:calc(100vw - 28px)">
    <div class="dd-head">
        <b>Notifications</b>
        <span>Latest activity</span>
    </div>

    @forelse (($recentAudit ?? collect())->take(6) as $entry)
        <div class="dd-item" style="cursor:default">
            <span class="feed-dot {{ \App\Support\Badge::tone($entry->action) }}">@icon('zap')</span>
            <span style="flex:1">
                <b>{{ $entry->action }}</b>
                <small style="display:block;color:var(--text-3)">
                    {{ $entry->detail ?? $entry->entity_type }} · {{ $entry->created_at->diffForHumans() }}
                </small>
            </span>
        </div>
    @empty
        <div class="dd-item" style="opacity:.6">Nothing to report</div>
    @endforelse
</div>
