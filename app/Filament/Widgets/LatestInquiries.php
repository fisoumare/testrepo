<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Actions\Action;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestInquiries extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Dernières demandes')
            ->description('Les dix demandes les plus récentes reçues depuis le site.')
            ->query(Inquiry::query()->latest()->limit(10))
            ->paginated(false)
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Inquiry $record): string => InquiryResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Aucune demande reçue')
            ->emptyStateDescription('Les formulaires de contact et d\'estimation du site alimentent cette liste.')
            ->emptyStateIcon(Heroicon::OutlinedInboxArrowDown)
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->fontFamily(FontFamily::Mono),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => InquiryResource::types()[$state] ?? $state)
                    ->color(fn (string $state): string => $state === Inquiry::TYPE_QUOTE ? 'primary' : 'gray'),
                TextColumn::make('name')
                    ->label('Nom')
                    ->weight(FontWeight::Medium)
                    ->wrap()
                    ->description(fn (Inquiry $record): ?string => $record->organization),
                TextColumn::make('total_ht')
                    ->label('Total HT')
                    ->alignEnd()
                    ->formatStateUsing(fn (?int $state): string => gnf((int) $state))
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Inquiry::statuses()[$state] ?? $state)
                    ->color(fn (string $state): string => InquiryResource::statusColor($state)),
                TextColumn::make('created_at')
                    ->label('Reçue le')
                    ->since()
                    ->tooltip(fn (Inquiry $record): string => $record->created_at->format('d/m/Y H:i')),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Voir')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (Inquiry $record): string => InquiryResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
