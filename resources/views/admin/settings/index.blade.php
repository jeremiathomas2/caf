@extends('admin.layouts.app')

@section('title', 'Settings & Platform')

@section('content')
    <x-admin.page-head title="Settings &amp; Platform" subtitle="Integrations &middot; roles &middot; fee configuration &middot; data governance">
        @can('settings.manage')
            <form method="POST" action="{{ route('admin.settings.test') }}">
                @csrf
                <button type="submit" class="btn">@icon('refresh') Test all connections</button>
            </form>

            <button type="submit" form="settings-form" class="btn btn-primary">@icon('check') Save changes</button>
        @endcan
    </x-admin.page-head>

    <div class="section-title">Integrations</div>
    <div class="grid grid-3" style="margin-bottom:26px">
        @forelse ($integrations as $integration)
            <div class="int-card">
                <div class="int-logo" style="background:{{ $integration->gradientCss() }}">
                    {{ $integration->initials ?? 'IN' }}
                </div>
                <div class="int-body">
                    <b>{{ $integration->name }}</b>
                    <span>{{ $integration->description }}</span>
                </div>
                @can('settings.manage')
                    <form method="POST" action="{{ route('admin.settings.integration', $integration) }}">
                        @csrf
                        <button
                            type="submit"
                            class="int-status {{ $integration->is_enabled ? '' : 'off' }}"
                            title="{{ $integration->last_checked_at ? 'Last checked '.$integration->last_checked_at->diffForHumans() : 'Never checked' }}"
                        >
                            <span class="pulse"></span>
                            {{ $integration->is_enabled ? 'Live' : 'Off' }}
                        </button>
                    </form>
                @else
                    <span class="int-status {{ $integration->is_enabled ? '' : 'off' }}">
                        <span class="pulse"></span>
                        {{ $integration->is_enabled ? 'Live' : 'Off' }}
                    </span>
                @endcan
            </div>
        @empty
            <div class="card">
                <div class="empty">
                    @icon('zap')
                    <b>No integrations</b>
                    <p>Payment, email and storage providers appear here.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="grid grid-2-1" style="margin-bottom:16px">
        <div class="card">
            <div class="card-head">
                <div>
                    <h3>@icon('users') Roles &amp; permissions</h3>
                    <p>{{ count($roles) }} defined roles &middot; per-resource permissions</p>
                </div>
            </div>
            <div class="card-body" style="padding:0">
                @foreach ($roles as $role)
                    <div class="queue-item" style="cursor:default">
                        <span class="queue-ico {{ in_array($role->accessLevel(), ['highest', 'high'], true) ? 'purple' : 'blue' }}">
                            @icon('lock')
                        </span>
                        <div class="queue-text">
                            <b>{{ $role->label() }}</b>
                            <span>{{ $role->description() }}</span>
                        </div>
                        <span class="badge gray" style="font-size:10px">{{ Str::headline($role->accessLevel()) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-head"><div><h3>@icon('card') Fee configuration</h3></div></div>
            <div class="card-body">
                <div class="stat-row"><span class="lbl">Primary currency</span><span class="val">{{ $season?->currency ?? '—' }}</span></div>
                <div class="stat-row"><span class="lbl">Secondary currency</span><span class="val">{{ $season?->secondary_currency ?? '—' }}</span></div>
                <div class="stat-row">
                    <span class="lbl">Early-bird window</span>
                    <span class="val">
                        @if ($season?->registration_opens_at && $season?->early_bird_closes_at)
                            {{ $season->registration_opens_at->format('d M') }} &ndash; {{ $season->early_bird_closes_at->format('d M') }}
                        @else
                            Not set
                        @endif
                    </span>
                </div>
                <div class="stat-row">
                    <span class="lbl">Standard fee</span>
                    <span class="val">{{ $season ? $season->currency.' '.number_format((float) $season->per_head_fee) : '—' }}</span>
                </div>
                <div class="stat-row">
                    <span class="lbl">Partial payment</span>
                    <span class="val">{{ $season ? 'Allowed &middot; min '.$season->min_partial_payment_pct.'%' : '—' }}</span>
                </div>
                <div class="stat-row">
                    <span class="lbl">Rounding rule</span>
                    <span class="val">{{ $season ? 'Nearest '.number_format($season->rounding_increment).' '.$season->currency : '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h3>@icon('sliders') Operational settings</h3>
                <p>{{ $settings->count() }} settings across {{ $groups->count() }} group(s)</p>
            </div>
        </div>
        <div class="card-body">
            @can('settings.manage')
                <form method="POST" action="{{ route('admin.settings.update') }}" id="settings-form">
                    @csrf

                    @foreach ($groups as $group)
                        <div class="section-title">{{ Str::headline($group->first()->group) }}</div>

                        @foreach ($group as $setting)
                            <div class="stat-row">
                                <span class="lbl">
                                    {{ $setting->label ?? $setting->key }}
                                    @if ($setting->hint)
                                        <small class="cell-sub" style="display:block">{{ $setting->hint }}</small>
                                    @endif
                                </span>
                                <span class="val">
                                    @if ($setting->type === 'boolean')
                                        <input
                                            type="hidden"
                                            name="settings[{{ $setting->key }}]"
                                            value="0"
                                        >
                                        <input
                                            type="checkbox"
                                            name="settings[{{ $setting->key }}]"
                                            value="1"
                                            @checked($setting->value === '1' || $setting->value === 'true')
                                            aria-label="{{ $setting->label ?? $setting->key }}"
                                        >
                                    @else
                                        <input
                                            type="{{ $setting->type === 'integer' ? 'number' : 'text' }}"
                                            name="settings[{{ $setting->key }}]"
                                            value="{{ $setting->value }}"
                                            @if ($setting->type === 'integer') step="1" @endif
                                            aria-label="{{ $setting->label ?? $setting->key }}"
                                        >
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    @endforeach
                </form>
            @else
                @foreach ($groups as $group)
                    <div class="section-title">{{ Str::headline($group->first()->group) }}</div>

                    @foreach ($group as $setting)
                        <div class="stat-row">
                            <span class="lbl">{{ $setting->label ?? $setting->key }}</span>
                            <span class="val">
                                {{ $setting->type === 'boolean' ? ($setting->value === '1' || $setting->value === 'true' ? 'Enabled' : 'Disabled') : ($setting->value ?: '—') }}
                            </span>
                        </div>
                    @endforeach
                @endforeach
            @endcan
        </div>
    </div>
@endsection
