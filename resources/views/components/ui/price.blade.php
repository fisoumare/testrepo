@props(['service', 'dark' => false, 'size' => 'base'])
@php
    $sizes = ['sm' => 'text-base', 'base' => 'text-xl', 'lg' => 'text-3xl'];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-baseline gap-1.5 tabular']) }}>
    @if ($service->isOnQuote())
        <span class="{{ $sizes[$size] }} font-semibold {{ $dark ? 'text-white' : 'text-navy-900' }}">Sur devis</span>
    @else
        <span class="{{ $sizes[$size] }} font-semibold {{ $dark ? 'text-white' : 'text-navy-900' }}">{{ gnf($service->price, false) }}</span>
        <span class="label {{ $dark ? 'text-ardoise-light' : 'text-ardoise' }}">GNF HT{{ in_array($service->unit, ['Mois', 'An', 'Jour', 'Heure']) ? ' / '.mb_strtolower($service->unit) : '' }}</span>
    @endif
</span>
