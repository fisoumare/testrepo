<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Support\Typo;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Familles de prestations (codes U01–U08), typographie française appliquée aux descriptions.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function categories(): array
    {
        $categories = [
            ['code' => 'U01', 'name' => 'Conception', 'icon' => 'layout', 'description' => 'Sites vitrines, portails, boutiques en ligne, applications web Laravel / Filament et applications mobiles Android et iOS, à prix fixe ; les forfaits de création comprennent la maquette et une garantie de 3 mois.'],
            ['code' => 'U02', 'name' => 'Solutions clés en main', 'icon' => 'cube', 'description' => 'EduGes, MediGes SST, MediGes et Compiniya : des logiciels prêts à l\'emploi, en abonnement mensuel hébergé par nos soins ou en licence installée sur votre serveur.'],
            ['code' => 'U03', 'name' => 'Hébergement & Noms de domaine', 'icon' => 'server', 'description' => 'Serveurs professionnels du basique à la haute performance, noms de domaine .com et .gn, certificats SSL, adresses e-mail professionnelles, migration et sauvegarde externalisée.'],
            ['code' => 'U04', 'name' => 'API & Intégrations', 'icon' => 'plug', 'description' => 'API sécurisées, paiement mobile money (Orange Money, MTN MoMo), passerelle SMS, WhatsApp Business, e-mails automatiques, notifications push et connexions entre logiciels.'],
            ['code' => 'U05', 'name' => 'Maintenance & Support', 'icon' => 'wrench', 'description' => 'Contrats de maintenance annuels, interventions ponctuelles, heures prépayées, montées de version majeure et nettoyage après piratage.'],
            ['code' => 'U06', 'name' => 'Analyse & Cahier des charges', 'icon' => 'document', 'description' => 'Ateliers de cadrage, cahiers des charges de site ou d\'application, spécifications techniques, cartographie des processus et termes de référence d\'appels d\'offres.'],
            ['code' => 'U07', 'name' => 'Conseil & Audit', 'icon' => 'shield', 'description' => 'Diagnostic numérique de votre organisation, audits technique, de sécurité, de vitesse et de référencement, analyse d\'offres et journées d\'expertise.'],
            ['code' => 'U08', 'name' => 'Formation', 'icon' => 'academic', 'description' => 'Prise en main de votre espace d\'administration, formations sur mesure, formation développeur Laravel & Filament, sensibilisation à la cybersécurité, tutoriels et guides.'],
        ];

        foreach ($categories as $index => $category) {
            $categories[$index]['description'] = Typo::fr($category['description']);
        }

        return $categories;
    }

    public function run(): void
    {
        foreach (self::categories() as $position => $category) {
            ServiceCategory::query()->updateOrCreate(
                ['name' => $category['name']],
                $category + ['position' => $position + 1, 'is_active' => true],
            );
        }
    }
}
