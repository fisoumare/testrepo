<x-layouts.app title="Accès refusé" description="Cette page du site SOUMATIC n'est pas accessible.">
    <x-slot:head><meta name="robots" content="noindex"></x-slot:head>
    @include('errors.partials.unit', [
        'code' => 'ERR-403',
        'kicker' => 'Accès refusé',
        'title' => 'Cette unité est verrouillée.',
        'text' => "Vous n'avez pas l'autorisation d'ouvrir cette page.",
        'links' => ['Accueil' => route('home'), 'Prestations' => route('services.index'), 'Contact' => route('contact')],
    ])
</x-layouts.app>
