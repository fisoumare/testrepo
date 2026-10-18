@php
    $steps = [
        ['Cadrage', 'Un périmètre écrit avant de commencer : ce qui est compris, ce qui ne l\'est pas, le prix et le délai. Pour les projets plus larges, un atelier de cadrage ou un cahier des charges.'],
        ['Maquette', 'Maquette de l\'accueil et d\'une page intérieure pour un site, des écrans principaux pour une application, avec 2 cycles de retours avant tout développement.'],
        ['Développement', 'Laravel et Filament pour les applications et les espaces d\'administration, October CMS ou Laravel pour les sites, Android et iOS pour le mobile.'],
        ['Validation', 'Vous vérifiez la livraison sur la base du périmètre écrit. Le solde est dû à la validation.'],
        ['Mise en ligne', 'Mise en service sur nos serveurs ou sur le vôtre : nom de domaine, certificat SSL, envoi des e-mails, sauvegarde.'],
        ['Prise en main', 'Prise en main de votre espace d\'administration comprise dans les créations ; formations dans vos locaux à Conakry ou à distance.'],
        ['Garantie 3 mois', setting('warranty')],
        ['Maintenance', 'À la fin de la garantie : contrats de maintenance annuels, interventions ponctuelles ou heures prépayées, selon votre besoin.'],
    ];
