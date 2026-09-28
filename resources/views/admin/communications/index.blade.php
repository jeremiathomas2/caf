@extends('admin.layouts.app')

@section('title', 'Communications')

@section('content')
    <x-admin.page-head
        title="Communications"
        :subtitle="$needsReply.' conversations waiting on a reply · Season '.$season->number"
    />

    <div class="card">
        <form method="GET" class="toolbar">
            <div class="input-wrap">
                @icon('search')
                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Search subject or contact…"
                    aria-label="Search conversations"
                    data-filter-for="threads-table"
                >
            </div>

            <div class="chips">
                <a class="chip @checked($status === null)"
                   href="{{ route('admin.communications.index', request()->except(['status', 'page'])) }}">All</a>

                @foreach ($statuses as $option)
                    <a
                        class="chip @checked($status === $option)"
                        href="{{ route('admin.communications.index', array_merge(request()->except(['status', 'page']), ['status' => $option->value])) }}"
                    >{{ $option->label() }}</a>
                @endforeach
            </div>
        </form>

        <div class="table-wrap">
            <table id="threads-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Contact</th>
                        <th>Channel</th>
                        <th>Status</th>
                        <th>Owner</th>
                        <th>Last message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($threads as $thread)
                        <tr data-filter-row>
                            <td>
                                <a class="cell-strong" href="{{ route('admin.communications.show', $thread) }}">
                                    {{ $thread->subject }}
                                </a>
                                <div class="thread-preview">{{ $thread->preview() }}</div>
                                @if ($thread->registration)
                                    <div class="cell-sub">{{ $thread->registration->code }} · {{ $thread->registration->group_name }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="cell-strong">{{ $thread->contact_name }}</div>
                                <div class="cell-sub">{{ $thread->contact_email ?: '—' }}</div>
                            </td>
                            <td><span class="badge outline">{{ $thread->channel?->label() ?? $thread->channel }}</span></td>
                            <td><x-admin.badge :value="$thread->status" /></td>
                            <td>{{ $thread->assignedTo?->name ?? 'Unassigned' }}</td>
                            <td>
                                <div class="cell-sub">{{ $thread->last_message_at?->format('j M Y H:i') ?? '—' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:0">
                                <div class="empty" data-filter-empty>
                                    @icon('mail')
                                    <b>No conversations yet</b>
                                    <p>Messages from groups and visitors land here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($threads->hasPages())
            <div class="pager">
                <span>Showing <b>{{ $threads->firstItem() }}–{{ $threads->lastItem() }}</b> of <b>{{ $threads->total() }}</b></span>
                <div class="pager-btns">
                    @if ($threads->onFirstPage())
                        <span class="pg" aria-disabled="true">‹</span>
                    @else
                        <a class="pg" href="{{ $threads->previousPageUrl() }}" rel="prev">‹</a>
                    @endif

                    @foreach ($threads->getUrlRange(1, $threads->lastPage()) as $page => $url)
                        <a class="pg @checked($page === $threads->currentPage())" href="{{ $url }}">{{ $page }}</a>
                    @endforeach

                    @if ($threads->hasMorePages())
                        <a class="pg" href="{{ $threads->nextPageUrl() }}" rel="next">›</a>
                    @else
                        <span class="pg" aria-disabled="true">›</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
