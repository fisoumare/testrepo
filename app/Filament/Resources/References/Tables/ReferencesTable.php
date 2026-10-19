<?php

namespace App\Filament\Resources\References\Tables;

use App\Filament\Support\CommonActions;
use App\Models\Reference;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReferencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('acronym')
                    ->label('Acronyme')
                    ->weight(FontWeight::Medium)
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('name')
                    ->label('Nom')
                    ->wrap()
                    ->searchable()
                    ->sortable()
                    ->description(fn (Reference $record): ?string => $record->headline),
                TextColumn::make('sector')
                    ->label('Secteur')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('year')
                    ->label('Année')
                    ->sortable()
                    ->placeholder('—'),
                IconColumn::make('is_featured')
                    ->label('Mise en avant')
                    ->boolean()
                    ->trueIcon(Heroicon::Star)
                    ->falseIcon(Heroicon::OutlinedStar)
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label('Publiée')
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Position')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Publiée'),
                TernaryFilter::make('is_featured')->label('Mise en avant'),
            ])
            ->defaultSort('position')
            ->emptyStateHeading('Aucune référence')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...CommonActions::toggleBulkActions('is_published', 'Publier', 'Dépublier'),
                    DeleteBulkAction::make()->modalHeading('Supprimer les références sélectionnées'),
                ]),
            ]);
    }
}
