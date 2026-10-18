<?php

namespace App\Filament\Resources\Solutions\Tables;

use App\Filament\Support\CommonActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SolutionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('accent')
                    ->label('LED'),
                TextColumn::make('code')
                    ->label('Code')
                    ->fontFamily(FontFamily::Mono)
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->weight(FontWeight::Medium)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tagline')
                    ->label('Accroche')
                    ->wrap()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('services_count')
                    ->label('Prestations')
                    ->counts('services')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Position')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->defaultSort('position')
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
