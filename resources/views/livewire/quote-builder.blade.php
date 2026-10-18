@php
    $cart = $this->cart;
    $steps = [1 => 'Composer', 2 => 'Vos coordonnées', 3 => 'Envoyé'];
@endphp
<div class="wrap" x-data="{ cartOpen: false }" @keydown.escape.window="cartOpen = false">

    {{-- Fil des étapes --}}
    <ol class="mt-10 grid grid-cols-3 gap-2 sm:gap-3" aria-label="Étapes de l'estimation">
        @foreach ($steps as $number => $label)
            <li @class([
                'flex min-w-0 items-center gap-2 rounded-md border px-2.5 py-2.5 sm:gap-3 sm:px-4',
                'border-navy-900 bg-navy-900 text-white' => $step === $number,
                'border-navy-900/10 bg-paper text-navy-900' => $step !== $number,
            ]) @if ($step === $number) aria-current="step" @endif>
                <span class="led {{ $step >= $number ? 'led-on' : 'led-off' }}" aria-hidden="true"></span>
                <span class="label {{ $step === $number ? 'text-emerald-400' : 'text-ardoise' }}">0{{ $number }}</span>
                <span class="truncate text-sm font-semibold">{{ $label }}</span>
            </li>
        @endforeach
    </ol>

    @if ($step === 1)
        {{-- ===== ÉTAPE 1 : COMPOSER ===== --}}
        <div class="mt-10 grid gap-10 lg:grid-cols-12">
            <section class="min-w-0 lg:col-span-8" aria-labelledby="catalogue-titre">
                <h2 id="catalogue-titre" class="sr-only">Catalogue des prestations</h2>

                <div class="relative">
                    <label for="recherche" class="sr-only">Rechercher une prestation</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ardoise" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="4.5" stroke="currentColor" stroke-width="1.6"/><path d="m10.5 10.5 3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                    <input
                        id="recherche"
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher une prestation, un numéro, un mot-clé…"
                        autocomplete="off"
                        class="block w-full rounded-md border border-navy-900/15 bg-white py-3 pl-10 pr-28 text-[15px] text-navy-900 placeholder:text-ardoise/60 transition hover:border-navy-900/30 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/25"
                    >
                    <span wire:loading.delay wire:target="search" class="label absolute right-3.5 top-1/2 -translate-y-1/2 text-emerald-700">Recherche…</span>
                </div>

                <div class="mt-4 flex flex-wrap gap-2" role="group" aria-label="Filtrer par catégorie">
                    <button type="button" wire:click="filter(null)" aria-pressed="{{ $category ? 'false' : 'true' }}" @class([
                        'inline-flex items-center gap-2 rounded-md border px-3 py-1.5 text-sm font-medium transition',
                        'border-navy-900 bg-navy-900 text-white' => ! $category,
                        'border-navy-900/15 bg-white text-navy-900 hover:border-navy-900/40 hover:bg-navy-50' => $category,
                    ])>
                        Toutes <span class="label {{ $category ? 'text-ardoise' : 'text-emerald-400' }}">{{ $this->catalogue->count() }}</span>
                    </button>
                    @foreach ($this->categories as $item)
                        <button type="button" wire:key="categorie-{{ $item->id }}" wire:click="filter('{{ $item->slug }}')" aria-pressed="{{ $category === $item->slug ? 'true' : 'false' }}" @class([
                            'inline-flex items-center gap-2 rounded-md border px-3 py-1.5 text-sm font-medium transition',
                            'border-navy-900 bg-navy-900 text-white' => $category === $item->slug,
                            'border-navy-900/15 bg-white text-navy-900 hover:border-navy-900/40 hover:bg-navy-50' => $category !== $item->slug,
                        ])>
                            <span class="label {{ $category === $item->slug ? 'text-emerald-400' : 'text-emerald-700' }}">{{ $item->code }}</span>
                            <span>{{ $item->name }}</span>
                            <span class="label {{ $category === $item->slug ? 'text-ardoise-light' : 'text-ardoise' }}">{{ $item->services_count }}</span>
                        </button>
                    @endforeach
                </div>

                @php
                    $total = $this->services->count();
                    $shown = $this->visibleServices->count();
                @endphp
                <div class="mt-7 flex items-center justify-between gap-4">
                    <p class="label text-ardoise" aria-live="polite">
                        @if ($shown < $total){{ $shown }} sur {{ $total }} prestations @else{{ $total }} prestation{{ $total > 1 ? 's' : '' }}@endif
                        @if ($category) · {{ $this->categories->firstWhere('slug', $category)?->name }}@endif
                    </p>
                    @if ($search !== '' || $category)
                        <button type="button" wire:click="resetFilters" class="btn-link text-xs">Réinitialiser les filtres</button>
                    @endif
                </div>

                <ul class="mt-3 divide-y divide-navy-900/8 border-y border-navy-900/10 transition-opacity" wire:loading.class="opacity-50" wire:target="search, filter, resetFilters" aria-label="Prestations">
                    @forelse ($this->visibleServices as $service)
                        <li wire:key="prestation-{{ $service->id }}" class="grid grid-cols-[minmax(0,1fr)_auto] gap-x-4 gap-y-3 py-4 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center sm:gap-x-6">
                            <div class="min-w-0">
                                <p class="label flex flex-wrap items-center gap-x-2 text-ardoise">
                                    <span>{{ $service->number }}</span>
                                    <span class="text-emerald-700">{{ $service->category->code }}</span>
                                </p>
                                <h3 class="mt-1 text-[15px] font-semibold leading-snug text-navy-900 text-pretty">
                                    <a href="{{ route('services.show', $service) }}" class="transition hover:text-emerald-700">{{ $service->name }}</a>
                                </h3>
                                @if ($service->summary)
                                    <p class="mt-1 text-sm leading-relaxed text-ardoise line-clamp-2">{{ $service->summary }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <x-ui.price :service="$service" size="sm" class="justify-end whitespace-nowrap" />
                                @unless (in_array($service->unit, ['Mois', 'An', 'Jour', 'Heure'], true))
                                    <p class="label mt-0.5 text-ardoise">{{ $service->unit }}</p>
                                @endunless
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                @if (isset($items[$service->id]))
                                    <button type="button" wire:click="add({{ $service->id }})" class="btn-navy w-full sm:w-auto" aria-label="Ajouter une unité supplémentaire : {{ $service->name }}">
                                        <svg class="size-4" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m3.5 8.5 3 3 6-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        Ajouté <span class="label text-emerald-400">× {{ $items[$service->id] }}</span>
                                    </button>
                                @else
                                    <button type="button" wire:click="add({{ $service->id }})" class="btn-ghost w-full sm:w-auto" aria-label="Ajouter : {{ $service->name }}">
                                        <svg class="size-4" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                        Ajouter
                                    </button>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="py-14 text-center">
                            <p class="font-semibold text-navy-900">Aucune prestation ne correspond.</p>
                            <p class="mt-2 text-sm text-ardoise">Essayez un autre mot-clé, ou décrivez-nous votre besoin&nbsp;: les projets hors catalogue font l'objet d'un devis spécifique.</p>
                            <div class="mt-5 flex flex-wrap justify-center gap-3">
                                <button type="button" wire:click="resetFilters" class="btn-ghost">Afficher tout le catalogue</button>
                                <a href="{{ route('contact', ['sujet' => 'devis']) }}" class="btn-link">Décrire mon besoin →</a>
                            </div>
                        </li>
                    @endforelse
                </ul>

                @if ($shown < $total)
                    <div class="mt-6 flex flex-col items-center gap-3 text-center">
                        <button type="button" wire:click="showMore" class="btn-ghost w-full sm:w-auto disabled:cursor-wait disabled:opacity-70" wire:loading.attr="disabled" wire:target="showMore">
                            <span wire:loading.remove wire:target="showMore">Afficher les {{ min(\App\Livewire\QuoteBuilder::PER_PAGE, $total - $shown) }} prestations suivantes</span>
                            <span wire:loading wire:target="showMore" class="inline-flex items-center gap-2"><span class="led led-on" aria-hidden="true"></span> Chargement…</span>
                        </button>
                        <p class="label text-ardoise">{{ $total - $shown }} restante{{ $total - $shown > 1 ? 's' : '' }} · filtrez par catégorie ou par mot-clé pour aller plus vite</p>
                    </div>
                @endif
            </section>

            {{-- Panier : colonne collante sur grand écran --}}
            <aside class="hidden lg:col-span-4 lg:block" aria-labelledby="panier-titre">
                <div class="unit text-navy-900/40 rounded-lg border border-navy-900/10 bg-white p-5 shadow-unit lg:sticky lg:top-28">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="panier-titre" class="label flex items-center gap-2 text-emerald-700"><span class="led {{ $cart['count'] ? 'led-on' : 'led-off' }}"></span> Votre sélection</h2>
                        @if ($cart['count'])
                            <button type="button" wire:click="clear" class="label text-ardoise transition hover:text-red-700">Vider</button>
                        @endif
                    </div>

                    @if ($cart['count'] === 0)
                        <p class="mt-5 text-sm leading-relaxed text-ardoise">Votre sélection est vide. Ajoutez des prestations depuis la liste&nbsp;: le total se calcule au fur et à mesure.</p>
                    @else
                        <div class="mt-4 max-h-[50vh] overflow-y-auto pr-1">
                            @include('livewire.partials.quote-cart-lines', ['cart' => $cart, 'prefix' => 'panier'])
                        </div>
                    @endif

                    @error('items')
                        <p class="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror

                    <button type="button" wire:click="goToDetails" class="btn-primary mt-5 w-full disabled:cursor-not-allowed disabled:opacity-50" @disabled($cart['count'] === 0) wire:loading.attr="disabled" wire:target="goToDetails">
                        Demander ce devis
                        <svg class="size-4" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <p class="mt-3 text-xs leading-relaxed text-ardoise">Prix hors taxes en GNF, TVA 18&nbsp;% en sus. L'estimation ne vaut pas devis signé&nbsp;: nous confirmons par un devis valable 30 jours.</p>
                </div>
            </aside>
        </div>

        {{-- Panier : bandeau collant en bas de l'écran sur mobile --}}
        <div class="sticky bottom-0 z-40 -mx-5 mt-10 border-t border-navy-900/10 bg-white shadow-[0_-12px_32px_-16px_rgb(15_45_82/0.35)] sm:-mx-8 lg:hidden" aria-label="Votre sélection">
            <div class="px-5 py-3 sm:px-8">
                <div id="panier-mobile" x-show="cartOpen" x-collapse x-cloak>
                    @if ($cart['count'] === 0)
                        <p class="py-3 text-sm text-ardoise">Votre sélection est vide. Ajoutez des prestations depuis la liste.</p>
                    @else
                        <div class="max-h-[55vh] overflow-y-auto pb-3">
                            @include('livewire.partials.quote-cart-lines', ['cart' => $cart, 'prefix' => 'mobile'])
                        </div>
                    @endif
                </div>
                @error('items')
                    <p class="mb-2 text-sm text-red-700" role="alert">{{ $message }}</p>
                @enderror
                <div class="flex items-center justify-between gap-3">
                    <button type="button" @click="cartOpen = !cartOpen" :aria-expanded="cartOpen" aria-controls="panier-mobile" class="min-w-0 flex-1 text-left">
                        <span class="label flex items-center gap-2 text-ardoise"><span class="led {{ $cart['count'] ? 'led-on' : 'led-off' }}"></span><span class="truncate">Sélection · {{ $cart['count'] }}</span><span x-text="cartOpen ? '▾' : '▴'" aria-hidden="true"></span></span>
                        <span class="block truncate text-lg font-semibold tabular text-navy-900">{{ gnf($cart['total_ht']) }} <span class="label text-ardoise">HT{{ $cart['on_quote'] ? ' + sur devis' : '' }}</span></span>
                    </button>
                    <button type="button" wire:click="goToDetails" class="btn-primary shrink-0 disabled:cursor-not-allowed disabled:opacity-50" @disabled($cart['count'] === 0)>Demander ce devis</button>
                </div>
            </div>
        </div>

    @elseif ($step === 2)
        {{-- ===== ÉTAPE 2 : COORDONNÉES ===== --}}
        <div class="mt-10 grid gap-10 lg:grid-cols-12">
            <section class="min-w-0 lg:col-span-7" aria-labelledby="coordonnees-titre">
                <h2 id="coordonnees-titre" class="text-2xl font-semibold tracking-tight text-navy-900 sm:text-3xl">Vos coordonnées</h2>
                <p class="mt-3 text-ardoise text-pretty">Nous vous répondons au plus tard le jour ouvré suivant, avec un devis en bonne et due forme. {{ setting('hours') }}.</p>

                <form wire:submit="submit" novalidate class="relative mt-8 grid gap-5 sm:grid-cols-2">
                    <x-ui.form-field name="name" label="Nom et prénom" required wire:model.blur="name" autocomplete="name" maxlength="120" />
                    <x-ui.form-field name="organization" label="Organisation" wire:model.blur="organization" autocomplete="organization" maxlength="120" placeholder="Établissement, entreprise, administration…" />
                    <x-ui.form-field name="email" type="email" label="Adresse e-mail" required wire:model.blur="email" autocomplete="email" inputmode="email" maxlength="190" />
                    <x-ui.form-field name="phone" type="tel" label="Téléphone" required wire:model.blur="phone" autocomplete="tel" inputmode="tel" maxlength="30" hint="De préférence un numéro joignable sur WhatsApp." />
                    <x-ui.form-field name="message" type="textarea" label="Précisions sur votre projet" wire:model.blur="message" class="sm:col-span-2" rows="5" maxlength="2000" hint="Contexte, délais souhaités, contraintes particulières. 2 000 caractères au plus." />

                    {{-- Pot-de-miel : invisible pour les humains, doit rester vide. --}}
                    <div class="absolute -left-[9999px] top-0 h-px w-px overflow-hidden" aria-hidden="true">
                        <label for="site-web">Site web</label>
                        <input id="site-web" type="text" name="website" tabindex="-1" autocomplete="off" wire:model="website">
                    </div>

                    @error('form')
                        <p class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 sm:col-span-2" role="alert">{{ $message }}</p>
                    @enderror
                    @error('items')
                        <p class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 sm:col-span-2" role="alert">{{ $message }}</p>
                    @enderror

                    <div class="flex flex-col-reverse gap-3 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button" wire:click="backToCatalog" class="btn-ghost">
                            <svg class="size-4" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M13 8H3m4 4-4-4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Modifier la sélection
                        </button>
                        <button type="submit" class="btn-primary disabled:cursor-wait disabled:opacity-70" wire:loading.attr="disabled" wire:target="submit">
                            <span wire:loading.remove wire:target="submit">Envoyer la demande</span>
                            <span wire:loading wire:target="submit" class="inline-flex items-center gap-2"><span class="led led-on" aria-hidden="true"></span> Envoi en cours…</span>
                        </button>
                    </div>
                    <p class="text-xs leading-relaxed text-ardoise sm:col-span-2">Vos coordonnées servent uniquement à traiter cette demande. Vous recevrez un accusé de réception par e-mail avec sa référence.</p>
                </form>
            </section>

            <aside class="lg:col-span-5" aria-labelledby="recap-titre">
                <div class="unit text-navy-900/40 rounded-lg border border-navy-900/10 bg-paper p-5 lg:sticky lg:top-28">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="recap-titre" class="label flex items-center gap-2 text-emerald-700"><span class="led led-on"></span> Récapitulatif</h2>
                        <button type="button" wire:click="backToCatalog" class="btn-link text-xs">Modifier</button>
                    </div>
                    <ul class="mt-4 divide-y divide-navy-900/8 text-sm">
                        @foreach ($cart['lines'] as $line)
                            <li wire:key="recap-{{ $line['service']->id }}" class="flex items-start justify-between gap-4 py-2.5">
                                <span class="min-w-0"><span class="label text-ardoise">{{ $line['service']->number }}</span><br>{{ $line['service']->name }} <span class="text-ardoise">× {{ $line['quantity'] }}</span></span>
                                <span class="shrink-0 font-semibold tabular text-navy-900">{{ $line['service']->isOnQuote() ? 'Sur devis' : gnf($line['total']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <dl class="mt-3 space-y-1.5 border-t border-navy-900/10 pt-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-ardoise">Total HT</dt><dd class="font-semibold tabular">{{ gnf($cart['total_ht']) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-ardoise">TVA 18&nbsp;%</dt><dd class="tabular">{{ gnf($cart['vat']) }}</dd></div>
                        <div class="flex justify-between gap-3 text-base"><dt class="font-semibold">Total TTC</dt><dd class="font-semibold tabular">{{ gnf($cart['total_ttc']) }}</dd></div>
                        @if ($cart['on_quote'] > 0)
                            <div class="flex items-center gap-2 pt-1 text-xs text-ardoise"><span class="led led-amber"></span>+ {{ $cart['on_quote'] }} prestation{{ $cart['on_quote'] > 1 ? 's' : '' }} sur devis</div>
                        @endif
                    </dl>
                    <dl class="spec mt-5 border-t border-navy-900/10 pt-4 text-xs">
                        <dt>Validité</dt><dd>Devis valable {{ setting('quote_validity_days', '30') }} jours</dd>
                        <dt>Paiement</dt><dd>{{ setting('payment_terms') }}</dd>
                    </dl>
                </div>
            </aside>
        </div>

    @else
        {{-- ===== ÉTAPE 3 : ENVOYÉ ===== --}}
        <div class="mt-10 grid gap-10 lg:grid-cols-12">
            <section class="min-w-0 lg:col-span-7" aria-labelledby="envoye-titre">
                <p class="label flex items-center gap-2 text-emerald-700"><span class="led led-on"></span> Demande enregistrée</p>
                <h2 id="envoye-titre" class="h-section mt-4 text-navy-900">Merci, votre demande <span class="font-mono text-emerald-700">{{ $reference }}</span> est bien reçue.</h2>
                <p class="lead mt-5">Nous revenons vers vous au plus tard le jour ouvré suivant avec un devis en bonne et due forme. Un accusé de réception vient d'être envoyé à <span class="font-medium text-navy-900">{{ $email }}</span>.</p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $this->whatsappUrl() }}" target="_blank" rel="noopener" class="btn-primary">
                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.6.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.3.1-.2 0-.3 0-.5l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 12 12 0 0 0 4.6 4 5.3 5.3 0 0 0 3.2.7 2.7 2.7 0 0 0 1.8-1.3 2.2 2.2 0 0 0 .2-1.3c-.1-.1-.3-.2-.5-.3Z"/></svg>
                        Poursuivre sur WhatsApp
                    </a>
                    <button type="button" wire:click="restart" class="btn-ghost">Nouvelle estimation</button>
                    <a href="{{ route('home') }}" class="btn-link">Retour à l'accueil →</a>
                </div>

                <dl class="spec mt-10">
                    <dt>Référence</dt><dd class="font-mono text-[13px]">{{ $reference }}</dd>
                    <dt>Contact</dt><dd>{{ $name }}@if ($organization) — {{ $organization }}@endif<br>{{ $email }} · {{ $phone }}</dd>
                    <dt>Réponse</dt><dd>Au plus tard le jour ouvré suivant, {{ setting('hours') }}</dd>
                    <dt>Validité</dt><dd>Devis valable {{ setting('quote_validity_days', '30') }} jours</dd>
                    <dt>Paiement</dt><dd>{{ setting('payment_terms') }}</dd>
                </dl>
            </section>

            <aside class="lg:col-span-5" aria-labelledby="envoye-recap-titre">
                <div class="unit text-emerald-400/70 rounded-lg border border-white/10 bg-ink p-5 text-white rack-lines">
                    <h2 id="envoye-recap-titre" class="label flex items-center gap-2 text-emerald-400"><span class="led led-on"></span> Récapitulatif envoyé</h2>
                    <ul class="mt-4 divide-y divide-white/10 text-sm">
                        @foreach ($sent['items'] ?? [] as $item)
                            <li wire:key="sent-{{ $item['service_id'] }}" class="flex items-start justify-between gap-4 py-2.5">
                                <span class="min-w-0"><span class="label text-ardoise-light">{{ $item['number'] }}</span><br>{{ $item['name'] }} <span class="text-ardoise-light">× {{ $item['quantity'] }}</span></span>
                                <span class="shrink-0 font-semibold tabular">{{ (int) $item['unit_price'] > 0 ? gnf($item['total']) : 'Sur devis' }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <dl class="mt-3 space-y-1.5 border-t border-white/10 pt-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-ardoise-light">Total HT</dt><dd class="font-semibold tabular">{{ gnf($sent['total_ht'] ?? 0) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-ardoise-light">TVA 18&nbsp;%</dt><dd class="tabular">{{ gnf($sent['vat'] ?? 0) }}</dd></div>
                        <div class="flex justify-between gap-3 text-base"><dt class="font-semibold">Total TTC</dt><dd class="font-semibold tabular">{{ gnf($sent['total_ttc'] ?? 0) }}</dd></div>
                        @if (($sent['on_quote'] ?? 0) > 0)
                            <div class="flex items-center gap-2 pt-1 text-xs text-ardoise-light"><span class="led led-amber"></span>+ {{ $sent['on_quote'] }} prestation{{ $sent['on_quote'] > 1 ? 's' : '' }} sur devis</div>
                        @endif
                    </dl>
                </div>
            </aside>
        </div>
    @endif
</div>
