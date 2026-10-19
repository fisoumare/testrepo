<?php

namespace App\Filament\Resources\Inquiries\Tables;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => InquiryResource::types()[$state] ?? $state)
                    ->color(fn (string $state): string => $state === Inquiry::TYPE_QUOTE ? 'primary' : 'gray')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->weight(FontWeight::Medium)
                    ->wrap()
                    ->searchable(['name', 'organization'])
                    ->sortable()
                    ->description(fn (Inquiry $record): ?string => $record->organization),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('total_ht')
                    ->label('Total HT')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(fn (?int $state): string => gnf((int) $state))
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Inquiry::statuses()[$state] ?? $state)
                    ->color(fn (string $state): string => InquiryResource::statusColor($state))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Reçue le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(InquiryResource::types()),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(Inquiry::statuses())
                    ->multiple(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucune demande')
            ->emptyStateDescription('Les formulaires de contact et d\'estimation du site alimentent cette liste.')
            ->emptyStateIcon(Heroicon::OutlinedInboxArrowDown)
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->modalHeading('Supprimer les demandes sélectionnées'),
                ]),
            ]);
    }
}
