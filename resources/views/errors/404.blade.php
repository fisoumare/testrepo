<x-layouts.app title="Page introuvable" description="Cette adresse ne correspond à aucune page du site SOUMATIC.">
    <x-slot:head><meta name="robots" content="noindex"></x-slot:head>
    @include('errors.partials.unit', [
        'code' => 'ERR-404',
        'kicker' => 'Unité introuvable',
        'title' => "Cette unité n'existe pas.",
        'text' => "L'adresse demandée ne correspond à aucune page du site : elle a peut-être changé, ou comporte une faute de frappe.",
        'links' => ['Accueil' => route('home'), 'Prestations' => route('services.index'), 'Contact' => route('contact')],
    ])
</x-layouts.app>
