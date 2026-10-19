<?php

namespace App\Providers\Filament;

use App\Filament\Support\InitialsAvatarProvider;
use Filament\Actions\Action;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    /** Identifiants des groupes de navigation (dans l'ordre d'affichage). */
    public const GROUP_CONTENT = 'Contenu';

    public const GROUP_INQUIRIES = 'Demandes';

    public const GROUP_SETTINGS = 'Réglages';

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // Mot de passe oublié (lien sur la page de connexion, e-mail de réinitialisation)
            // et page « Profil » du menu utilisateur (nom, e-mail, mot de passe).
            ->passwordReset()
            ->profile(isSimple: false)
            ->brandName('SOUMATIC')
            ->brandLogo(fn (): string => asset('brand/logo.svg'))
            ->darkModeBrandLogo(fn (): string => asset('brand/logo-blanc.svg'))
            ->brandLogoHeight('2.25rem')
            ->favicon(fn (): string => asset('favicon.svg'))
            ->colors([
                'primary' => Color::generateV3Palette('#0F2D52'),
                'success' => Color::generateV3Palette('#0E9F6E'),
                'info' => Color::Sky,
                'gray' => Color::Slate,
                'danger' => Color::Rose,
                'warning' => Color::Amber,
            ])
            // Police locale : aucun appel réseau (voir public/css/fonts.css).
            ->font('Inter Variable', url: asset('css/fonts.css'), provider: LocalFontProvider::class)
            ->defaultAvatarProvider(InitialsAvatarProvider::class)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make()->label(self::GROUP_CONTENT),
                NavigationGroup::make()->label(self::GROUP_INQUIRIES),
                NavigationGroup::make()->label(self::GROUP_SETTINGS),
            ])
            ->userMenuItems([
                Action::make('site')
                    ->label('Voir le site')
                    ->url(fn (): string => url('/'), shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedGlobeAlt),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
