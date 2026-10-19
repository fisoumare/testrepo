@props(['title' => null, 'description' => null, 'canonical' => null, 'ogType' => 'website', 'ogImage' => null, 'ogImageAlt' => null])
@php
    $siteName = setting('company_name', 'SOUMATIC');
    $tagline = setting('tagline', 'Numérique de confiance');
    $pageTitle = $title !== null && $title !== '' ? $title.' · '.$siteName : $siteName.' — '.$tagline;
    $pageDescription = $description ?? setting('meta_description', '');
    $canonical = $canonical ?? url()->current();
    $defaultOgImage = asset('brand/og.png'); // 1200 × 630
    $ogImage = $ogImage ?? $defaultOgImage;
    $ogImageAlt = $ogImageAlt ?? ($ogImage === $defaultOgImage ? $siteName.' — '.$tagline : $pageTitle);
    // JSON_HEX_* : une valeur saisie en back-office contenant « </script> » ne peut pas fermer le bloc JSON-LD.
    $jsonLdFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
@endphp
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="theme-color" content="#0f2d52">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    @if ($ogImage === $defaultOgImage)
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif
    <meta property="og:image:alt" content="{{ $ogImageAlt }}">
    <meta property="og:locale" content="fr_FR">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon-32.png') }}" sizes="32x32" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('brand/icone.png') }}">
    {{ $head ?? '' }}
    <script type="application/ld+json">{!! json_encode([
        '@@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => url('/'),
        'logo' => asset('brand/logo.png'),
        'email' => setting('email'),
        'telephone' => setting('phone'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => setting('address'),
            'addressLocality' => setting('city', 'Conakry'),
            'addressCountry' => 'GN',
        ],
    ], $jsonLdFlags) !!}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col bg-white text-navy-900 font-sans">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-md focus:bg-emerald-500 focus:px-4 focus:py-2 focus:text-white">Aller au contenu</a>

    <x-site.header />

    <main id="contenu" class="flex-1">
        {{ $slot }}
    </main>

    <x-site.footer />

    @livewireScriptConfig
</body>
</html>
