<?php

namespace App\Support;

/**
 * Typographie française des textes affichés.
 *
 * Remplace des espaces existantes, jamais plus : espace insécable (U+00A0) avant « : », à l'intérieur des
 * guillemets « » et entre un nombre et son unité ou symbole (« 8 h 30 », « 18 % », « 2 500 000 GNF ») ;
 * espace fine insécable (U+202F) avant « ; », « ! », « ? » et entre les groupes de milliers.
 * Les balises et attributs HTML sont laissés intacts ; une URL, qui ne contient pas d'espace, ne l'est jamais.
 * Idempotent : appliquer deux fois donne le même résultat.
 *
 * Un numéro de téléphone (« +224 628 18 79 30 ») n'est pas un texte : ne pas le passer ici, la règle des
 * milliers y insérerait une espace fine.
 */
final class Typo
{
    public const NBSP = "\u{00A0}";

    public const NNBSP = "\u{202F}";

    /** Unités et symboles collés à leur nombre par une insécable. */
    private const UNITS = '%|h|min|s|j|GNF|€|\$|k|M|Mo|Go|To|km|kg|g|m|cm|mm|°C|°';

    public static function fr(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $parts = preg_split('/(<[^>]*>)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];

        return implode('', array_map(
            fn (string $part): string => str_starts_with($part, '<') ? $part : self::text($part),
            $parts,
        ));
    }

    /** Applique les règles à un fragment de texte (hors balises). */
    private static function text(string $t): string
    {
        $rules = [
            // « mot : » → insécable avant le deux-points (jamais dans « http:// », sans espace avant). Le fragment
            // peut commencer par cette espace quand le mot précédent est dans une balise : « <a>…</a> : suite ».
            '/(?<!\s) :(?=[\s&»)\]]|$)/u' => self::NBSP.':',
            // « mot ? », « mot ! », « mot ; » (et « ?! ») → espace fine insécable.
            '/(?<!\s) ([;!?]+)(?=[\s&»)\]]|$)/u' => self::NNBSP.'$1',
            // Guillemets français : «&nbsp;texte&nbsp;».
            '/« /u' => '«'.self::NBSP,
            '/ »/u' => self::NBSP.'»',
            // Nombre + unité ou symbole : 18 %, 8 h, 600 000 GNF, 2 Go.
            '/(?<=\d) (?=(?:'.self::UNITS.')(?![\p{L}\d]))/u' => self::NBSP,
            // Heures écrites « 8 h 30 » : la minute reste collée au « h ».
            '/(?<=\d'.self::NBSP.'h) (?=\d)/u' => self::NBSP,
            // Groupes de milliers : 2 500 000.
            '/(?<=\d) (?=\d{3}(?!\d))/u' => self::NNBSP,
        ];

        return preg_replace(array_keys($rules), array_values($rules), $t) ?? $t;
    }
}
