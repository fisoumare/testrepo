<x-layouts.app :title="$title" :description="$description">
    {{-- ===== HÉRO ===== --}}
    <section class="relative overflow-hidden bg-ink text-white rack-lines">
        <div class="absolute inset-y-0 right-0 w-1/2 grid-dots-dark opacity-50 [mask-image:linear-gradient(90deg,transparent,#000)]" aria-hidden="true"></div>
        <span class="pointer-events-none absolute right-0 -top-6 select-none font-mono text-[10rem] font-semibold leading-none text-white/[0.035] sm:text-[14rem] lg:text-[18rem]" aria-hidden="true">{{ $solution->code }}</span>
        <div class="wrap relative grid gap-12 py-14 sm:py-20 lg:grid-cols-12 lg:items-end lg:py-24">
            <div class="min-w-0 lg:col-span-8">
                <x-ui.breadcrumb :items="[['Solutions', route('solutions.index')], [$solution->name, null]]" dark />
                <p class="label mt-10 flex items-center gap-3 text-emerald-400">
                    <span class="rounded-sm border border-emerald-400/40 px-1.5 py-0.5">{{ $solution->code }}</span>
                    <x-ui.led :color="$solution->accent" />
                    <span>Solution clés en main</span>
                </p>
                <h1 class="h-display mt-5 text-4xl text-white sm:text-5xl lg:text-6xl">{{ $solution->name }}</h1>
                <p class="mt-3 text-2xl font-medium tracking-tight text-ardoise-light sm:text-3xl">{{ $solution->tagline }}</p>
                <p class="mt-6 flex flex-wrap items-baseline gap-x-3 gap-y-1 text-sm">
                    <span class="label text-ardoise-light">Pour</span>
                    <span class="text-white">{{ $solution->audience }}</span>
                </p>
                <p class="lead mt-6 max-w-2xl text-ardoise-light">{{ $solution->summary }}</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('contact', ['sujet' => 'demo', 'solution' => $solution->slug]) }}" class="btn-primary">Demander une démonstration</a>
                    <a href="#formules" class="btn-ghost-dark">Formules et tarifs</a>
                    @if ($solution->website_url)
                        <a href="{{ $solution->website_url }}" target="_blank" rel="noopener" class="btn-link self-center font-mono text-emerald-400 hover:text-emerald-300">{{ preg_replace('#^https?://#', '', $solution->website_url) }} ↗</a>
                    @endif
                </div>
            </div>

            {{-- Fiche technique : une seule LED, de la couleur de la solution (comme le code S0x et la liste des fonctions) --}}
            <div class="lg:col-span-4">
                <div class="unit rounded-xl border border-white/10 bg-ink-soft/80 p-6 text-emerald-400/70">
                    <p class="label flex items-center justify-between text-ardoise-light">
                        <span>Fiche technique</span>
                        <x-ui.led :color="$solution->accent" />
                    </p>
                    <dl class="spec spec-dark mt-5">
                        <dt>Code</dt><dd class="font-mono text-[13px]">{{ $solution->code }}</dd>
                        <dt>Formules</dt><dd>{{ \Illuminate\Support\Str::ucfirst(mb_strtolower($offers->pluck('label')->filter(fn ($label) => in_array($label, ['Abonnement', 'Licence installée'], true))->implode(' ou '))) ?: 'Sur devis' }}</dd>
                        @if ($fromMonthly)
                            <dt>À partir de</dt><dd class="tabular"><span class="font-semibold">{{ gnf($fromMonthly, false) }}</span> <span class="label text-ardoise-light">GNF HT / mois</span></dd>
                        @endif
                        <dt>Prestations</dt><dd>{{ $solution->services->count() }} au catalogue</dd>
                        <dt>Assistance</dt><dd>{{ typo(setting('hours_short')) }}</dd>
                        @if ($solution->website_url)
                            <dt>Site</dt><dd><a href="{{ $solution->website_url }}" target="_blank" rel="noopener" class="font-mono text-[13px] text-emerald-400 hover:text-emerald-300">{{ preg_replace('#^https?://#', '', $solution->website_url) }} ↗</a></dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== FONCTIONS : liste numérotée compacte sur deux colonnes, sans carte vide ===== --}}
    @if ($solution->features)
        <section class="wrap mt-24 grid gap-10 lg:grid-cols-12" aria-labelledby="fonctions">
            <div class="lg:col-span-4">
                <x-ui.section-heading code="{{ $solution->code }}" kicker="Fonctions" title="Ce que couvre {{ $solution->name }}." class="reveal" id="fonctions" />
                <p class="label mt-6 flex items-center gap-2 text-ardoise reveal">
                    <x-ui.led :color="$solution->accent" />
                    <span class="tabular">{{ count($solution->features) }} {{ count($solution->features) > 1 ? 'fonctions' : 'fonction' }} au catalogue</span>
                </p>
            </div>
            <ol class="grid border-t border-navy-900/10 sm:grid-cols-2 sm:gap-x-10 lg:col-span-8 reveal reveal-delay-1">
                @foreach ($solution->features as $feature)
                    <li class="flex items-baseline gap-4 border-b border-navy-900/10 py-4">
                        <span class="label w-6 shrink-0 text-ardoise">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="min-w-0 text-[17px] font-semibold leading-snug text-navy-900">{{ $feature }}</span>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    {{-- ===== COMPRIS ===== --}}
    @if ($solution->included)
        <section class="mt-24 border-y border-navy-900/8 bg-paper" aria-labelledby="compris">
            <div class="wrap grid gap-10 py-16 sm:py-20 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <x-ui.section-heading code="INCL" kicker="Abonnement" title="Compris dans l'abonnement." class="reveal" id="compris">
                        Hébergement, sauvegardes, mises à jour et assistance&nbsp;: l'abonnement est payable au mois ou à l'année. Ce qui n'est pas compris est précisé sur chaque fiche.
                    </x-ui.section-heading>
                </div>
                <ul class="grid gap-3 lg:col-span-7">
                    @foreach ($solution->included as $item)
                        <li class="flex items-start gap-4 rounded-lg border border-navy-900/10 bg-white px-5 py-4 text-[15px] leading-relaxed text-navy-900/85 reveal">
                            <span class="led led-on mt-2" aria-hidden="true"></span>
                            <span class="min-w-0">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ===== EN DÉTAIL : avant les formules et la mise en service, dont la fiche détaille ensuite le périmètre ===== --}}
    @if ($solution->description)
        <section class="wrap mt-24 grid gap-10 lg:grid-cols-12" aria-labelledby="en-detail">
            <div class="lg:col-span-4">
                <div class="lg:sticky lg:top-32">
                    <x-ui.section-heading code="{{ $solution->code }}" kicker="En détail" title="{{ $solution->name }}, en pratique." class="reveal" id="en-detail" />
                    <a href="{{ route('contact', ['sujet' => 'demo', 'solution' => $solution->slug]) }}" class="btn-primary mt-8">Demander une démonstration</a>
                </div>
            </div>
            <div class="prose-site min-w-0 text-[17px] lg:col-span-8 reveal">{!! $solution->description !!}</div>
        </section>
    @endif

    {{-- ===== FORMULES ET TARIFS ===== --}}
    <section id="formules" class="wrap mt-24 scroll-mt-28" aria-labelledby="formules-titre">
        <div class="grid gap-8 lg:grid-cols-12 lg:items-end">
            <x-ui.section-heading code="GNF" kicker="Formules et tarifs" title="Des prix fixes, affichés hors taxes." class="lg:col-span-8 reveal" id="formules-titre">
                TVA 18&nbsp;% en sus. Abonnements, hébergement et maintenance payés d'avance&#8239;; licence et mise en service&nbsp;: 50&nbsp;% à la commande, solde à la validation.
            </x-ui.section-heading>
            <div class="lg:col-span-4 lg:text-right reveal reveal-delay-1">
                <a href="{{ route('quote') }}" class="btn-navy">Composer une estimation</a>
            </div>
        </div>
        <x-ui.offer-table :offers="$offers" :solution="$solution" class="mt-10 reveal" />
    </section>

    {{-- ===== MISE EN SERVICE (une seule fois : la fiche de la prestation, qui fait foi) ===== --}}
    @if ($setupServices->isNotEmpty())
        <section class="wrap mt-16" aria-labelledby="mise-en-service">
            <div class="grid gap-6 {{ $setupServices->count() > 1 ? 'lg:grid-cols-2' : '' }}">
                @foreach ($setupServices as $service)
                    <article class="unit rounded-xl border border-emerald-500/30 bg-emerald-50/40 p-6 text-emerald-700/50 sm:p-8 reveal">
                        <p class="label flex flex-wrap items-center gap-x-3 gap-y-1 text-emerald-700">
                            <span class="led led-on" aria-hidden="true"></span>
                            <span>Mise en service · facturée une fois</span>
                            <span class="text-ardoise">{{ $service->number }}</span>
                        </p>
                        <div class="mt-4 flex flex-wrap items-end justify-between gap-x-6 gap-y-3">
                            <h2 @if($loop->first) id="mise-en-service" @endif class="text-2xl font-semibold tracking-tight text-navy-900">{{ \Illuminate\Support\Str::after($service->name, ' – ') }}</h2>
                            <p class="flex items-baseline gap-2"><x-ui.price :service="$service" size="base" /><span class="label text-ardoise">· {{ $service->unit }}</span></p>
                        </div>
                        <x-ui.text-blocks :text="$service->description" class="mt-4 text-[15px]" />
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('quote', ['service' => $service->slug]) }}" class="btn-primary">Ajouter à mon estimation</a>
                            <a href="{{ route('services.show', $service) }}" class="btn-link">Fiche détaillée →</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===== AUTRES SOLUTIONS ===== --}}
    @if ($others->isNotEmpty())
        <section class="mt-24 bg-ink text-white rack-lines" aria-labelledby="autres">
            <div class="wrap py-16 sm:py-20">
                <x-ui.section-heading code="S01–S04" kicker="Les autres solutions" title="Même rack, autres unités." dark class="reveal" id="autres" />
                <ul class="mt-10 grid gap-4 md:grid-cols-3">
                    @foreach ($others as $other)
                        <li>
                            <x-ui.unit :href="route('solutions.show', $other)" dark class="group flex h-full flex-col no-underline transition hover:bg-ink-soft reveal reveal-delay-{{ $loop->index % 3 }}">
                                <span class="flex items-center justify-between">
                                    <span class="label text-ardoise-light">{{ $other->code }}</span>
                                    <x-ui.led :color="$other->accent" />
                                </span>
                                <span class="mt-5 block text-xl font-semibold text-white">{{ $other->name }}</span>
                                <span class="mt-1 block text-sm text-ardoise-light">{{ $other->tagline }}</span>
                                <span class="mt-4 block text-sm leading-relaxed text-ardoise-light/80">{{ $other->audience }}</span>
                                <span class="btn-link mt-auto pt-6 text-emerald-400 group-hover:text-emerald-300">Découvrir →</span>
                            </x-ui.unit>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <x-ui.cta-band title="{{ $solution->name }} pour votre structure ?" text="Demandez une démonstration ou une estimation : nous revenons vers vous avec une proposition à prix fixe. {{ setting('hours') }}" />
</x-layouts.app>
