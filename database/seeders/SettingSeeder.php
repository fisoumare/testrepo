<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Paramètres du site. Les valeurs vides sont « à compléter » depuis le back-office.
     */
    public function run(): void
    {
        $defaults = [
            'company_name' => 'SOUMATIC',
            'tagline' => 'Numérique de confiance',
            'baseline' => 'Sites, applications et logiciels de gestion, conçus et maintenus à Conakry.',
            'manager_name' => 'Fodé Idrissa SOUMARÉ',
            'manager_title' => 'Gérant',
            'phone' => '+224 628 18 79 30',
            'whatsapp' => '+224 628 18 79 30',
            'email' => 'fisoumare@soumatic.com',
            'address' => 'Yattaya, Commune de Sonfonia',
            'city' => 'Conakry',
            'country' => 'Guinée',
            'rccm' => 'GN.TCC.2026.A.06358',
            'nif' => '484274303',
            'hours' => 'Lundi – vendredi, 8 h 30 – 17 h 30 (GMT)',
            'hours_short' => 'Lun–ven · 8 h 30 – 17 h 30',
            'support_channels' => 'WhatsApp, e-mail, espace client, téléphone en cas de blocage',
            'vat_rate' => '18',
            'quote_validity_days' => '30',
            'payment_terms' => '50 % à la commande, solde à la validation. Abonnements, hébergement et maintenance payés d\'avance.',
            'warranty' => 'Garantie de 3 mois sur ce qui ne fonctionne pas comme prévu, hors évolutions, contenus et hébergement.',
            'hero_kicker' => 'Conakry · Laravel · Filament · October CMS',
            'hero_title' => 'Des logiciels qui tiennent la charge.',
            'hero_subtitle' => 'SOUMATIC conçoit, héberge et maintient des sites, des applications web et mobiles et des logiciels de gestion pour les écoles, les structures de santé, les administrations et les entreprises de Guinée. Prix fixes, affichés en GNF.',
            'meta_description' => 'SOUMATIC, à Conakry : conception de sites et d\'applications web et mobiles (Laravel, Filament, October CMS), logiciels de gestion EduGes, MediGes SST, MediGes et Compiniya, hébergement, maintenance, conseil et formation. Prix fixes affichés en GNF.',
            'compiniya_url' => 'https://compiniya.com',
            'facebook_url' => '',
            'linkedin_url' => '',
            'youtube_url' => '',
            'map_url' => '',
            'notification_email' => 'fisoumare@soumatic.com',
            'footer_note' => 'Montants hors taxes, en francs guinéens (GNF). TVA 18 % en sus. Devis valables 30 jours.',
        ];

        foreach ($defaults as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
