@php
    // Sur la page de demande de devis, l'appel principal renvoie vers le contact plutôt que vers la page courante.
    $cta = request()->routeIs('quote')
        ? ['href' => route('contact'), 'label' => 'Nous contacter']
        : ['href' => route('quote'), 'label' => 'Demander un devis'];
@endphp
<header class="sticky top-0 z-50" x-data="{ open: false, menu: null }" @keydown.escape.window="open = false; menu = null">
    <x-site.status-bar />
    <div class="border-b border-navy-900/10 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/75">
        <div class="wrap flex h-[72px] items-center justify-between gap-6">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center" aria-label="SOUMATIC — accueil">
                <img src="{{ asset('brand/logo.svg') }}" alt="SOUMATIC — Numérique de confiance" class="h-11 w-auto" width="1000" height="300">
            </a>

            <nav class="hidden lg:flex items-center gap-1 text-[15px] font-medium" aria-label="Navigation principale">
                <div class="relative" @mouseenter="menu = 'solutions'" @mouseleave="menu = null">
                    <a href="{{ route('solutions.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('solutions.*')]) aria-haspopup="true">
                        Solutions
                        <svg class="size-3.5 opacity-60" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    <div x-show="menu === 'solutions'" x-cloak x-transition.opacity.duration.150ms class="absolute left-0 top-full pt-2 w-[520px]">
                        <div class="unit text-navy-900/50 rounded-lg border border-navy-900/10 bg-white p-2 shadow-unit grid grid-cols-2 gap-1">
                            @foreach ($navSolutions as $solution)
                                <a href="{{ route('solutions.show', $solution) }}" class="group flex items-start gap-3 rounded-md p-3 hover:bg-navy-50">
                                    <span class="mt-1.5 led" style="--tw-shadow-color: {{ $solution->accent }}; background: {{ $solution->accent }}; box-shadow: 0 0 0 3px color-mix(in srgb, {{ $solution->accent }} 18%, transparent), 0 0 10px color-mix(in srgb, {{ $solution->accent }} 60%, transparent)" aria-hidden="true"></span>
                                    <span>
                                        <span class="block font-semibold text-navy-900">{{ $solution->name }}</span>
                                        <span class="block text-sm text-ardoise">{{ $solution->tagline }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="relative" @mouseenter="menu = 'services'" @mouseleave="menu = null">
                    <a href="{{ route('services.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('services.*')]) aria-haspopup="true">
                        Prestations
                        <svg class="size-3.5 opacity-60" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    <div x-show="menu === 'services'" x-cloak x-transition.opacity.duration.150ms class="absolute left-0 top-full pt-2 w-[560px]">
                        <div class="unit text-navy-900/50 rounded-lg border border-navy-900/10 bg-white p-2 shadow-unit grid grid-cols-2 gap-1">
                            @foreach ($navCategories as $category)
                                <a href="{{ route('services.category', $category) }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 hover:bg-navy-50">
                                    <span class="label text-emerald-700 w-8">{{ $category->code }}</span>
                                    <span class="font-medium text-navy-900">{{ $category->name }}</span>
                                </a>
                            @endforeach
                            <a href="{{ route('quote') }}" class="col-span-2 mt-1 flex items-center justify-between rounded-md bg-navy-900 px-4 py-3 text-white hover:bg-navy-800">
                                <span class="font-semibold">Demander un devis en ligne</span>
                                <span class="label text-emerald-400">Prix fixes</span>
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('references.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('references.*')])>Références</a>
                <a href="{{ route('posts.index') }}" @class(['nav-link', 'is-active' => request()->routeIs('posts.*')])>Actualités</a>
                <a href="{{ route('about') }}" @class(['nav-link', 'is-active' => request()->routeIs('about')])>À propos</a>
                <a href="{{ route('contact') }}" @class(['nav-link', 'is-active' => request()->routeIs('contact')])>Contact</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ $cta['href'] }}" class="btn-primary hidden sm:inline-flex">{{ $cta['label'] }}</a>
                <button type="button" class="lg:hidden inline-flex size-11 items-center justify-center rounded-md border border-navy-900/15" @click="open = !open" :aria-expanded="open" aria-controls="menu-mobile" aria-label="Ouvrir le menu">
                    <svg x-show="!open" class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <svg x-show="open" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>

        <div id="menu-mobile" x-show="open" x-cloak x-transition.opacity.duration.200ms class="lg:hidden border-t border-navy-900/10 bg-white">
            <div class="wrap py-4 grid gap-6 max-h-[calc(100vh-104px)] overflow-y-auto">
                <div>
                    <p class="label text-ardoise mb-2">Solutions</p>
                    <div class="grid gap-1">
                        @foreach ($navSolutions as $solution)
                            <a href="{{ route('solutions.show', $solution) }}" class="flex items-start gap-3 rounded-md px-2 py-2.5 hover:bg-navy-50">
                                <span class="led mt-2" style="background: {{ $solution->accent }}; box-shadow: none" aria-hidden="true"></span>
                                <span class="min-w-0">
                                    <span class="block font-semibold whitespace-nowrap">{{ $solution->name }}</span>
                                    <span class="block text-sm text-ardoise">{{ $solution->tagline }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="label text-ardoise mb-2">Prestations</p>
                    <div class="grid gap-1 sm:grid-cols-2">
                        @foreach ($navCategories as $category)
                            <a href="{{ route('services.category', $category) }}" class="flex items-center gap-3 rounded-md px-2 py-2.5 hover:bg-navy-50">
                                <span class="label text-emerald-700 w-8">{{ $category->code }}</span>
                                <span class="font-medium">{{ $category->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-1 text-[15px] font-medium">
                    <a href="{{ route('references.index') }}" class="rounded-md px-2 py-2.5 hover:bg-navy-50">Références</a>
                    <a href="{{ route('posts.index') }}" class="rounded-md px-2 py-2.5 hover:bg-navy-50">Actualités</a>
                    <a href="{{ route('about') }}" class="rounded-md px-2 py-2.5 hover:bg-navy-50">À propos</a>
                    <a href="{{ route('contact') }}" class="rounded-md px-2 py-2.5 hover:bg-navy-50">Contact</a>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ $cta['href'] }}" class="btn-primary flex-1">{{ $cta['label'] }}</a>
                    <a href="{{ whatsapp_url('Bonjour SOUMATIC,') }}" class="btn-ghost flex-1" rel="noopener" target="_blank">WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</header>
