<x-layouts.app title="Contact" description="Écrivez à SOUMATIC, à Conakry : demande de devis, démonstration d'une solution, assistance ou partenariat. Réponse au plus tard le jour ouvré suivant.">
    <x-ui.page-header code="CONTACT" kicker="Nous écrire" title="Parlons de votre projet.">
        Un devis, une démonstration d'EduGes, de MediGes ou de Compiniya, une question d'assistance&nbsp;: dites-nous tout, nous répondons au plus tard le jour ouvré suivant.
        <x-slot:aside>
            <div class="flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row lg:justify-end">
                <a href="{{ whatsapp_url('Bonjour SOUMATIC,') }}" target="_blank" rel="noopener" class="btn-primary">
                    <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.6.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.3.1-.2 0-.3 0-.5l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 12 12 0 0 0 4.6 4 5.3 5.3 0 0 0 3.2.7 2.7 2.7 0 0 0 1.8-1.3 2.2 2.2 0 0 0 .2-1.3c-.1-.1-.3-.2-.5-.3Z"/></svg>
                    WhatsApp
                </a>
                <a href="tel:{{ preg_replace('/\s+/', '', setting('phone', '')) }}" class="btn-ghost">{{ setting('phone') }}</a>
            </div>
        </x-slot:aside>
    </x-ui.page-header>

    <section class="wrap mt-16 grid gap-12 lg:mt-20 lg:grid-cols-12">
        <div class="min-w-0 lg:col-span-7">
            <livewire:contact-form />
        </div>

        <aside class="lg:col-span-5" aria-labelledby="coordonnees-titre">
            <div class="unit text-navy-900/40 rounded-lg border border-navy-900/10 bg-white p-6 shadow-unit lg:sticky lg:top-28">
                <h2 id="coordonnees-titre" class="label flex items-center gap-2 text-emerald-700"><span class="led led-on"></span> Coordonnées</h2>
                <dl class="spec mt-5">
                    <dt>Téléphone</dt><dd><a href="tel:{{ preg_replace('/\s+/', '', setting('phone', '')) }}" class="font-medium hover:text-emerald-700">{{ setting('phone') }}</a></dd>
                    <dt>WhatsApp</dt><dd><a href="{{ whatsapp_url('Bonjour SOUMATIC,') }}" target="_blank" rel="noopener" class="btn-link">{{ setting('whatsapp', setting('phone')) }} ↗</a></dd>
                    <dt>E-mail</dt><dd><a href="mailto:{{ setting('email') }}" class="font-medium break-all hover:text-emerald-700">{{ setting('email') }}</a></dd>
                    <dt>Adresse</dt><dd>{{ setting('address') }}<br>{{ setting('city') }}, {{ setting('country') }}</dd>
                    <dt>Horaires</dt><dd>{{ setting('hours') }}</dd>
                    <dt>Assistance</dt><dd>{{ setting('support_channels') }}</dd>
                    <dt>Réponse</dt><dd>Au plus tard le jour ouvré suivant</dd>
                </dl>
                <div class="vent mt-6 text-navy-900" aria-hidden="true"></div>
                <p class="mt-4 text-xs leading-relaxed text-ardoise">Vous préférez chiffrer vous-même&nbsp;? <a href="{{ route('quote') }}" class="btn-link text-xs">Composer une estimation en ligne →</a></p>
            </div>
        </aside>
    </section>

    {{-- ===== FAQ ===== --}}
    @if ($faqs->isNotEmpty())
        <section class="mt-24 bg-paper border-y border-navy-900/8" id="faq" aria-label="Questions fréquentes">
            <div class="wrap py-16 sm:py-20">
                <x-ui.section-heading code="FAQ" kicker="Questions fréquentes" title="Ce que l'on nous demande avant de commander." class="reveal">
                    Prix, paiement, garantie, assistance, technique&nbsp;: les réponses à lire avant de nous écrire.
                </x-ui.section-heading>

                <div class="mt-12 grid gap-10 lg:grid-cols-12" x-data="{ open: null }">
                    @foreach ($faqs->groupBy('group') as $group => $questions)
                        <div class="lg:col-span-6 reveal">
                            <h3 class="label mb-2 flex items-center gap-2 text-emerald-700"><span class="led led-off"></span> {{ $group ?: 'Autres questions' }}</h3>
                            <div class="divide-y divide-navy-900/10 border-y border-navy-900/10">
                                @foreach ($questions as $faq)
                                    @php $key = 'faq-'.$faq->id; @endphp
                                    <div>
                                        <h4>
                                            <button type="button" id="{{ $key }}-bouton" class="flex w-full items-center justify-between gap-4 py-4 text-left font-semibold text-navy-900 transition hover:text-emerald-700" @click="open = open === '{{ $key }}' ? null : '{{ $key }}'" :aria-expanded="open === '{{ $key }}'" aria-controls="{{ $key }}">
                                                <span class="text-pretty">{{ $faq->question }}</span>
                                                <span class="label shrink-0 text-ardoise" x-text="open === '{{ $key }}' ? '−' : '+'" aria-hidden="true">+</span>
                                            </button>
                                        </h4>
                                        <div id="{{ $key }}" role="region" aria-labelledby="{{ $key }}-bouton" x-show="open === '{{ $key }}'" x-collapse x-cloak>
                                            <p class="pb-5 text-[15px] leading-relaxed text-ardoise">{{ $faq->answer }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-ui.cta-band title="Vous préférez chiffrer vous-même ?" text="Composez votre projet à partir des {{ $servicesCount }} prestations à prix fixe du catalogue : total HT, TVA et TTC calculés en direct, devis confirmé au plus tard le jour ouvré suivant." />
</x-layouts.app>
