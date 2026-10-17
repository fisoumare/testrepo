<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Compiniya, notre logiciel de gestion commerciale, est en ligne sur compiniya.com',
                'category' => 'Solutions',
                'author' => 'SOUMATIC',
                'excerpt' => "Clients, devis, factures, encaissements, contrats à faire signer, projets et tâches : Compiniya est disponible en abonnement à partir de 100 000 GNF HT par mois, et en licence installée.",
                'body' => "<p>Compiniya est le logiciel de gestion commerciale en ligne de SOUMATIC. Il est accessible sur <a href=\"https://compiniya.com\">compiniya.com</a> pour les entreprises de Guinée et d'ailleurs.</p><h2>Ce que Compiniya gère</h2><ul><li>clients, devis, factures et encaissements ;</li><li>propositions commerciales techniques et financières ;</li><li>contrats à faire signer en ligne, découpés en jalons facturables ;</li><li>projets et tâches de l'équipe ;</li><li>demandes d'assistance de vos clients (tickets) ;</li><li>fournisseurs et dépenses ;</li><li>un espace en ligne pour vos clients, qui ne comptent pas comme utilisateurs.</li></ul><h2>Formules</h2><p>Abonnement mensuel jusqu'à 2, 5 ou 15 utilisateurs (100 000, 300 000 et 700 000 GNF HT par mois), payable au mois ou à l'année, hébergement, sauvegardes, mises à jour et assistance compris. Licence installée sur votre serveur jusqu'à 15 utilisateurs : 17 500 000 GNF HT, maintenance annuelle 16 %.</p><p>En option, Compiniya se pilote en écrivant vos demandes à un assistant d'intelligence artificielle : préparer un devis, faire le point du jour, relancer une facture.</p>",
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'SOUMATIC accompagne la DGSST sur la plateforme nationale de santé au travail',
                'category' => 'Références',
                'author' => 'SOUMATIC',
                'excerpt' => "Un contrat de cinq ans, signé en mai 2026, confie à SOUMATIC la maintenance, les évolutions, le support et la formation autour de la plateforme snst-guinee.com.",
                'body' => "<p>La Direction Générale de la Sécurité et de la Santé au Travail (DGSST), sous tutelle du Ministère de l'Emploi, du Travail et de la Protection Sociale, a confié à SOUMATIC l'accompagnement de la plateforme nationale de santé au travail, <a href=\"https://snst-guinee.com\">snst-guinee.com</a>.</p><p>La plateforme gère l'inscription des structures de santé au travail, leurs habilitations, les rendez-vous médicaux et les certificats d'aptitude des travailleurs.</p><h2>Notre mission</h2><ul><li>maintenance corrective, avec des délais d'intervention de 48 heures pour un incident grave ;</li><li>évolutions et mises à jour, de la proposition à la recette puis à la mise en production ;</li><li>support du lundi au vendredi, de 8 h à 17 h ;</li><li>surveillance de la plateforme et rapport mensuel d'activité ;</li><li>formation des utilisateurs.</li></ul><p>Le contrat, signé le 29 mai 2026, court sur cinq ans.</p>",
                'is_published' => true,
                'published_at' => now()->subDays(10),
            ],
        ];

        foreach ($posts as $post) {
            Post::query()->updateOrCreate(['title' => $post['title']], $post);
        }
    }
}
