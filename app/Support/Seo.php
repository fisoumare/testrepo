<?php

namespace App\Support;

/**
 * Balises <title> et meta description des pages publiques.
 *
 * - title() : première variante qui tient en 60 caractères, suffixe « · SOUMATIC » du layout compris ;
 *   à défaut, la dernière variante raccourcie au mot, sans mot-outil, nombre, ponctuation, guillemet ni
 *   parenthèse laissés en suspens.
 * - description() : 120 à 160 caractères. Un texte plus long est ramené à 155 caractères sur une phrase
 *   complète (à défaut sur une proposition terminée par un point, à défaut au mot avec « … ») ; un texte
 *   plus court est complété par les précisions fournies, dans l'ordre, tant qu'il reste sous 120.
 *
 * Les coupes passent par preg_replace en mode /u : trim() et rtrim() travaillent par octet et abîmeraient
 * un « à » (C3 A0) en retirant l'octet A0 d'une insécable.
 */
final class Seo
{
    public const TITLE_MAX = 60;

    public const DESCRIPTION_MIN = 120;

    public const DESCRIPTION_MAX = 155;

    /** Un texte qui tient là est gardé entier, sans coupe. */
    public const DESCRIPTION_HARD_MAX = 160;

    /** Mots qu'une coupe ne doit pas laisser en suspens. */
    private const STOP_WORDS = [
        'de', 'des', 'du', 'd', 'et', 'ou', 'à', 'a', 'au', 'aux', 'en', 'la', 'le', 'les', 'l', 'un', 'une',
        'par', 'pour', 'sur', 'sous', 'avec', 'sans', 'dans', 'selon', 'chez', 'vers', 'entre', 'après', 'avant',
        'depuis', 'pendant', 'comme', 'jusqu', 'jusqu\'à', 'ni', 'que', 'qui', 'dont', 'mais', 'donc', 'car', 'puis',
        'son', 'sa', 'ses', 'votre', 'vos', 'notre', 'nos', 'leur', 'leurs', 'ce', 'cet', 'cette', 'ces', 'se', 'ne',
        'pas', 'plus', 'très', 'si', 'y',
    ];

    /** Espaces, insécables comprises (classe de caractères PCRE). */
    private const SPACES = '\s\x{00A0}\x{202F}';

    /** Ce qu'une coupe ne doit pas laisser en fin de texte. */
    private const DANGLING = self::SPACES.',;:(«–-';

    public static function title(?string ...$candidates): string
    {
        $budget = self::TITLE_MAX - mb_strlen(' · '.setting('company_name', 'SOUMATIC'));
        $last = '';

        foreach ($candidates as $candidate) {
            $candidate = self::clean((string) $candidate);

            if ($candidate === '') {
                continue;
            }

            if (mb_strlen($candidate) <= $budget) {
                return $candidate;
            }

            $last = $candidate;
        }

        return self::tidy(self::atWordBoundary($last, $budget));
    }

    /** @param  list<string>  $extras  précisions ajoutées, dans l'ordre, tant que le texte reste sous 120 caractères */
    public static function description(?string $text, array $extras = []): string
    {
        $text = self::clean((string) $text);

        foreach ($extras as $extra) {
            if (mb_strlen($text) >= self::DESCRIPTION_MIN) {
                break;
            }

            $candidate = trim($text.' '.self::clean($extra));

            if (mb_strlen($candidate) <= self::DESCRIPTION_MAX) {
                $text = $candidate;
            }
        }

        return mb_strlen($text) <= self::DESCRIPTION_HARD_MAX ? $text : self::cut($text, self::DESCRIPTION_MAX);
    }

    /** Texte brut sur une ligne : sans balise, entités décodées, espaces ASCII réduites (les insécables restent). */
    private static function clean(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    /** Coupe sur une phrase, sinon sur une proposition suivie d'un point, sinon au mot avec « … » ; jamais sous 120 ni, si possible, dans une parenthèse. */
    private static function cut(string $text, int $max): string
    {
        $head = mb_substr($text, 0, $max + 1);

        if (preg_match_all('/[.!?](?=\s|$)/u', $head, $matches, PREG_OFFSET_CAPTURE)) {
            foreach (array_reverse($matches[0]) as [, $offset]) {
                $sentence = mb_strcut($head, 0, $offset + 1);

                if (mb_strlen($sentence) >= self::DESCRIPTION_MIN && self::isBalanced($sentence)) {
                    return $sentence;
                }
            }
        }

        if (preg_match_all('/['.self::SPACES.']?[;:,](?=\s|$)/u', $head, $matches, PREG_OFFSET_CAPTURE)) {
            foreach (array_reverse($matches[0]) as [, $offset]) {
                $clause = self::rtrim(mb_strcut($head, 0, $offset), self::SPACES);

                if (mb_strlen($clause) >= self::DESCRIPTION_MIN && self::isBalanced($clause)) {
                    return $clause.'.';
                }
            }
        }

        $words = self::atWordBoundary($text, $max - 1);
        $tidy = self::tidy($words);

        return (mb_strlen($tidy) >= self::DESCRIPTION_MIN ? $tidy : self::tidy($words, balance: false)).'…';
    }

    /** Retire ce qu'une coupe laisse en suspens : ponctuation, guillemet ou parenthèse ouverts, mot-outil ou nombre final. */
    private static function tidy(string $text, bool $balance = true): string
    {
        do {
            $before = $text;
            $text = self::rtrim($text, self::DANGLING);

            if ($balance && ($open = self::unclosedAt($text)) !== null) {
                $text = self::rtrim(mb_substr($text, 0, $open), self::DANGLING);
            }

            $words = preg_split('/['.self::SPACES.']+/u', $text) ?: [];
            $lastWord = mb_strtolower(preg_replace('/^[,;:().«»–-]+|[,;:().«»–-]+$/u', '', (string) end($words)) ?? '');

            if ($lastWord === '' || in_array($lastWord, self::STOP_WORDS, true) || preg_match('/^\d+$/', $lastWord)) {
                array_pop($words);
                $text = implode(' ', $words);
            }
        } while ($text !== $before && $text !== '');

        return $text;
    }

    /** Position du dernier guillemet ou de la dernière parenthèse restés ouverts, s'il y en a. */
    private static function unclosedAt(string $text): ?int
    {
        $positions = [];

        foreach (['«' => '»', '(' => ')'] as $open => $close) {
            if (mb_substr_count($text, $open) > mb_substr_count($text, $close)) {
                $positions[] = (int) mb_strrpos($text, $open);
            }
        }

        return $positions === [] ? null : max($positions);
    }

    private static function isBalanced(string $text): bool
    {
        return self::unclosedAt($text) === null;
    }

    private static function atWordBoundary(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $head = mb_substr($text, 0, $max);

        if (mb_substr($text, $max, 1) !== ' ') {
            $head = preg_replace('/['.self::SPACES.']+[^'.self::SPACES.']*$/u', '', $head) ?? $head;
        }

        return self::rtrim($head, self::SPACES);
    }

    /** rtrim() sûr en UTF-8 : $chars est une classe de caractères PCRE. */
    private static function rtrim(string $text, string $chars): string
    {
        return preg_replace('/['.$chars.']+$/u', '', $text) ?? $text;
    }
}
