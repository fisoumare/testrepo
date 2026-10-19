<?php

namespace App\Filament\Resources\Solutions;

use App\Filament\Resources\Solutions\Pages\CreateSolution;
use App\Filament\Resources\Solutions\Pages\EditSolution;
use App\Filament\Resources\Solutions\Pages\ListSolutions;
use App\Filament\Resources\Solutions\Schemas\SolutionForm;
use App\Filament\Resources\Solutions\Tables\SolutionsTable;
use App\Models\Solution;
use App\Providers\Filament\AdminPanelProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SolutionResource extends Resource
{
    protected static ?string $model = Solution::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCubeTransparent;

    protected static string|UnitEnum|null $navigationGroup = AdminPanelProvider::GROUP_CONTENT;

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'solution';

    protected static ?string $pluralModelLabel = 'solutions';

    protected static ?string $navigationLabel = 'Solutions';

    /** Le modèle expose son slug dans les URL publiques ; l'admin reste sur l'identifiant. */
    protected static ?string $recordRouteKeyName = 'id';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SolutionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SolutionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSolutions::route('/'),
            'create' => CreateSolution::route('/create'),
            'edit' => EditSolution::route('/{record:id}/edit'),
        ];
    }
}
