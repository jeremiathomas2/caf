@props([
    'value',
    'dot' => true,
    'tone' => null,
    'label' => null,
])

@php
    $tone = $tone ?? \App\Support\Badge::tone($value);
    $text = $label ?? \App\Support\Badge::label($value);
@endphp
<span class="badge {{ $tone }}">@if ($dot)<span class="dot"></span>@endif{{ $text }}</span>
