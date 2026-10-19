<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Support\Typo;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Questions fréquentes. Les réponses reprennent les conditions du catalogue Compiniya et des
     * paramètres du site (rien d'inventé : prix fixes, TVA 18 %, devis 30 jours, 50 % à la commande,
     * garantie 3 mois, horaires d'assistance).
     */
    public function run(): void
    {
        $faqs = [
            ['group' => 'Prix et paiement', 'question' => 'Comment sont fixés vos prix ?', 'answer' => "Nos prestations ont un prix fixe, affiché hors taxes en francs guinéens (GNF). La TVA de 18 % s'ajoute sur la facture. Un devis est valable 30 jours. Seuls les projets hors catalogue (serveur sur mesure, logiciels sans documentation technique) font l'objet d'un devis spécifique."],
            ['group' => 'Prix et paiement', 'question' => 'Quelles sont vos conditions de paiement ?', 'answer' => "Pour les créations, licences, formations et conseil : 50 % à la commande, le solde à la validation. Les abonnements, l'hébergement et la maintenance sont payés d'avance."],
            ['group' => 'Prestations', 'question' => 'Que comprend la garantie de 3 mois ?', 'answer' => "Après la livraison d'un site, d'une application ou d'un module, nous corrigeons gratuitement pendant 3 mois ce qui ne fonctionne pas comme prévu. Les évolutions, les contenus et l'hébergement ne sont pas compris. Pour une création SOUMATIC, le contrat de maintenance commence à la fin de cette garantie."],
            ['group' => 'Prestations', 'question' => "L'hébergement et le nom de domaine sont-ils compris ?", 'answer' => 'Non. Ils sont proposés à part, dans la catégorie Hébergement & Noms de domaine : serveurs professionnels du basique (1 500 000 GNF HT par an) à la haute performance, noms de domaine .com et .gn, certificats SSL, adresses e-mail professionnelles, migration et sauvegarde externalisée.'],
            ['group' => 'Solutions', 'question' => 'Abonnement ou licence installée : quelle différence ?', 'answer' => "L'abonnement est mensuel, hébergé sur nos serveurs, avec sauvegardes, mises à jour et assistance compris ; il est payable au mois ou à l'année. La licence est achetée une seule fois et installée sur votre serveur ; sa maintenance annuelle (16 % du prix de la licence) couvre les mises à jour et l'assistance. Dans les deux cas, une mise en service est facturée une fois."],
            ['group' => 'Support', 'question' => "Quels sont vos horaires d'assistance ?", 'answer' => 'Du lundi au vendredi, de 8 h 30 à 17 h 30 (hors jours fériés), par WhatsApp, e-mail ou depuis votre espace client, et par téléphone en cas de blocage. Première réponse au plus tard le jour ouvré suivant ; pour une application bloquée sous contrat de maintenance, prise en charge le jour même pour une demande reçue avant 12 h.'],
            ['group' => 'Support', 'question' => 'Intervenez-vous hors de Conakry ?', 'answer' => "Oui. Les ateliers, formations et diagnostics se tiennent dans vos locaux à Conakry ou à distance. Hors Conakry, les frais de déplacement et de séjour s'ajoutent au prix de la prestation."],
            ['group' => 'Technique', 'question' => 'Quelles technologies utilisez-vous ?', 'answer' => "Laravel et Filament pour les applications web et les espaces d'administration, October CMS ou Laravel pour les sites, Android et iOS pour les applications mobiles reliées à une API REST. Les sites livrés comprennent un espace d'administration pour modifier vous-même textes et images."],
            ['group' => 'Technique', 'question' => 'À qui appartient le code source ?', 'answer' => 'Cela dépend de chaque contrat : les droits cédés sont précisés dans le devis ou le contrat signé.'],
        ];

        foreach ($faqs as $position => $faq) {
            $attributes = [
                'group' => $faq['group'],
                'question' => Typo::fr($faq['question']),
                'answer' => Typo::fr($faq['answer']),
                'position' => $position + 1,
                'is_active' => true,
            ];

            // Une question déjà enregistrée sans sa typographie (« … ? » au lieu de « … ? » insécable)
            // est reconnue et mise à jour, jamais dupliquée.
            $existing = Faq::query()->whereIn('question', array_unique([$faq['question'], $attributes['question']]))->first();

            $existing ? $existing->update($attributes) : Faq::query()->create($attributes);
        }
    }
}
