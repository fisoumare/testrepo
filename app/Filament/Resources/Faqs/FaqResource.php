<?php

namespace App\Filament\Resources\Faqs;

use App\Filament\Resources\Faqs\Pages\ManageFaqs;
use App\Filament\Support\CommonActions;
use App\Filament\Support\CommonFields;
use App\Models\Faq;
use App\Providers\Filament\AdminPanelProvider;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use UnitEnum;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = AdminPanelProvider::GROUP_CONTENT;

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'question';

    protected static ?string $pluralModelLabel = 'FAQ';

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('question')
                            ->label('Question')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('answer')
                            ->label('Réponse')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                        TextInput::make('group')
                            ->label('Groupe')
                            ->maxLength(255)
                            ->datalist(fn (): array => Faq::query()->whereNotNull('group')->distinct()->orderBy('group')->pluck('group')->all())
                            ->helperText('Rubrique d\'affichage, par exemple « Prix et paiement » ou « Support ».'),
                        CommonFields::position(),
                        CommonFields::active('Active'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->label('Question')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn (Faq $record): string => str($record->answer)->limit(120)->toString()),
                TextColumn::make('group')
                    ->label('Groupe')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('position')
                    ->label('Position')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
            ])
            ->defaultGroup(
                Group::make('group')
                    ->label('Groupe')
                    ->collapsible(),
            )
            ->filters([
                SelectFilter::make('group')
                    ->label('Groupe')
                    ->options(fn (): array => Faq::query()->whereNotNull('group')->distinct()->orderBy('group')->pluck('group', 'group')->all()),
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->defaultSort('position')
            ->emptyStateHeading('Aucune question')
            ->recordActions([
                EditAction::make()->modalHeading('Modifier la question'),
                DeleteAction::make()->modalHeading('Supprimer la question'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...CommonActions::toggleBulkActions(),
                    DeleteBulkAction::make()->modalHeading('Supprimer les questions sélectionnées'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFaqs::route('/'),
        ];
    }
}
