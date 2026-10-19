<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Modifier l\'utilisateur';

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecordTitle();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                // Masquée pour son propre compte et pour le dernier compte ; refus explicite par sécurité.
                ->hidden(fn (User $record): bool => UserResource::deletionBlockedMessage($record) !== null)
                ->before(function (DeleteAction $action, User $record): void {
                    if ($message = UserResource::deletionBlockedMessage($record)) {
                        Notification::make()->danger()->title('Suppression impossible')->body($message)->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
