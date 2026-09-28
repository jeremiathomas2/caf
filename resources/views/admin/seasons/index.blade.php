@extends('admin.layouts.app')

@section('title', 'Seasons')

@section('content')
    <x-admin.page-head title="Seasons" subtitle="Every edition of the festival, past and upcoming.">
        @can('seasons.manage')
            <a class="btn btn-primary" href="{{ route('admin.seasons.create') }}">@icon('plus') New season</a>
        @endcan
    </x-admin.page-head>

    <div class="card">
        <form method="GET" class="toolbar">
            <div class="input-wrap">
                @icon('search')
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search seasons…"
                    aria-label="Search seasons"
                    data-filter-for="seasons-table"
                >
            </div>
            <div class="chips">
                <a class="chip @checked(request()->fullUrlIs(request()->path()))" href="{{ route('admin.seasons.index') }}">All</a>
                @foreach (\App\Enums\SeasonState::cases() as $option)
                    <a
                        class="chip @checked(request('state') === $option->value)"
                        href="{{ route('admin.seasons.index', ['state' => $option->value]) }}"
                    >{{ $option->label() }}</a>
                @endforeach
            </div>
        </form>

        <div class="table-wrap">
            <table id="seasons-table">
                <thead>
                    <tr>
                        <th>Season</th>
                        <th>State</th>
                        <th>Dates</th>
                        <th>Location</th>
                        <th class="num">Registrations</th>
                        <th class="num">Fee / head</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($seasons as $season)
                        <tr data-filter-row>
                            <td>
                                <a href="{{ route('admin.seasons.show', $season) }}"><b>Season {{ $season->number }}</b></a>
                                <div style="color:var(--text-3);font-size:12.5px">{{ $season->name }}</div>
                            </td>
                            <td><x-admin.badge :value="$season->state" /></td>
                            <td>
                                @if ($season->starts_on)
                                    {{ $season->starts_on->format('j M Y') }}
                                    @if ($season->ends_on)
                                        <span style="color:var(--text-3)"> → {{ $season->ends_on->format('j M Y') }}</span>
                                    @endif
                                @else
                                    <span class="muted">TBC</span>
                                @endif
                            </td>
                            <td>{{ $season->city ?: '—' }}</td>
                            <td class="num">{{ number_format($season->registrations_count) }}</td>
                            <td class="num">{{ number_format((float) $season->per_head_fee, 0) }} {{ $season->currency }}</td>
                            <td class="right">
                                <div style="display:flex;gap:6px;justify-content:flex-end">
                                    <a class="btn btn-sm btn-ghost" href="{{ route('admin.seasons.show', $season) }}">Open</a>
                                    @can('seasons.manage')
                                        <a class="btn btn-sm btn-ghost" href="{{ route('admin.seasons.edit', $season) }}">Edit</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:0">
                                <div class="empty" data-filter-empty>
                                    @icon('search')
                                    <b>No seasons match your filters</b>
                                    <p>Clear the search, or create the first season to start accepting registrations.</p>
                                    <a class="btn btn-sm" href="{{ route('admin.seasons.index') }}">Clear filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($seasons->hasPages())
            <div class="pager">{{ $seasons->links() }}</div>
        @endif
    </div>
@endsection
