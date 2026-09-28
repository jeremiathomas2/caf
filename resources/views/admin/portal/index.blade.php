@extends('admin.layouts.app')

@section('title', 'Group Portal')

@section('content')
    <x-admin.page-head
        title="Group Portal"
        subtitle="Responsive self-service for group leaders &middot; registration, roster, payments, messaging"
    />

    <div class="grid grid-2-1" style="margin-bottom:16px">
        <div class="card">
            <div class="card-head">
                <div>
                    <h3>@icon('users') Portal capabilities</h3>
                    <p>{{ $availableCount }} of {{ count($capabilities) }} capabilities are live</p>
                </div>
            </div>

            <div class="card-body" style="padding:0">
                @foreach ($capabilities as $capability)
                    <div class="queue-item" style="cursor:default">
                        <span class="queue-ico {{ $capability['tone'] }}">@icon($capability['icon'])</span>
                        <div class="queue-text">
                            <b>{!! $capability['label'] !!}</b>
                            <span>{{ $capability['detail'] }}</span>
                        </div>
                        @if ($capability['available'])
                            <span style="color:var(--ok)" title="Available">@icon('check')</span>
                        @else
                            <span class="badge gray">Planned</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-head"><div><h3>@icon('eye') Group preview</h3><p>What a leader sees</p></div></div>
            <div class="card-body">
                @if ($preview === null)
                    <div class="empty">
                        @icon('users')
                        <b>Nothing to preview</b>
                        <p>No approved registrations in this season yet.</p>
                    </div>
                @else
                    <div class="portal-preview">
                        <div class="pp-head">
                            <b>{{ $preview->group_name }}</b>
                            <span>Season {{ $season->number }} &middot; {{ $preview->code }}</span>
                        </div>

                        <div class="pp-body">
                            <div class="section-title">Status timeline</div>
                            <div class="timeline">
                                @forelse ($preview->statusEvents->sortBy('created_at') as $event)
                                    @php
                                        $isLatest = $loop->last;
                                        $label = $event->to_status instanceof \BackedEnum
                                            ? $event->to_status->label()
                                            : Str::headline((string) $event->to_status);
                                    @endphp
                                    <div class="tl-item">
                                        <span class="tl-dot {{ $isLatest ? 'now' : 'done' }}"></span>
                                        <div class="tl-text">
                                            <b>{{ Str::headline($label) }}</b>
                                            <span>{{ $event->created_at->format('j M Y · H:i') }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty">
                                        @icon('file')
                                        <b>No history yet</b>
                                        <p>Status changes appear here as they happen.</p>
                                    </div>
                                @endforelse
                            </div>

                            @php
                                $balance = $preview->outstandingBalance();
                                $settled = $balance <= 0;
                            @endphp

                            <div style="background:{{ $settled ? 'var(--ok-soft)' : 'var(--warn-soft)' }};border-radius:12px;border:1px solid transparent;padding:12px 14px">
                                <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;color:{{ $settled ? 'var(--ok)' : 'var(--warn)' }}">
                                    Balance due
                                </div>
                                <div style="font-size:22px;font-weight:800;margin-top:2px">
                                    {{ $preview->season->currency }} {{ number_format($balance) }}
                                </div>
                                <div style="font-size:11px;color:var(--text-2);margin-top:2px">
                                    {{ $settled ? 'Fully settled · receipt available' : 'Payment required to hold the place' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h3>@icon('file') Groups with portal access</h3>
                <p>{{ $groups->count() }} groups &middot; {{ $threads }} open conversation{{ $threads === 1 ? '' : 's' }}</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Code</th>
                        <th>Contact</th>
                        <th class="num">Members</th>
                        <th>Status</th>
                        <th>Balance</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groups as $group)
                        <tr>
                            <td>
                                <div class="cell-group">
                                    <x-admin.avatar :name="$group->group_name" class="mini-avatar" />
                                    <div>
                                        <div class="cell-strong">{{ $group->group_name }}</div>
                                        <div class="cell-sub">{{ Str::headline($group->category) }} &middot; {{ $group->city ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><div class="cell-strong">{{ $group->code }}</div></td>
                            <td>
                                <div>{{ $group->contact_name }}</div>
                                <div class="cell-sub">{{ $group->contact_email }}</div>
                            </td>
                            <td class="num">{{ $group->members_count }}</td>
                            <td><x-admin.badge :value="$group->status" /></td>
                            <td class="num">
                                @php $balance = $group->outstandingBalance(); @endphp
                                <b style="color:{{ $balance > 0 ? 'var(--danger)' : 'var(--text-3)' }}">
                                    {{ number_format($balance) }}
                                </b>
                            </td>
                            <td>
                                @can('portal.manage')
                                    <form method="POST" action="{{ route('admin.portal.magic-link', $group) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-ghost" aria-label="Send magic link to {{ $group->group_name }}">
                                            @icon('zap')
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:0">
                                <div class="empty">
                                    @icon('users')
                                    <b>No groups registered</b>
                                    <p>Groups appear here as registrations come in.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
