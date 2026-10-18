<x-layouts.app title="Références" description="Les références de SOUMATIC : la plateforme nationale de santé au travail de la DGSST (snst-guinee.com), et la manière dont nous travaillons avec chaque client.">
    <x-ui.page-header code="REF" kicker="Références" title="Ce que nous avons réalisé, et pour qui." dark>
        Chaque référence publiée décrit ce que SOUMATIC a réalisé : le périmètre, les services rendus, le site en ligne. Les conditions de travail sont les mêmes pour chaque client.
    </x-ui.page-header>

    {{-- ===== RÉFÉRENCE MISE EN AVANT ===== --}}
    @if ($featured)
        <section class="wrap mt-16 sm:mt-20" aria-labelledby="ref-{{ $featured->slug }}">
            <x-ui.unit padding="p-0" class="overflow-hidden text-navy-900/30 reveal">
                <div class="grid lg:grid-cols-12">
                    <div class="bg-ink p-7 text-white sm:p-9 lg:col-span-4 lg:p-10 rack-lines">
                        <p class="label flex items-center gap-2 text-emerald-400"><span class="led led-on" aria-hidden="true"></span> Référence</p>
                        <p class="mt-8 font-mono text-5xl font-semibold tracking-tight text-white sm:text-6xl">{{ $featured->acronym ?? $featured->name }}</p>
                        @if ($featured->acronym)
                            <p class="mt-3 text-[15px] leading-snug text-ardoise-light">{{ $featured->name }}</p>
                        @endif
                        <dl class="spec spec-dark mt-8">
                            @if ($featured->sector)<dt>Secteur</dt><dd>{{ $featured->sector }}</dd>@endif
                            @if ($featured->location)<dt>Lieu</dt><dd>{{ $featured->location }}</dd>@endif
                            @if ($featured->year)<dt>Année</dt><dd class="font-mono text-[13px]">{{ $featured->year }}</dd>@endif
                            @if ($featured->website_url)
                                <dt>Site</dt><dd><a href="{{ $featured->website_url }}" target="_blank" rel="noopener" class="font-mono text-[13px] text-emerald-400 hover:text-emerald-300">{{ preg_replace('#^https?://#', '', $featured->website_url) }} ↗</a></dd>
                            @endif
                        </dl>
                    </div>
                    <div class="p-7 sm:p-9 lg:col-span-8 lg:p-10">
                        <h2 id="ref-{{ $featured->slug }}" class="h-section text-navy-900">{{ $featured->headline }}</h2>
                        <p class="mt-6 max-w-3xl text-[17px] leading-relaxed text-navy-900/85">{{ $featured->description }}</p>
                        @if ($featured->services)
                            <p class="label mt-8 text-ardoise">Services rendus</p>
                            <ul class="mt-3 flex flex-wrap gap-2">
                                @foreach ($featured->services as $item)
                                    <li class="flex items-center gap-2 rounded-sm border border-navy-900/10 bg-paper px-2.5 py-1 text-xs font-medium text-navy-900"><span class="led led-on size-1.5" aria-hidden="true"></span>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @if ($featured->website_url)
                            <a href="{{ $featured->website_url }}" target="_blank" rel="noopener" class="btn-ghost mt-8">Voir {{ preg_replace('#^https?://#', '', $featured->website_url) }} ↗</a>
                        @endif
                    </div>
                </div>
            </x-ui.unit>
        </section>
    @else
        <section class="wrap mt-16 sm:mt-20">
            <div class="unit rounded-xl border border-navy-900/10 bg-paper p-8 text-navy-900/30 sm:p-12">
                <p class="label flex items-center gap-2 text-ardoise"><span class="led led-off" aria-hidden="true"></span> Références</p>
                <p class="lead mt-4">Aucune référence publiée pour le moment.</p>
            </div>
        </section>
    @endif

    {{-- ===== AUTRES RÉFÉRENCES (si publiées) ===== --}}
    @if ($others->isNotEmpty())
        <section class="wrap mt-20" aria-labelledby="autres-references">
            <x-ui.section-heading code="REF" kicker="Autres références" title="Ils nous ont fait confiance." class="reveal" id="autres-references" />
            <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($others as $reference)
                    <li>
                        <x-ui.unit padding="p-5" class="flex h-full flex-col text-navy-900/30 reveal reveal-delay-{{ $loop->index % 3 }}">
                            <p class="flex items-center justify-between gap-3">
                                <span class="font-mono text-xl font-semibold tracking-tight text-navy-900">{{ $reference->acronym ?? $reference->name }}</span>
                                @if ($reference->year)<span class="label text-ardoise">{{ $reference->year }}</span>@endif
                            </p>
                            @if ($reference->acronym)<p class="mt-1 text-sm text-ardoise">{{ $reference->name }}</p>@endif
                            @if ($reference->headline)<h3 class="mt-4 font-semibold leading-snug text-navy-900">{{ $reference->headline }}</h3>@endif
                            @if ($reference->description)<p class="mt-2 text-sm leading-relaxed text-ardoise line-clamp-4">{{ $reference->description }}</p>@endif
                            <p class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-5 text-xs text-ardoise">
                                <span>{{ collect([$reference->sector, $reference->location])->filter()->implode(' · ') }}</span>
                                @if ($reference->website_url)<a href="{{ $reference->website_url }}" target="_blank" rel="noopener" class="btn-link text-xs">{{ preg_replace('#^https?://#', '', $reference->website_url) }} ↗</a>@endif
                            </p>
                        </x-ui.unit>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ===== COMMENT NOUS TRAVAILLONS ===== --}}
    <section class="mt-24 border-y border-navy-900/8 bg-paper" aria-labelledby="methode">
        <div class="wrap py-16 sm:py-20">
            <div class="grid gap-8 lg:grid-cols-12 lg:items-end">
                <x-ui.section-heading code="SPEC" kicker="Comment nous travaillons" title="Les mêmes conditions écrites, pour chaque client." class="lg:col-span-8 reveal" id="methode">
                    Ce qui vaut pour la DGSST vaut pour une école ou une PME : un périmètre écrit, un prix fixe et des engagements connus avant de commander.
                </x-ui.section-heading>
                <div class="lg:col-span-4 lg:text-right reveal reveal-delay-1">
                    <a href="{{ route('about') }}" class="btn-link">Notre manière de travailler →</a>
                </div>
            </div>
            <ol class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Prix fixes', 'Affichés hors taxes en GNF, TVA 18 % en sus. Chaque prestation a un périmètre écrit : ce qui n\'est pas compris est dit. Devis valable 30 jours.'],
                    ['50 % à la commande', setting('payment_terms')],
                    ['Garantie 3 mois', setting('warranty')],
                    ['Assistance', setting('hours').'. '.setting('support_channels').'. Première réponse au plus tard le jour ouvré suivant.'],
                ] as [$term, $detail])
                    <li class="unit flex flex-col rounded-lg border border-navy-900/10 bg-white p-5 text-navy-900/30 shadow-unit reveal reveal-delay-{{ $loop->index % 4 }}">
                        <span class="flex items-center justify-between">
                            <span class="label text-ardoise">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="led led-on" aria-hidden="true"></span>
                        </span>
                        <span class="mt-5 text-lg font-semibold text-navy-900">{{ $term }}</span>
                        <span class="mt-2 text-sm leading-relaxed text-ardoise">{{ $detail }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <x-ui.cta-band title="Votre projet, aux mêmes conditions." />
</x-layouts.app>
