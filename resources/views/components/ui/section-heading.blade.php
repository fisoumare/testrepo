@props(['code' => null, 'title', 'kicker' => null, 'dark' => false, 'align' => 'left'])
<div {{ $attributes->merge(['class' => 'max-w-3xl '.($align === 'center' ? 'mx-auto text-center' : '')]) }}>
    @if ($code || $kicker)
        <p class="label mb-4 flex items-center gap-3 {{ $align === 'center' ? 'justify-center' : '' }} {{ $dark ? 'text-emerald-400' : 'text-emerald-700' }}">
            @if ($code)<span class="rounded-sm border px-1.5 py-0.5 {{ $dark ? 'border-emerald-400/40' : 'border-emerald-700/30' }}">{{ $code }}</span>@endif
            @if ($kicker)<span>{{ typo($kicker) }}</span>@endif
        </p>
    @endif
    <h2 class="h-section {{ $dark ? 'text-white' : 'text-navy-900' }}">{{ typo($title) }}</h2>
    @if (trim($slot))
        <p class="lead mt-5 {{ $dark ? 'text-ardoise-light' : '' }}">{!! typo($slot->toHtml()) !!}</p>
    @endif
</div>
