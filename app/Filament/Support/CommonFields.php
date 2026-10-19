<?php

namespace App\Filament\Support;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * Champs de formulaire partagés par les ressources du back-office.
 */
class CommonFields
{
    /** Taille des colonnes VARCHAR par défaut (`$table->string()`) : MySQL refuse une valeur plus longue. */
    public const STRING_MAX = 255;

    /** Plus grande position acceptée (colonne `position`, entier non signé). */
    public const POSITION_MAX = 9999;

    /** Format d'un slug : lettres minuscules sans accent, chiffres, mots séparés par un tiret. */
    public const SLUG_REGEX = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /**
     * Champ « nom/titre » qui alimente automatiquement le slug tant que l'on crée
     * l'enregistrement (en édition, le slug existant est conservé). Le slug est calculé
     * par le modèle (HasSlug::slugFrom()), qui le borne à 255 caractères.
     */
    public static function slugSource(string $name, string $label, string $slugField = 'slug'): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->required()
            ->maxLength(self::STRING_MAX)
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, ?string $state, Set $set, ?string $model) use ($slugField): void {
                if ($operation === 'create') {
                    $set($slugField, ($model && method_exists($model, 'slugFrom')) ? $model::slugFrom($state) : Str::slug((string) $state));
                }
            });
    }

    public static function slug(string $sourceLabel = 'le nom'): TextInput
    {
        return TextInput::make('slug')
            ->label('Slug (URL)')
            ->required()
            ->maxLength(self::STRING_MAX)
            // Minuscules ASCII seulement : la contrainte d'unicité se comporte alors de la même façon
            // sous SQLite (comparaison binaire) et sous MySQL (utf8mb4_unicode_ci, qui confond
            // majuscules et minuscules, « é » et « e »).
            ->regex(self::SLUG_REGEX)
            ->validationMessages(['regex' => "Utilisez uniquement des lettres minuscules sans accent, des chiffres et des tirets (par exemple «\u{00A0}site-vitrine\u{00A0}»)."])
            ->unique(ignoreRecord: true)
            ->helperText("Généré depuis {$sourceLabel} ; modifiable. Lettres minuscules, chiffres et tirets.");
    }

    public static function position(): TextInput
    {
        return TextInput::make('position')
            ->label('Position')
            ->integer()
            ->minValue(0)
            ->maxValue(self::POSITION_MAX)
            ->default(0)
            ->helperText('Ordre d\'affichage (croissant).');
    }

    public static function active(string $label = 'Actif'): Toggle
    {
        return Toggle::make('is_active')
            ->label($label)
            ->default(true);
    }
}
