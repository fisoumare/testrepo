<?php

namespace Database\Seeders;

use App\Models\Solution;
use Illuminate\Database\Seeder;

class SolutionSeeder extends Seeder
{
    /**
     * Contenu établi à partir des fiches du catalogue Compiniya de SOUMATIC (rien d'inventé).
     */
    public function run(): void
    {
        $support = 'Assistance à distance du lundi au vendredi, de 8 h 30 à 17 h 30 (hors jours fériés), première réponse au plus tard le jour ouvré suivant';

        $solutions = [
            [
                'code' => 'S01',
                'name' => 'EduGes',
                'tagline' => 'Gestion scolaire en ligne',
                'audience' => 'Écoles primaires, collèges, lycées et groupes scolaires',
                'accent' => '#0E9F6E',
                'website_url' => null,
                'summary' => 'EduGes gère l\'année scolaire de bout en bout : inscriptions, élèves et classes, notes et bulletins, frais de scolarité et paiements. En abonnement mensuel hébergé par SOUMATIC, ou en licence installée sur le serveur de l\'école.',
                'description' => "<p>EduGes est le logiciel de gestion scolaire de SOUMATIC. Il est proposé par école (un site), en abonnement mensuel payable au mois ou à l'année, ou en licence installée achetée une seule fois.</p><p>L'abonnement court sur les 12 mois de l'année, vacances scolaires comprises : l'accès et les données sont conservés pendant les vacances. Jusqu'à 50 élèves, une offre gratuite en libre-service est proposée.</p><p>La mise en service comprend le paramétrage de l'année scolaire (classes, matières, coefficients, périodes, frais de scolarité et échéances), la création des comptes du personnel, l'import de la liste des élèves à partir de notre modèle Excel et une formation à l'école, à Conakry ou à distance, par groupes de 12 personnes au maximum.</p>",
                'features' => ['Inscriptions', 'Élèves et classes', 'Notes et bulletins', 'Frais de scolarité et paiements'],
                'included' => [
                    'Hébergement sur nos serveurs, accès par identifiant et mot de passe, connexion chiffrée',
                    'Sauvegarde automatique chaque nuit, conservée 7 jours',
                    'Stockage des fichiers joints jusqu\'à 10 Go',
                    'Mises à jour d\'EduGes (corrections et améliorations)',
                    $support,
                ],
            ],
            [
                'code' => 'S02',
                'name' => 'MediGes SST',
                'tagline' => 'Gestion médicale des services de santé au travail',
                'audience' => 'Services de santé au travail, services médicaux d\'entreprise et structures SST',
                'accent' => '#0EA5E9',
                'website_url' => null,
                'summary' => 'MediGes SST suit les entreprises, les postes et les risques, les dossiers médicaux, les visites d\'embauche et périodiques, les aptitudes et les fiches d\'aptitude, avec des statistiques. Jusqu\'à 15 000 salariés suivis.',
                'description' => "<p>MediGes SST est le logiciel de gestion médicale de SOUMATIC destiné aux structures de santé au travail. Il est proposé en abonnement mensuel (jusqu'à 1 000, 5 000 ou 15 000 salariés suivis) ou en licence installée sur votre serveur.</p><p>La mise en service, payée une seule fois, comprend une réunion de démarrage, le paramétrage de votre service (jusqu'à 3 sites ou infirmeries ; comptes et droits par profil : médecin, infirmier, secrétariat ; types de visites et périodicités ; fiche d'aptitude à votre en-tête ; liste des postes et des risques), le chargement de vos entreprises et de vos salariés à partir de notre modèle Excel, et 2 journées de formation.</p>",
                'features' => ['Entreprises suivies (votre entreprise, ses sous-traitants ou vos entreprises adhérentes)', 'Postes et risques', 'Dossiers médicaux', 'Visites d\'embauche et périodiques', 'Aptitudes et fiches d\'aptitude', 'Statistiques'],
                'included' => [
                    'Hébergement sur nos serveurs, connexion chiffrée',
                    'Sauvegarde automatique chaque nuit, conservée 7 jours',
                    'Stockage des documents jusqu\'à 20 Go',
                    'Mises à jour de MediGes SST',
                    $support,
                ],
            ],
            [
                'code' => 'S03',
                'name' => 'MediGes',
                'tagline' => 'Gestion hospitalière',
                'audience' => 'Centres de santé, cliniques et hôpitaux',
                'accent' => '#F43F5E',
                'website_url' => null,
                'summary' => 'MediGes couvre le parcours du patient et la gestion de l\'établissement : accueil et admissions, dossiers patients, consultations, hospitalisation, facturation et caisse, assurances et prises en charge, pharmacie, laboratoire.',
                'description' => "<p>MediGes est le logiciel de gestion hospitalière de SOUMATIC. Il est proposé en abonnement mensuel selon la taille de l'établissement (centre de santé jusqu'à 10 lits et 15 comptes utilisateurs ; clinique jusqu'à 30 lits et 40 comptes ; hôpital jusqu'à 150 lits) ou en licence installée sur le serveur de l'établissement.</p><p>La mise en service comprend la création de l'espace en ligne ou l'installation sur le serveur retenu, le paramétrage de l'établissement, la création des comptes et la formation des équipes.</p>",
                'features' => ['Accueil et admissions', 'Dossiers patients', 'Consultations', 'Hospitalisation', 'Facturation et caisse', 'Assurances et prises en charge', 'Pharmacie', 'Laboratoire'],
                'included' => [
                    'Hébergement sur nos serveurs, connexion chiffrée',
                    'Sauvegarde automatique chaque nuit, conservée 7 jours',
                    'Stockage des documents jusqu\'à 20 Go',
                    'Mises à jour de MediGes',
                    $support,
                ],
            ],
            [
                'code' => 'S04',
                'name' => 'Compiniya',
                'tagline' => 'Gestion commerciale en ligne',
                'audience' => 'Entreprises, cabinets et prestataires de services',
                'accent' => '#6366F1',
                'website_url' => 'https://compiniya.com',
                'summary' => 'Compiniya centralise clients, devis, factures, encaissements, propositions commerciales, contrats à faire signer, projets et tâches, assistance aux clients, fournisseurs et dépenses, avec un espace en ligne pour vos clients.',
                'description' => "<p>Compiniya est le logiciel de gestion commerciale en ligne de SOUMATIC, disponible sur <a href=\"https://compiniya.com\">compiniya.com</a>. Il est proposé en abonnement mensuel (jusqu'à 2, 5 ou 15 utilisateurs, puis par utilisateur supplémentaire) ou en licence installée sur votre serveur. Vos clients disposent d'un espace en ligne et ne comptent pas comme utilisateurs.</p><p>En option, Compiniya se pilote en écrivant vos demandes à un assistant d'intelligence artificielle (par exemple, préparer un devis).</p><p>La mise en service comprend l'ouverture de votre espace, le paramétrage de votre entreprise (coordonnées, logo, NIF et RCCM, TVA, numérotation, modèles de devis, factures et contrats, moyens de paiement acceptés), la création des comptes utilisateurs, l'import de vos clients, articles et fournisseurs jusqu'à 500 lignes et une formation d'une demi-journée.</p>",
                'features' => ['Clients', 'Devis et factures', 'Encaissements', 'Propositions commerciales', 'Contrats à faire signer', 'Projets et tâches', 'Suivi des demandes d\'assistance de vos clients', 'Fournisseurs et dépenses', 'Espace en ligne pour vos clients', 'Option : assistant d\'intelligence artificielle'],
                'included' => [
                    'Hébergement sur nos serveurs, connexion chiffrée',
                    'Sauvegarde automatique chaque nuit, conservée 7 jours',
                    'Stockage des fichiers joints (5 à 20 Go selon la formule)',
                    'Mises à jour de Compiniya',
                    $support,
                ],
            ],
        ];

        foreach ($solutions as $position => $solution) {
            Solution::query()->updateOrCreate(
                ['name' => $solution['name']],
                $solution + ['position' => $position + 1, 'is_active' => true],
            );
        }
    }
}
