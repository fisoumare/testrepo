<x-layouts.app title="Page expirée" description="La page est restée ouverte trop longtemps.">
    <x-slot:head><meta name="robots" content="noindex"></x-slot:head>
    @include('errors.partials.unit', [
        'code' => 'ERR-419',
        'kicker' => 'Session expirée',
        'title' => 'Cette page a expiré.',
        'text' => 'Elle est restée ouverte trop longtemps : revenez en arrière et renvoyez le formulaire.',
        'links' => ['Accueil' => route('home'), 'Contact' => route('contact')],
    ])
</x-layouts.app>
