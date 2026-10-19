<?php

namespace Database\Seeders;

use App\Models\Reference;
use App\Support\Typo;
use Illuminate\Database\Seeder;

class ReferenceSeeder extends Seeder
{
    /**
     * Seule la DGSST est publiée : son accompagnement est documenté. Les autres clients
     * enregistrés dans Compiniya sont créés non publiés, à compléter depuis le back-office.
     */
    public function run(): void
    {
        $references = [
            [
                'name' => 'Direction Générale de la Sécurité et de la Santé au Travail',
                'acronym' => 'DGSST',
                'sector' => 'Administration publique · Santé au travail',
                'location' => 'Conakry, Guinée',
                'website_url' => 'https://snst-guinee.com',
                'headline' => 'Plateforme nationale de santé au travail',
                'description' => "SOUMATIC accompagne la DGSST, sous tutelle du Ministère de l'Emploi, du Travail et de la Protection Sociale, sur la plateforme nationale de santé au travail (snst-guinee.com) : inscription des structures de santé au travail, habilitations, rendez-vous médicaux et certificats d'aptitude. Contrat de 5 ans signé en mai 2026 : maintenance corrective, évolutions, mises à jour, support, surveillance avec rapport mensuel et formation des utilisateurs.",
                'services' => ['Maintenance corrective', 'Évolutions et mises à jour', 'Support du lundi au vendredi', 'Surveillance et rapport mensuel', 'Formation'],
                'year' => 2026,
                'is_featured' => true,
                'is_published' => true,
            ],
            ['name' => 'Guinée Santé Travail', 'acronym' => 'GST', 'sector' => 'Santé au travail', 'location' => 'Conakry, Guinée', 'headline' => 'À compléter', 'is_published' => false],
            ['name' => 'Clinique PREVENTICA', 'acronym' => 'PREVENTICA', 'sector' => 'Santé', 'location' => 'Guinée', 'headline' => 'À compléter', 'is_published' => false],
            ['name' => 'CMTST', 'acronym' => 'CMTST', 'sector' => null, 'location' => 'Guinée', 'headline' => 'À compléter', 'is_published' => false],
            ['name' => 'FONIJ', 'acronym' => 'FONIJ', 'sector' => null, 'location' => 'Guinée', 'headline' => 'À compléter', 'is_published' => false],
        ];

        foreach ($references as $position => $reference) {
            foreach (['sector', 'headline', 'description'] as $field) {
                if (isset($reference[$field])) {
                    $reference[$field] = Typo::fr($reference[$field]);
                }
            }

            if (isset($reference['services'])) {
                $reference['services'] = array_map(Typo::fr(...), $reference['services']);
            }

            Reference::query()->updateOrCreate(
                ['acronym' => $reference['acronym']],
                $reference + ['position' => $position + 1],
            );
        }
    }
}
