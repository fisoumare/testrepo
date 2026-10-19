<?php

namespace App\Filament\Resources\ServiceCategories;

use App\Filament\Resources\ServiceCategories\Pages\ManageServiceCategories;
use App\Filament\Support\CommonActions;
use App\Filament\Support\CommonFields;
use App\Models\ServiceCategory;
use App\Providers\Filament\AdminPanelProvider;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class ServiceCategoryResource extends Resource
{
    protected static ?string $model = ServiceCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = AdminPanelProvider::GROUP_CONTENT;

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'catégorie';

    protected static ?string $pluralModelLabel = 'catégories';

    protected static ?string $navigationLabel = 'Catégories';

    protected static ?string $recordTitleAttribute = 'name';

    /** Nombre de prestations rattachées (compteur déjà chargé par la table quand il existe). */
    public static function servicesCount(ServiceCategory $category): int
    {
        return (int) ($category->services_count ?? $category->services()->count());
    }

    /**
     * La contrainte `service_category_id … cascadeOnDelete()` effacerait les prestations avec leur
     * catégorie (fiches publiques, plan du site, estimations) : la suppression est refusée tant
     * qu'il en reste. Retourne le message d'explication, ou null si la suppression est possible.
     */
    public static function deletionBlockedMessage(ServiceCategory $category): ?string
    {
        $count = self::servicesCount($category);

        if ($count === 0) {
            return null;
        }

        return typo(sprintf(
            'La catégorie « %s » contient %d %s : réaffectez-les ou supprimez-les avant de la supprimer.',
            $category->name,
            $count,
            $count > 1 ? 'prestations' : 'prestation',
        ));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('code')
                            ->label('Code')
                            ->maxLength(8)
                            ->placeholder('U01')
                            ->helperText('Repère monospace de l\'unité (U01…U08).'),
                        CommonFields::position(),
                        CommonFields::slugSource('name', 'Nom'),
                        CommonFields::slug(),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        TextInput::make('icon')
                            ->label('Icône')
                            ->maxLength(255)
                            ->helperText('Nom de l\'icône utilisée sur le site (texte libre).'),
                        CommonFields::active('Active'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->fontFamily(FontFamily::Mono)
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn (ServiceCategory $record): ?string => $record->description),
                TextColumn::make('services_count')
                    ->label('Prestations')
                    ->counts('services')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Position')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->defaultSort('position')
            ->emptyStateHeading('Aucune catégorie')
            ->emptyStateDescription('Créez une catégorie pour organiser le catalogue des prestations.')
            ->recordActions([
                EditAction::make()
                    ->modalHeading(fn (ServiceCategory $record): string => typo('Modifier la catégorie « '.$record->name.' »')),
                DeleteAction::make()
                    ->modalHeading(fn (ServiceCategory $record): string => typo('Supprimer la catégorie « '.$record->name.' »'))
                    // Bouton grisé avec explication tant que la catégorie contient des prestations…
                    ->disabled(fn (ServiceCategory $record): bool => self::servicesCount($record) > 0)
                    ->tooltip(fn (ServiceCategory $record): ?string => self::deletionBlockedMessage($record))
                    // … et refus explicite si l'action est tout de même déclenchée.
                    ->before(function (DeleteAction $action, ServiceCategory $record): void {
                        if ($message = self::deletionBlockedMessage($record)) {
                            Notification::make()->danger()->title('Suppression impossible')->body($message)->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...CommonActions::toggleBulkActions(),
                    DeleteBulkAction::make()
                        ->modalHeading('Supprimer les catégories sélectionnées')
                        ->before(function (DeleteBulkAction $action, Collection $records): void {
                            $blocked = $records->filter(fn (ServiceCategory $category): bool => self::servicesCount($category) > 0);

                            if ($blocked->isEmpty()) {
                                return;
                            }

                            Notification::make()
                                ->danger()
                                ->title('Suppression impossible')
                                ->body(typo(sprintf(
                                    'Ces catégories contiennent encore des prestations : %s. Réaffectez-les ou supprimez-les avant de supprimer les catégories.',
                                    $blocked->map(fn (ServiceCategory $category): string => sprintf('« %s » (%d)', $category->name, self::servicesCount($category)))->implode(', '),
                                )))
                                ->send();

                            $action->cancel();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageServiceCategories::route('/'),
        ];
    }
}
