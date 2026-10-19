<x-layouts.app title="Solutions clés en main" description="EduGes, MediGes SST, MediGes et Compiniya : quatre logiciels prêts à l'emploi, en abonnement mensuel hébergé par SOUMATIC ou en licence installée. Prix en GNF.">
    <x-ui.page-header code="S01–S04" kicker="Solutions clés en main" title="Quatre logiciels prêts à l'emploi, en abonnement ou en licence." dark>
        Gestion scolaire, santé au travail, gestion hospitalière et gestion commerciale. Hébergés par SOUMATIC en abonnement mensuel, ou installés sur votre serveur en licence. Mise en service, formation et reprise des données au catalogue.
        <x-slot:aside>
            <ul class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1" aria-label="Aller à une solution">
                @foreach ($solutions as $solution)
                    <li>
                        <a href="#{{ $solution->slug }}" class="group flex items-center gap-3 rounded-md border border-white/10 px-3 py-2.5 transition hover:border-emerald-400/60 hover:bg-white/[0.04]">
                            <span class="label w-8 shrink-0 text-ardoise-light group-hover:text-emerald-400">{{ $solution->code }}</span>
                            <x-ui.led :color="$solution->accent" />
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold text-white">{{ $solution->name }}</span>
                                <span class="block text-xs text-ardoise-light">{{ $solution->tagline }}</span>
                            </span>
                            <svg class="size-4 shrink-0 text-ardoise-light transition group-hover:translate-y-0.5 group-hover:text-emerald-400" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 6l5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-slot:aside>
    </x-ui.page-header>

    {{-- ===== UNE GRANDE UNITÉ PAR SOLUTION ===== --}}
    @foreach ($solutions as $solution)
        <section id="{{ $solution->slug }}" class="wrap scroll-mt-32 {{ $loop->first ? 'mt-16 sm:mt-20' : 'mt-10' }}" aria-labelledby="{{ $solution->slug }}-titre">
            <x-ui.unit padding="p-0" class="overflow-hidden text-navy-900/30 reveal">
                <div class="grid lg:grid-cols-12">
                    {{-- Présentation : colonne en flex-col, le bloc « Compris » et les boutons calés en bas, à hauteur du tableau --}}
                    <div class="flex flex-col border-b border-navy-900/10 p-6 sm:p-8 lg:col-span-5 lg:border-b-0 lg:border-r lg:p-10" style="box-shadow: inset 4px 0 0 {{ $solution->accent }}">
                        <p class="label flex items-center gap-3 text-ardoise">
                            <span class="rounded-sm border border-navy-900/15 px-1.5 py-0.5">{{ $solution->code }}</span>
                            <x-ui.led :color="$solution->accent" />
                            <span>{{ $solution->tagline }}</span>
                        </p>
                        <h2 id="{{ $solution->slug }}-titre" class="mt-5 text-3xl font-semibold tracking-tight text-navy-900 sm:text-4xl">{{ $solution->name }}</h2>
                        <p class="mt-2 text-ardoise">{{ $solution->audience }}</p>
                        <p class="mt-5 text-[15px] leading-relaxed text-navy-900/85">{{ $solution->summary }}</p>

                        @if ($solution->features)
                            <p class="label mt-8 text-emerald-700">Fonctions</p>
                            <ul class="mt-3 flex flex-wrap gap-2">
                                @foreach ($solution->features as $feature)
                                    <li class="rounded-sm border border-navy-900/10 bg-paper px-2 py-1 text-xs font-medium text-navy-900">{{ $feature }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-auto pt-8">
                            @if ($solution->included)
                                <p class="label text-emerald-700">Compris dans l'abonnement</p>
                                <ul class="mt-3 space-y-2 text-sm leading-relaxed text-navy-900/80">
                                    @foreach ($solution->included as $item)
                                        <li class="flex gap-3"><span class="led led-on mt-[7px]" aria-hidden="true"></span><span class="min-w-0">{{ $item }}</span></li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="mt-8 flex flex-wrap gap-3">
                                <a href="{{ route('solutions.show', $solution) }}" class="btn-navy">Découvrir {{ $solution->name }}</a>
                                <a href="{{ route('contact', ['sujet' => 'demo', 'solution' => $solution->slug]) }}" class="btn-ghost">Demander une démonstration</a>
                            </div>
                        </div>
                    </div>

                    {{-- Comparatif des formules --}}
                    <div class="bg-paper/70 p-6 sm:p-8 lg:col-span-7 lg:p-10">
                        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                            <h3 class="text-lg font-semibold text-navy-900">Formules et tarifs</h3>
                            <span class="label text-ardoise">Hors taxes · TVA 18&nbsp;% en sus</span>
                        </div>
                        <x-ui.offer-table :offers="$offers[$solution->id]" :solution="$solution" class="mt-4" />
                        <p class="mt-4 text-xs leading-relaxed text-ardoise">
                            Abonnements payables au mois ou à l'année. Mise en service facturée une seule fois, pour l'abonnement comme pour la licence.
                            @if ($solution->website_url)
                                <a href="{{ $solution->website_url }}" target="_blank" rel="noopener" class="btn-link ml-1 font-mono text-xs">{{ preg_replace('#^https?://#', '', $solution->website_url) }} ↗</a>
                            @endif
                        </p>
                    </div>
                </div>
            </x-ui.unit>
        </section>
    @endforeach

    {{-- ===== ABONNEMENT OU LICENCE ? (deux formules valides : un code chacune, pas de LED d'état) ===== --}}
    <section class="mt-24 border-y border-navy-900/8 bg-paper">
        <div class="wrap grid gap-12 py-16 sm:py-20 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <x-ui.section-heading code="FAQ" kicker="Avant de choisir" title="Abonnement ou licence ?" class="reveal">
                    Les deux formules donnent accès au même logiciel. Elles diffèrent par l'hébergement, le mode de paiement et la maintenance.
                </x-ui.section-heading>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                    <div class="unit rounded-lg border border-navy-900/10 bg-white p-5 text-navy-900/30 reveal">
                        <p class="label flex items-center gap-3 text-navy-900"><span class="rounded-sm border border-navy-900/15 px-1.5 py-0.5 text-emerald-700">F01</span> Abonnement</p>
                        <ul class="mt-4 space-y-2 text-sm leading-relaxed text-navy-900/80">
                            <li>Mensuel, hébergé sur nos serveurs</li>
                            <li>Sauvegardes, mises à jour et assistance compris</li>
                            <li>Payable au mois ou à l'année</li>
                            <li>Mise en service facturée une fois</li>
                        </ul>
                    </div>
                    <div class="unit rounded-lg border border-navy-900/10 bg-white p-5 text-navy-900/30 reveal reveal-delay-1">
                        <p class="label flex items-center gap-3 text-navy-900"><span class="rounded-sm border border-navy-900/15 px-1.5 py-0.5 text-emerald-700">F02</span> Licence installée</p>
                        <ul class="mt-4 space-y-2 text-sm leading-relaxed text-navy-900/80">
                            <li>Achetée une seule fois</li>
                            <li>Installée sur votre serveur</li>
                            <li>Maintenance annuelle&nbsp;: 16&nbsp;% du prix de la licence (mises à jour et assistance)</li>
                            <li>Mise en service facturée une fois</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-7">
                <div class="divide-y divide-navy-900/10 border-y border-navy-900/10">
                    @foreach ($faqs as $faq)
                        <article class="py-6 reveal">
                            <h3 class="font-semibold text-navy-900">{{ $faq->question }}</h3>
                            <p class="mt-2 text-[15px] leading-relaxed text-ardoise">{{ $faq->answer }}</p>
                        </article>
                    @endforeach
                </div>
                <a href="{{ route('contact') }}#faq" class="btn-link mt-6">Toutes les questions fréquentes →</a>
            </div>
        </div>
    </section>

    <x-ui.cta-band title="Une démonstration ? Un déploiement à préparer ?" />
</x-layouts.app>
