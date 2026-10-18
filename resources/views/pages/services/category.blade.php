<x-layouts.app :title="$category->name.' · Prestations'" :description="$category->description">
    <x-ui.page-header :code="$category->code" kicker="Prestations" :title="$category->name">
        {{ $category->description }}
        <x-slot:aside>
            <div class="flex flex-wrap items-center gap-x-8 gap-y-4 lg:flex-col lg:items-end lg:text-right">
                <p class="tabular">
                    <span class="block text-4xl font-semibold tracking-tight text-navy-900">{{ $category->services->count() }}</span>
                    <span class="label text-ardoise">prestations à prix fixe</span>
                </p>
                <a href="{{ route('quote') }}" class="btn-navy">Composer une estimation</a>
            </div>
        </x-slot:aside>
    </x-ui.page-header>

    <div class="wrap mt-10 pt-2 sm:mt-14">
        <x-ui.breadcrumb :items="[['Prestations', route('services.index')], [$category->name, null]]" />
    </div>

    <div class="wrap mt-8 grid gap-10 lg:grid-cols-12">
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
                    <p class="mt-3 text-sm leading-relaxed text-ardoise-light">Sélectionnez des prestations du catalogue : prix fixes, hors taxes, devis valable 30 jours.</p>
                    <a href="{{ route('quote') }}" class="btn-primary mt-4 w-full">Composer une estimation</a>
                </div>
            </div>
        </aside>

        {{-- ===== GRILLE ===== --}}
        <div class="min-w-0 lg:col-span-9">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($category->services as $service)
                    <x-ui.service-card :service="$service" class="reveal reveal-delay-{{ $loop->index % 3 }}" />
                @endforeach
            </div>

            <div class="unit mt-10 grid gap-6 rounded-xl border border-navy-900/10 bg-paper p-6 text-navy-900/30 sm:p-8 md:grid-cols-12 md:items-center">
                <div class="md:col-span-8">
                    <p class="label flex items-center gap-2 text-emerald-700"><span class="led led-on" aria-hidden="true"></span> Composer une estimation</p>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight text-navy-900">Ajoutez des prestations, obtenez un total hors taxes.</h2>
                    <p class="mt-2 text-[15px] leading-relaxed text-ardoise">Prix fixes, TVA 18 % en sus, 50 % à la commande et solde à la validation. Devis valable 30 jours.</p>
                </div>
                <div class="flex flex-wrap gap-3 md:col-span-4 md:justify-end">
                    <a href="{{ route('quote') }}" class="btn-navy">Composer une estimation</a>
                </div>
            </div>
        </div>
    </div>

    <x-ui.cta-band />
</x-layouts.app>
