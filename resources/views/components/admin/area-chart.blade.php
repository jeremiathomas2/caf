@props([
    'values' => [],
    'labels' => [],
    'color' => 'var(--primary)',
    'fill' => '#E4572E',
    'label' => 'Trend chart',
])

{!! \App\Support\AreaChart::render($values, $labels, $color, $fill, $label) !!}
