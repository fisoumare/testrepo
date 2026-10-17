# SOUMATIC — dossier de conception du site

Référence pour toute personne (ou agent) qui touche au site. Lire en entier avant de coder.

## 1. Stack et commandes

- Laravel 13 · Filament v5.10 (panel `/admin`) · Livewire 4 (Alpine.js inclus, chargé sur toutes les pages via `resources/js/app.js` + `@livewireScriptConfig`) · Tailwind CSS v4 (`@theme`, `@utility`) · Vite 8 · SQLite (`database/database.sqlite`).
- Police : Inter Variable, auto-hébergée (`@fontsource-variable/inter`). Monospace : pile système (`font-mono`).
- Commandes : `npm run build` (obligatoire après tout changement de Blade/CSS : Tailwind v4 scanne les vues) · `php artisan migrate --seed` · `php artisan test` · serveur de développement : `php artisan serve --port=8080`.
- Aucune ressource externe (CDN, Google Fonts, cartes embarquées) : le site doit fonctionner hors ligne.

## 2. Concept : « la salle des serveurs »

Le logo SOUMATIC est un S formé de trois serveurs empilés aux LED vertes. Le site prolonge cette idée : chaque offre est une **unité** d'un rack, repérée par un **code** monospace (U01…U08 pour les familles de prestations, S01…S04 pour les solutions, PREST-2026-00xx pour chaque prestation), et un état signalé par une **LED**.

