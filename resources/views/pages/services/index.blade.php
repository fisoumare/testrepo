{{-- $categories, $total, $onQuote (prestations sur devis), $preview (cartes affichées d'emblée par famille), $description : ServiceController::index(). --}}
<x-layouts.app title="Prestations" :description="$description">
    <x-ui.page-header code="U01–U08" kicker="Prestations" :title="$onQuote ? 'Un catalogue à prix affichés, unité par unité.' : 'Un catalogue à prix fixes, unité par unité.'">
        Chaque prestation a un périmètre écrit, un prix hors taxes en GNF et un délai. Ce qui n'est pas compris est dit. Vous composez votre projet comme un rack.
        <x-slot:aside>
            <dl class="spec unit rounded-lg border border-navy-900/10 bg-white p-5 text-navy-900/30 shadow-unit">
                <dt>Catalogue</dt><dd class="tabular">{{ $total }} prestations · {{ $categories->count() }} familles</dd>
                <dt>Prix</dt><dd>{{ $onQuote ? 'Affichés hors taxes, en GNF ('.$onQuote.' sur devis)' : 'Fixes, hors taxes, en GNF' }}</dd>
                <dt>TVA</dt><dd>18&nbsp;% en sus</dd>
                <dt>Paiement</dt><dd>{{ setting('payment_terms') }}</dd>
                <dt>Devis</dt><dd>Valable 30 jours</dd>
            </dl>
        </x-slot:aside>
    </x-ui.page-header>

    <div
        x-data="{
            q: '',
            preview: {{ $preview }},
            expanded: {},
            shown: {{ $total }},
            norm(s) { return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); },
            expand(slug) { this.expanded[slug] = true; this.filter(); },
            // Toutes les cartes restent dans la page : une recherche montre toutes les correspondances,
            // sans recherche chaque famille n'en montre que `preview` tant qu'elle n'est pas dépliée.
            filter() {
                const needle = this.norm(this.q.trim());
                const searching = needle !== '';
                let shown = 0;
                this.$root.querySelectorAll('[data-section]').forEach((section) => {
                    const all = searching || this.expanded[section.dataset.section] === true;
                    let matches = 0;
                    section.querySelectorAll('[data-search]').forEach((card) => {
                        const ok = ! searching || this.norm(card.dataset.search).includes(needle);
                        if (ok) matches++;
                        card.hidden = ! ok || (! all && matches > this.preview);
                    });
                    section.hidden = matches === 0;
                    section.querySelectorAll('[data-count]').forEach((el) => { el.textContent = matches; });
                    shown += matches;
                });
                this.shown = shown;
            },
        }"
        x-init="$watch('q', () => filter())"
    >
        {{-- ===== BARRE COLLANTE : FAMILLES + RECHERCHE ===== --}}
        <div class="sticky top-[104px] z-40 border-b border-navy-900/10 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/80">
            <div class="wrap flex flex-col gap-3 py-3 lg:flex-row lg:items-center lg:justify-between">
                <nav class="hidden flex-wrap gap-1 lg:flex" aria-label="Familles de prestations">
                    @foreach ($categories as $category)
                        <a href="#{{ $category->slug }}" class="inline-flex items-center gap-2 rounded-md px-2.5 py-1.5 text-[13px] font-medium text-navy-900/80 transition hover:bg-navy-50 hover:text-navy-900">
                            <span class="label text-emerald-700">{{ $category->code }}</span>{{ $category->name }}
                        </a>
                    @endforeach
                </nav>
                <div class="flex gap-2">
                    <label class="lg:hidden">
                        <span class="sr-only">Aller à une famille de prestations</span>
                        <select class="h-10 max-w-40 rounded-md border border-navy-900/15 bg-white px-2 text-sm text-navy-900 focus:border-emerald-500 focus:outline-none" @change="if ($event.target.value) { location.hash = $event.target.value; $event.target.selectedIndex = 0; }">
                            <option value="">Familles…</option>
                            @foreach ($categories as $category)
                                <option value="#{{ $category->slug }}">{{ $category->code }} · {{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="relative min-w-0 flex-1 lg:w-72 lg:flex-none">
                        <span class="sr-only">Rechercher une prestation</span>
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ardoise" viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="8.5" cy="8.5" r="5.5" stroke="currentColor" stroke-width="1.8"/><path d="M13 13l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <input type="search" x-model.debounce.120ms="q" placeholder="Rechercher (nom, numéro…)" autocomplete="off" class="h-10 w-full rounded-md border border-navy-900/15 bg-white pl-9 pr-3 text-sm text-navy-900 placeholder:text-ardoise/70 focus:border-emerald-500 focus:outline-none">
                    </label>
                </div>
            </div>
            <p class="wrap label flex items-center gap-3 pb-3 text-ardoise" x-show="q.trim() !== ''" x-cloak aria-live="polite">
                <span class="tabular"><span x-text="shown"></span> <span x-text="shown > 1 ? 'résultats' : 'résultat'"></span> pour «&nbsp;<span class="text-navy-900" x-text="q.trim()"></span>&nbsp;»</span>
                <button type="button" class="btn-link text-xs" @click="q = ''">Effacer</button>
            </p>
        </div>

        {{-- ===== UNE SECTION PAR FAMILLE ===== --}}
        @foreach ($categories as $category)
            @php $rest = $category->services->count() - $preview; @endphp
            <section id="{{ $category->slug }}" data-section="{{ $category->slug }}" class="wrap mt-20 scroll-mt-44" aria-labelledby="{{ $category->slug }}-titre">
                <div class="grid gap-6 border-t border-navy-900/10 pt-8 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-8">
                        <p class="label flex items-center gap-3 text-emerald-700">
                            <span class="rounded-sm border border-emerald-700/30 px-1.5 py-0.5">{{ $category->code }}</span>
                            <span>Famille {{ $loop->iteration }} / {{ $categories->count() }}</span>
                        </p>
                        <h2 id="{{ $category->slug }}-titre" class="h-section mt-4">{{ $category->name }}</h2>
                        <p class="lead mt-4 max-w-2xl text-base">{{ $category->description }}</p>
                    </div>
                    <div class="flex items-center gap-5 lg:col-span-4 lg:justify-end">
                        <span class="label text-ardoise tabular"><span data-count>{{ $category->services->count() }}</span> prestations</span>
                        <a href="{{ route('services.category', $category) }}" class="btn-link">Voir la famille →</a>
                    </div>
                </div>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($category->services as $service)
                        <div data-search="{{ mb_strtolower($service->number.' '.$service->name.' '.$service->summary) }}" @if ($loop->index >= $preview) hidden @endif>
                            <x-ui.service-card :service="$service" />
                        </div>
                    @endforeach
                </div>
                @if ($rest > 0)
                    {{-- Sans JavaScript, le lien mène à la page de la famille, qui liste tout --}}
                    <p class="mt-6" x-show="q.trim() === '' && expanded['{{ $category->slug }}'] !== true">
                        <a href="{{ route('services.category', $category) }}" class="btn-ghost" @click.prevent="expand('{{ $category->slug }}')">
                            {{ $rest === 1 ? 'Afficher la dernière prestation' : 'Afficher les '.$rest.' autres prestations' }}
                        </a>
                    </p>
                @endif
            </section>
        @endforeach

        {{-- ===== AUCUN RÉSULTAT : rendu seulement à la demande, dans une zone vivante présente dès le départ ===== --}}
        <div aria-live="polite">
            <template x-if="shown === 0">
                <section class="wrap mt-20">
                    <div class="unit rounded-xl border border-navy-900/10 bg-paper p-8 text-navy-900/30 sm:p-12">
                        <p class="label flex items-center gap-2 text-ardoise"><span class="led led-amber" aria-hidden="true"></span> Aucun résultat</p>
                        <h2 class="mt-4 text-2xl font-semibold tracking-tight text-navy-900">Aucune prestation ne correspond à «&nbsp;<span x-text="q.trim()"></span>&nbsp;».</h2>
                        <p class="mt-3 max-w-xl text-ardoise">Essayez un autre mot, ou décrivez-nous votre besoin&nbsp;: les projets hors catalogue font l'objet d'un devis spécifique.</p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <button type="button" class="btn-ghost" @click="q = ''">Afficher tout le catalogue</button>
                            <a href="{{ route('contact') }}" class="btn-navy">Décrire mon besoin</a>
                        </div>
                    </div>
                </section>
            </template>
        </div>
    </div>

    <x-ui.cta-band title="Un besoin hors catalogue ?" text="Serveur sur mesure, application au-delà du niveau 3, logiciel sans documentation technique : décrivez votre projet, nous vous répondons avec un devis spécifique. {{ setting('hours') }}" />
</x-layouts.app>
