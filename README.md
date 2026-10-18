# Site web de SOUMATIC

Site vitrine et catalogue de **SOUMATIC**, entreprise de Conakry (Guinée) qui conçoit, héberge et maintient des sites, des applications web et mobiles et des logiciels de gestion (EduGes, MediGes SST, MediGes, Compiniya).

Le site comprend :

- les pages publiques : accueil, solutions, catalogue des 120 prestations à prix fixe (8 familles), références, actualités, à propos, mentions légales ;
- un **configurateur d'estimation** (`/devis`) : sélection de prestations, totaux HT / TVA 18 % / TTC calculés en direct, envoi d'une demande de devis ;
- un **formulaire de contact** (`/contact`) ;
- un **back-office** Filament (`/admin`) pour gérer le contenu, les demandes reçues et les paramètres du site ;
- un plan du site (`/sitemap.xml`), un `robots.txt` généré et une image Open Graph.

Tout le contenu est en français, en francs guinéens (GNF), hors taxes, TVA 18 % en sus.

## Stack

| Composant | Version |
|---|---|
| PHP | 8.3 |
| Laravel | 13 |
| Filament (panel d'administration) | v5 |
| Livewire (formulaires interactifs, inclut Alpine.js) | 4 |
| Tailwind CSS | v4 (`@theme`, `@utility`) |
| Vite | 8 |
| Base de données | SQLite (fichier `database/database.sqlite`) |
| Tests | PHPUnit 12 |
| Style de code | Laravel Pint |

Aucune ressource externe n'est chargée (polices auto-hébergées, pas de CDN) : le site fonctionne hors ligne.

## Prérequis

- PHP 8.3 avec les extensions `pdo_sqlite`, `sqlite3`, `mbstring`, `intl`, `gd` (ou `imagick`), `zip`, `xml`, `curl` ;
- [Composer](https://getcomposer.org) 2 ;
- Node.js 20 ou plus récent et npm.

## Installation

```bash
git clone <dépôt> soumatic && cd soumatic

composer install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed       # schéma + contenu réel (prestations, solutions, FAQ, actualités, compte admin)
php artisan storage:link         # expose storage/app/public (images envoyées depuis le back-office)

npm install
npm run build                    # Tailwind v4 scanne les vues : à relancer après tout changement de Blade/CSS

php artisan serve                # http://localhost:8000
```

Le raccourci `composer setup` enchaîne ces étapes (il ne régénère pas `APP_KEY` si la clé existe déjà).

En développement, `composer dev` lance ensemble le serveur PHP, la file d'attente, les journaux et Vite.

## Compte administrateur

Le seeder `AdminUserSeeder` crée le premier compte à partir du fichier `.env` :

```dotenv
ADMIN_NAME="Fodé Idrissa SOUMARÉ"
ADMIN_EMAIL=fisoumare@soumatic.com
ADMIN_PASSWORD=password
```

**Changez `ADMIN_PASSWORD` avant de lancer `php artisan migrate --seed`**, ou changez le mot de passe ensuite depuis `/admin/users`. Le seeder ne modifie jamais un compte existant. Connexion : `/admin/login`.

Tout compte présent dans la table `users` a accès au back-office ; les comptes se créent uniquement depuis le panel.

## Structure du projet

```
app/
  Http/Controllers/     HomeController, SolutionController, ServiceController, ReferenceController,
                        PostController, PageController (à propos, contact, devis, mentions), SitemapController
  Livewire/             QuoteBuilder (estimation en 3 étapes), ContactForm, Concerns/SubmitsInquiries
                        (pot-de-miel, limitation à 5 envois / 10 min par IP, notifications e-mail)
  Mail/                 InquiryReceived (vers SOUMATIC), InquiryConfirmation (vers le client)
  Models/               Setting, ServiceCategory, Service, Solution, Reference, Post, Faq, Inquiry, User
  Filament/             Resources (prestations, catégories, solutions, références, actualités, FAQ,
                        demandes en lecture seule, utilisateurs), Pages/SiteSettings, Widgets, Support
  Support/              Money (gnf, short, vat, ttc) et helpers.php (setting(), gnf(), whatsapp_url())
database/
  migrations/           schéma complet dans 2026_10_17_000001_create_site_tables.php
  seeders/              contenu réel : SettingSeeder, ServiceCategorySeeder, SolutionSeeder,
                        ServiceSeeder (depuis database/data/catalogue.json), ReferenceSeeder, FaqSeeder,
                        PostSeeder, AdminUserSeeder
resources/
  css/app.css           tokens Tailwind v4 (couleurs, utilitaires .unit, .led, .label, .spec, .rack-lines…)
  views/components/     layouts/app (en-tête, pied de page, SEO, JSON-LD), site/*, ui/* (unit, price, led,
                        page-header, section-heading, cta-band, breadcrumb, offer-table, pagination…)
  views/pages/          home, solutions/, services/, references, posts/, about, contact, quote, legal
  views/livewire/       quote-builder, contact-form et partiels
  views/mail/           gabarits Markdown des deux e-mails
lang/fr/                messages de validation et de pagination en français
routes/web.php          routes publiques, sitemap.xml, robots.txt
tests/                  Feature (pages publiques, estimation, contact, back-office) et Unit (Money)
docs/DESIGN.md          dossier de conception : concept « salle des serveurs », tokens, composants,
                        règles de contenu — à lire avant toute modification
```

Routes nommées : `home`, `solutions.index`, `solutions.show`, `services.index`, `services.category`, `services.show`, `references.index`, `posts.index`, `posts.show`, `about`, `contact`, `quote`, `legal`, `sitemap`, `robots`.

### Données

- Les prestations proviennent du catalogue Compiniya (`database/data/catalogue.json`) : numéro `PREST-2026-00xx`, nom, unité, prix entier en GNF HT. Un prix à 0 signifie « sur devis ».
- Les paramètres du site (coordonnées, RCCM, NIF, textes d'accueil, réseaux, facturation) sont des paires clé/valeur de la table `settings`, lues partout via `setting('clé')` et modifiables dans `/admin/parametres`.
- Les demandes reçues (`Inquiry`) portent une référence automatique `DEM-AAAA-0001` (devis) ou `MSG-AAAA-0001` (contact).

## Contenu à compléter après l'installation

Le site ne contient que des informations vérifiées ; ce qui manque est laissé vide et se renseigne dans le back-office :

- **Paramètres du site** (`/admin/parametres`) : vérifier l'adresse, le téléphone, le RCCM et le NIF déjà renseignés ; saisir les liens Facebook, LinkedIn, YouTube et le lien de carte (vides par défaut) ; renseigner l'**hébergeur** (`legal_hosting`) affiché dans les mentions légales ; vérifier l'e-mail de notification qui reçoit les demandes.
- **Références** (`/admin/references`) : seule la DGSST est publiée. Les autres clients sont créés non publiés avec la mention « À compléter » : rédiger leur description, ajouter un logo, puis publier.
- **Actualités** (`/admin/posts`) : ajouter des images de couverture ; les deux articles seedés n'en ont pas.
- **Logo définitif** : remplacer `public/brand/logo.svg`, `logo-blanc.svg`, `icone.svg`, `favicon.svg` et l'image de partage `public/brand/og.png` (1200 × 630) si l'identité visuelle évolue.
- **Prestations** : les prix peuvent être ajustés depuis `/admin/services` ; une prestation désactivée disparaît du site, du configurateur et du plan du site.

## Configuration e-mail

Deux e-mails partent à chaque demande (devis ou contact) : une notification à l'adresse « E-mail de notification » des paramètres du site et un accusé de réception au client. En local, `MAIL_MAILER=log` les écrit dans `storage/logs/laravel.log`.

En production, renseignez un transport SMTP dans `.env` :

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.exemple.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS="noreply@soumatic.com"
MAIL_FROM_NAME="SOUMATIC"
```

Les e-mails sont envoyés de façon synchrone ; une panne du courrier est journalisée mais n'empêche jamais l'enregistrement de la demande. Pour les envoyer en arrière-plan, utilisez une file d'attente (voir ci-dessous) et faites implémenter `ShouldQueue` aux deux Mailables.

## Mise en production

1. `.env` : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://soumatic.com` (utilisé par le plan du site, `robots.txt`, les e-mails et les balises canoniques), `LOG_LEVEL=warning`, un `ADMIN_PASSWORD` fort, les réglages `MAIL_*`.
2. Installer sans dépendances de développement et compiler les ressources :
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan migrate --force
   php artisan storage:link
   php artisan optimize          # met en cache la configuration, les routes, les vues et les événements
   php artisan filament:optimize # met en cache les composants et icônes de Filament
   ```
   Après chaque déploiement, relancer `php artisan optimize` (ou `optimize:clear` pour repartir à zéro).
3. Servir le dossier `public/` uniquement, en HTTPS (le site suppose une connexion sécurisée pour les cookies de session du back-office). Donner les droits en écriture au serveur web sur `storage/` et `bootstrap/cache/`.
4. Sauvegarder régulièrement `database/database.sqlite` et `storage/app/public` (images envoyées depuis le back-office).
5. File d'attente (facultatif) : `QUEUE_CONNECTION=database` est déjà prévu ; lancez `php artisan queue:work` sous un superviseur (systemd, Supervisor) si vous passez les e-mails en file d'attente. Sinon, `QUEUE_CONNECTION=sync` suffit.
6. Planificateur (facultatif, pour la purge des sessions et du cache) : `* * * * * php /chemin/artisan schedule:run`.

## Commandes utiles

| Commande | Rôle |
|---|---|
| `php artisan test` | Tests PHPUnit (SQLite en mémoire, base migrée et seedée automatiquement) |
| `vendor/bin/pint` | Formatage du code PHP (`--test` pour vérifier sans modifier) |
| `npm run build` | Compilation Tailwind / Vite (obligatoire après tout changement de vue ou de CSS) |
| `npm run dev` | Vite en mode développement (rechargement à chaud) |
| `php artisan migrate` | Applique les nouvelles migrations (ne jamais `migrate:fresh` sur une base contenant du contenu) |
| `php artisan db:seed` | Rejoue les seeders (idempotents : ils ne dupliquent rien et n'écrasent pas les comptes) |
| `php artisan serve --port=8080` | Serveur de développement |
| `php artisan optimize` / `optimize:clear` | Mise en cache / purge de la configuration, des routes et des vues |

## SEO et fichiers techniques

- `/sitemap.xml` est généré par `SitemapController` à partir des solutions, familles, prestations actives et actualités publiées.
- `/robots.txt` est une route (dans `routes/web.php`) : il autorise tout sauf `/admin` et `/livewire` et pointe vers le plan du site avec l'URL de `APP_URL`. Ne créez pas de fichier `public/robots.txt`, il serait servi à la place de la route.
- `public/brand/og.png` (1200 × 630) est l'image de partage déclarée dans le layout (`og:image`). Elle a été rendue depuis une page HTML aux couleurs du site ; régénérez-la si le logo change.
- Chaque page déclare un `title`, une `description`, une URL canonique, les balises Open Graph et un bloc JSON-LD (`Organization`, `BreadcrumbList`, `NewsArticle`).

## Conventions

- Lire `docs/DESIGN.md` avant toute modification (concept, tokens, composants, règles de contenu) et `AGENTS.md` pour les consignes de contribution.
- Orthographe des produits : **EduGes**, **MediGes SST**, **MediGes**, **Compiniya**, **SOUMATIC**.
- Rien d'inventé : aucun chiffre, client, témoignage ou date qui ne figure pas dans les seeders ou les paramètres.
- Montants entiers HT en GNF via `gnf()`, TVA 18 %, devis valable 30 jours.
- Tout changement de schéma passe par une nouvelle migration, jamais par `migrate:fresh` sur la base partagée.
