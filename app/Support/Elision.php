<?php

namespace App\Support;

/**
 * Élision française : « de » devient « d' » devant une voyelle ou un h muet
 * (« d'EduGes », « d'une solution », mais « de MediGes », « de Compiniya »).
 */
final class Elision
{
    /** « de » + nom : d'EduGes, de MediGes SST. */
    public static function de(string $noun): string
    {
        return self::elide('de', $noun);
    }

    private static function elide(string $word, string $noun): string
    {
        $noun = trim($noun);

        if ($noun === '') {
            return $word;
        }

        return preg_match('/^[aeiouyàâäéèêëíìîïóòôöúùûüh]/iu', $noun)
            ? mb_substr($word, 0, -1)."'".$noun
            : $word.' '.$noun;
    }
}
