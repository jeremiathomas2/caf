@extends('admin.layouts.app')

@section('title', 'Season '.$season->number)

@section('content')
    <x-admin.page-head
        :title="'Season '.$season->number.' · '.$season->name"
        :subtitle="trim(($season->tagline ?? '').' · '.($season->starts_on?->format('j F Y') ?? 'Dates TBC'))"
    >
        <a class="btn btn-ghost" href="{{ route('admin.seasons.index') }}">@icon('chevron') All seasons</a>
        @can('seasons.manage')
            <a class="btn" href="{{ route('admin.seasons.edit', $season) }}">@icon('edit') Edit</a>
            @unless ($season->is_current)
                <form method="POST" action="{{ route('admin.seasons.make-current', $season) }}"
                      data-confirm="Make Season {{ $season->number }} the current season?">
                    @csrf
                    <button type="submit" class="btn btn-primary">@icon('check') Make current</button>
                </form>
            @endunless
        @endcan
    </x-admin.page-head>

    <div class="grid grid-kpi" style="margin-bottom:16px">
        <x-admin.kpi label="Registrations" :value="number_format($stats['registrations'])" icon="file" />
        <x-admin.kpi label="Confirmed" :value="number_format($stats['confirmed'])" icon="check" tone="ok" />
        <x-admin.kpi label="Invoices" :value="number_format($stats['invoices'])" icon="card" tone="info" />
        <x-admin.kpi label="Review rounds" :value="number_format($stats['rounds'])" icon="star" />
    </div>

    <div class="grid grid-2">
        <div class="card">
            <div class="card-head">
                <div>
                    <h3>@icon('file') Details</h3>
                    <p>Identity and window for this edition.</p>
                </div>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr><th>State</th><td><x-admin.badge :value="$season->state" /></td></tr>
                        <tr><th>Theme</th><td>{{ $season->theme ?: '—' }}</td></tr>
                        <tr><th>Slug</th><td class="mono">{{ $season->slug ?: '—' }}</td></tr>
                        <tr><th>Location</th><td>{{ trim(($season->venue ?? '').' '.($season->city ?? '').' '.($season->country ?? '')) ?: '—' }}</td></tr>
                        <tr><th>Registration opens</th><td>{{ $season->registration_opens_at?->format('j M Y, H:i') ?? '—' }}</td></tr>
                        <tr>
                            <th>Registration closes</th>
                            <td>
                                {{ $season->registration_closes_at?->format('j M Y, H:i') ?? '—' }}
                                <x-admin.badge :value="$season->registrationIsOpen() ? 'Open' : 'Closed'" />
                            </td>
                        </tr>
                        <tr><th>Early bird</th><td>{{ $season->isEarlyBird() ? 'Active until '.$season->early_bird_closes_at->format('j M Y') : 'Closed' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div>
                    <h3>@icon('card') Fee schedule</h3>
                    <p>Computed from {{ number_format((float) $season->per_head_fee, 0) }} {{ $season->currency }} per head.</p>
                </div>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr><th>Performers</th><th class="num">Fee ({{ $season->currency }})</th><th class="num">USD</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($fees['sizes'] as $size => $fee)
                            <tr>
                                <td>{{ $size }}</td>
                                <td class="num">{{ number_format((float) $fee, 0) }}</td>
                                <td class="num">{{ number_format((float) $fee / (float) $season->usd_fx_rate, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
