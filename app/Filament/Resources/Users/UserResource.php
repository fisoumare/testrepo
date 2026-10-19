<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use App\Providers\Filament\AdminPanelProvider;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = AdminPanelProvider::GROUP_SETTINGS;

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'utilisateur';

    protected static ?string $pluralModelLabel = 'utilisateurs';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Les comptes ne se créent que depuis le back-office (pas d'inscription publique) :
     * on interdit de supprimer son propre compte et le dernier compte, sans quoi plus
     * personne ne pourrait se connecter. Retourne le motif du refus, ou null.
     */
    public static function deletionBlockedMessage(User $record): ?string
    {
        if ($record->is(Filament::auth()->user())) {
            return 'Vous ne pouvez pas supprimer votre propre compte.';
        }

        if (User::query()->whereKeyNot($record->getKey())->doesntExist()) {
            return typo('Impossible de supprimer le dernier compte : il doit rester au moins un utilisateur.');
        }

        return null;
    }

    /**
     * Même règle pour une sélection de masse (la ligne du compte connecté n'est pas sélectionnable,
     * la vérification reste par sécurité).
     *
     * @param  Collection<int, User>  $records
     */
    public static function bulkDeletionBlockedMessage(Collection $records): ?string
    {
        if ($records->contains(fn (User $record): bool => $record->is(Filament::auth()->user()))) {
            return typo('Votre propre compte fait partie de la sélection : retirez-le avant de supprimer.');
        }

        if (User::query()->whereKeyNot($records->modelKeys())->doesntExist()) {
            return typo('Impossible de supprimer tous les comptes : il doit rester au moins un utilisateur.');
        }

        return null;
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
