<x-layouts.app title="Actualités" description="Les actualités de SOUMATIC : nouveautés des solutions EduGes, MediGes SST, MediGes et Compiniya, références et évolutions du catalogue.">
    <x-ui.page-header code="NEWS" kicker="Actualités" title="Journal de bord.">
        Nouveautés des solutions, références et évolutions du catalogue, dans l'ordre où elles arrivent.
    </x-ui.page-header>

    <section class="wrap mt-12 sm:mt-16" aria-label="Liste des actualités">
        @if ($posts->isEmpty())
            <div class="unit rounded-xl border border-navy-900/10 bg-paper p-8 text-navy-900/30 sm:p-12">
                <p class="label flex items-center gap-2 text-ardoise"><span class="led led-off" aria-hidden="true"></span> Actualités</p>
                <p class="lead mt-4">Aucune actualité publiée pour le moment.</p>
                <a href="{{ route('home') }}" class="btn-link mt-6">← Retour à l'accueil</a>
            </div>
        @else
            <ol class="divide-y divide-navy-900/10 border-y border-navy-900/10">
                @foreach ($posts as $post)
                    <li>
                        <article class="grid gap-4 py-8 sm:grid-cols-12 sm:gap-8 sm:py-10 reveal">
                            <div class="flex flex-wrap items-center gap-3 sm:col-span-3 sm:flex-col sm:items-start lg:col-span-2">
                                <time datetime="{{ $post->published_at->toDateString() }}" class="label text-ardoise tabular">{{ $post->published_at->locale('fr')->translatedFormat('j F Y') }}</time>
                                @if ($post->category)
                                    <span class="rounded-sm bg-navy-50 px-2 py-0.5 text-xs font-medium text-navy-900">{{ $post->category }}</span>
                                @endif
                            </div>
                            <div class="min-w-0 sm:col-span-9 lg:col-span-8">
                                <h2 class="text-xl font-semibold leading-snug tracking-tight text-navy-900 text-balance sm:text-2xl">
                                    <a href="{{ route('posts.show', $post) }}" class="transition hover:text-emerald-700">{{ $post->title }}</a>
                                </h2>
                                @if ($post->excerpt)
                                    <p class="mt-3 text-[15px] leading-relaxed text-ardoise">{{ $post->excerpt }}</p>
                                @endif
                                <p class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2">
                                    <span class="label text-ardoise">{{ $post->readingTime() }} min de lecture</span>
                                    <a href="{{ route('posts.show', $post) }}" class="btn-link" aria-label="Lire : {{ $post->title }}">Lire →</a>
                                </p>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ol>

            {{ $posts->links('components.ui.pagination') }}
        @endif
    </section>

    <x-ui.cta-band />
</x-layouts.app>
