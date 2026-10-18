<x-mail::layout>
<x-slot:header>
<x-mail::header :url="url('/')">
{{ setting('company_name', 'SOUMATIC') }}
</x-mail::header>
</x-slot:header>

# {{ $inquiry->isQuote() ? 'Nouvelle demande de devis' : 'Nouveau message' }} {{ $inquiry->reference }}

Reçu le {{ $receivedAt }} depuis le site.

<x-mail::panel>
**{{ $inquiry->name }}**@if ($inquiry->organization) — {{ $inquiry->organization }}@endif

- E-mail : {{ $inquiry->email }}
@if ($inquiry->phone)
- Téléphone : {{ $inquiry->phone }}
@endif
@if ($inquiry->subject)
- Objet : {{ $inquiry->subject }}
@endif
</x-mail::panel>

@if ($inquiry->isQuote() && $inquiry->items)
## Prestations demandées

<x-mail::table>
| N° | Prestation | Qté | Prix unitaire HT | Total HT |
|:---|:---|---:|---:|---:|
@foreach ($inquiry->items as $item)
| {{ $item['number'] ?? '' }} | {{ $item['name'] }}@if (! empty($item['unit'])) ({{ $item['unit'] }})@endif | {{ $item['quantity'] }} | {{ (int) ($item['unit_price'] ?? 0) > 0 ? gnf($item['unit_price']) : 'Sur devis' }} | {{ (int) ($item['unit_price'] ?? 0) > 0 ? gnf($item['total']) : 'Sur devis' }} |
@endforeach
</x-mail::table>

**Total HT : {{ gnf($inquiry->total_ht) }}** · TVA 18 % : {{ gnf(\App\Support\Money::vat((int) $inquiry->total_ht)) }} · **Total TTC : {{ gnf($totalTtc) }}**
@endif

@if ($inquiry->message)
## Message

<x-mail::panel>
{{ $inquiry->message }}
</x-mail::panel>
@endif

<x-mail::button :url="$adminUrl">
Ouvrir dans le back-office
</x-mail::button>

Vous pouvez répondre directement à cet e-mail : la réponse partira à {{ $inquiry->email }}.

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ setting('company_name', 'SOUMATIC') }} · {{ setting('address') }}, {{ setting('city') }}, {{ setting('country') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
