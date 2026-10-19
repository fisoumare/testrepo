<?php

namespace App\Support;

class Money
{
    public const VAT_RATE = 0.18;

    /** Formate un montant entier en GNF : 13 500 000 GNF. */
    public static function gnf(int|float|null $amount, bool $withCurrency = true): string
    {
        $formatted = number_format((float) ($amount ?? 0), 0, ',', "\u{202F}");

        return $withCurrency ? $formatted."\u{00A0}GNF" : $formatted;
    }

    /**
     * Version courte, jamais arrondie : 5 M GNF, 13,5 M GNF, 750 k GNF. Un montant qui ne s'écrit pas
     * exactement sous cette forme (3 750 000) est rendu en entier par gnf(), un prix affiché devant exister au catalogue.
     */
    public static function short(int|float|null $amount): string
    {
        $amount = (float) ($amount ?? 0);

        if ($amount >= 1_000_000 && fmod($amount, 100_000.0) == 0.0) {
            $value = $amount / 1_000_000;
            $text = fmod($value, 1.0) == 0.0 ? number_format($value, 0, ',', "\u{202F}") : number_format($value, 1, ',', "\u{202F}");

            return $text."\u{00A0}M\u{00A0}GNF";
        }

        if ($amount >= 1_000 && $amount < 1_000_000 && fmod($amount, 1_000.0) == 0.0) {
            return number_format($amount / 1_000, 0, ',', "\u{202F}")."\u{00A0}k\u{00A0}GNF";
        }

        return self::gnf($amount);
    }

    public static function vat(int $amountHt): int
    {
        return (int) round($amountHt * self::VAT_RATE);
    }

    public static function ttc(int $amountHt): int
    {
        return $amountHt + self::vat($amountHt);
    }
}
