@extends('admin.layouts.app')

@section('title', 'Content & Programme')

@section('content')
    <x-admin.page-head
        title="Content &amp; Programme"
        subtitle="Pages &middot; News &middot; Gallery &middot; Programme &middot; Sponsors &middot; Judges"
    >
        @can('content.manage')
            <button type="button" class="btn btn-primary" data-modal-open="new-page-modal">@icon('plus') New page</button>
        @endcan
    </x-admin.page-head>

    <div class="card" style="margin-bottom:16px">
        <div class="card-head">
            <div>
                <h3>@icon('calendar') Programme schedule</h3>
                <p>
                    @if ($clashes !== [])
                        Conflict detection active &mdash; {{ count($clashes) }} clash{{ count($clashes) === 1 ? '' : 'es' }} detected
                    @else
                        No scheduling clashes detected
                    @endif
                </p>
            </div>

            <div class="chips">
                @forelse ($days as $option)
                    <a
                        class="chip @checked($day === $option)"
                        href="{{ route('admin.content.index', ['day' => $option]) }}"
                    >{{ \Carbon\Carbon::parse($option)->format('D j M') }}</a>
                @empty
                    <span class="badge gray">No days scheduled</span>
                @endforelse
            </div>
        </div>

        <div class="card-body">
            @forelse ($daySlots as $stage => $stageSlots)
                <div class="day-col" style="margin-bottom:14px">
                    <div class="day-head">
                        <b>{{ $stage }}</b>
                        @if ($stageSlots->first()->stage_location)
                            <small class="cell-sub">{{ $stageSlots->first()->stage_location }}</small>
                        @endif
                    </div>

                    @foreach ($stageSlots as $slot)
                        @php
                            $slotWhen = $slot->event_date->toDateString().' '.$slot->starts_at;
                            $clashed = collect($clashes)->contains(
                                fn (array $clash): bool => $clash['when'] === $slotWhen
                                    && in_array($stage, $clash['stages'], true),
                            );
                        @endphp

                        <div class="slot @checked($clashed) conflict">
                            <div class="slot-time">{{ \Carbon\Carbon::parse($slot->starts_at)->format('H:i') }}</div>
                            <div class="slot-body">
                                <b>{{ $slot->title }}</b>
                                <span>
                                    {{ $slot->registration?->group_name ?? $slot->description ?? $season->venue }}
                                    @if ($slot->registration?->category)
                                        <small class="cell-sub">{{ Str::headline($slot->registration->category).' · '.$slot->registration->members_count.' members' }}</small>
                                    @endif
                                    @if ($clashed)
                                        &middot; Warning: double-booked with {{ collect($clashes)->firstWhere('when', $slotWhen)['stages'][1] ?? 'another stage' }}
                                    @endif
                                </span>
                            </div>
                            <x-admin.badge :value="$slot->status" />
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="empty">
                    @icon('calendar')
                    <b>Nothing scheduled for this day</b>
                    <p>Programme slots appear here once they are added for {{ \Carbon\Carbon::parse($day)->format('j M Y') }}.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="grid grid-2-1">
        <div class="card">
            <div class="card-head"><div><h3>@icon('file') Pages &amp; news</h3></div></div>

            <div class="table-wrap">
                <table style="min-width:auto">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pages as $page)
                            <tr>
                                <td>
                                    <div class="cell-strong">{{ $page->title }}</div>
                                    @if ($page->url())
                                        <div class="cell-sub">/{{ $page->url() }}</div>
                                    @endif
                                </td>
                                <td><span class="badge outline">{{ Str::headline($page->type->value) }}</span></td>
                                <td><x-admin.badge :value="$page->status" :dot="false" /></td>
                                <td style="color:var(--text-2)">{{ $page->updated_at->diffForHumans() }}</td>
                                <td>
                                    @can('content.manage')
                                        <form method="POST" action="{{ route('admin.content.publish', $page) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $page->status === \App\Enums\PublishStatus::Published ? \App\Enums\PublishStatus::Draft : \App\Enums\PublishStatus::Published }}">
                                            <button type="submit" class="btn btn-sm btn-ghost" aria-label="Toggle publication of {{ $page->title }}">
                                                @icon('edit')
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding:0">
                                    <div class="empty">
                                        @icon('file')
                                        <b>No content yet</b>
                                        <p>Create the first page to get the public site started.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><div><h3>@icon('star') Sponsors &amp; judges</h3></div></div>
            <div class="card-body" style="padding:0">
                @foreach ($sponsors as $sponsor)
                    <div class="queue-item" style="cursor:default">
                        <x-admin.avatar :name="$sponsor->name" variant="alt" class="mini-avatar" />
                        <div class="queue-text">
                            <b>{{ $sponsor->name }}</b>
                            <span>{{ Str::headline($sponsor->tier) }} sponsor</span>
                        </div>
                    </div>
                @endforeach

                @foreach ($judges as $judge)
                    <div class="queue-item" style="cursor:default">
                        <x-admin.avatar :name="$judge->name" variant="alt2" class="mini-avatar" />
                        <div class="queue-text">
                            <b>{{ $judge->name }}</b>
                            <span>{{ $judge->role_title }}</span>
                        </div>
                    </div>
                @endforeach

                @if ($sponsors->isEmpty() && $judges->isEmpty())
                    <div class="empty">
                        @icon('star')
                        <b>No partners or judges</b>
                        <p>Sponsors and the judging panel are listed here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @can('content.manage')
        <div class="modal" id="new-page-modal" role="dialog" aria-modal="true" aria-labelledby="new-page-title">
            <div class="modal-box">
                <div class="modal-head">
                    <div>
                        <h3 id="new-page-title">@icon('plus') New page</h3>
                        <p>Pages are served from the public site once published.</p>
                    </div>
                    <button type="button" class="icon-btn" data-modal-close aria-label="Close">@icon('x')</button>
                </div>
                <form method="POST" action="{{ route('admin.content.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="grid grid-2">
                            <label>
                                <span class="lbl">Title</span>
                                <input type="text" name="title" required maxlength="255">
                            </label>
                            <label>
                                <span class="lbl">Slug</span>
                                <input type="text" name="slug" maxlength="255" placeholder="generated-from-title">
                            </label>
                            <label>
                                <span class="lbl">Type</span>
                                <select name="type" required>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                <span class="lbl">Status</span>
                                <select name="status" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label style="grid-column:1/-1">
                                <span class="lbl">Excerpt</span>
                                <textarea name="excerpt" rows="2" maxlength="500"></textarea>
                            </label>
                            <label style="grid-column:1/-1">
                                <span class="lbl">Body</span>
                                <textarea name="body" rows="6" maxlength="20000"></textarea>
                            </label>
                        </div>
                    </div>
                    <div class="modal-foot">
                        <label class="check-label">
                            <input type="checkbox" name="is_in_footer" value="1"> Show in footer
                        </label>
                        <button type="button" class="btn" data-modal-close>Cancel</button>
                        <button type="submit" class="btn btn-primary">@icon('check') Create page</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endsection
