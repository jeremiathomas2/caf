@extends('admin.layouts.app')

@section('title', 'Payments & Invoicing')

@section('content')
    <x-admin.page-head
        title="Payments &amp; Invoicing"
        :subtitle="'Season '.$season->number.' · '.number_format($totals['invoiceCount']).' invoices issued · '.($totals['openCount'] > 0 ? number_format($totals['openCount']).' still open' : 'everything settled')"
    >
        @can('payments.manage')
            <form method="POST" action="{{ route('admin.payments.reconcile') }}">
                @csrf
                <button type="submit" class="btn">@icon('refresh') Run reconciliation</button>
            </form>
        @endcan
    </x-admin.page-head>

    <div class="grid grid-kpi" style="margin-bottom:16px">
        <x-admin.kpi
            label="Collected"
            :value="number_format($totals['collected'], 0)"
            :foot="number_format($totals['paymentCount']).' transactions'"
            icon="card"
            tone="ok"
        />
        <x-admin.kpi
            label="Outstanding"
            :value="number_format($totals['outstanding'], 0)"
            :foot="number_format($totals['openCount']).' invoices open'"
            icon="clock"
            tone="warn"
        />
        <x-admin.kpi
            label="Refunded"
            :value="number_format($totals['refunded'], 0)"
            :foot="number_format($totals['refundCount']).' refunds approved'"
            icon="refresh"
            tone="danger"
        />
        <x-admin.kpi
            label="Waived"
            :value="number_format($totals['waived'], 0)"
            :foot="number_format($totals['waiverCount']).' waivers applied'"
            icon="tag"
        />
    </div>

    <div class="grid grid-2" style="margin-bottom:16px">
        <div class="card">
            <div class="card-head"><div><h3>@icon('refresh') Nightly reconciliation</h3></div></div>
            <div class="card-body">
                <div class="stat-row">
                    <span class="lbl">Provider settlements matched</span>
                    <span class="val" style="color:var(--ok)">{{ number_format($totals['paymentCount']) }}</span>
                </div>
                <div class="stat-row">
                    <span class="lbl">Unmatched &mdash; flagged for review</span>
                    <span class="val" style="color:var(--warn)">{{ number_format($overdueCount) }}</span>
                </div>
                <div class="stat-row">
                    <span class="lbl">Ledger entries this season</span>
                    <span class="val">{{ number_format($totals['invoiceCount'] + $totals['paymentCount']) }}</span>
                </div>
                <div class="stat-row">
                    <span class="lbl">Last run</span>
                    <span class="val">{{ $lastRun ?? 'Not run yet' }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><div><h3>@icon('globe') Currency snapshot</h3></div></div>
            <div class="card-body">
                <div class="stat-row"><span class="lbl">Primary currency</span><span class="val">{{ $season->currency }}</span></div>
                <div class="stat-row"><span class="lbl">Secondary</span><span class="val">{{ $season->secondary_currency }}</span></div>
                <div class="stat-row">
                    <span class="lbl">FX rate used (1 {{ $season->secondary_currency }})</span>
                    <span class="val">{{ $season->usd_fx_rate ? number_format((float) $season->usd_fx_rate, 2).' '.$season->currency : 'Not set' }}</span>
                </div>
                <div class="stat-row">
                    <span class="lbl">Rounding</span>
                    <span class="val">Nearest {{ number_format($season->rounding_increment) }} {{ $season->currency }}</span>
                </div>
            </div>
        </div>
    </div>

    @can('payments.manage')
        <div class="card" style="margin-bottom:16px">
            <div class="card-head">
                <div><h3>@icon('plus') Record a payment</h3><p>Appends a payment row and re-derives the invoice and registration states.</p></div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.payments.store') }}" class="grid grid-2">
                    @csrf
                    <label>
                        <span class="lbl">Invoice</span>
                        <select name="invoice_id" required>
                            @foreach ($invoices as $invoice)
                                <option value="{{ $invoice->id }}">
                                    {{ $invoice->number }} &middot; {{ $invoice->registration?->group_name }} &middot; balance {{ number_format($invoice->balance()) }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="lbl">Amount ({{ $season->currency }})</span>
                        <input type="number" name="amount" step="0.01" min="1" required>
                    </label>
                    <label>
                        <span class="lbl">Method</span>
                        <select name="method" required>
                            @foreach ($methods as $method)
                                <option value="{{ $method->value }}">{{ $method->label() }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="lbl">Reference</span>
                        <input type="text" name="reference" maxlength="64" required placeholder="MPESA-00000">
                    </label>
                    <div style="display:flex;align-items:flex-end;gap:10px">
                        <button type="submit" class="btn btn-primary">@icon('check') Record payment</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-head">
            <div>
                <h3>@icon('card') Recent invoices</h3>
                <p>Append-only ledger &middot; corrections via adjustment entries</p>
            </div>
            <a class="btn btn-sm" href="{{ route('admin.payments.export', request()->query()) }}">@icon('download') Export CSV</a>
        </div>

        <form method="GET" class="toolbar">
            <div class="input-wrap">
                @icon('search')
                <input type="search" name="q" value="{{ $search }}" placeholder="Search invoice or group…" aria-label="Search invoices">
            </div>
            <div class="chips">
                <a class="chip @checked($status === null)" href="{{ route('admin.payments.index', request()->except(['status', 'page'])) }}">All</a>
                @foreach ($statuses as $option)
                    <a
                        class="chip @checked($status === $option)"
                        href="{{ route('admin.payments.index', array_merge(request()->except(['status', 'page']), ['status' => $option->value])) }}"
                    >{{ $option->label() }}</a>
                @endforeach
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Group</th>
                        <th>Method</th>
                        <th class="num">Amount</th>
                        <th class="num">Paid</th>
                        <th class="num">Balance</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td><div class="cell-strong">{{ $invoice->number }}</div></td>
                            <td>{{ $invoice->registration?->group_name ?? '—' }}</td>
                            <td>
                                @if ($invoice->method)
                                    <span class="badge outline">{{ \App\Support\Badge::label($invoice->method) }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="num">{{ number_format((float) $invoice->amount) }}</td>
                            <td class="num">{{ number_format((float) $invoice->amount_paid) }}</td>
                            <td class="num">
                                <b style="color:{{ $invoice->balance() > 0 ? 'var(--danger)' : 'var(--text-3)' }}">
                                    {{ number_format($invoice->balance()) }}
                                </b>
                            </td>
                            <td><x-admin.badge :value="$invoice->status" /></td>
                            <td style="color:var(--text-2)">{{ $invoice->issued_at?->format('j M Y') ?? '—' }}</td>
                            <td>
                                <a
                                    class="btn btn-sm btn-ghost"
                                    href="{{ route('admin.registrations.show', $invoice->registration_id) }}"
                                    aria-label="View {{ $invoice->number }}"
                                >@icon('eye')</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding:0">
                                <div class="empty">
                                    @icon('card')
                                    <b>No invoices yet</b>
                                    <p>Invoices appear here as registrations are confirmed.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="pager">
                <div class="pager-btns">
                    @foreach ($invoices->getUrlRange(1, $invoices->lastPage()) as $page => $url)
                        <a class="pg @checked($invoices->currentPage() === $page)" href="{{ $url }}">{{ $page }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
