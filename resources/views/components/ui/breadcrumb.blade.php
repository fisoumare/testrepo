{{-- Fil d'Ariane. $items : liste de paires [libellé, URL|null] ; le dernier élément est la page courante. --}}
@props(['items' => [], 'dark' => false])
<nav aria-label="Fil d'Ariane" {{ $attributes->merge(['class' => 'text-sm '.($dark ? 'text-ardoise-light' : 'text-ardoise')]) }}>
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
        <li class="flex items-center gap-2">
            <a href="{{ route('home') }}" class="transition {{ $dark ? 'hover:text-white' : 'hover:text-emerald-700' }}">Accueil</a>
            <span aria-hidden="true" class="{{ $dark ? 'text-white/30' : 'text-navy-900/30' }}">›</span>
        </li>
        @foreach ($items as [$label, $url])
            <li class="flex min-w-0 items-center gap-2">
                @if ($url && ! $loop->last)
                    <a href="{{ $url }}" class="transition {{ $dark ? 'hover:text-white' : 'hover:text-emerald-700' }}">{{ $label }}</a>
                    <span aria-hidden="true" class="{{ $dark ? 'text-white/30' : 'text-navy-900/30' }}">›</span>
                @else
                    <span class="font-medium {{ $dark ? 'text-white' : 'text-navy-900' }}" aria-current="page">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect([['Accueil', route('home')], ...$items])->values()->map(fn (array $item, int $index) => array_filter([
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item[0],
            'item' => $item[1],
        ]))->all(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
</nav>
