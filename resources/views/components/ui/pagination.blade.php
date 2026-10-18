{{-- Pagination aux couleurs du site. Utilisation : {{ $posts->links('components.ui.pagination') }} --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="mt-12 flex flex-col gap-4 border-t border-navy-900/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
        <p class="label text-ardoise tabular">
            Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            · {{ $paginator->total() }} {{ $paginator->total() > 1 ? 'actualités' : 'actualité' }}
        </p>
        <ul class="flex flex-wrap items-center gap-1.5">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="inline-flex h-10 cursor-not-allowed items-center rounded-md border border-navy-900/10 px-3 text-sm text-ardoise/60" aria-disabled="true">← Précédent</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-10 items-center rounded-md border border-navy-900/15 px-3 text-sm font-medium transition hover:border-navy-900/40 hover:bg-navy-50" aria-label="Page précédente">← Précédent</a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="label px-2 text-ardoise" aria-hidden="true">{{ $element }}</span></li>
                @else
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex size-10 items-center justify-center rounded-md bg-navy-900 font-mono text-sm text-white tabular" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex size-10 items-center justify-center rounded-md border border-navy-900/15 font-mono text-sm tabular transition hover:border-navy-900/40 hover:bg-navy-50" aria-label="Page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-10 items-center rounded-md border border-navy-900/15 px-3 text-sm font-medium transition hover:border-navy-900/40 hover:bg-navy-50" aria-label="Page suivante">Suivant →</a>
                @else
                    <span class="inline-flex h-10 cursor-not-allowed items-center rounded-md border border-navy-900/10 px-3 text-sm text-ardoise/60" aria-disabled="true">Suivant →</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
