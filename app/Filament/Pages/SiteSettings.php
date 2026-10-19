<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Providers\Filament\AdminPanelProvider;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use UnitEnum;

/**
 * Paramètres du site : paires clé/valeur de la table `settings`, lues partout
 * via setting('clé'). Le formulaire est un schéma Filament sur une page Livewire ;
 * la page rend son contenu (formulaire + bouton Enregistrer) via content().
 */
class SiteSettings extends Page
{
    use InteractsWithFormActions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = AdminPanelProvider::GROUP_SETTINGS;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Paramètres du site';

    protected static ?string $title = 'Paramètres du site';

    protected static ?string $slug = 'parametres';

    /**
     * Longueur maximale des zones de texte. Les valeurs sont stockées dans `settings.value` (TEXT,
     * 65 535 octets sous MySQL) ; les champs d'une ligne sont limités à 255 caractères.
     */
    public const TEXTAREA_MAX = 1000;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::all_());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Paramètres')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Identité')
                            ->icon(Heroicon::OutlinedBuildingOffice2)
                            ->columns(2)
                            ->schema([
                                TextInput::make('company_name')->label('Nom de l\'entreprise')->required()->maxLength(255),
                                TextInput::make('tagline')->label('Signature')->maxLength(255)->helperText('Exemple : « Numérique de confiance ».'),
                                Textarea::make('baseline')->label('Phrase d\'accroche')->rows(2)->maxLength(self::TEXTAREA_MAX)->columnSpanFull(),
                                TextInput::make('manager_name')->label('Nom du gérant')->maxLength(255),
                                TextInput::make('manager_title')->label('Fonction du gérant')->maxLength(255),
                            ]),
                        Tab::make('Coordonnées')
                            ->icon(Heroicon::OutlinedMapPin)
                            ->columns(2)
                            ->schema([
                                TextInput::make('phone')->label('Téléphone')->tel()->maxLength(32),
                                TextInput::make('whatsapp')->label('WhatsApp')->tel()->maxLength(32)->helperText('Numéro international, utilisé pour les liens wa.me.'),
                                TextInput::make('email')->label('E-mail public')->email()->maxLength(255),
                                TextInput::make('notification_email')->label('E-mail de notification')->email()->maxLength(255)->helperText('Reçoit les demandes envoyées depuis le site.'),
                                TextInput::make('address')->label('Adresse')->maxLength(255)->columnSpanFull(),
                                TextInput::make('city')->label('Ville')->maxLength(255),
                                TextInput::make('country')->label('Pays')->maxLength(255),
                                TextInput::make('hours')->label('Horaires')->maxLength(255),
                                TextInput::make('hours_short')->label('Horaires (version courte)')->maxLength(255)->helperText('Affichés dans la barre d\'état.'),
                                TextInput::make('support_channels')->label('Canaux d\'assistance')->maxLength(255)->columnSpanFull(),
                                TextInput::make('map_url')->label('Lien carte')->url()->maxLength(255)->columnSpanFull()->helperText('Lien externe vers un plan ; laissez vide pour ne rien afficher.'),
                            ]),
                        Tab::make('Légal')
                            ->icon(Heroicon::OutlinedScale)
                            ->columns(2)
                            ->schema([
                                TextInput::make('rccm')->label('RCCM')->maxLength(64),
                                TextInput::make('nif')->label('NIF')->maxLength(64),
                                Textarea::make('legal_hosting')->label('Hébergeur du site')->rows(3)->maxLength(self::TEXTAREA_MAX)->columnSpanFull()->helperText('Mention affichée sur la page des mentions légales ; « à compléter » si vide.'),
                            ]),
                        Tab::make('Accueil & SEO')
                            ->icon(Heroicon::OutlinedHome)
                            ->columns(2)
                            ->schema([
                                TextInput::make('hero_kicker')->label('Sur-titre de l\'accueil')->maxLength(255)->columnSpanFull(),
                                TextInput::make('hero_title')->label('Titre de l\'accueil')->maxLength(255)->columnSpanFull(),
                                Textarea::make('hero_subtitle')->label('Sous-titre de l\'accueil')->rows(3)->maxLength(self::TEXTAREA_MAX)->columnSpanFull(),
                                Textarea::make('meta_description')->label('Description SEO par défaut')->rows(3)->maxLength(320)->columnSpanFull(),
                                Textarea::make('footer_note')->label('Note du pied de page')->rows(2)->maxLength(self::TEXTAREA_MAX)->columnSpanFull(),
                                TextInput::make('compiniya_url')->label('Lien Compiniya')->url()->maxLength(255),
                            ]),
                        Tab::make('Réseaux')
                            ->icon(Heroicon::OutlinedShare)
                            ->columns(1)
                            ->schema([
                                TextInput::make('facebook_url')->label('Facebook')->url()->maxLength(255)->placeholder('https://'),
                                TextInput::make('linkedin_url')->label('LinkedIn')->url()->maxLength(255)->placeholder('https://'),
                                TextInput::make('youtube_url')->label('YouTube')->url()->maxLength(255)->placeholder('https://'),
                            ]),
                        Tab::make('Facturation')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->columns(2)
                            ->schema([
                                TextInput::make('vat_rate')->label('Taux de TVA')->numeric()->minValue(0)->maxValue(100)->suffix('%'),
                                TextInput::make('quote_validity_days')->label('Validité des devis')->integer()->minValue(1)->maxValue(365)->suffix('jours'),
                                Textarea::make('payment_terms')->label('Conditions de paiement')->rows(3)->maxLength(self::TEXTAREA_MAX)->columnSpanFull(),
                                Textarea::make('warranty')->label('Garantie')->rows(3)->maxLength(self::TEXTAREA_MAX)->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /** Contenu de la page : le formulaire, suivi des actions (Enregistrer). */
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make($this->getFormActions())->key('form-actions'),
                    ]),
            ]);
    }

    /**
     * @return array<int, Action>
     */
    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Enregistrer')
                ->icon(Heroicon::OutlinedCheck)
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            Setting::set($key, $value);
        }

        Cache::forget(Setting::CACHE_KEY);

        Notification::make()
            ->title('Paramètres enregistrés')
            ->body('Les modifications sont visibles immédiatement sur le site.')
            ->success()
            ->send();
    }
}
