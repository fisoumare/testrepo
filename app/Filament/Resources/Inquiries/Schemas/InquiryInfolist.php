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
use Illuminate\Support\HtmlString;

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
                            ->formatStateUsing(fn (?string $state): HtmlString => self::multiline($state))
                            ->columnSpanFull(),
                    ]),
                Section::make('Estimation')
                    ->description(typo('Lignes sélectionnées sur le site. Montants hors taxes en GNF ; TVA 18 %.'))
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
                            // Les libellés servent en affichage empilé (mobile), où les en-têtes du tableau disparaissent.
                            ->schema([
                                TextEntry::make('number')
                                    ->label('Numéro')
                                    ->fontFamily(FontFamily::Mono)
                                    ->placeholder('—'),
                                TextEntry::make('name')
                                    ->label('Prestation')
                                    ->weight(FontWeight::Medium),
                                TextEntry::make('unit')
                                    ->label('Unité')
                                    ->placeholder('—'),
                                TextEntry::make('quantity')
                                    ->label('Qté')
                                    ->alignment(Alignment::End),
                                TextEntry::make('unit_price')
                                    ->label('Prix unitaire HT')
                                    ->alignment(Alignment::End)
                                    ->formatStateUsing(fn (int|string|null $state): string => (int) $state > 0 ? gnf((int) $state) : 'Sur devis'),
                                TextEntry::make('total')
                                    ->label('Total HT')
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
                                    ->label(typo('TVA (18 %)'))
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
                            ->formatStateUsing(fn (?string $state): HtmlString => self::multiline($state))
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

    /**
     * Texte brut (saisi par le visiteur ou en interne) : échappé, puis sauts de ligne rendus par <br>.
     * HtmlString évite le passage par l'assainisseur HTML, inutile puisque tout est déjà échappé par e().
     */
    private static function multiline(?string $text): HtmlString
    {
        return new HtmlString(nl2br(e((string) $text)));
    }
}
