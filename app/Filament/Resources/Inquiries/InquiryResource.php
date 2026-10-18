<?php

namespace App\Filament\Resources\Inquiries;

use App\Filament\Resources\Inquiries\Pages\ListInquiries;
use App\Filament\Resources\Inquiries\Pages\ViewInquiry;
use App\Filament\Resources\Inquiries\Schemas\InquiryInfolist;
use App\Filament\Resources\Inquiries\Tables\InquiriesTable;
use App\Models\Inquiry;
use App\Providers\Filament\AdminPanelProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Demandes reçues depuis le site (contact et estimation). Lecture seule :
 * seuls le statut et les notes internes se modifient, depuis la page de détail.
 */
class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = AdminPanelProvider::GROUP_INQUIRIES;

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'demande';

    protected static ?string $pluralModelLabel = 'demandes';

    protected static ?string $navigationLabel = 'Demandes';

    protected static ?string $recordTitleAttribute = 'reference';

    /** @return array<string, string> */
    public static function types(): array
    {
        return [
            Inquiry::TYPE_QUOTE => 'Devis',
            Inquiry::TYPE_CONTACT => 'Contact',
        ];
    }

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            Inquiry::STATUS_NEW => 'warning',
            Inquiry::STATUS_IN_PROGRESS => 'info',
            Inquiry::STATUS_ANSWERED => 'success',
            Inquiry::STATUS_CLOSED => 'gray',
            default => 'gray',
        };
    }

    /** Lien WhatsApp vers le numéro du demandeur (indicatif guinéen ajouté si absent). */
    public static function whatsappUrl(Inquiry $inquiry): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $inquiry->phone);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 9 && str_starts_with($digits, '6')) {
            $digits = '224'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }

    public static function mailtoUrl(Inquiry $inquiry): string
    {
        return 'mailto:'.$inquiry->email.'?subject='.rawurlencode('Votre demande '.$inquiry->reference);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InquiryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InquiriesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Inquiry::query()->where('status', Inquiry::STATUS_NEW)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Demandes nouvelles';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInquiries::route('/'),
            'view' => ViewInquiry::route('/{record}'),
        ];
    }
}
