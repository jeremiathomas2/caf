@extends('admin.layouts.app')

@section('title', 'Audit trail')

@section('content')
    <x-admin.page-head
        title="Audit trail"
        :subtitle="number_format($entries->total()).' of '.number_format($total).' recorded events'"
    />

    <div class="card">
        <form method="GET" class="toolbar">
            <div class="input-wrap">
                @icon('search')
                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Search action, record or person…"
                    aria-label="Search the audit trail"
                    data-filter-for="audit-table"
                >
            </div>

            <div class="chips">
                <a class="chip @checked($category === '')"
                   href="{{ route('admin.audit.index', request()->except(['category', 'page'])) }}">All</a>

                @foreach ($categories as $option)
                    <a
                        class="chip @checked($category === $option)"
                        href="{{ route('admin.audit.index', array_merge(request()->except(['category', 'page']), ['category' => $option])) }}"
                    >{{ Str::headline($option) }}</a>
                @endforeach
            </div>
        </form>

        <div class="table-wrap">
            <table id="audit-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Action</th>
                        <th>Actor</th>
                        <th>Record</th>
                        <th>Detail</th>
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr data-filter-row>
                            <td>
                                <div class="cell-strong">{{ $entry->created_at->format('j M Y') }}</div>
                                <div class="cell-sub">{{ $entry->created_at->format('H:i') }}</div>
                            </td>
                            <td>
                                <x-admin.badge :value="$entry->action" :tone="$entry->tone()" :label="Str::headline($entry->action)" />
                            </td>
                            <td>{{ $entry->actorName() }}</td>
                            <td>
                                @if ($entry->entity_label)
                                    <div class="cell-strong">{{ $entry->entity_label }}</div>
                                    <div class="cell-sub">{{ Str::headline((string) $entry->entity_type) }}</div>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td><span class="muted">{{ Str::limit((string) $entry->detail, 70) ?: '—' }}</span></td>
                            <td>
                                <div class="cell-sub">{{ $entry->ip_address ?: '—' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:0">
                                <div class="empty" data-filter-empty>
                                    @icon('shield')
                                    <b>Nothing recorded yet</b>
                                    <p>Every change staff make is written here as it happens.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($entries->hasPages())
            <div class="pager">
                <span>Showing <b>{{ $entries->firstItem() }}–{{ $entries->lastItem() }}</b> of <b>{{ $entries->total() }}</b></span>
                <div class="pager-btns">
                    @if ($entries->onFirstPage())
                        <span class="pg" aria-disabled="true">‹</span>
                    @else
                        <a class="pg" href="{{ $entries->previousPageUrl() }}" rel="prev">‹</a>
                    @endif

                    @foreach ($entries->getUrlRange(1, $entries->lastPage()) as $page => $url)
                        <a class="pg @checked($page === $entries->currentPage())" href="{{ $url }}">{{ $page }}</a>
                    @endforeach

                    @if ($entries->hasMorePages())
                        <a class="pg" href="{{ $entries->nextPageUrl() }}" rel="next">›</a>
                    @else
                        <span class="pg" aria-disabled="true">›</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
