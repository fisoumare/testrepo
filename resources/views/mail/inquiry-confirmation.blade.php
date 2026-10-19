<x-mail::layout>
<x-slot:header>
<x-mail::header :url="url('/')">
{{ setting('company_name', 'SOUMATIC') }}
</x-mail::header>
</x-slot:header>

# Nous avons bien reçu {{ $inquiry->isQuote() ? 'votre demande de devis' : 'votre message' }}

{{-- Textes saisis par le visiteur : MarkdownText::inline() neutralise la syntaxe Markdown (gras, liens, barres de tableau). --}}
Bonjour {{ \App\Mail\MarkdownText::inline($inquiry->name) }},

{{ $inquiry->isQuote() ? 'Votre demande de devis a été enregistrée' : 'Votre message a été enregistré' }} sous la référence **{{ $inquiry->reference }}**. Merci de la rappeler dans vos échanges avec nous.

Première réponse **au plus tard le jour ouvré suivant**, {{ setting('hours') }}.

@if ($inquiry->isQuote() && $inquiry->items)
## Récapitulatif de votre sélection

<x-mail::table>
| N° | Prestation | Qté | Prix unitaire HT | Total HT |
|:---|:---|---:|---:|---:|
@foreach ($inquiry->items as $item)
| {{ $item['number'] ?? '' }} | {{ \App\Mail\MarkdownText::inline($item['name']) }}@if (! empty($item['unit'])) ({{ \App\Mail\MarkdownText::inline($item['unit']) }})@endif | {{ $item['quantity'] }} | {{ (int) ($item['unit_price'] ?? 0) > 0 ? gnf($item['unit_price']) : 'Sur devis' }} | {{ (int) ($item['unit_price'] ?? 0) > 0 ? gnf($item['total']) : 'Sur devis' }} |
@endforeach
</x-mail::table>

**Total HT&nbsp;: {{ gnf($inquiry->total_ht) }}** · TVA 18&nbsp;%&nbsp;: {{ gnf(\App\Support\Money::vat((int) $inquiry->total_ht)) }} · **Total TTC&nbsp;: {{ gnf($totalTtc) }}**

Cette estimation ne vaut pas devis signé&nbsp;: nous confirmons ensuite votre demande par un devis en bonne et due forme, valable {{ setting('quote_validity_days', '30') }} jours. Prix hors taxes en francs guinéens, TVA {{ setting('vat_rate', '18') }}&nbsp;% en sus&#8239;; {{ setting('payment_terms') }}
@endif

@if ($inquiry->message)
## Votre message

<x-mail::visitor-text :text="$inquiry->message" />
@endif

## Nous joindre

- Téléphone&nbsp;: {{ setting('phone') }}
- WhatsApp&nbsp;: {{ setting('whatsapp', setting('phone')) }}
- E-mail&nbsp;: {{ setting('email') }}
- Adresse&nbsp;: {{ setting('address') }}, {{ setting('city') }}, {{ setting('country') }}

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
