@php
    $user = auth()->user();
    $roleLabel = $user?->roleLabel() ?? '—';
@endphp
<header class="topbar">
    <button class="icon-btn only-mobile" id="menuBtn" aria-label="Open navigation">
        @icon('sliders', 'ico')
    </button>

    <button class="icon-btn only-desktop" id="collapseBtn" aria-label="Collapse sidebar">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/></svg>
    </button>

    <button class="season-switch" id="seasonBtn" data-dd-trigger="seasonDD" aria-haspopup="true" aria-expanded="false">
        <span class="season-dot {{ $adminSeason?->state?->value }}"></span>
        <span class="season-meta">
            <b>{{ $adminSeason ? 'Season '.$adminSeason->number : 'No season' }}</b>
            <small>{{ $adminSeason?->theme ?? 'Select a season' }}</small>
        </span>
        @icon('chevron', 'chev')
    </button>

    <button class="search-trigger" id="searchBtn">
        @icon('search')
        <span>Search registrations, groups, invoices…</span>
        <kbd>⌘K</kbd>
    </button>

    <div class="topbar-right">
        <button class="icon-btn" id="themeBtn" aria-label="Toggle theme">
            <svg class="ico" id="themeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></svg>
        </button>

        <button class="icon-btn" id="bellBtn" data-dd-trigger="bellDD" aria-haspopup="true" aria-expanded="false" aria-label="Notifications">
            @icon('bell')
            <span class="ping"></span>
        </button>

        <div class="user" data-dd-trigger="userDD" aria-haspopup="true" aria-expanded="false" style="cursor:pointer">
            <div class="avatar">{{ $user?->initials() ?? '—' }}</div>
            <div class="user-meta">
                <b>{{ $user?->name ?? 'Signed out' }}</b>
                <small>{{ $roleLabel }}</small>
            </div>
        </div>
    </div>
</header>

<div class="dd" id="userDD" role="menu" data-align="right" style="right:14px;min-width:230px">
    <div class="dd-head">
        <b>{{ $user?->email }}</b>
        <span>{{ $roleLabel }}</span>
    </div>
    <a class="dd-item" href="{{ route('admin.seasons.index') }}">@icon('calendar') Seasons</a>
    <a class="dd-item" href="/" target="_blank" rel="noopener">@icon('globe') View public site</a>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="dd-item danger">@icon('lock') Sign out</button>
    </form>
</div>
