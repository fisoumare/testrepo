<x-layouts.app :description="setting('meta_description')">
    {{-- ===== HÉRO ===== --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 grid-dots opacity-70 [mask-image:radial-gradient(ellipse_at_top_left,#000_30%,transparent_70%)]" aria-hidden="true"></div>
        <div class="wrap relative grid grid-cols-1 gap-12 py-16 sm:py-20 lg:grid-cols-12 lg:items-center lg:py-24">
            <div class="min-w-0 lg:col-span-7 reveal">
                <p class="label text-emerald-700 mb-6 flex items-center gap-3">
                    <span class="led led-on"></span> {{ setting('hero_kicker') }}
                </p>
                <h1 class="h-display text-[2.6rem] leading-[1.02] sm:text-6xl lg:text-[4.6rem]">{{ setting('hero_title') }}</h1>
                <p class="lead mt-7 max-w-2xl text-xl">{{ setting('hero_subtitle') }}</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('quote') }}" class="btn-primary">Demander un devis</a>
                    <a href="{{ route('services.index') }}" class="btn-ghost">Voir les {{ $servicesCount }}&nbsp;prestations</a>
                </div>
                @if ($reference)
                    <p class="mt-10 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ardoise">
                        <span class="label text-ardoise">Référence</span>
                        <a href="{{ route('references.index') }}" class="font-medium text-navy-900 hover:text-emerald-700">{{ $reference->acronym }} — {{ $reference->headline }}</a>
                    </p>
                @endif
            </div>

            {{-- Le « rack » : les 4 solutions comme autant d'unités en service --}}
            <div class="min-w-0 lg:col-span-5 reveal reveal-delay-2">
                <div class="unit unit-dark relative overflow-hidden rounded-xl bg-ink p-3 text-white shadow-2xl ring-1 ring-white/10">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-emerald-400/10 to-transparent animate-scan motion-reduce:hidden" aria-hidden="true"></div>
                    <div class="flex items-center justify-between px-3 py-2">
                        <span class="label text-ardoise-light">Solutions · en service</span>
                        <span class="flex items-center gap-1.5" aria-hidden="true"><span class="led led-on"></span><span class="led led-on [animation-delay:0.6s]"></span><span class="led led-on [animation-delay:1.2s]"></span></span>
                    </div>
                    <ul class="mt-1 divide-y divide-white/8 rounded-lg bg-ink-soft/60 ring-1 ring-white/8">
                        @foreach ($solutions as $solution)
                            <li>
                                <a href="{{ route('solutions.show', $solution) }}" class="group flex items-center gap-4 px-4 py-4 transition hover:bg-white/[0.04]">
                                    <span class="label w-8 shrink-0 text-ardoise-light group-hover:text-emerald-400">{{ $solution->code }}</span>
                                    <x-ui.led :color="$solution->accent" class="shrink-0" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-semibold text-white">{{ $solution->name }}</span>
                                        <span class="block text-sm text-ardoise-light text-pretty">{{ $solution->tagline }}</span>
                                    </span>
                                    <span class="hidden sm:block vent w-16 text-ardoise-light" aria-hidden="true"></span>
                                    <svg class="size-4 shrink-0 text-ardoise-light transition group-hover:translate-x-0.5 group-hover:text-emerald-400" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    {{-- Les trois formules : un vrai lien (vers la comparaison), pas des faux boutons --}}
                    <a href="{{ route('solutions.index') }}" class="mt-3 flex flex-wrap items-center justify-between gap-x-4 gap-y-1 rounded border border-white/10 px-3 py-2 text-[11px] font-mono text-ardoise-light transition hover:border-emerald-400/50 hover:text-emerald-400">
                        <span>Abonnement · Licence installée · Maintenance</span>
                        <span class="ml-auto shrink-0">Comparer les formules →</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Bandeau des tarifs --}}
        @if ($featured->isNotEmpty())
            <div class="border-y border-navy-900/8 bg-paper py-3 ticker-mask overflow-hidden" aria-label="Exemples de prestations à prix fixe">
                <div class="ticker">
                    @for ($i = 0; $i < 2; $i++)
                        @foreach ($featured as $service)
                            <a href="{{ route('services.show', $service) }}" class="flex items-center gap-3 text-sm hover:text-emerald-700" @if($i) aria-hidden="true" tabindex="-1" @endif>
                                <span class="led led-off size-1.5"></span>
                                <span class="font-medium">{{ $service->name }}</span>
                                <span class="font-mono text-ardoise tabular">{{ $service->isOnQuote() ? 'sur devis' : gnf($service->price).' HT' }}</span>
                            </a>
                        @endforeach
                    @endfor
                </div>
            </div>
        @endif
    </section>

    {{-- ===== CATÉGORIES ===== --}}
    <section class="wrap mt-24" id="prestations">
        <x-ui.section-heading code="U01–U08" kicker="Ce que nous faisons" title="Huit familles de prestations, chacune à prix fixe." class="reveal">
            Chaque prestation a un périmètre écrit et un prix en GNF. Ce qui n'est pas compris est dit. Vous composez votre projet comme un rack&nbsp;: unité par unité.
        </x-ui.section-heading>
        <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($categories as $category)
                <x-ui.unit :href="route('services.category', $category)" padding="p-5" class="group flex h-full flex-col reveal reveal-delay-{{ $loop->index % 4 }} no-underline transition hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="flex items-center justify-between">
                        <span class="label text-emerald-700">{{ $category->code }}</span>
                        <span class="led led-off group-hover:led-on group-hover:bg-emerald-400 transition" aria-hidden="true"></span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold leading-snug text-navy-900">{{ $category->name }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-ardoise line-clamp-4">{{ $category->description }}</p>
                    <div class="mt-auto pt-5 text-sm">
                        @if ($category->example)
                            <div class="border-t border-navy-900/8 pt-3">
                                <p class="label text-ardoise">Par exemple</p>
                                <p class="mt-1 min-h-10 leading-5 text-navy-900 line-clamp-2">{{ $category->example->name }}</p>
                                <p class="mt-1 font-mono tabular text-navy-900">{{ $category->example->isOnQuote() ? 'Sur devis' : \App\Support\Money::short($category->example->price).' HT' }}</p>
                            </div>
                        @endif
                        <p class="mt-3 flex items-center justify-between text-ardoise"><span>{{ $category->services_count }}&nbsp;prestations</span><span class="btn-link">Voir →</span></p>
                    </div>
                </x-ui.unit>
            @endforeach
        </div>
    </section>

    {{-- ===== SOLUTIONS ===== --}}
    <section class="mt-24 bg-ink text-white rack-lines">
        <div class="wrap py-20 sm:py-24">
            <div class="grid gap-10 lg:grid-cols-12 lg:items-end">
                <x-ui.section-heading code="S01–S04" kicker="Solutions clés en main" title="Quatre logiciels prêts à l'emploi, conçus par SOUMATIC à Conakry." dark class="lg:col-span-8 reveal">
                    En abonnement mensuel hébergé par SOUMATIC, ou en licence installée sur votre serveur. Mise en service, formation et reprise de vos données&nbsp;: des prestations à part, au catalogue.
                </x-ui.section-heading>
                <div class="lg:col-span-4 lg:text-right reveal reveal-delay-1">
                    <a href="{{ route('solutions.index') }}" class="btn-ghost-dark">Comparer les formules</a>
                </div>
            </div>
            <div class="mt-12 grid gap-4 md:grid-cols-2">
                @foreach ($solutions as $solution)
                    <x-ui.unit :href="route('solutions.show', $solution)" dark class="group flex h-full flex-col no-underline reveal reveal-delay-{{ $loop->index % 2 }} transition hover:bg-ink-soft">
                        <div class="flex items-center justify-between">
                            <span class="label text-ardoise-light">{{ $solution->code }}</span>
                            <x-ui.led :color="$solution->accent" />
                        </div>
                        <h3 class="mt-5 text-2xl font-semibold tracking-tight text-white">{{ $solution->name }} <span class="text-ardoise-light font-normal">· {{ $solution->tagline }}</span></h3>
                        <p class="mt-2 text-sm text-ardoise-light">{{ $solution->audience }}</p>
                        <ul class="mt-5 flex flex-wrap gap-2">
                            @foreach (array_slice($solution->features ?? [], 0, 6) as $feature)
                                <li class="rounded-sm border border-white/10 px-2 py-1 text-xs text-ardoise-light">{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <div class="mt-auto flex items-end justify-between pt-6">
                            @if ($solution->from_monthly)
                                <span class="tabular"><span class="text-xl font-semibold text-white">{{ gnf($solution->from_monthly, false) }}</span> <span class="label whitespace-nowrap text-ardoise-light">GNF HT / mois</span></span>
                            @else
                                <span class="label text-ardoise-light">Sur devis</span>
                            @endif
                            <span class="btn-link text-emerald-400 group-hover:text-emerald-300">Découvrir →</span>
                        </div>
                    </x-ui.unit>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== ENGAGEMENTS ===== --}}
    <section class="wrap mt-24">
        <x-ui.section-heading code="SPEC" kicker="Nos engagements" title="Des conditions écrites, les mêmes pour tous." class="reveal" />
        <dl class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Prix fixes', 'Affichés hors taxes en GNF, TVA 18 % en sus. Devis valable 30 jours.'],
                ['50 % à la commande', 'Le solde à la validation. Abonnements, hébergement et maintenance payés d\'avance.'],
                ['Garantie 3 mois', 'Correction gratuite de ce qui ne fonctionne pas comme prévu, hors évolutions, contenus et hébergement.'],
                ['Assistance', setting('hours').'. Première réponse au plus tard le jour ouvré suivant.'],
            ] as [$term, $detail])
                <div class="unit unit-light rounded-lg border border-navy-900/10 bg-paper p-5 reveal reveal-delay-{{ $loop->index % 4 }}">
                    <dt class="flex items-center gap-2 font-semibold text-navy-900"><span class="led led-on"></span>{{ typo($term) }}</dt>
                    <dd class="mt-2 text-sm leading-relaxed text-ardoise">{{ typo($detail) }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- ===== RÉFÉRENCE ===== --}}
    @if ($reference)
        <section class="wrap mt-24">
            <div class="grid gap-10 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-5 reveal">
                    <x-ui.section-heading code="REF" kicker="Référence" :title="$reference->headline">{{ $reference->name }} ({{ $reference->acronym }}), {{ $reference->location }}.</x-ui.section-heading>
                    <a href="{{ route('references.index') }}" class="btn-link mt-6">Toutes nos références →</a>
                </div>
                <div class="lg:col-span-7 reveal reveal-delay-1">
                    <x-ui.unit padding="p-7 sm:p-9">
                        <p class="text-[17px] leading-relaxed text-navy-900/85">{{ $reference->description }}</p>
                        @if ($reference->services)
                            <ul class="mt-6 flex flex-wrap gap-2">
                                @foreach ($reference->services as $item)
                                    <li class="rounded-sm bg-navy-50 px-2.5 py-1 text-xs font-medium text-navy-900">{{ $item }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @if ($reference->website_url)
                            <a href="{{ $reference->website_url }}" target="_blank" rel="noopener" class="btn-link mt-6 font-mono text-sm">{{ preg_replace('#^https?://#', '', $reference->website_url) }} ↗</a>
                        @endif
                    </x-ui.unit>
                </div>
            </div>
        </section>
    @endif

    {{-- ===== FAQ + ACTUALITÉS ===== --}}
    <section class="wrap mt-24 grid gap-14 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <x-ui.section-heading code="FAQ" kicker="Questions fréquentes" title="Ce que l'on nous demande avant de commander." class="reveal" />
            <div class="mt-8 divide-y divide-navy-900/10 border-y border-navy-900/10" x-data="{ open: 0 }">
                @foreach ($faqs as $faq)
                    <div class="reveal">
                        <h3>
                            <button type="button" id="faq-{{ $faq->id }}-question" class="flex w-full items-center justify-between gap-4 py-5 text-left font-semibold text-navy-900 hover:text-emerald-700" @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}" :aria-expanded="open === {{ $loop->index }}" aria-controls="faq-{{ $faq->id }}-reponse">
                                <span>{{ $faq->question }}</span>
                                <svg class="size-5 shrink-0 text-navy-900 transition-transform duration-200" :class="open === {{ $loop->index }} && 'rotate-180'" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 7.5l5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                        </h3>
                        <div id="faq-{{ $faq->id }}-reponse" role="region" aria-labelledby="faq-{{ $faq->id }}-question" x-show="open === {{ $loop->index }}" x-collapse x-cloak>
                            <p class="pb-6 text-[15px] leading-relaxed text-ardoise">{{ $faq->answer }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('contact') }}#faq" class="btn-link mt-6">Toutes les questions →</a>
        </div>
        <div class="lg:col-span-5">
            <x-ui.section-heading code="NEWS" kicker="Actualités" title="Dernières nouvelles." class="reveal" />
            <div class="mt-8 grid gap-4">
                @forelse ($posts as $post)
                    <x-ui.unit :href="route('posts.show', $post)" padding="p-5" class="no-underline reveal transition hover:-translate-y-0.5 hover:shadow-lg">
                        <p class="label text-ardoise">{{ $post->published_at?->translatedFormat('d F Y') }} @if($post->category)· {{ $post->category }}@endif</p>
                        <h3 class="mt-3 text-lg font-semibold leading-snug text-navy-900">{{ $post->title }}</h3>
                        <p class="mt-2 text-sm text-ardoise line-clamp-2">{{ $post->excerpt }}</p>
                    </x-ui.unit>
                @empty
                    <p class="text-ardoise">Aucune actualité pour le moment.</p>
                @endforelse
            </div>
            <a href="{{ route('posts.index') }}" class="btn-link mt-6">Toutes les actualités →</a>
        </div>
    </section>

    <x-ui.cta-band />
</x-layouts.app>
