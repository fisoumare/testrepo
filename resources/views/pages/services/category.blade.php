{{-- $category, $categories (avec services_count), $onQuote (prestations sur devis de la famille), $title, $description : ServiceController::category(). --}}
@php
    $count = $category->services->count();
@endphp
<x-layouts.app :title="$title" :description="$description">
    {{-- ===== HÉRO : même composition que x-ui.page-header, avec le fil d'Ariane au-dessus du titre (comme sur les fiches) ===== --}}
    <section class="border-b border-navy-900/8 bg-paper">
        <div class="wrap py-14 sm:py-20">
            <x-ui.breadcrumb :items="[['Prestations', route('services.index')], [$category->name, null]]" />
            <div class="mt-10 grid gap-8 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-8">
                    <p class="label mb-4 flex items-center gap-3 text-emerald-700">
                        <span class="rounded-sm border border-emerald-700/30 px-1.5 py-0.5">{{ $category->code }}</span>
                        <span>Prestations</span>
                    </p>
                    <h1 class="h-display text-4xl text-navy-900 sm:text-5xl lg:text-6xl">{{ $category->name }}</h1>
                    <p class="lead mt-6 max-w-2xl">{{ $category->description }}</p>
                </div>
                <div class="lg:col-span-4">
                    <div class="flex flex-wrap items-center gap-x-8 gap-y-4 lg:flex-col lg:items-end lg:text-right">
                        <p class="tabular">
                            <span class="block text-4xl font-semibold tracking-tight text-navy-900">{{ $count }}</span>
                            <span class="label text-ardoise">{{ $onQuote ? ($count > 1 ? 'prestations' : 'prestation').', dont '.$onQuote.' sur devis' : ($count > 1 ? 'prestations à prix fixe' : 'prestation à prix fixe') }}</span>
                        </p>
                        <a href="{{ route('quote') }}" class="btn-navy">Composer une estimation</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="wrap mt-10 grid gap-10 sm:mt-14 lg:grid-cols-12">
        {{-- ===== COLONNE LATÉRALE ===== --}}
        <aside class="lg:col-span-3">
            <label class="block lg:hidden" x-data>
                <span class="label text-ardoise">Changer de famille</span>
                <select class="mt-2 h-11 w-full rounded-md border border-navy-900/15 bg-white px-3 text-sm text-navy-900 focus:border-emerald-500 focus:outline-none" @change="window.location = $event.target.value">
                    @foreach ($categories as $item)
                        <option value="{{ route('services.category', $item) }}" @selected($item->is($category))>{{ $item->code }} · {{ $item->name }} ({{ $item->services_count }})</option>
                    @endforeach
                </select>
            </label>

            <div class="hidden lg:sticky lg:top-32 lg:block">
                <nav aria-label="Familles de prestations">
                    <p class="label px-3 text-ardoise">Familles</p>
                    <ul class="mt-3 grid gap-1">
                        @foreach ($categories as $item)
                            @php $active = $item->is($category); @endphp
                            <li>
                                <a href="{{ route('services.category', $item) }}" @class(['flex items-center gap-3 rounded-md px-3 py-2 text-sm transition', 'bg-navy-900 text-white' => $active, 'text-navy-900/80 hover:bg-navy-50 hover:text-navy-900' => ! $active]) @if ($active) aria-current="page" @endif>
                                    <span class="label w-8 shrink-0 {{ $active ? 'text-emerald-400' : 'text-emerald-700' }}">{{ $item->code }}</span>
                                    <span class="min-w-0 flex-1 font-medium">{{ $item->name }}</span>
                                    <span class="label tabular {{ $active ? 'text-ardoise-light' : 'text-ardoise' }}">{{ $item->services_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <div class="unit mt-8 rounded-lg bg-ink p-5 text-emerald-400/60 rack-lines">
                    <p class="label flex items-center gap-2 text-emerald-400"><span class="led led-on" aria-hidden="true"></span> Estimation en ligne</p>
                    <p class="mt-3 text-sm leading-relaxed text-ardoise-light">Sélectionnez des prestations du catalogue&nbsp;: total hors taxes immédiat, devis valable 30 jours.</p>
                    <a href="{{ route('quote') }}" class="btn-primary mt-4 w-full">Composer une estimation</a>
                </div>
            </div>
        </aside>

        {{-- ===== GRILLE ===== --}}
        <div class="min-w-0 lg:col-span-9">
            <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-2">
                <h2 class="text-xl font-semibold tracking-tight text-navy-900">{{ $count > 1 ? 'Les '.$count.' prestations' : 'La prestation' }} {{ $category->code }}</h2>
                <span class="label text-ardoise">Prix hors taxes en GNF · TVA 18&nbsp;% en sus</span>
            </div>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($category->services as $service)
                    <x-ui.service-card :service="$service" class="reveal reveal-delay-{{ $loop->index % 3 }}" />
                @endforeach
            </div>
        </div>
    </div>

    {{-- Un seul appel à l'estimation en bas de page : la bande d'appel --}}
    <x-ui.cta-band />
</x-layouts.app>
