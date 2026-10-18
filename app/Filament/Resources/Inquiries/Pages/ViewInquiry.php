<?php

namespace App\Filament\Resources\Inquiries\Pages;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewInquiry extends ViewRecord
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changeStatus')
                ->label('Changer le statut')
                ->icon(Heroicon::OutlinedArrowPath)
                ->modalHeading('Changer le statut de la demande')
                ->modalSubmitActionLabel('Enregistrer')
                ->fillForm(fn (Inquiry $record): array => [
                    'status' => $record->status,
                    'internal_notes' => $record->internal_notes,
                ])
                ->schema([
                    Select::make('status')
                        ->label('Statut')
                        ->options(Inquiry::statuses())
                        ->required()
                        ->native(false),
                    Textarea::make('internal_notes')
                        ->label('Notes internes')
                        ->rows(5)
                        ->helperText('Visibles uniquement dans le back-office.'),
                ])
                ->action(function (array $data, Inquiry $record): void {
                    $record->update([
                        'status' => $data['status'],
                        'internal_notes' => $data['internal_notes'] ?? null,
                    ]);

                    Notification::make()
                        ->title('Statut mis à jour')
                        ->body('La demande '.$record->reference.' est désormais « '.Inquiry::statuses()[$record->status].' ».')
                        ->success()
                        ->send();
                }),
            Action::make('email')
                ->label('Répondre par e-mail')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->url(fn (Inquiry $record): string => InquiryResource::mailtoUrl($record)),
            Action::make('whatsapp')
                ->label('WhatsApp')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color('success')
                ->url(fn (Inquiry $record): ?string => InquiryResource::whatsappUrl($record), shouldOpenInNewTab: true)
                ->visible(fn (Inquiry $record): bool => InquiryResource::whatsappUrl($record) !== null),
            DeleteAction::make(),
        ];
    }
}
