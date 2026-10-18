<?php

namespace App\Filament\Support;

use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Actions de masse partagées : activer / désactiver (ou publier / dépublier).
 */
class CommonActions
{
    /**
     * @return array<int, BulkAction>
     */
    public static function toggleBulkActions(string $attribute = 'is_active', string $onLabel = 'Activer', string $offLabel = 'Désactiver'): array
    {
        return [
            BulkAction::make('enable')
                ->label($onLabel)
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->action(fn (Collection $records) => $records->each->update([$attribute => true]))
                ->deselectRecordsAfterCompletion(),
            BulkAction::make('disable')
                ->label($offLabel)
                ->icon(Heroicon::OutlinedXCircle)
                ->color('gray')
                ->requiresConfirmation()
                ->action(fn (Collection $records) => $records->each->update([$attribute => false]))
                ->deselectRecordsAfterCompletion(),
        ];
    }
}
