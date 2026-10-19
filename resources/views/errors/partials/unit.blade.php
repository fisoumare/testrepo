{{-- Bloc commun des pages d'erreur : une « unité » hors service, repérée par son code et une LED ambre.
     Variables : code (ERR-404), kicker, title, text, links (libellé => URL). --}}
<section class="bg-paper border-b border-navy-900/8">
    <div class="wrap py-20 sm:py-28">
        <div class="max-w-2xl">
            <p class="label mb-5 flex items-center gap-3 text-ardoise">
                <span class="led led-amber" aria-hidden="true"></span>
                <span class="rounded-sm border border-navy-900/20 px-1.5 py-0.5 text-navy-900">{{ $code }}</span>
                <span>{{ $kicker }}</span>
            </p>
            <h1 class="h-display text-4xl sm:text-5xl lg:text-6xl text-navy-900">{{ $title }}</h1>
            <p class="lead mt-6">{{ $text }}</p>
            <div class="mt-10 flex flex-wrap gap-3">
                @foreach ($links as $label => $url)
                    <a href="{{ $url }}" class="{{ $loop->first ? 'btn-primary' : 'btn-ghost' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </div>
</section>
