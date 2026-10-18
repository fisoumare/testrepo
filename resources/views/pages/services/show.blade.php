@php
    $onQuote = $service->isOnQuote();
    $whatsapp = whatsapp_url('Bonjour SOUMATIC, je souhaite un devis pour : '.$service->name);
@endphp
<x-layouts.app :title="$service->name" :description="$service->summary">
    <div class="wrap pt-8">
        <x-ui.breadcrumb :items="[['Prestations', route('services.index')], [$service->category->name, route('services.category', $service->category)], [$service->name, null]]" />
    </div>

    <article class="wrap mt-8">
        <header class="max-w-4xl">
            <p class="label flex flex-wrap items-center gap-x-3 gap-y-1 text-emerald-700">
                <span class="rounded-sm border border-emerald-700/30 px-1.5 py-0.5">{{ $service->number }}</span>
                <a href="{{ route('services.category', $service->category) }}" class="hover:text-emerald-800">{{ $service->category->code }} · {{ $service->category->name }}</a>
                @if ($service->solution)
                    <span class="flex items-center gap-2 text-ardoise"><x-ui.led :color="$service->solution->accent" /> {{ $service->solution->name }}</span>
                @endif
            </p>
            <h1 class="h-display mt-5 text-3xl sm:text-4xl lg:text-5xl">{{ $service->name }}</h1>
        </header>

        <div class="mt-10 grid gap-10 lg:grid-cols-12 lg:gap-14">
            {{-- ===== FICHE TECHNIQUE ===== --}}
            <aside class="lg:col-span-4 lg:order-last">
                <div class="unit rounded-xl border border-navy-900/10 bg-paper p-6 text-navy-900/30 lg:sticky lg:top-32">
                    <p class="label flex items-center justify-between text-ardoise">
                        <span>Fiche technique</span>
                        <span class="led led-on" aria-hidden="true"></span>
                    </p>
                    <p class="mt-4">
                        <x-ui.price :service="$service" size="lg" />
                        @unless ($onQuote)
                            <span class="label mt-1 block text-ardoise">par {{ mb_strtolower($service->unit) }}</span>
                        @endunless
                    </p>
                    <dl class="spec mt-6 border-t border-navy-900/10 pt-5">
                        <dt>Numéro</dt><dd class="font-mono text-[13px]">{{ $service->number }}</dd>
                        <dt>Famille</dt><dd><a href="{{ route('services.category', $service->category) }}" class="hover:text-emerald-700">{{ $service->category->name }}</a></dd>
                        <dt>Unité</dt><dd>{{ $service->unit }}</dd>
                        @if ($onQuote)
                            <dt>Prix</dt><dd>Sur devis, selon le périmètre</dd>
                        @else
                            <dt>Prix HT</dt><dd class="tabular">{{ gnf($service->price) }}</dd>
                            <dt>TVA 18 %</dt><dd class="tabular">{{ gnf(\App\Support\Money::vat($service->price)) }}</dd>
                            <dt>Prix TTC</dt><dd class="tabular font-semibold">{{ gnf(\App\Support\Money::ttc($service->price)) }}</dd>
                        @endif
                        @if ($service->solution)
                            <dt>Solution</dt><dd><a href="{{ route('solutions.show', $service->solution) }}" class="hover:text-emerald-700">{{ $service->solution->name }} <span class="text-ardoise">· {{ $service->solution->tagline }}</span></a></dd>
                        @endif
                    </dl>
                    <div class="mt-6 grid gap-2">
                        <a href="{{ route('quote', ['service' => $service->slug]) }}" class="btn-primary">{{ $onQuote ? 'Demander cette prestation' : 'Ajouter à mon estimation' }}</a>
                        <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn-ghost">
                            <svg class="size-4 text-emerald-700" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.6.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.3.1-.2 0-.3 0-.5l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 12 12 0 0 0 4.6 4 5.3 5.3 0 0 0 3.2.7 2.7 2.7 0 0 0 1.8-1.3 2.2 2.2 0 0 0 .2-1.3c-.1-.1-.3-.2-.5-.3Z"/></svg>
                            Demander par WhatsApp
                        </a>
                    </div>
                    <p class="mt-4 text-xs leading-relaxed text-ardoise">Prix fixe. 50 % à la commande, solde à la validation. Devis valable 30 jours.</p>
                </div>
            </aside>

            {{-- ===== DESCRIPTION ===== --}}
            <div class="min-w-0 lg:col-span-8">
                <h2 class="label text-ardoise">Description complète</h2>
                <x-ui.text-blocks :text="$service->description" class="mt-4 text-[16px] sm:text-[17px]" />

                <div class="mt-10 flex flex-wrap gap-3 border-t border-navy-900/10 pt-8">
                    <a href="{{ route('quote', ['service' => $service->slug]) }}" class="btn-primary">{{ $onQuote ? 'Demander cette prestation' : 'Ajouter à mon estimation' }}</a>
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn-ghost">WhatsApp · {{ setting('phone') }}</a>
                </div>

                @if ($service->solution)
                    <a href="{{ route('solutions.show', $service->solution) }}" class="unit group mt-10 flex items-center gap-4 rounded-lg border border-white/10 bg-ink p-5 text-emerald-400/60 no-underline transition hover:bg-ink-soft rack-lines">
                        <span class="label w-8 shrink-0 text-ardoise-light">{{ $service->solution->code }}</span>
                        <x-ui.led :color="$service->solution->accent" />
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold text-white">Cette prestation fait partie de {{ $service->solution->name }}</span>
                            <span class="block text-sm text-ardoise-light">{{ $service->solution->tagline }} · toutes les formules, ce qui est compris, la mise en service</span>
                        </span>
                        <svg class="size-4 shrink-0 text-ardoise-light transition group-hover:translate-x-0.5 group-hover:text-emerald-400" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @endif
            </div>
        </div>
    </article>

    {{-- ===== DANS LA MÊME FAMILLE ===== --}}
    @if ($related->isNotEmpty())
        <section class="mt-24 border-t border-navy-900/8 bg-paper" aria-labelledby="meme-famille">
            <div class="wrap py-16 sm:py-20">
                <div class="grid gap-6 lg:grid-cols-12 lg:items-end">
                    <x-ui.section-heading :code="$service->category->code" kicker="Dans la même famille" :title="$service->category->name" class="lg:col-span-8 reveal" id="meme-famille" />
                    <div class="lg:col-span-4 lg:text-right reveal reveal-delay-1">
                        <a href="{{ route('services.category', $service->category) }}" class="btn-link">Toutes les prestations {{ $service->category->code }} →</a>
                    </div>
                </div>
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($related as $item)
                        <x-ui.service-card :service="$item" class="reveal reveal-delay-{{ $loop->index % 4 }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-ui.cta-band />
</x-layouts.app>
