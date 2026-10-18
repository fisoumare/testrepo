{{--
    Rend lisible un texte brut sans l'altérer : paragraphes sur les lignes vides, listes pour les
    lignes commençant par « - », et mentions « Comprend : », « Non compris : », « Délai : » détachées
    en paragraphes distincts, le mot-clé mis en valeur.
--}}
@props(['text'])
@php
    $markers = '(?:Comprend|Compris|Non compris|Non inclus|Délai indicatif|Délai)';
    $space = '[\s\x{00A0}\x{202F}]';
    $blocks = [];

    $pushParagraph = function (array $lines) use (&$blocks, $markers, $space): void {
        $parts = preg_split(
            '/(?:^|(?<=[.;!?]'.$space.'))(?='.$markers.$space.'?:)/u',
            implode(' ', $lines),
            -1,
            PREG_SPLIT_NO_EMPTY,
        ) ?: [];

        foreach ($parts as $part) {
            $part = trim($part);
            $label = null;

            if (preg_match('/^('.$markers.$space.'?:)'.$space.'*/u', $part, $match)) {
                $label = $match[1];
                $part = substr($part, strlen($match[0]));
            }

            if ($label !== null || $part !== '') {
                $blocks[] = ['type' => 'p', 'label' => $label, 'text' => $part];
            }
        }
    };

    foreach (preg_split('/\n\s*\n/u', trim(str_replace("\r\n", "\n", (string) $text))) ?: [] as $paragraph) {
        $lines = [];
        $items = [];

        foreach (explode("\n", $paragraph) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (preg_match('/^[-–•]\s+(.+)$/u', $line, $match)) {
                if ($lines !== []) {
                    $pushParagraph($lines);
                    $lines = [];
                }
                $items[] = $match[1];

                continue;
            }

            if ($items !== []) {
                $blocks[] = ['type' => 'ul', 'items' => $items];
                $items = [];
            }
            $lines[] = $line;
        }

        if ($lines !== []) {
            $pushParagraph($lines);
        }
        if ($items !== []) {
            $blocks[] = ['type' => 'ul', 'items' => $items];
        }
    }
@endphp
<div {{ $attributes->merge(['class' => 'prose-site']) }}>
    @foreach ($blocks as $block)
        @if ($block['type'] === 'ul')
            <ul>
                @foreach ($block['items'] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        @else
            <p>
                @if ($block['label'])
                    <strong class="{{ str_starts_with($block['label'], 'Non') ? 'text-ardoise' : 'text-emerald-700' }}">{{ $block['label'] }}</strong>
                @endif
                {{ $block['text'] }}
            </p>
        @endif
    @endforeach
</div>
