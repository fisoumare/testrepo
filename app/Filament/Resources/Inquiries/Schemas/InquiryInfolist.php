<?php

namespace App\Filament\Resources\Inquiries\Schemas;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use App\Support\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;

class InquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Demande')
                    ->columns(2)
                    ->components([
                        TextEntry::make('reference')
                            ->label('Référence')
                            ->fontFamily(FontFamily::Mono)
                            ->weight(FontWeight::SemiBold)
                            ->copyable(),
                        TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => InquiryResource::types()[$state] ?? $state)
                            ->color(fn (string $state): string => $state === Inquiry::TYPE_QUOTE ? 'primary' : 'gray'),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => Inquiry::statuses()[$state] ?? $state)
                            ->color(fn (string $state): string => InquiryResource::statusColor($state)),
                        TextEntry::make('created_at')
                            ->label('Reçue le')
                            ->dateTime('d/m/Y à H:i'),
                    ]),
                Section::make('Coordonnées')
                    ->columns(2)
                    ->components([
                        TextEntry::make('name')
                            ->label('Nom'),
                        TextEntry::make('organization')
                            ->label('Organisation')
                            ->placeholder('—'),
                        TextEntry::make('email')
                            ->label('E-mail')
                            ->copyable()
                            ->url(fn (Inquiry $record): string => 'mailto:'.$record->email),
                        TextEntry::make('phone')
                            ->label('Téléphone')
                            ->copyable()
                            ->placeholder('—'),
                        TextEntry::make('subject')
                            ->label('Objet')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make('Message')
                    ->columnSpanFull()
                    ->components([
                        TextEntry::make('message')
                            ->hiddenLabel()
                            ->placeholder('Aucun message.')
                            ->prose()
                            ->columnSpanFull(),
                    ]),
                Section::make('Estimation')
                    ->description('Lignes sélectionnées sur le site. Montants hors taxes en GNF ; TVA 18 %.')
                    ->visible(fn (Inquiry $record): bool => $record->isQuote())
                    ->columnSpanFull()
                    ->components([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Numéro'),
                                TableColumn::make('Prestation'),
                                TableColumn::make('Unité'),
                                TableColumn::make('Qté')->alignment(Alignment::End),
                                TableColumn::make('Prix unitaire HT')->alignment(Alignment::End),
                                TableColumn::make('Total HT')->alignment(Alignment::End),
                            ])
                            ->schema([
                                TextEntry::make('number')->fontFamily(FontFamily::Mono)->placeholder('—'),
                                TextEntry::make('name')->weight(FontWeight::Medium),
                                TextEntry::make('unit')->placeholder('—'),
                                TextEntry::make('quantity')->alignment(Alignment::End),
                                TextEntry::make('unit_price')
                                    ->alignment(Alignment::End)
                                    ->formatStateUsing(fn (int|string|null $state): string => (int) $state > 0 ? gnf((int) $state) : 'Sur devis'),
                                TextEntry::make('total')
                                    ->alignment(Alignment::End)
                                    ->formatStateUsing(fn (int|string|null $state): string => (int) $state > 0 ? gnf((int) $state) : 'Sur devis'),
                            ])
                            ->columnSpanFull(),
                        Grid::make(3)
                            ->components([
                                TextEntry::make('total_ht')
                                    ->label('Total HT')
                                    ->formatStateUsing(fn (?int $state): string => gnf((int) $state)),
                                TextEntry::make('vat')
                                    ->label('TVA (18 %)')
                                    ->state(fn (Inquiry $record): string => gnf(Money::vat((int) $record->total_ht))),
                                TextEntry::make('total_ttc')
                                    ->label('Total TTC')
                                    ->weight(FontWeight::SemiBold)
                                    ->state(fn (Inquiry $record): string => gnf(Money::ttc((int) $record->total_ht))),
                            ]),
                    ]),
                Section::make('Suivi interne')
                    ->columnSpanFull()
                    ->columns(2)
                    ->components([
                        TextEntry::make('internal_notes')
                            ->label('Notes internes')
                            ->placeholder('Aucune note.')
                            ->prose()
                            ->columnSpanFull(),
                        TextEntry::make('ip')
                            ->label('Adresse IP')
                            ->fontFamily(FontFamily::Mono)
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->label('Dernière modification')
                            ->dateTime('d/m/Y à H:i'),
                    ])
                    ->collapsible(),
            ]);
    }
}
