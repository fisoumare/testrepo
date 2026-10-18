{{--
    Lignes du panier d'estimation (quantité modifiable, retrait) et totaux.
    Variables attendues : $cart (QuoteBuilder::cart()), $prefix (clés wire:key uniques).
--}}
<ul class="divide-y divide-navy-900/8" aria-label="Prestations sélectionnées">
    @foreach ($cart['lines'] as $line)
        @php $service = $line['service']; @endphp
        <li wire:key="{{ $prefix }}-{{ $service->id }}" class="grid grid-cols-[minmax(0,1fr)_auto] gap-x-3 gap-y-2.5 py-3.5">
            <div class="min-w-0">
                <p class="label text-ardoise">{{ $service->number }}</p>
                <p class="mt-0.5 text-sm font-medium leading-snug text-navy-900 text-pretty">{{ $service->name }}</p>
                <p class="mt-0.5 text-xs text-ardoise tabular">
                    @if ($service->isOnQuote())
                        Prix communiqué sur devis
                    @else
                        {{ gnf($service->price) }} HT / {{ mb_strtolower($service->unit) }}
                    @endif
                </p>
            </div>
            <p class="text-right text-sm font-semibold tabular text-navy-900">{{ $service->isOnQuote() ? 'Sur devis' : gnf($line['total']) }}</p>
            <div class="col-span-2 flex items-center justify-between gap-3">
                <div class="inline-flex items-stretch overflow-hidden rounded-md border border-navy-900/15" role="group" aria-label="Quantité pour {{ $service->name }}">
                    <button type="button" wire:click="decrement({{ $service->id }})" class="inline-flex w-9 items-center justify-center text-navy-900 transition hover:bg-navy-50" aria-label="Diminuer la quantité">−</button>
                    <label for="qte-{{ $prefix }}-{{ $service->id }}" class="sr-only">Quantité</label>
                    <input
                        id="qte-{{ $prefix }}-{{ $service->id }}"
                        type="number"
                        min="1"
                        max="99"
                        step="1"
                        inputmode="numeric"
                        wire:model.live.debounce.500ms="items.{{ $service->id }}"
                        class="w-12 border-x border-navy-900/15 bg-white py-1.5 text-center text-sm tabular text-navy-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500/40 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                    >
                    <button type="button" wire:click="increment({{ $service->id }})" class="inline-flex w-9 items-center justify-center text-navy-900 transition hover:bg-navy-50" aria-label="Augmenter la quantité">+</button>
                </div>
                <button type="button" wire:click="remove({{ $service->id }})" class="label text-ardoise transition hover:text-red-700" aria-label="Retirer {{ $service->name }}">Retirer</button>
            </div>
        </li>
    @endforeach
</ul>

<dl class="mt-4 space-y-1.5 border-t border-navy-900/10 pt-4 text-sm">
    <div class="flex items-baseline justify-between gap-3"><dt class="text-ardoise">Total HT</dt><dd class="font-semibold tabular text-navy-900">{{ gnf($cart['total_ht']) }}</dd></div>
    <div class="flex items-baseline justify-between gap-3"><dt class="text-ardoise">TVA 18&nbsp;%</dt><dd class="tabular text-navy-900">{{ gnf($cart['vat']) }}</dd></div>
    <div class="flex items-baseline justify-between gap-3 text-base"><dt class="font-semibold text-navy-900">Total TTC</dt><dd class="font-semibold tabular text-navy-900">{{ gnf($cart['total_ttc']) }}</dd></div>
    @if ($cart['on_quote'] > 0)
        <div class="flex items-center gap-2 pt-1 text-xs text-ardoise"><span class="led led-amber"></span>+ {{ $cart['on_quote'] }} prestation{{ $cart['on_quote'] > 1 ? 's' : '' }} sur devis, chiffrée{{ $cart['on_quote'] > 1 ? 's' : '' }} dans notre réponse</div>
    @endif
</dl>
