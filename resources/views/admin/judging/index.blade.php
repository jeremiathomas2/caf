@extends('admin.layouts.app')

@section('title', 'Judging & Review')

@section('content')
    <x-admin.page-head
        title="Judging & Review"
        :subtitle="($round?->is_blind ? 'Blind mode active' : 'Blind mode off')
            .' · '.($round ? $round->status->label().' round '.$round->sequence.' open' : 'No rounds yet')
            .' · '.number_format($assignedCount).' applications assigned'"
    >
        @can('judging.manage')
            <form method="POST" action="{{ route('admin.judging.nudge') }}">
                @csrf
                <button type="submit" class="btn">@icon('bell') Nudge judges</button>
            </form>
        @endcan

        @if ($round && $round->status !== \App\Enums\RoundStatus::Published)
            @can('judging.manage')
                <form method="POST" action="{{ route('admin.judging.publish', $round) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">@icon('check') Publish results</button>
                </form>
            @endcan
        @endif
    </x-admin.page-head>

    <div class="grid grid-2-1" style="margin-bottom:16px">
        <div class="card">
            <div class="card-head">
                <div>
                    <h3>@icon('star') Review progress by judge</h3>
                    <p>
                        {{ $round ? $round->status->label().' · '.($round->closes_at ? 'closes '.$round->closes_at->diffForHumans() : 'no deadline') : 'No round selected' }}
                    </p>
                </div>
                @if ($round?->is_blind)
                    <span class="badge blue"><span class="dot"></span>Blind mode on</span>
                @endif
            </div>

            <div class="card-body">
                @forelse ($progress as $row)
                    @php
                        $percent = $row['total'] > 0 ? (int) round($row['done'] / $row['total'] * 100) : 0;
                        $fill = $percent >= 90 ? 'green' : ($percent >= 60 ? '' : 'amber');
                    @endphp
                    <div class="prog-row">
                        <div class="name">
                            {{ $row['judge']->name }}
                            <small class="cell-sub">{{ $row['judge']->role_title }}</small>
                        </div>
                        <div class="prog">
                            <div class="prog-fill {{ $fill }}" style="width:{{ $percent }}%"></div>
                        </div>
                        <div class="val">{{ $row['done'] }}/{{ $row['total'] }}</div>
                    </div>
                @empty
                    <div class="empty">
                        @icon('star')
                        <b>No judges assigned</b>
                        <p>Add judges to the panel to start collecting scores.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div>
                    <h3>@icon('layers') Season rubric</h3>
                </div>
            </div>

            <div class="card-body">
                @forelse ($criteria as $criterion)
                    <div class="stat-row">
                        <span class="lbl">{{ $criterion->name }}</span>
                        <span class="val">{{ $criterion->max_points }} pts &middot; {{ $criterion->weight }}%</span>
                    </div>
                @empty
                    <div class="empty">
                        @icon('layers')
                        <b>No rubric yet</b>
                        <p>Define the criteria judges score against.</p>
                    </div>
                @endforelse

                @if ($criteria->isNotEmpty())
                    <div class="stat-row" style="border-top:1px solid var(--border);margin-top:10px;padding-top:10px;font-size:12.5px">
                        <span class="lbl">Aggregation</span>
                        <span class="val"><b>{{ $round?->aggregation ?? 'Trimmed mean (drop high &amp; low)' }}</b></span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h3>@icon('trend') Ranking table</h3>
                <p>{{ $round ? 'Round '.$round->sequence.' · live standings' : 'No round selected' }}</p>
            </div>

            <div class="chips">
                @foreach ($rounds as $option)
                    <a
                        class="chip @checked($round?->id === $option->id)"
                        href="{{ route('admin.judging.index', ['round' => $option->id]) }}"
                    >{{ $option->status->label() }} &middot; {{ $option->name }}</a>
                @endforeach
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="num">#</th>
                        <th>Application</th>
                        <th>Category</th>
                        @foreach ($criteria as $criterion)
                            <th class="num">{{ Str::limit($criterion->name, 12) }}</th>
                        @endforeach
                        <th class="num">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($standings as $index => $row)
                        <tr>
                            <td class="num"><b>{{ $index + 1 }}</b></td>
                            <td>
                                <div class="cell-group">
                                    <x-admin.avatar
                                        :name="$row['registration']->group_name"
                                        :variant="['', 'alt', 'alt2'][$index % 3]"
                                        class="mini-avatar"
                                    />
                                    <div>
                                        <div class="cell-strong">
                                            <a href="{{ route('admin.registrations.show', $row['registration']) }}">
                                                {{ $round->is_blind ? 'Entry '.$row['registration']->code : $row['registration']->group_name }}
                                            </a>
                                        </div>
                                        <div class="cell-sub">{{ $row['registration']->code }} &middot; {{ $row['votes'] }} vote{{ $row['votes'] === 1 ? '' : 's' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge outline">{{ Str::headline($row['registration']->category) }}</span></td>
                            @foreach ($criteria as $criterion)
                                <td class="num">
                                    @php $score = $row['scores'][$criterion->getKey()]; @endphp
                                    {{ $score === null ? '—' : number_format($score, 1) }}
                                </td>
                            @endforeach
                            <td class="num"><b style="color:var(--primary-ink)">{{ number_format((float) $row['weighted'], 1) }}</b></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + $criteria->count() + 1 }}" style="padding:0">
                                <div class="empty">
                                    @icon('star')
                                    <b>No scores yet</b>
                                    <p>Standings appear here as soon as judges submit scores for this round.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
