<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Support\CommonFields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    /** Prix maximal d'une prestation : 999 999 999 999 GNF HT. */
    public const PRICE_MAX = 999_999_999_999;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->description('Rattachement au catalogue et nom affiché sur le site.')
                    ->columnSpanFull()
                    ->columns(3)
                    ->components([
                        Select::make('service_category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Select::make('solution_id')
                            ->label('Solution liée')
                            ->relationship('solution', 'name')
                            ->nullable()
                            ->preload()
                            ->searchable()
                            ->helperText('Uniquement pour les prestations rattachées à EduGes, MediGes SST, MediGes ou Compiniya.'),
                        TextInput::make('number')
                            ->label('Numéro')
                            ->maxLength(32)
                            ->placeholder('PREST-2026-0001')
                            ->helperText('Code affiché en monospace sur les fiches.'),
                        CommonFields::slugSource('name', 'Nom'),
                        CommonFields::slug(),
                        Select::make('unit')
                            ->label('Unité')
                            ->options(array_combine(ServiceResource::UNITS, ServiceResource::UNITS))
                            ->default('Forfait')
                            ->required()
                            ->native(false),
                    ]),
                Section::make('Prix')
                    ->columnSpanFull()
                    ->columns(3)
                    ->components([
                        TextInput::make('price')
                            ->label('Prix')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            // Borne haute : le total d'une estimation (prix × 99 × 50 lignes) doit tenir
                            // dans la colonne `inquiries.total_ht` (entier 64 bits).
                            ->maxValue(self::PRICE_MAX)
                            ->default(0)
                            ->required()
                            ->suffix('GNF HT')
                            ->helperText('Montant entier hors taxes. Laissez 0 pour afficher « Sur devis ».'),
                    ]),
                Section::make('Contenu')
                    ->columnSpanFull()
                    ->components([
                        Textarea::make('summary')
                            ->label('Résumé')
                            ->rows(2)
                            ->maxLength(170)
                            ->helperText('170 caractères maximum : affiché sur les cartes et dans les résultats de recherche.'),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(8)
                            ->maxLength(5000)
                            ->helperText('Texte de la fiche détaillée (un paragraphe par ligne vide).'),
                    ]),
                Section::make('Publication')
                    ->columnSpanFull()
                    ->columns(3)
                    ->components([
                        Toggle::make('is_featured')
                            ->label('Mise en avant')
                            ->helperText('Affichée sur la page d\'accueil.'),
                        CommonFields::active('Active'),
                        CommonFields::position(),
                    ]),
            ]);
    }
}