@endphp
<x-layouts.app title="À propos" description="SOUMATIC, à Conakry : une entreprise guinéenne qui conçoit, héberge et maintient des sites, des applications web et mobiles et des logiciels de gestion. Prix fixes, conditions écrites, technologies Laravel, Filament, October CMS, Android et iOS.">
    <x-ui.page-header code="SOUMATIC" kicker="À propos" title="Des logiciels conçus, hébergés et maintenus à Conakry.">
        {{ setting('baseline') }} {{ setting('company_name') }} travaille à prix fixes, avec des conditions écrites, les mêmes pour tous.
        <x-slot:aside>
            <dl class="spec unit rounded-lg border border-navy-900/10 bg-white p-5 text-navy-900/30 shadow-unit">
                <dt>{{ setting('manager_title', 'Gérant') }}</dt><dd>{{ setting('manager_name') }}</dd>
                <dt>Siège</dt><dd>{{ setting('address') }}, {{ setting('city') }}, {{ setting('country') }}</dd>
                <dt>RCCM</dt><dd class="font-mono text-[13px]">{{ setting('rccm') }}</dd>
                <dt>NIF</dt><dd class="font-mono text-[13px]">{{ setting('nif') }}</dd>
                <dt>Horaires</dt><dd>{{ setting('hours') }}</dd>
            </dl>
        </x-slot:aside>
    </x-ui.page-header>

    {{-- ===== CE QUE NOUS FAISONS ===== --}}
    <section class="wrap mt-24 grid gap-12 lg:grid-cols-12" aria-labelledby="ce-que-nous-faisons">
        <div class="lg:col-span-5">
            <x-ui.section-heading code="U01–U08" kicker="Ce que nous faisons" title="Un catalogue, quatre solutions." class="reveal" id="ce-que-nous-faisons">
                {{ $servicesCount }} prestations à prix fixe, réparties en {{ $categoriesCount }} familles : de la conception d'un site ou d'une application à son hébergement, sa maintenance et la formation de vos équipes. Et quatre logiciels prêts à l'emploi.
            </x-ui.section-heading>
            <div class="mt-8 flex flex-wrap gap-3 reveal reveal-delay-1">
                <a href="{{ route('services.index') }}" class="btn-navy">Voir les prestations</a>
                <a href="{{ route('solutions.index') }}" class="btn-ghost">Voir les solutions</a>
            </div>
        </div>
        <div class="grid gap-8 lg:col-span-7">
            <ul class="divide-y divide-navy-900/10 border-y border-navy-900/10 reveal">
                @foreach ($categories as $category)
                    <li>
                        <a href="{{ route('services.category', $category) }}" class="group flex items-center gap-4 py-3 transition hover:text-emerald-700">
                            <span class="label w-9 shrink-0 text-emerald-700">{{ $category->code }}</span>
                            <span class="min-w-0 flex-1 font-medium">{{ $category->name }}</span>
                            <span class="label shrink-0 text-ardoise tabular">{{ $category->services_count }} prest.</span>
                            <svg class="size-4 shrink-0 text-ardoise transition group-hover:translate-x-0.5 group-hover:text-emerald-700" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </li>
                @endforeach
            </ul>
            <ul class="grid gap-3 sm:grid-cols-2">
                @foreach ($solutions as $solution)
                    <li>
                        <a href="{{ route('solutions.show', $solution) }}" class="unit flex h-full items-center gap-3 rounded-lg border border-navy-900/10 bg-paper px-4 py-3 text-navy-900/30 no-underline transition hover:bg-navy-50 reveal reveal-delay-{{ $loop->index % 2 }}">
                            <span class="label w-8 shrink-0 text-ardoise">{{ $solution->code }}</span>
                            <x-ui.led :color="$solution->accent" />
                            <span class="min-w-0">
                                <span class="block font-semibold text-navy-900">{{ $solution->name }}</span>
                                <span class="block text-xs text-ardoise">{{ $solution->tagline }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ===== COMMENT NOUS TRAVAILLONS ===== --}}
    <section class="mt-24 bg-ink text-white rack-lines" aria-labelledby="methode">
        <div class="wrap py-16 sm:py-20">
            <x-ui.section-heading code="PROC" kicker="Comment nous travaillons" title="Du cadrage à la maintenance, dans cet ordre." dark class="reveal" id="methode">
                Le même parcours pour un site vitrine, une application de gestion ou le déploiement d'une solution : chaque étape est écrite dans la prestation que vous commandez.
            </x-ui.section-heading>
            <ol class="mt-12 grid gap-px overflow-hidden rounded-xl border border-white/10 bg-white/10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($steps as [$title, $detail])
                    <li class="group relative flex flex-col bg-ink p-6 transition hover:bg-ink-soft reveal reveal-delay-{{ $loop->index % 4 }}">
                        <span class="flex items-center justify-between">
                            <span class="font-mono text-2xl font-semibold tracking-tight text-emerald-400 tabular">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="led {{ $loop->last ? 'led-on' : 'led-off' }} group-hover:led-on group-hover:bg-emerald-400 transition" aria-hidden="true"></span>
                        </span>
                        <span class="mt-5 text-lg font-semibold text-white">{{ $title }}</span>
                        <span class="mt-2 text-sm leading-relaxed text-ardoise-light">{{ $detail }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ===== TECHNOLOGIES + ENGAGEMENTS ===== --}}
    <section class="wrap mt-24 grid gap-14 lg:grid-cols-12" aria-labelledby="technologies">
        <div class="lg:col-span-5">
            <x-ui.section-heading code="TECH" kicker="Technologies" title="Des outils éprouvés, choisis avec vous." class="reveal" id="technologies">
                Laravel et Filament pour les applications web et les espaces d'administration, October CMS ou Laravel pour les sites, Android et iOS pour les applications mobiles reliées à une API REST. Les sites livrés comprennent un espace d'administration pour modifier vous-même textes et images.
            </x-ui.section-heading>
            <ul class="mt-8 flex flex-wrap gap-2 reveal reveal-delay-1" aria-label="Technologies">
                @foreach (['Laravel', 'Filament', 'October CMS', 'Android', 'iOS'] as $tech)
                    <li class="flex items-center gap-2 rounded-md border border-navy-900/10 bg-white px-3 py-1.5 font-mono text-[13px] text-navy-900 shadow-unit"><span class="led led-on size-1.5" aria-hidden="true"></span>{{ $tech }}</li>
                @endforeach
            </ul>
        </div>
        <div class="lg:col-span-7">
            <p class="label flex items-center gap-3 text-emerald-700"><span class="rounded-sm border border-emerald-700/30 px-1.5 py-0.5">SPEC</span> Nos engagements</p>
            <dl class="mt-6 divide-y divide-navy-900/10 border-y border-navy-900/10 reveal">
                @foreach ([
                    ['Prix', 'Fixes, affichés hors taxes en GNF, TVA 18 % en sus. Devis valable 30 jours. Seuls les projets hors catalogue font l\'objet d\'un devis spécifique.'],
                    ['Paiement', setting('payment_terms')],
                    ['Garantie', setting('warranty')],
                    ['Assistance', setting('hours').', par '.mb_strtolower((string) setting('support_channels')).'. Première réponse au plus tard le jour ouvré suivant.'],
                    ['Hors Conakry', 'Ateliers, formations et diagnostics dans vos locaux à Conakry ou à distance ; hors Conakry, frais de déplacement et de séjour en sus.'],
                    ['Code source', 'Les droits cédés sont précisés dans chaque devis ou contrat signé. Avant paiement complet, aucun fichier final, code ou accès technique n\'est remis.'],
                ] as [$term, $detail])
                    <div class="grid gap-1 py-4 sm:grid-cols-12 sm:gap-6">
                        <dt class="label pt-1 text-ardoise sm:col-span-3">{{ $term }}</dt>
                        <dd class="text-[15px] leading-relaxed text-navy-900/85 sm:col-span-9">{{ $detail }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- ===== RÉFÉRENCE ===== --}}
    @if ($reference)
        <section class="mt-24 border-y border-navy-900/8 bg-paper" aria-labelledby="reference">
            <div class="wrap grid gap-10 py-16 sm:py-20 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-5">
                    <x-ui.section-heading code="REF" kicker="Référence" :title="$reference->headline" class="reveal" id="reference">
                        {{ $reference->name }} ({{ $reference->acronym }}), {{ $reference->location }}.
                    </x-ui.section-heading>
                    <a href="{{ route('references.index') }}" class="btn-link mt-6 reveal reveal-delay-1">Toutes nos références →</a>
                </div>
                <div class="lg:col-span-7 reveal reveal-delay-1">
                    <x-ui.unit padding="p-7 sm:p-9" class="text-navy-900/30">
                        <p class="text-[16px] leading-relaxed text-navy-900/85">{{ $reference->description }}</p>
                        @if ($reference->website_url)
                            <a href="{{ $reference->website_url }}" target="_blank" rel="noopener" class="btn-link mt-6 font-mono text-sm">{{ preg_replace('#^https?://#', '', $reference->website_url) }} ↗</a>
                        @endif
                    </x-ui.unit>
                </div>
            </div>
        </section>
    @endif

    <x-ui.cta-band />
</x-layouts.app>
