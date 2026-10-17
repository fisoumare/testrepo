@props(['as' => 'div', 'href' => null, 'dark' => false, 'padding' => 'p-6'])
@php
    $tag = $href ? 'a' : $as;
    $base = $dark
        ? 'unit text-emerald-400/70 rounded-lg border border-white/10 bg-ink-soft/80 '.$padding
        : 'unit text-navy-900/40 rounded-lg border border-navy-900/10 bg-white shadow-unit '.$padding;
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => $base]) }}>
    {{ $slot }}
</{{ $tag }}>
