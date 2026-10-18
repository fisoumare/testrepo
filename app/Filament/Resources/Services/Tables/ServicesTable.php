<?php

namespace App\Filament\Resources\Services\Tables;

use App\Filament\Support\CommonActions;
use App\Models\Service;
use App\Models\ServiceCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Numéro')
                    ->fontFamily(FontFamily::Mono)
                    ->size(TextSize::ExtraSmall)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight(FontWeight::Medium)
                    ->tooltip(fn (Service $record): ?string => $record->summary),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Prix HT')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? gnf($state) : 'Sur devis')
                    ->color(fn (int $state): ?string => $state > 0 ? null : 'gray'),
                TextColumn::make('unit')
                    ->label('Unité')
                    ->toggleable(),
                IconColumn::make('is_featured')
                    ->label('Mise en avant')
                    ->boolean()
                    ->trueIcon(Heroicon::Star)
                    ->falseIcon(Heroicon::OutlinedStar)
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Position')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Group::make('category.name')
                    ->label('Catégorie')
                    ->collapsible(),
                Group::make('solution.name')
                    ->label('Solution'),
            ])
            ->filters([
                SelectFilter::make('service_category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name')
                    ->preload()
                    ->multiple(),
                SelectFilter::make('solution_id')
                    ->label('Solution')
                    ->relationship('solution', 'name')
                    ->preload(),
                TernaryFilter::make('is_featured')
                    ->label('Mise en avant'),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            // Tri par défaut : position de la catégorie, puis position de la prestation.
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderBy(ServiceCategory::query()->select('position')->whereColumn('service_categories.id', 'services.service_category_id'))
                ->orderBy('position')
                ->orderBy('name'))
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...CommonActions::toggleBulkActions(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
