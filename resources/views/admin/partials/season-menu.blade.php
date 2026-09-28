<div class="dd" id="seasonDD" role="menu">
    <div class="dd-head">
        <b>Switch season</b>
        <span>{{ count($cafSeasons ?? []) }} available</span>
    </div>

    @forelse ($cafSeasons ?? [] as $option)
        <a class="dd-item" href="{{ request()->fullUrlWithQuery(['season' => $option->getKey()]) }}"
           @class(['active' => $adminSeason?->is($option)])>
            <span class="season-dot {{ $option->state?->value }}"></span>
            <span style="flex:1">
                <b>Season {{ $option->number }}</b>
                <small style="display:block;color:var(--text-3)">{{ $option->name }}</small>
            </span>
            @if ($option->is_current)
                @icon('check')
            @endif
        </a>
    @empty
        <div class="dd-item" style="opacity:.6">No seasons yet</div>
    @endforelse
</div>
