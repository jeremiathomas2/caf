@props([
    'label',
    'value',
    'foot' => null,
    'icon' => 'trend',
    'tone' => '',
    'delta' => null,
    'dir' => 'up',
])

<div {{ $attributes->class(['kpi', $tone]) }}>
    <div class="kpi-top">
        <span class="kpi-label">{{ $label }}</span>
        <span class="kpi-ico">@icon($icon)</span>
    </div>
    <div class="kpi-value">{{ $value }}</div>
    <div class="kpi-foot">
        @if ($delta)
            <span class="delta {{ $dir }}">
                @icon($dir === 'up' ? 'arrowUp' : 'arrowDown')
                {{ $delta }}
            </span>
        @endif
        @if ($foot)
            <span>{{ $foot }}</span>
        @endif
    </div>
</div>