Signatures visuelles (à réutiliser, ne pas en inventer d'autres) :

1. **Barre d'état** en haut de page (fond `ink`, LED, « Systèmes opérationnels », heure de Conakry, support, téléphone).
2. **Unités** `.unit` : cartes à coins marqués (crochets aux quatre angles), qui s'allument au survol (`group-hover:led-on`).
3. **Étiquettes** `.label` : monospace 11 px, capitales espacées — pour les codes, unités, métadonnées.
4. **LED** `.led` (+ `.led-on` pulsée, `.led-off` grise, couleur par solution via `<x-ui.led :color="…" />`).
5. **Fonds techniques** : `.grid-dots` (points) sur blanc, `.rack-lines` (traits horizontaux tous les 44 px, un « U ») sur `ink`, `.vent` (grille d'aération), `.ticker` (bandeau défilant des tarifs).
6. **Fiche technique** `.spec` : liste `dl` clé monospace / valeur — pour les données administratives et les caractéristiques.
7. Apparition au défilement : classe `reveal` (+ `reveal-delay-1/2/3`).

Rythme des pages : fond blanc → section sombre `bg-ink text-white rack-lines` pour la matière « serveur » → fond `bg-paper` pour les blocs secondaires → bande d'appel `<x-ui.cta-band />` avant le pied de page. Grands titres serrés (`.h-section`, `.h-display`), beaucoup d'air (`mt-24` entre sections), chiffres tabulaires (`.tabular`).

## 3. Couleurs (tokens Tailwind, `resources/css/app.css`)

| Usage | Classe | Hex |
|---|---|---|
| Bleu marine (texte, fonds) | `navy-900` (50→950) | #0F2D52 |
| Vert émeraude (CTA, LED, accents) | `emerald-500` / `emerald-400` sur fond sombre / `emerald-700` pour du texte sur blanc | #0E9F6E / #2BC48A / #0A7350 |
| Gris ardoise (texte secondaire) | `ardoise` / `ardoise-light` sur fond sombre | #52637A / #A3B3C7 |
| Fond sombre | `ink`, `ink-soft`, `ink-line` | #0F172A, #16213A, #243049 |
| Fond clair secondaire | `paper` | #F6F8FB |

Contraste : jamais `emerald-500` pour du texte courant sur blanc (utiliser `emerald-700`). Sur `ink`, texte courant `ardoise-light`, titres blancs, accents `emerald-400`.

## 4. Composants Blade disponibles

| Composant | Usage |
|---|---|
| `<x-layouts.app :title="…" :description="…">` | Layout de page (header, footer, SEO, JSON-LD). Slot `head` pour balises supplémentaires. |
| `<x-ui.page-header code="U03" kicker="Prestations" title="…" [dark]>texte d'intro</x-ui.page-header>` | En-tête de page intérieure ; slot nommé `aside` pour un bloc à droite. |
| `<x-ui.section-heading code="…" kicker="…" title="…" [dark] [align="center"]>intro</x-ui.section-heading>` | Titre de section. |
| `<x-ui.unit [href] [dark] [padding="p-6"] class="…">` | Carte unité (devient un `<a>` si `href`). |
| `<x-ui.service-card :service="$service" [show-category]/>` | Carte prestation (numéro, nom, résumé, prix, unité). |
| `<x-ui.price :service="$service" [dark] size="sm|base|lg"/>` | Prix formaté « 13 500 000 GNF HT / mois » ou « Sur devis ». |
| `<x-ui.led [:color] [:on]/>` | LED. |
| `<x-ui.cta-band [title] [text]/>` | Bande d'appel (estimation + WhatsApp). |

Classes utilitaires : `.wrap` (conteneur), `.btn-primary`, `.btn-navy`, `.btn-ghost`, `.btn-ghost-dark`, `.btn-link`, `.lead`, `.prose-site` (contenu riche), `.spec` / `.spec-dark`, `.label`, `.tabular`.

Helpers PHP : `setting('clé', défaut)`, `gnf(montant, avecDevise = true)`, `App\Support\Money::short()`, `Money::vat()`, `Money::ttc()`, `whatsapp_url('texte')`.

## 5. Données (modèles et seeders)

`Setting` (clé/valeur, cache), `ServiceCategory` (8, codes U01–U08), `Service` (120 prestations issues du catalogue Compiniya : `number`, `name`, `unit`, `price` entier GNF HT, `summary`, `description`, `is_featured`, `solution_id`), `Solution` (EduGes, MediGes SST, MediGes, Compiniya : `code`, `tagline`, `audience`, `summary`, `description` HTML, `features[]`, `included[]`, `accent`, `website_url`), `Reference` (DGSST publiée ; autres non publiées « à compléter »), `Post` (actualités, `body` HTML), `Faq`, `Inquiry` (`type` contact|quote, `items[]`, `total_ht`, `status`, `reference` auto DEM-2026-0001 / MSG-2026-0001).

Routes nommées : `home`, `solutions.index`, `solutions.show`, `services.index`, `services.category`, `services.show`, `references.index`, `posts.index`, `posts.show`, `about`, `contact`, `quote`, `legal`, `sitemap`.

## 6. Règles de contenu

- Français soigné, vouvoiement, typographie française (espaces insécables avant « : ; ? ! », guillemets « »). Écrire **EduGes** (jamais EduGest), **MediGes SST**, **MediGes**, **Compiniya**, **SOUMATIC**.
- **Rien d'inventé** : pas de chiffres, clients, témoignages, dates, équipes ou récompenses qui ne figurent pas dans les seeders ou les paramètres. Ce qui manque est laissé vide ou marqué « à compléter » dans le back-office. Les données de l'entreprise viennent de `setting()`.
- Montants : entiers, HT, en GNF, via `gnf()` ; TVA 18 % ; devis valable 30 jours ; paiement « 50 % à la commande, solde à la validation » ; garantie 3 mois ; assistance lundi–vendredi 8 h 30 – 17 h 30.
- Ne pas affirmer à qui appartient le code source (cela dépend de chaque contrat).
- Accessibilité : contrastes, focus visibles, `aria-*` sur les menus et accordéons, images avec `alt`, titres hiérarchisés (un seul `h1` par page). Responsive dès 360 px, sans défilement horizontal (attention : pas de `truncate`/`whitespace-nowrap` dans une colonne de grille sans `min-w-0`).
- SEO : `title` et `description` par page, canonical, Open Graph (layout), `sitemap.xml`, `robots.txt`.
