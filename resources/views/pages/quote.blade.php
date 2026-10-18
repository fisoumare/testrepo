<x-layouts.app title="Estimation en ligne" description="Composez votre projet à partir des prestations à prix fixe de SOUMATIC : total HT, TVA 18 % et TTC calculés en direct, puis demande de devis en quelques clics.">
    <x-ui.page-header code="DEVIS" kicker="Estimation en ligne" title="Composez votre projet, unité par unité.">
        Choisissez vos prestations dans le catalogue, ajustez les quantités et envoyez-nous la sélection&nbsp;: nous la confirmons par un devis en bonne et due forme. L'estimation ne vaut pas devis signé.
        <x-slot:aside>
            <dl class="spec rounded-lg border border-navy-900/10 bg-white p-5 shadow-unit">
                <dt>Prix</dt><dd>Fixes, affichés hors taxes en GNF</dd>
                <dt>TVA</dt><dd>{{ setting('vat_rate', '18') }}&nbsp;% en sus</dd>
                <dt>Validité</dt><dd>Devis valable {{ setting('quote_validity_days', '30') }} jours</dd>
                <dt>Paiement</dt><dd>50&nbsp;% à la commande, solde à la validation</dd>
                <dt>Réponse</dt><dd>Au plus tard le jour ouvré suivant</dd>
            </dl>
        </x-slot:aside>
    </x-ui.page-header>

    <livewire:quote-builder />

    <section class="wrap mt-24">
        <div class="grid gap-6 rounded-lg border border-navy-900/10 bg-paper p-6 sm:p-8 lg:grid-cols-12 lg:items-center">
            <div class="lg:col-span-8">
                <p class="label mb-3 text-emerald-700">Hors catalogue</p>
                <h2 class="text-xl font-semibold tracking-tight text-navy-900 sm:text-2xl text-balance">Votre besoin ne figure pas dans la liste&nbsp;?</h2>
                <p class="mt-3 text-ardoise text-pretty">Serveur sur mesure, application au-delà du niveau 3, reprise d'un logiciel sans documentation&nbsp;: décrivez-nous votre projet, nous établissons un devis spécifique.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row lg:col-span-4 lg:justify-end">
                <a href="{{ route('contact', ['sujet' => 'devis']) }}" class="btn-navy">Décrire mon besoin</a>
                <a href="{{ whatsapp_url('Bonjour SOUMATIC, j\'ai un projet hors catalogue à vous présenter.') }}" target="_blank" rel="noopener" class="btn-ghost">WhatsApp</a>
            </div>
        </div>
    </section>
</x-layouts.app>
