@php
    $user = auth()->user();
    $currentPath = request()->path();
@endphp
<aside class="sidebar" id="sidebar">
    <div class="brand">
        <img class="brand-logo" src="{{ asset('caf.png') }}" alt="Culture Acapella Festival logo" width="668" height="344">
        <div class="brand-text">
            <b>{{ config('caf.brand.name') }}</b>
            <span>{{ $adminSeason?->theme ?? config('caf.brand.tagline') }}</span>
        </div>
    </div>

    <nav class="nav" id="nav" aria-label="Main navigation">
        @foreach ($adminNavGroups ?? [] as $group)
            @if ($group['items'] !== [])
                <div class="nav-group">
                    <div class="nav-label">{{ $group['label'] }}</div>

                    @foreach ($group['items'] as $item)
                        @php
                            $enabled = $item['enabled'] ?? true;
                            $active = $enabled && request()->routeIs($item['route']);
                        @endphp

                        @if ($enabled)
                            <a href="{{ route($item['route']) }}" @class(['nav-item', 'active' => $active]) @if($active) aria-current="page" @endif>
                                @icon($item['icon'])
                                <span>{{ $item['label'] }}</span>
                                @if (($item['badge'] ?? null))
                                    <em class="nav-badge">{{ $item['badge'] }}</em>
                                @endif
                            </a>
                        @else
                            <span class="nav-item is-disabled" aria-disabled="true" title="This screen has not been built yet.">
                                @icon($item['icon'])
                                <span>{{ $item['label'] }}</span>
                                <em class="nav-tag">Soon</em>
                            </span>
                        @endif
                    @endforeach
                </div>
            @endif
        @endforeach

        <div class="nav-group">
            <div class="nav-label">Seasons</div>
            @if ($adminSeason)
                <a href="{{ route('admin.seasons.index') }}" @class(['nav-item', 'active' => request()->routeIs('admin.seasons.*')])>
                    @icon('calendar')
                    <span>All seasons</span>
                </a>
            @endif
        </div>
    </nav>

    <div class="sidebar-foot">
        <div class="sf-card">
            @icon('shield')
            <div class="sf-text">
                <b>All systems operational</b>
                <span>{{ $user?->last_login_at ? 'Last sign-in '.$user->last_login_at->diffForHumans() : 'First sign-in' }}</span>
            </div>
        </div>
    </div>
</aside>
