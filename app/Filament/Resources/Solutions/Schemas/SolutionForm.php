<?php

namespace App\Filament\Resources\Solutions\Schemas;

use App\Filament\Support\CommonFields;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SolutionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns(2)
                    ->components([
                        TextInput::make('code')
                            ->label('Code')
                            ->maxLength(8)
                            ->placeholder('S01'),
                        ColorPicker::make('accent')
                            ->label('Couleur d\'accent')
                            ->hex()
                            // La valeur est injectée dans des attributs style du site : seul #RRGGBB est accepté.
                            ->regex('/^#[0-9A-Fa-f]{6}$/')
                            ->validationMessages(['regex' => 'Indiquez une couleur au format #RRGGBB, par exemple #0E9F6E.'])
                            ->helperText('Couleur de la LED et des accents sur le site, au format #RRGGBB (#0E9F6E par défaut).'),
                        CommonFields::slugSource('name', 'Nom'),
                        CommonFields::slug(),
                        TextInput::make('tagline')
                            ->label('Accroche')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('audience')
                            ->label('Public visé')
                            ->maxLength(255)
                            ->placeholder('Écoles et universités'),
                        TextInput::make('website_url')
                            ->label('Site web')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://'),
                        FileUpload::make('image')
                            ->label('Image')
                            ->image()
                            ->disk('public')
                            ->directory('solutions')
                            ->visibility('public')
                            ->maxSize(4096)
                            ->columnSpanFull(),
                    ]),
                Section::make('Contenu')
                    ->components([
                        Textarea::make('summary')
                            ->label('Résumé')
                            ->rows(3)
                            ->helperText('Affiché sur les cartes et en introduction de la fiche.'),
                        RichEditor::make('description')
                            ->label('Description')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('solutions/fichiers'),
                    ]),
                Section::make('Fonctions et contenu de l\'offre')
                    ->columns(2)
                    ->components([
                        Repeater::make('features')
                            ->label('Fonctions principales')
                            ->simple(TextInput::make('feature')->label('Fonction')->required()->maxLength(255))
                            ->addActionLabel('Ajouter une fonction')
                            ->reorderable()
                            ->defaultItems(0),
                        Repeater::make('included')
                            ->label('Compris dans l\'offre')
                            ->simple(TextInput::make('item')->label('Élément')->required()->maxLength(255))
                            ->addActionLabel('Ajouter un élément')
                            ->reorderable()
                            ->defaultItems(0),
                    ]),
                Section::make('Publication')
                    ->columns(2)
                    ->components([
                        CommonFields::position(),
                        CommonFields::active('Active'),
                    ]),
            ]);
    }
}
