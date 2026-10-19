@php
    // La section « Hébergement » n'existe que si le paramètre est renseigné dans le back-office :
    // une page à valeur légale ne montre jamais de consigne interne ni de section vide.
    $hosting = trim((string) setting('legal_hosting', ''));
    $sections = array_filter([
        'editeur' => 'Éditeur du site',
        'hebergement' => $hosting !== '' ? 'Hébergement' : null,
        'propriete' => 'Propriété intellectuelle',
        'donnees' => 'Données personnelles',
        'credits' => 'Crédits',
    ]);
    $company = setting('company_name', 'SOUMATIC');
    $summary = $hosting !== ''
        ? 'éditeur, hébergement, propriété intellectuelle, données personnelles et crédits'
        : 'éditeur, propriété intellectuelle, données personnelles et crédits';
@endphp
<x-layouts.app title="Mentions légales" :description="'Mentions légales du site de '.$company.' : '.$summary.'.'">
    <x-ui.page-header code="LEGAL" kicker="Mentions légales" title="Mentions légales.">
        Informations sur l'éditeur de ce site,@if ($hosting !== '') son hébergement,@endif les droits sur ses contenus et l'usage des données que vous nous confiez.
    </x-ui.page-header>

    <div class="wrap mt-12 grid gap-12 sm:mt-16 lg:grid-cols-12">
        <nav class="lg:col-span-3" aria-label="Sommaire">
            <div class="lg:sticky lg:top-32">
                <p class="label text-ardoise">Sommaire</p>
                <ol class="mt-3 grid gap-1 text-sm">
                    @foreach ($sections as $anchor => $heading)
                        <li><a href="#{{ $anchor }}" class="flex items-center gap-3 rounded-md px-2 py-1.5 text-navy-900/80 transition hover:bg-navy-50 hover:text-navy-900"><span class="label text-ardoise tabular">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $heading }}</a></li>
                    @endforeach
                </ol>
            </div>
        </nav>

        <div class="prose-site min-w-0 text-[16px] lg:col-span-8">
            <h2 id="editeur" class="scroll-mt-32 !mt-0">{{ $sections['editeur'] }}</h2>
            <p>Ce site est édité par {{ $company }}, entreprise de droit guinéen dont le siège est à {{ setting('city') }}.</p>
            {{-- Sous 640 px, clés et valeurs s'empilent : l'adresse e-mail garde toute la largeur et n'est jamais coupée. --}}
            <dl class="spec my-6 rounded-lg border border-navy-900/10 bg-paper p-5 max-sm:grid-cols-1 max-sm:gap-y-1 max-sm:[&>dt:not(:first-child)]:mt-3">
                <dt>Raison sociale</dt><dd>{{ $company }}</dd>
                <dt>{{ setting('manager_title', 'Gérant') }}</dt><dd>{{ setting('manager_name') }}</dd>
                <dt>Siège</dt><dd>{{ setting('address') }}, {{ setting('city') }}, {{ setting('country') }}</dd>
                <dt>RCCM</dt><dd class="font-mono text-[13px]">{{ setting('rccm') }}</dd>
                <dt>NIF</dt><dd class="font-mono text-[13px]">{{ setting('nif') }}</dd>
                <dt>Téléphone</dt><dd><a href="tel:{{ preg_replace('/\s+/', '', (string) setting('phone', '')) }}" class="!no-underline hover:!underline">{{ setting('phone') }}</a></dd>
                <dt>E-mail</dt><dd class="min-w-0 wrap-anywhere"><a href="mailto:{{ setting('email') }}" class="!no-underline hover:!underline">{{ setting('email') }}</a></dd>
            </dl>
            <p>Directeur de la publication&nbsp;: {{ setting('manager_name') }}, {{ mb_strtolower((string) setting('manager_title', 'gérant')) }}.</p>

            @if ($hosting !== '')
                <h2 id="hebergement" class="scroll-mt-32">{{ $sections['hebergement'] }}</h2>
                <p>{!! nl2br(typo(e($hosting))) !!}</p>
            @endif

            <h2 id="propriete" class="scroll-mt-32">{{ $sections['propriete'] }}</h2>
            <p>L'ensemble de ce site (textes, logo, illustrations, mise en page, code) est la propriété de {{ $company }}, sauf mention contraire. Toute reproduction, représentation ou diffusion, totale ou partielle, par quelque procédé que ce soit, sans l'autorisation écrite de {{ $company }}, est interdite.</p>
            <p>EduGes, MediGes SST, MediGes et Compiniya désignent des logiciels édités par {{ $company }}. Les autres noms de produits ou de sociétés cités appartiennent à leurs titulaires respectifs.</p>
            <p>Les droits sur les logiciels et les sites réalisés pour nos clients sont définis par chaque devis ou contrat signé.</p>

            <h2 id="donnees" class="scroll-mt-32">{{ $sections['donnees'] }}</h2>
            <p>Les formulaires de contact et d'estimation de ce site servent uniquement à répondre à vos demandes. Les informations que vous y saisissez (nom, organisation, e-mail, téléphone, message, prestations retenues) sont utilisées par {{ $company }} pour vous répondre et établir, le cas échéant, un devis. Elles ne sont ni cédées ni vendues à des tiers.</p>
            <p>Le site n'utilise que les cookies techniques nécessaires à son fonctionnement (session et protection des formulaires). Il ne dépose aucun cookie publicitaire ni de mesure d'audience et ne charge aucune ressource externe.</p>
            <p>Vous pouvez demander l'accès aux données qui vous concernent, leur rectification ou leur suppression en écrivant à <a href="mailto:{{ setting('email') }}" class="wrap-anywhere">{{ setting('email') }}</a>.</p>

            <h2 id="credits" class="scroll-mt-32">{{ $sections['credits'] }}</h2>
            <p>Site réalisé par {{ $company }}.</p>
        </div>
    </div>
</x-layouts.app>
