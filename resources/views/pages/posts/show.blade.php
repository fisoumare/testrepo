@php
    $cover = $post->cover ? \Illuminate\Support\Facades\Storage::disk('public')->url($post->cover) : null;
    $published = $post->published_at;
@endphp
<x-layouts.app :title="$post->title" :description="$post->excerpt" og-type="article" :og-image="$cover">
    <x-slot:head>
        <meta property="article:published_time" content="{{ $published->toIso8601String() }}">
        @if ($post->updated_at)<meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">@endif
        @if ($post->category)<meta property="article:section" content="{{ $post->category }}">@endif
        @if ($post->author)<meta property="article:author" content="{{ $post->author }}">@endif
        <script type="application/ld+json">{!! json_encode(array_filter([
            '@@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $post->title,
            'description' => $post->excerpt,
            'datePublished' => $published->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'image' => $cover ?? asset('brand/og.png'),
            'mainEntityOfPage' => route('posts.show', $post),
            'author' => ['@type' => $post->author && $post->author !== setting('company_name') ? 'Person' : 'Organization', 'name' => $post->author ?: setting('company_name')],
            'publisher' => ['@type' => 'Organization', 'name' => setting('company_name'), 'logo' => ['@type' => 'ImageObject', 'url' => asset('brand/logo.png')]],
        ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    </x-slot:head>

    <article>
        {{-- ===== EN-TÊTE ===== --}}
        <header class="border-b border-navy-900/8 bg-paper">
            <div class="wrap py-12 sm:py-16">
                <x-ui.breadcrumb :items="[['Actualités', route('posts.index')], [$post->title, null]]" />
                <p class="label mt-8 flex flex-wrap items-center gap-x-3 gap-y-1 text-emerald-700">
                    @if ($post->category)<span class="rounded-sm border border-emerald-700/30 px-1.5 py-0.5">{{ $post->category }}</span>@endif
                    <time datetime="{{ $published->toDateString() }}">{{ $published->locale('fr')->translatedFormat('j F Y') }}</time>
                    <span aria-hidden="true">·</span>
                    <span>{{ $post->readingTime() }} min de lecture</span>
                </p>
                <h1 class="h-display mt-5 max-w-4xl text-3xl sm:text-4xl lg:text-5xl">{{ $post->title }}</h1>
                @if ($post->excerpt)
                    <p class="lead mt-6 max-w-3xl">{{ $post->excerpt }}</p>
                @endif
                @if ($post->author)
                    <p class="mt-6 flex items-center gap-3 text-sm text-ardoise">
                        <span class="led led-on" aria-hidden="true"></span>
                        Par <span class="font-medium text-navy-900">{{ $post->author }}</span>
                    </p>
                @endif
            </div>
        </header>

        {{-- ===== IMAGE DE COUVERTURE ===== --}}
        @if ($cover)
            <figure class="wrap mt-10 sm:mt-14">
                <img src="{{ $cover }}" alt="{{ $post->title }}" class="aspect-[21/9] w-full rounded-xl object-cover shadow-unit" loading="eager">
            </figure>
        @endif

        {{-- ===== CORPS ===== --}}
        <div class="wrap mt-10 sm:mt-14 lg:grid lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-8 lg:col-start-3">
                <div class="prose-site text-[17px]">{!! $post->body !!}</div>
                <footer class="mt-12 flex flex-wrap items-center justify-between gap-4 border-t border-navy-900/10 pt-6">
                    <a href="{{ route('posts.index') }}" class="btn-link">← Toutes les actualités</a>
                    <span class="label text-ardoise">Publié le {{ $published->locale('fr')->translatedFormat('j F Y') }}</span>
                </footer>
            </div>
        </div>
    </article>

    {{-- ===== À LIRE AUSSI ===== --}}
    @if ($more->isNotEmpty())
        <section class="wrap mt-24" aria-labelledby="a-lire-aussi">
            <x-ui.section-heading code="NEWS" kicker="À lire aussi" title="D'autres actualités." class="reveal" id="a-lire-aussi" />
            <div class="mt-10 grid gap-4 md:grid-cols-3">
                @foreach ($more as $item)
                    <x-ui.unit :href="route('posts.show', $item)" padding="p-5" class="flex h-full flex-col text-navy-900/40 no-underline transition hover:-translate-y-0.5 hover:shadow-lg reveal reveal-delay-{{ $loop->index % 3 }}">
                        <p class="label text-ardoise">{{ $item->published_at->locale('fr')->translatedFormat('j F Y') }}@if ($item->category) · {{ $item->category }}@endif</p>
                        <h3 class="mt-3 text-lg font-semibold leading-snug text-navy-900">{{ $item->title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-ardoise line-clamp-3">{{ $item->excerpt }}</p>
                        <span class="btn-link mt-auto pt-4">Lire →</span>
                    </x-ui.unit>
                @endforeach
            </div>
        </section>
    @endif

    <x-ui.cta-band />
</x-layouts.app>
