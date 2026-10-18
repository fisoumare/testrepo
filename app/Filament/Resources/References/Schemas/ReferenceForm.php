<?php

namespace App\Filament\Resources\References\Schemas;

use App\Filament\Support\CommonFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReferenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Organisation')
                    ->columns(2)
                    ->components([
                        CommonFields::slugSource('name', 'Nom'),
                        TextInput::make('acronym')
                            ->label('Acronyme')
                            ->maxLength(32)
                            ->placeholder('DGSST'),
                        CommonFields::slug(),
                        TextInput::make('sector')
                            ->label('Secteur')
                            ->maxLength(255)
                            ->placeholder('Administration publique'),
                        TextInput::make('location')
                            ->label('Lieu')
                            ->maxLength(255)
                            ->placeholder('Conakry'),
                        TextInput::make('website_url')
                            ->label('Site web')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://'),
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('references')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ]),
                Section::make('Projet')
                    ->columns(2)
                    ->components([
                        TextInput::make('headline')
                            ->label('Titre du projet')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(5)
                            ->columnSpanFull(),
                        Repeater::make('services')
                            ->label('Prestations réalisées')
                            ->simple(TextInput::make('service')->label('Prestation')->required()->maxLength(255))
                            ->addActionLabel('Ajouter une prestation')
                            ->reorderable()
                            ->defaultItems(0),
                        TextInput::make('year')
                            ->label('Année')
                            ->integer()
                            ->minValue(2000)
                            ->maxValue(2100),
                    ]),
                Section::make('Publication')
                    ->columns(3)
                    ->components([
                        Toggle::make('is_featured')
                            ->label('Mise en avant')
                            ->helperText('Affichée sur la page d\'accueil.'),
                        Toggle::make('is_published')
                            ->label('Publiée')
                            ->default(true),
                        CommonFields::position(),
                    ]),
            ]);
    }
}
