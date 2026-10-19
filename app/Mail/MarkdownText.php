<?php

namespace App\Mail;

/**
 * Neutralise la syntaxe Markdown dans les textes saisis par les visiteurs avant leur insertion
 * dans un gabarit d'e-mail Markdown (`{{ }}` n'échappe que le HTML, pas « *gras* » ni « [lien](…) »).
 */
final class MarkdownText
{
    /**
     * Échappe les caractères Markdown actifs en ligne (emphase, liens, code, barre de tableau).
     * Les marqueurs de bloc (#, -, >) n'ont d'effet qu'en début de ligne : inutile ici, le texte
     * est toujours inséré au milieu d'une ligne (cellule de tableau, « Bonjour … »).
     */
    public static function inline(?string $text): string
    {
        return preg_replace('/([\\\\`*_\[\]|~])/u', '\\\\$1', (string) $text) ?? '';
    }
}
