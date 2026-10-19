<x-mail::layout>
<x-slot:header>
<x-mail::header :url="url('/')">
{{ setting('company_name', 'SOUMATIC') }}
</x-mail::header>
</x-slot:header>

# {{ $inquiry->isQuote() ? 'Nouvelle demande de devis' : 'Nouveau message' }} {{ $inquiry->reference }}

Reçu le {{ $receivedAt }} depuis le site.

{{-- Textes saisis par le visiteur : MarkdownText::inline() neutralise la syntaxe Markdown (gras, liens, barres de tableau). --}}
<x-mail::panel>
**{{ \App\Mail\MarkdownText::inline($inquiry->name) }}**@if ($inquiry->organization) — {{ \App\Mail\MarkdownText::inline($inquiry->organization) }}@endif

- E-mail&nbsp;: {{ $inquiry->email }}
@if ($inquiry->phone)
- Téléphone&nbsp;: {{ \App\Mail\MarkdownText::inline($inquiry->phone) }}
@endif
@if ($inquiry->subject)
- Objet&nbsp;: {{ $inquiry->subject }}
@endif
</x-mail::panel>

@if ($inquiry->isQuote() && $inquiry->items)
## Prestations demandées

<x-mail::table>
| N° | Prestation | Qté | Prix unitaire HT | Total HT |
|:---|:---|---:|---:|---:|
@foreach ($inquiry->items as $item)
| {{ $item['number'] ?? '' }} | {{ \App\Mail\MarkdownText::inline($item['name']) }}@if (! empty($item['unit'])) ({{ \App\Mail\MarkdownText::inline($item['unit']) }})@endif | {{ $item['quantity'] }} | {{ (int) ($item['unit_price'] ?? 0) > 0 ? gnf($item['unit_price']) : 'Sur devis' }} | {{ (int) ($item['unit_price'] ?? 0) > 0 ? gnf($item['total']) : 'Sur devis' }} |
@endforeach
</x-mail::table>

**Total HT&nbsp;: {{ gnf($inquiry->total_ht) }}** · TVA 18&nbsp;%&nbsp;: {{ gnf(\App\Support\Money::vat((int) $inquiry->total_ht)) }} · **Total TTC&nbsp;: {{ gnf($totalTtc) }}**
@endif

@if ($inquiry->message)
## Message

<x-mail::visitor-text :text="$inquiry->message" />
@endif

<x-mail::button :url="$adminUrl">
Ouvrir dans le back-office
</x-mail::button>

Vous pouvez répondre directement à cet e-mail&nbsp;: la réponse partira à {{ $inquiry->email }}.

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ setting('company_name', 'SOUMATIC') }} · {{ setting('address') }}, {{ setting('city') }}, {{ setting('country') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
