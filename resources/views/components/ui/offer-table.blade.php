{{--
    Tableau des formules d'une solution, groupées par famille (abonnement, licence, mise en service…).
    $offers : résultat de SolutionController::groupOffers(). Chaque ligne mène à la fiche prestation
    et propose l'ajout à l'estimation.
--}}
@props(['offers', 'solution', 'dark' => false, 'actions' => true])
@php
    $line = $dark ? 'border-white/10' : 'border-navy-900/10';
    $groupBg = $dark ? 'bg-white/[0.04]' : 'bg-paper';
@endphp
<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg border '.$line.($dark ? ' bg-ink-soft/60' : ' bg-white')]) }}>
    <table class="w-full text-sm">
        <caption class="sr-only">Formules et tarifs de {{ $solution->name }}, hors taxes en francs guinéens</caption>
        <thead>
            <tr class="label {{ $dark ? 'text-ardoise-light' : 'text-ardoise' }}">
                <th scope="col" class="px-4 py-3 text-left font-medium sm:px-5">Formule</th>
                <th scope="col" class="hidden px-4 py-3 text-right font-medium sm:table-cell sm:px-5">Prix HT</th>
                @if ($actions)
                    <th scope="col" class="hidden px-4 py-3 text-right font-medium sm:table-cell sm:px-5"><span class="sr-only">Estimation</span></th>
                @endif
            </tr>
        </thead>
        @foreach ($offers as $group)
            <tbody class="border-t {{ $line }}">
                <tr class="{{ $groupBg }}">
                    <th scope="rowgroup" colspan="{{ $actions ? 3 : 2 }}" class="px-4 py-2 text-left sm:px-5">
                        <span class="label {{ $dark ? 'text-emerald-400' : 'text-emerald-700' }}">{{ $group['label'] }}</span>
                        @if ($group['note'])
                            <span class="ml-2 text-xs font-normal {{ $dark ? 'text-ardoise-light' : 'text-ardoise' }}">· {{ $group['note'] }}</span>
                        @endif
                    </th>
                </tr>
                @foreach ($group['services'] as $service)
                    <tr class="border-t {{ $line }} align-top">
                        <td class="px-4 py-3 sm:px-5">
                            <a href="{{ route('services.show', $service) }}" class="font-medium {{ $dark ? 'text-white hover:text-emerald-400' : 'text-navy-900 hover:text-emerald-700' }} transition">{{ \Illuminate\Support\Str::after($service->name, ' – ') }}</a>
                            <span class="label mt-1 block {{ $dark ? 'text-ardoise-light' : 'text-ardoise' }}">{{ $service->number }} · {{ $service->unit }}</span>
                            <span class="mt-2 flex flex-wrap items-center justify-between gap-x-4 gap-y-1 sm:hidden">
                                <x-ui.price :service="$service" :dark="$dark" size="sm" />
                                @if ($actions)
                                    <a href="{{ route('quote', ['service' => $service->slug]) }}" class="btn-link {{ $dark ? 'text-emerald-400 hover:text-emerald-300' : '' }}">Ajouter →</a>
                                @endif
                            </span>
                        </td>
                        <td class="hidden px-4 py-3 text-right sm:table-cell sm:px-5">
                            <x-ui.price :service="$service" :dark="$dark" size="sm" class="justify-end" />
                        </td>
                        @if ($actions)
                            <td class="hidden px-4 py-3 text-right sm:table-cell sm:px-5">
                                <a href="{{ route('quote', ['service' => $service->slug]) }}" class="btn-link {{ $dark ? 'text-emerald-400 hover:text-emerald-300' : '' }}" aria-label="Ajouter « {{ \Illuminate\Support\Str::after($service->name, ' – ') }} » à mon estimation">Ajouter →</a>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        @endforeach
    </table>
</div>
