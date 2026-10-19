<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->weight(FontWeight::Medium)
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record): ?string => $record->is(Filament::auth()->user()) ? 'Votre compte' : null),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('Aucun utilisateur')
            // Le compte connecté ne peut pas être sélectionné : il n'entre jamais dans une suppression de masse.
            ->checkIfRecordIsSelectableUsing(fn (User $record): bool => ! $record->is(Filament::auth()->user()))
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Supprimer les utilisateurs sélectionnés')
                        ->before(function (DeleteBulkAction $action, Collection $records): void {
                            if ($message = UserResource::bulkDeletionBlockedMessage($records)) {
                                Notification::make()->danger()->title('Suppression impossible')->body($message)->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }
}
