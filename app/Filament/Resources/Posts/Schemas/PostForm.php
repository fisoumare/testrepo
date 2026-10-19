<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\CommonFields;
use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Article')
                    ->columns(2)
                    ->components([
                        CommonFields::slugSource('title', 'Titre')->columnSpanFull(),
                        CommonFields::slug('le titre'),
                        TextInput::make('category')
                            ->label('Catégorie')
                            ->maxLength(255)
                            ->datalist(fn (): array => Post::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all()),
                        Textarea::make('excerpt')
                            ->label('Extrait')
                            ->rows(3)
                            ->maxLength(300)
                            ->columnSpanFull()
                            ->helperText('Chapeau affiché dans la liste et comme description SEO.'),
                        RichEditor::make('body')
                            ->label('Corps')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('posts/fichiers')
                            ->columnSpanFull(),
                    ]),
                Section::make('Illustration et auteur')
                    ->columns(2)
                    ->components([
                        FileUpload::make('cover')
                            ->label('Image de couverture')
                            ->image()
                            ->disk('public')
                            ->directory('posts')
                            ->visibility('public')
                            ->maxSize(4096)
                            ->columnSpanFull(),
                        TextInput::make('author')
                            ->label('Auteur')
                            ->maxLength(255)
                            ->default(fn (): ?string => setting('manager_name')),
                    ]),
                Section::make('Publication')
                    ->columns(2)
                    ->components([
                        Toggle::make('is_published')
                            ->label('Publiée')
                            ->helperText('Sans date, la publication prend la date du jour à l\'enregistrement.'),
                        DateTimePicker::make('published_at')
                            ->label('Date de publication')
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            // Colonne TIMESTAMP : MySQL n'accepte que les dates de 1970 à janvier 2038.
                            ->minDate('1970-01-02')
                            ->maxDate('2037-12-31 23:59')
                            ->helperText('Une date future programme la publication.'),
                    ]),
            ]);
    }
}
