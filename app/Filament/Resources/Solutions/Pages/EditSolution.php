<?php

namespace App\Filament\Resources\Solutions\Pages;

use App\Filament\Resources\Solutions\SolutionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditSolution extends EditRecord
{
    protected static string $resource = SolutionResource::class;

    protected static ?string $title = 'Modifier la solution';

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecordTitle();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
