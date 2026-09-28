@extends('admin.layouts.app')

@section('title', 'Registrations')

@section('content')
    <x-admin.page-head
        title="Registrations"
        :subtitle="$registrations->total().' of '.number_format($total).' records · Season '.$season->number"
    >
        @can('registrations.export')
            <a class="btn" href="{{ route('admin.registrations.export', request()->query()) }}">
                @icon('download') Export
            </a>
        @endcan
        @can('registrations.manage')
            <a class="btn btn-primary" href="{{ route('admin.registrations.create') }}">@icon('plus') New registration</a>
        @endcan
    </x-admin.page-head>

    <div class="card">
        <form method="GET" class="toolbar">
            <div class="input-wrap">
                @icon('search')
                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Search group, code, contact…"
                    aria-label="Search registrations"
                    data-filter-for="registrations-table"
                >
            </div>

            <div class="chips">
                <a class="chip @checked($status === null && $payment === null)"
                   href="{{ route('admin.registrations.index', request()->except(['status', 'payment', 'page'])) }}">All</a>

                @foreach ($statuses as $option)
                    <a
                        class="chip @checked($status === $option)"
                        href="{{ route('admin.registrations.index', array_merge(request()->except(['status', 'payment', 'page']), ['status' => $option->value])) }}"
                    >{{ $option->label() }}</a>
                @endforeach

                <span style="width:1px;height:20px;background:var(--border)"></span>

                @foreach (\App\Enums\PaymentStatus::cases() as $option)
                    <a
                        class="chip @checked($payment === $option)"
                        href="{{ route('admin.registrations.index', array_merge(request()->except(['status', 'payment', 'page']), ['payment' => $option->value])) }}"
                    >{{ $option->label() }}</a>
                @endforeach
            </div>
        </form>

        <div class="table-wrap">
            <table id="registrations-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Group</th>
                        <th>Country</th>
                        <th>Category</th>
                        <th class="num">Members</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Submitted</th>
                        <th>Reviewer</th>
                        <th class="num">Score</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registrations as $index => $registration)
                        <tr data-filter-row>
                            <td>
                                <a class="cell-strong" href="{{ route('admin.registrations.show', $registration) }}">
                                    {{ $registration->code }}
                                </a>
                            </td>
                            <td>
                                <div class="cell-group">
                                    <x-admin.avatar
                                        :name="$registration->group_name"
                                        :variant="['', 'alt', 'alt2', 'alt3'][$index % 4]"
                                        size="30px"
                                        class="mini-avatar"
                                    />
                                    <div>
                                        <div class="cell-strong">{{ $registration->group_name }}</div>
                                        <div class="cell-sub">{{ $registration->contact_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $registration->country ?: '—' }}</td>
                            <td><span class="badge outline">{{ $registration->role_type?->label() ?? $registration->category }}</span></td>
                            <td class="num">{{ $registration->members_count }}</td>
                            <td><x-admin.badge :value="$registration->status" /></td>
                            <td><x-admin.badge :value="$registration->payment_status" /></td>
                            <td><span class="muted">{{ $registration->submitted_at?->format('j M Y') ?? '—' }}</span></td>
                            <td>
                                @if ($registration->reviewer)
                                    {{ $registration->reviewer->name }}
                                @else
                                    <span class="muted">Unassigned</span>
                                @endif
                            </td>
                            <td class="num">
                                @if ($registration->score_total !== null)
                                    <b>{{ number_format((float) $registration->score_total, 1) }}</b>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="padding:0">
                                <div class="empty" data-filter-empty>
                                    @icon('search')
                                    <b>No registrations match your filters</b>
                                    <p>Try clearing the search or choosing a different status filter.</p>
                                    <a class="btn btn-sm" href="{{ route('admin.registrations.index') }}">Clear filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($registrations->hasPages())
            <div class="pager">
                <span>Showing <b>{{ $registrations->firstItem() }}–{{ $registrations->lastItem() }}</b> of <b>{{ $registrations->total() }}</b></span>
                <div class="pager-btns">
                    @if ($registrations->onFirstPage())
                        <span class="pg" aria-disabled="true">‹</span>
                    @else
                        <a class="pg" href="{{ $registrations->previousPageUrl() }}" rel="prev">‹</a>
                    @endif

                    @foreach ($registrations->getUrlRange(1, $registrations->lastPage()) as $page => $url)
                        <a class="pg @checked($page === $registrations->currentPage())" href="{{ $url }}">{{ $page }}</a>
                    @endforeach

                    @if ($registrations->hasMorePages())
                        <a class="pg" href="{{ $registrations->nextPageUrl() }}" rel="next">›</a>
                    @else
                        <span class="pg" aria-disabled="true">›</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
