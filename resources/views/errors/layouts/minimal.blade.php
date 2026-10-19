{{-- Layout autonome des erreurs serveur (500, 503) : aucune lecture de la base de données ni de setting(),
     aucun composant partagé (les composeurs de vue du layout du site interrogent la base). --}}
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · SOUMATIC</title>
    <meta name="theme-color" content="#0f2d52">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon-32.png') }}" sizes="32x32" type="image/png">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full flex flex-col bg-white text-navy-900 font-sans">
    <header class="bg-ink text-white">
        <div class="wrap flex h-11 items-center justify-between text-xs">
            <p class="label flex items-center gap-2 text-ardoise-light">
                <span class="led led-amber" aria-hidden="true"></span>
                <span>@yield('status')</span>
            </p>
            <p class="label text-ardoise-light">SOUMATIC · Conakry</p>
        </div>
        <div class="border-t border-white/10">
            <div class="wrap flex h-16 items-center">
                <a href="{{ url('/') }}" class="inline-flex items-center" aria-label="SOUMATIC, accueil">
                    <img src="{{ asset('brand/logo-blanc.svg') }}" alt="SOUMATIC" class="h-9 w-auto">
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-navy-900/8">
        <div class="wrap flex flex-wrap items-center justify-between gap-3 py-6 text-sm text-ardoise">
            <p>© {{ date('Y') }} SOUMATIC</p>
            <a href="{{ url('/') }}" class="btn-link">Retour à l'accueil</a>
        </div>
    </footer>
</body>
</html>
