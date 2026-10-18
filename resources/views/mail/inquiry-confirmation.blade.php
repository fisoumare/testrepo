<x-mail::layout>
<x-slot:header>
<x-mail::header :url="url('/')">
{{ setting('company_name', 'SOUMATIC') }}
</x-mail::header>
</x-slot:header>

# Nous avons bien reçu {{ $inquiry->isQuote() ? 'votre demande de devis' : 'votre message' }}

Bonjour {{ $inquiry->name }},

{{ $inquiry->isQuote() ? 'Votre demande de devis a été enregistrée' : 'Votre message a été enregistré' }} sous la référence **{{ $inquiry->reference }}**. Merci de la rappeler dans vos échanges avec nous.

Nous vous répondons **au plus tard le jour ouvré suivant**, {{ setting('hours') }}.

@if ($inquiry->isQuote() && $inquiry->items)
## Récapitulatif de votre sélection

<x-mail::table>
| N° | Prestation | Qté | Prix unitaire HT | Total HT |
|:---|:---|---:|---:|---:|
@foreach ($inquiry->items as $item)
| {{ $item['number'] ?? '' }} | {{ $item['name'] }}@if (! empty($item['unit'])) ({{ $item['unit'] }})@endif | {{ $item['quantity'] }} | {{ (int) ($item['unit_price'] ?? 0) > 0 ? gnf($item['unit_price']) : 'Sur devis' }} | {{ (int) ($item['unit_price'] ?? 0) > 0 ? gnf($item['total']) : 'Sur devis' }} |
@endforeach
</x-mail::table>

**Total HT : {{ gnf($inquiry->total_ht) }}** · TVA 18 % : {{ gnf(\App\Support\Money::vat((int) $inquiry->total_ht)) }} · **Total TTC : {{ gnf($totalTtc) }}**

Cette estimation ne vaut pas devis signé : nous vous adressons un devis en bonne et due forme, valable {{ setting('quote_validity_days', '30') }} jours. Prix hors taxes en francs guinéens, TVA {{ setting('vat_rate', '18') }} % en sus ; {{ setting('payment_terms') }}
@endif

@if ($inquiry->message)
## Votre message

<x-mail::panel>
{{ $inquiry->message }}
</x-mail::panel>
@endif

## Nous joindre

- Téléphone : {{ setting('phone') }}
- WhatsApp : {{ setting('whatsapp', setting('phone')) }}
- E-mail : {{ setting('email') }}
- Adresse : {{ setting('address') }}, {{ setting('city') }}, {{ setting('country') }}

<x-mail::button :url="url('/')">
Retour au site
</x-mail::button>

Cordialement,

L'équipe {{ setting('company_name', 'SOUMATIC') }}

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ setting('company_name', 'SOUMATIC') }} · {{ setting('address') }}, {{ setting('city') }}, {{ setting('country') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
