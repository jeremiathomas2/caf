@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-admin.page-head
        :title="'Season '.$season->number.' Dashboard'"
        :subtitle="trim($season->name.' · '.$season->tagline.' · '.$season->starts_on?->format('j F Y').' · '.$season->city)"
    >
        <a class="btn" href="{{ route('admin.seasons.index') }}">@icon('calendar') All seasons</a>
        <a class="btn btn-primary" href="{{ route('admin.registrations.create') }}">@icon('plus') New registration</a>
    </x-admin.page-head>

    <div class="grid grid-kpi" style="margin-bottom:16px">
        <x-admin.kpi
            label="Total registrations"
            :value="number_format($kpis['registrations']['value'])"
            :foot="$kpis['registrations']['foot']"
            icon="file"
        />
        <x-admin.kpi
            label="Payments collected"
            :value="$kpis['collected']['value'].'%'"
            :foot="$kpis['collected']['foot']"
            icon="card"
            tone="ok"
        />
        <x-admin.kpi
            label="Outstanding"
            :value="number_format($kpis['awaiting_payment']['value'])"
            :foot="$kpis['awaiting_payment']['foot']"
            icon="alert"
            :tone="$kpis['awaiting_payment']['tone']"
        />
        <x-admin.kpi
            label="Unanswered messages"
            :value="number_format($kpis['open_threads']['value'])"
            :foot="$kpis['open_threads']['foot']"
            icon="message"
            tone="info"
        />
    </div>

    <div class="grid grid-2-1" style="margin-bottom:16px">
        <div class="card">
            <div class="card-head">
                <div>
                    <h3>@icon('trend') Registration &amp; revenue trend</h3>
                    <p>Collected against expected across the last six months</p>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-legend" style="margin-bottom:6px">
                    <span class="legend-item">
                        <span class="legend-swatch" style="background:var(--primary)"></span> Collected
                    </span>
                    <span class="legend-item">
                        <span class="legend-swatch" style="background:var(--text-3)"></span> Expected
                    </span>
                </div>
                <x-admin.area-chart
                    :values="$revenue['collected']"
                    :labels="$revenue['labels']"
                    label="Payments collected over the last six months"
                />
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h3>@icon('filter') Action queue</h3>
                <a class="btn btn-sm btn-ghost" href="{{ route('admin.registrations.index', ['status' => 'submitted']) }}">View all</a>
            </div>
            <div class="queue">
                @forelse ($attention as $item)
                    <a class="queue-item" href="{{ route('admin.registrations.show', $item) }}" style="text-decoration:none;color:inherit">
                        <div class="queue-ico {{ \App\Support\Badge::tone($item->status) }}">@icon('file')</div>
                        <div class="queue-text">
                            <b>{{ $item->group_name }}</b>
                            <span>{{ $item->code }} · {{ $item->members_count }} performers</span>
                        </div>
                        <div class="queue-count">{{ $item->members_count }}</div>
                    </a>
                @empty
                    <div class="queue-item">
                        <div class="queue-ico amber">@icon('check')</div>
                        <div class="queue-text"><b>Queue is clear</b><span>Nothing needs attention right now</span></div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-2" style="margin-bottom:16px">
        <div class="card">
            <div class="card-head">
                <div>
                    <h3>@icon('filter') Season funnel</h3>
                    <p>Submitted through to confirmed</p>
                </div>
            </div>
            <div class="card-body">
                <div class="funnel">
                    @foreach ($statusBreakdown as $row)
                        <div class="funnel-row">
                            <span class="funnel-label">{{ $row['status']->label() }}</span>
                            <div class="funnel-track">
                                <div class="funnel-fill" style="width:{{ $row['pct'] }}%">{{ $row['pct'] }}%</div>
                            </div>
                            <span class="funnel-val">{{ number_format($row['count']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div>
                    <h3>@icon('card') Collection summary</h3>
                    <p>{{ $season->currency }} · current season</p>
                </div>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Invoiced</th>
                            <td class="num">{{ number_format((float) $season->invoices()->sum('amount'), 0) }}</td>
                        </tr>
                        <tr>
                            <th>Collected</th>
                            <td class="num">{{ number_format((float) $season->invoices()->sum('amount_paid'), 0) }}</td>
                        </tr>
                        <tr>
                            <th>Waived</th>
                            <td class="num">{{ number_format((float) $season->invoices()->sum('amount_waived'), 0) }}</td>
                        </tr>
                        <tr>
                            <th>Balance</th>
                            <td class="num"><b>{{ number_format((float) $season->invoices()->sum('amount') - (float) $season->invoices()->sum('amount_paid') - (float) $season->invoices()->sum('amount_waived'), 0) }}</b></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-2-1">
        <div class="card">
            <div class="card-head">
                <h3>@icon('clock') Recent activity</h3>
                <a class="btn btn-sm btn-ghost" href="{{ route('admin.audit.index') }}">Open audit log</a>
            </div>
            <div class="feed">
                @forelse ($recentAudit as $entry)
                    <div class="feed-item">
                        <div class="feed-dot {{ \App\Support\Badge::tone($entry->action) }}">@icon('zap')</div>
                        <div class="feed-body">
                            <p><b>{{ $entry->actor_label ?? $entry->actor?->name ?? 'System' }}</b> · {{ $entry->action }}</p>
                            <div class="feed-time">
                                {{ $entry->created_at->diffForHumans() }}{{ $entry->detail ? ' · '.$entry->detail : '' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="feed-item">
                        <div class="feed-dot green">@icon('check')</div>
                        <div class="feed-body">
                            <p>No activity recorded yet.</p>
                            <div class="feed-time">Actions will appear here as staff work.</div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h3>@icon('message') Conversations needing a reply</h3>
            </div>
            <div class="feed">
                @forelse ($openThreads as $thread)
                    <a class="feed-item" href="{{ route('admin.communications.show', $thread) }}" style="text-decoration:none;color:inherit">
                        <div class="feed-dot blue">@icon('message')</div>
                        <div class="feed-body">
                            <p><b>{{ $thread->subject }}</b></p>
                            <div class="feed-time">
                                {{ $thread->participant_name ?? $thread->sender_name }} ·
                                {{ $thread->last_message_at?->diffForHumans() ?? 'no messages' }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="feed-item">
                        <div class="feed-dot green">@icon('check')</div>
                        <div class="feed-body">
                            <p>Inbox is clear.</p>
                            <div class="feed-time">Every conversation has a reply.</div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
