<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Slug d'URL généré depuis un nom ou un titre, borné à la taille de la colonne `slug` (VARCHAR 255).
 *
 * Str::slug() peut allonger un texte (« œ » → « oe », « @ » → « at ») : un titre de 255 caractères
 * peut donner un slug plus long, que MySQL en mode strict refuse (« Data too long »). Le slug est
 * donc coupé à 255 caractères, de préférence entre deux mots.
 */
trait HasSlug
{
    public static function slugFrom(?string $source): string
    {
        $max = 255;
        $slug = Str::slug((string) $source);

        if (strlen($slug) <= $max) {
            return $slug;
        }

        // Str::slug() ne produit que des caractères ASCII : substr() ne coupe pas de caractère en deux.
        $head = substr($slug, 0, $max + 1);
        $cut = strrpos($head, '-');

        // Coupe au dernier tiret s'il laisse un slug assez long, sinon à 255 caractères tout rond.
        $slug = ($cut !== false && $cut >= $max / 2) ? substr($head, 0, $cut) : substr($slug, 0, $max);

        return rtrim($slug, '-');
    }
}
