@extends('admin.layouts.app')

@section('title', 'Analytics & Reporting')

@section('content')
    <x-admin.page-head
        title="Analytics &amp; Reporting"
        subtitle="Growth &middot; Review &middot; Finance &middot; Communications &middot; Engagement &middot; Impact"
    >
        <form method="POST" action="{{ route('admin.analytics.impact') }}">
            @csrf
            <button type="submit" class="btn btn-primary">@icon('trend') Sponsor impact report</button>
        </form>
    </x-admin.page-head>

    <div class="grid grid-kpi" style="margin-bottom:16px">
        <x-admin.kpi
            label="Registration conversion"
            :value="number_format($metrics['registrationConversion'], 1).'%'"
            foot="started &rarr; approved"
            icon="trend"
        />
        <x-admin.kpi
            label="Payment conversion"
            :value="number_format($metrics['paymentConversion'], 1).'%'"
            foot="approved &rarr; paid"
            icon="card"
            tone="ok"
        />
        <x-admin.kpi
            label="Outstanding balance"
            :value="number_format($metrics['outstanding'], 0)"
            :foot="number_format($metrics['openInvoices']).' invoices'"
            icon="alert"
            tone="warn"
        />
        <x-admin.kpi
            label="Threads resolved"
            :value="number_format($metrics['threadsResolved']).'/'.number_format($metrics['threads'])"
            foot="all channels"
            icon="send"
            tone="info"
        />
    </div>

    <div class="grid grid-2" style="margin-bottom:16px">
        <div class="card">
            <div class="card-head">
                <div>
                    <h3>@icon('trend') Cumulative submissions</h3>
                    <p>Season to date, per four-week interval</p>
                </div>
            </div>
            <div class="card-body">
                @if (count($growth) > 1)
                    <div class="chart-wrap">
                        <x-admin.area-chart
                            :values="array_column($growth, 'value')"
                            :labels="array_column($growth, 'label')"
                        />
                    </div>
                @else
                    <div class="empty">
                        @icon('chart')
                        <b>Not enough history yet</b>
                        <p>The trend line needs at least two intervals of submissions.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div>
                    <h3>@icon('globe') Participation by country</h3>
                    <p>Approved registrations</p>
                </div>
            </div>
            <div class="card-body">
                @forelse ($countries as $row)
                    <div class="prog-row">
                        <div class="name">{{ $row['country'] }}</div>
                        <div class="prog"><div class="prog-fill" style="width:{{ $row['percent'] }}%"></div></div>
                        <div class="val">{{ number_format($row['value']) }}</div>
                    </div>
                @empty
                    <div class="empty">
                        @icon('globe')
                        <b>No approved registrations</b>
                        <p>Country mix appears once groups are approved.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-2-1" style="margin-bottom:16px">
        <div class="card">
            <div class="card-head">
                <div>
                    <h3>@icon('filter') Registration funnel</h3>
                    <p>Where applications are sitting right now</p>
                </div>
            </div>
            <div class="card-body">
                @php $peak = max(1, (int) max(array_column($funnel, 'value') ?: [1])); @endphp
                @foreach ($funnel as $row)
                    <div class="prog-row">
                        <div class="name">{{ $row['label'] }}</div>
                        <div class="prog">
                            <div class="prog-fill" style="width:{{ $row['percent'] }}%;background:{{ $row['gradient'] }}"></div>
                        </div>
                        <div class="val">{{ number_format($row['value']) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-head"><div><h3>@icon('users') Group categories</h3></div></div>
            <div class="card-body">
                @forelse ($categories as $row)
                    <div class="stat-row">
                        <span class="lbl">{{ $row['category'] }}</span>
                        <span class="val">{{ number_format($row['value']) }}</span>
                    </div>
                @empty
                    <div class="empty">
                        @icon('users')
                        <b>Nothing approved yet</b>
                        <p>Category counts appear after the first approvals.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <div class="card-head"><div><h3>@icon('card') Payment states</h3></div></div>
            <div class="card-body">
                <div class="stat-row">
                    <span class="lbl">Settled (paid or waived)</span>
                    <span class="val" style="color:var(--ok)">{{ number_format($payments['settledPercent'], 1) }}%</span>
                </div>
                @foreach ($payments['states'] as $row)
                    <div class="stat-row">
                        <span class="lbl">{{ Str::headline($row['state']) }}</span>
                        <span class="val">{{ number_format($row['count']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-head"><div><h3>@icon('message') Payment methods</h3></div></div>
            <div class="card-body">
                @forelse ($payments['methods'] as $row)
                    <div class="stat-row">
                        <span class="lbl">{{ $row['method'] }}</span>
                        <span class="val">
                            {{ number_format($row['count']) }} &middot; {{ number_format($row['value'], 0) }}
                        </span>
                    </div>
                @empty
                    <div class="empty">
                        @icon('card')
                        <b>No payments captured</b>
                        <p>Method mix appears once a payment is recorded.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
