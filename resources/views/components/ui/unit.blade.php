@props(['as' => 'div', 'href' => null, 'dark' => false, 'padding' => 'p-6'])
@php
    $tag = $href ? 'a' : $as;
    // La couleur des crochets passe par --unit-color (unit-light / unit-dark), jamais par la couleur du texte :
    // le contenu hérite ainsi de la couleur de la page (navy sur fond clair, blanc dans une section sombre).
    $base = $dark
        ? 'unit unit-dark rounded-lg border border-white/10 bg-ink-soft/80 '.$padding
        : 'unit unit-light rounded-lg border border-navy-900/10 bg-white shadow-unit '.$padding;
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => $base]) }}>
    {{ $slot }}
</{{ $tag }}>
