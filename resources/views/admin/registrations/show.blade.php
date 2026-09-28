@extends('admin.layouts.app')

@section('title', $registration->group_name)

@section('content')
    <x-admin.page-head
        :title="$registration->group_name"
        :subtitle="trim($registration->code.' · '.$registration->role_type?->label().' · '.($registration->season->name ?? ''))"
    >
        <a class="btn btn-ghost" href="{{ route('admin.registrations.index') }}">@icon('chevron') All registrations</a>
        @can('registrations.manage')
            @if ($registration->isEditable())
                <a class="btn" href="{{ route('admin.registrations.edit', $registration) }}">@icon('edit') Edit</a>
            @endif
        @endcan
    </x-admin.page-head>

    @if ($errors->any())
        <div class="alert danger" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-kpi" style="margin-bottom:16px">
        <x-admin.kpi label="Performers" :value="$registration->members_count" icon="users" />
        <x-admin.kpi label="Status" :value="$registration->status->label()" icon="file" :tone="\App\Support\Badge::tone($registration->status)" />
        <x-admin.kpi
            label="Payment"
            :value="$registration->payment_status->label()"
            icon="card"
            :tone="\App\Support\Badge::tone($registration->payment_status)"
        />
        <x-admin.kpi
            label="Outstanding"
            :value="number_format($registration->outstandingBalance(), 0).' '.($registration->season->currency ?? '')"
            icon="alert"
            :tone="$registration->outstandingBalance() > 0 ? 'warn' : 'ok'"
        />
    </div>

    <div class="grid grid-2-1">
        <div class="stack">
            <div class="card">
                <div class="card-head"><h3>@icon('file') Registration</h3></div>
                <div class="card-body">
                    <dl class="kv">
                        <div><dt>Code</dt><dd class="mono">{{ $registration->code }}</dd></div>
                        <div><dt>Category</dt><dd>{{ $registration->role_type?->label() ?? $registration->category }}</dd></div>
                        <div><dt>Season</dt><dd>{{ $registration->season->name ?? '—' }}</dd></div>
                        <div><dt>Location</dt><dd>{{ trim(($registration->city ?? '').', '.($registration->country ?? '')) ?: '—' }}</dd></div>
                        <div><dt>Contact</dt><dd>{{ $registration->contact_name }}<br><a href="mailto:{{ $registration->contact_email }}">{{ $registration->contact_email }}</a></dd></div>
                        <div><dt>Phone</dt><dd>{{ $registration->contact_phone ?: '—' }}</dd></div>
                        <div>
                            <dt>Performance link</dt>
                            <dd>
                                @if ($registration->performance_link)
                                    <a href="{{ $registration->performance_link }}" target="_blank" rel="noopener">{{ $registration->performance_link }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div><dt>Reviewer</dt><dd>{{ $registration->reviewer?->name ?? 'Unassigned' }}</dd></div>
                        <div><dt>Submitted</dt><dd>{{ $registration->submitted_at?->format('j M Y, H:i') ?? '—' }}</dd></div>
                        <div><dt>Source</dt><dd>{{ ucfirst((string) $registration->source) }}</dd></div>
                        <div>
                            <dt>Tags</dt>
                            <dd>
                                @forelse ($registration->tags ?? [] as $tag)
                                    <span class="badge outline">{{ $tag }}</span>
                                @empty
                                    —
                                @endforelse
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>@icon('users') Members</h3>
                        <p>{{ $registration->members->count() }} listed performers.</p>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Name</th><th>Part</th><th>Email</th><th>Phone</th><th>Lead</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($registration->members as $member)
                                <tr>
                                    <td class="muted">{{ $member->sort_order + 1 }}</td>
                                    <td class="cell-strong">{{ $member->name }}</td>
                                    <td>{{ $member->part ?: '—' }}</td>
                                    <td>{{ $member->email ?: '—' }}</td>
                                    <td>{{ $member->phone ?: '—' }}</td>
                                    <td>@if ($member->is_lead) @icon('check') @else <span class="muted">—</span> @endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="muted center">No members recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($invoice)
                <div class="card">
                    <div class="card-head">
                        <div>
                            <h3>@icon('card') Invoice {{ $invoice->number }}</h3>
                            <p>{{ $invoice->currency }} · issued {{ $invoice->issued_at?->format('j M Y') }}</p>
                        </div>
                        <x-admin.badge :value="$invoice->status" />
                    </div>
                    <div class="card-body">
                        <dl class="kv">
                            <div><dt>Amount</dt><dd>{{ number_format((float) $invoice->amount, 2) }} {{ $invoice->currency }}</dd></div>
                            <div><dt>Paid</dt><dd>{{ number_format((float) $invoice->amount_paid, 2) }} {{ $invoice->currency }}</dd></div>
                            <div><dt>Waived</dt><dd>{{ number_format((float) $invoice->amount_waived, 2) }} {{ $invoice->currency }}</dd></div>
                            <div><dt>Balance</dt><dd><b>{{ number_format($invoice->balance(), 2) }} {{ $invoice->currency }}</b></dd></div>
                            <div><dt>Due</dt><dd>{{ $invoice->due_at?->format('j M Y') ?? '—' }} @if ($invoice->isOverdue()) <x-admin.badge value="Overdue" /> @endif</dd></div>
                        </dl>

                        @if ($invoice->payments->isNotEmpty())
                            <div class="table-wrap" style="margin-top:14px">
                                <table>
                                    <thead><tr><th>Paid at</th><th>Method</th><th>Reference</th><th class="num">Amount</th></tr></thead>
                                    <tbody>
                                        @foreach ($invoice->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->paid_at?->format('j M Y') }}</td>
                                                <td>{{ $payment->method?->label() }}</td>
                                                <td class="mono">{{ $payment->reference ?: '—' }}</td>
                                                <td class="num">{{ number_format((float) $payment->amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-head"><h3>@icon('clock') History</h3></div>
                <div class="card-body">
                    <div class="feed">
                        @forelse ($registration->statusEvents as $event)
                            <div class="feed-item">
                                <div class="feed-dot {{ \App\Support\Badge::tone($event->to_status) }}">@icon('refresh')</div>
                                <div class="feed-body">
                                    <p>
                                        <b>{{ $event->actor_label ?? $event->actor?->name ?? 'System' }}</b>
                                        moved this to
                                        <x-admin.badge :value="$event->to_status" />
                                    </p>
                                    <div class="feed-time">
                                        {{ $event->created_at->diffForHumans() }}
                                        @if ($event->from_status) · from {{ $event->from_status }} @endif
                                        @if ($event->note) · “{{ $event->note }}” @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="muted">No history recorded.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="stack">
            @can('registrations.manage')
                @if ($nextStatuses !== [])
                    <div class="card">
                        <div class="card-head"><h3>@icon('check') Move status</h3></div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.registrations.status', $registration) }}"
                                  data-confirm="Change the status of {{ $registration->code }}?">
                                @csrf
                                <div class="field">
                                    <label for="status">New status</label>
                                    <select id="status" name="status" required>
                                        @foreach ($nextStatuses as $option)
                                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="note">Note (optional)</label>
                                    <input id="note" type="text" name="note" placeholder="Why is it changing?">
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Update status</button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-head"><h3>@icon('lock') Status locked</h3></div>
                        <div class="card-body">
                            <p class="muted">A {{ $registration->status->label() }} registration cannot move to another status.</p>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-head"><h3>@icon('users') Reviewer</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.registrations.assign', $registration) }}">
                            @csrf
                            <div class="field">
                                <label for="reviewer_id">Assigned to</label>
                                <select id="reviewer_id" name="reviewer_id">
                                    <option value="">Unassigned</option>
                                    @foreach ($reviewers as $reviewer)
                                        <option value="{{ $reviewer->getKey() }}" @selected($registration->reviewer_id === $reviewer->getKey())>
                                            {{ $reviewer->name }} — {{ $reviewer->roleLabel() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-block">Save</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head"><h3>@icon('tag') Tags</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.registrations.tag', $registration) }}">
                            @csrf
                            <div class="field">
                                <label for="tags">Comma separated</label>
                                <input id="tags" type="text" name="tags"
                                       value="{{ implode(',', $registration->tags ?? []) }}"
                                       placeholder="vocal, needs-review">
                            </div>
                            <p class="field-hint">Up to ten tags, separated by commas. Leave empty to clear.</p>
                            <button type="submit" class="btn btn-block">Save tags</button>
                        </form>
                    </div>
                </div>
            @endcan

            <div class="card">
                <div class="card-head"><h3>@icon('calendar') Public listing</h3></div>
                <div class="card-body">
                    @if ($registration->is_public)
                        <p class="muted">This group appears on the public groups page.</p>
                    @else
                        <p class="muted">This group is hidden from the public site.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
