@props([
    'name',
    'variant' => '',
    'size' => null,
])

@php
    $initials = collect(explode(' ', trim((string) $name)))
        ->filter()
        ->take(2)
        ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');

    $styles = $size ? 'width:'.$size.';height:'.$size.';font-size:'.round((int) $size * 0.38).'px' : null;
@endphp
<div {{ $attributes->class(['avatar', $variant]) }} @if ($styles) style="{{ $styles }}" @endif>{{ $initials }}</div>
