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
    /**
     * Champ « nom/titre » qui alimente automatiquement le slug tant que l'on crée
     * l'enregistrement (en édition, le slug existant est conservé).
     */
    public static function slugSource(string $name, string $label, string $slugField = 'slug'): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->required()
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, ?string $state, Set $set) use ($slugField): void {
                if ($operation === 'create') {
                    $set($slugField, Str::slug((string) $state));
                }
            });
    }

    public static function slug(string $sourceLabel = 'le nom'): TextInput
    {
        return TextInput::make('slug')
            ->label('Slug (URL)')
            ->required()
            ->maxLength(255)
            ->alphaDash()
            ->unique(ignoreRecord: true)
            ->helperText("Généré depuis {$sourceLabel} ; modifiable. Lettres minuscules, chiffres et tirets.");
    }

    public static function position(): TextInput
    {
        return TextInput::make('position')
            ->label('Position')
            ->integer()
            ->minValue(0)
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
