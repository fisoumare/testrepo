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
| Base de données | MySQL 8 (base `soumatic`, jeu de caractères `utf8mb4`, collation `utf8mb4_unicode_ci`) |
| Tests | PHPUnit 12, sur SQLite en mémoire (ou sur MySQL, voir « Tests ») |
| Style de code | Laravel Pint |

Aucune ressource externe n'est chargée (polices auto-hébergées, pas de CDN) : le site fonctionne hors ligne.

## Installation locale avec Laragon (Windows)

### Prérequis

- [Laragon](https://laragon.org) avec Apache (ou Nginx) et MySQL, réglé sur **PHP 8.3** ou plus récent (menu PHP › Version). Composer est fourni avec Laragon.
- Node.js 20 ou plus récent et npm (vérifier avec `node -v` dans le terminal de Laragon).
- Extensions PHP à activer (menu PHP › Extensions, ou `php.ini`) : `pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `fileinfo` ; `openssl`, `curl`, `dom`/`xml`, `tokenizer` et `ctype` sont actives par défaut. `pdo_sqlite` n'est utile que pour lancer les tests (SQLite en mémoire).

### Étapes

1. Placer le projet dans `C:\laragon\www\soumatic` (par exemple `git clone <dépôt> C:\laragon\www\soumatic`).
2. Démarrer Laragon (bouton « Start All ») : Apache ou Nginx, et MySQL.
3. Créer la base `soumatic`, au choix :
   - avec HeidiSQL (bouton « Database » de Laragon) : créer une base `soumatic` avec la collation `utf8mb4_unicode_ci` ;
   - ou dans le terminal de Laragon :
     ```bat
     mysql -u root -e "CREATE DATABASE soumatic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
     ```
4. Dans le terminal de Laragon (bouton « Terminal »), depuis `C:\laragon\www\soumatic` :
   ```bat
   composer install
   copy .env.example .env
   php artisan key:generate
   ```
5. Ouvrir `.env` et **renseigner `ADMIN_PASSWORD` avant le seed** (mot de passe du premier compte du back-office, voir « Compte administrateur »). Les réglages de base de données correspondent à Laragon (`DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=soumatic`, `DB_USERNAME=root`, `DB_PASSWORD` vide) et `APP_URL=http://soumatic.test` à son hôte virtuel.
6. Toujours dans le terminal :
   ```bat
   php artisan migrate --seed
   php artisan storage:link
   npm install
   npm run build
   ```
   `migrate --seed` crée le schéma et le contenu réel (prestations, solutions, FAQ, actualités, compte administrateur) ; `storage:link` expose `storage/app/public` (images envoyées depuis le back-office) ; `npm run build` compile les ressources (Tailwind v4 scanne les vues : à relancer après tout changement de Blade ou de CSS).
7. Laragon crée un hôte virtuel `nom-du-dossier.test` pour chaque dossier de `C:\laragon\www`, avec le dossier `public` comme racine (réglage « Auto create virtual hosts », actif par défaut ; Laragon peut demander les droits administrateur pour écrire dans le fichier `hosts`). Si l'adresse ne répond pas, redémarrer Laragon (« Stop » puis « Start All »). Le site est alors sur **http://soumatic.test** et le back-office sur **http://soumatic.test/admin** (identifiants : `ADMIN_EMAIL` et `ADMIN_PASSWORD` du fichier `.env`).

**Si la base n'existe pas encore**, `php artisan migrate` la détecte (erreur MySQL 1049, base inconnue), affiche « The database 'soumatic' does not exist on the 'mysql' connection. » et demande « Would you like to create it? » (oui par défaut). En répondant oui, il exécute `CREATE DATABASE IF NOT EXISTS soumatic` avec le jeu de caractères et la collation par défaut du serveur MySQL (les tables, elles, sont toujours créées en `utf8mb4_unicode_ci`, réglage de `config/database.php`), puis poursuit la migration. Avec `--force` (comme dans `composer setup`), la base est créée sans question ; avec `--no-interaction` sans `--force`, elle n'est pas créée et la commande échoue. Créer la base soi-même (étape 3) garde la collation `utf8mb4_unicode_ci` comme valeur par défaut de la base.

Le raccourci `composer setup` enchaîne les étapes 4 à 6 (`composer install`, `.env` copié s'il n'existe pas, `APP_KEY` générée seulement si elle est vide, `migrate --seed --force` qui crée aussi la base si elle manque, `storage:link`, `npm install`, `npm run build`). Copier d'abord `.env.example` en `.env` pour y renseigner `ADMIN_PASSWORD` ; sinon, noter le mot de passe généré qu'il affiche.

En développement, `composer dev` lance ensemble le serveur PHP, la file d'attente, les journaux et Vite.

### Dépannage

| Message | Cause probable |
|---|---|
| `SQLSTATE[HY000] [2002]` (connexion refusée) | MySQL n'est pas démarré dans Laragon, ou `DB_HOST` / `DB_PORT` ne correspondent pas. |
| `SQLSTATE[HY000] [1045] Access denied` | `DB_USERNAME` / `DB_PASSWORD` incorrects (Laragon : `root`, mot de passe vide). |
| `could not find driver` | Extension `pdo_mysql` désactivée (ou `pdo_sqlite` pour les tests). |
| La page affiche « Index of / » et la liste des fichiers du projet | L'hôte virtuel de Laragon pointe vers la racine du projet au lieu de `public` (il a été créé avant que le dossier `public` existe). Supprimer `C:\laragon\etc\apache2\sites-enabled\auto.soumatic.test.conf`, créer à côté un fichier `soumatic.test.conf` dont `DocumentRoot` et `<Directory>` visent `C:/laragon/www/soumatic/public`, puis redémarrer Laragon. |
| `Vite manifest not found at: …\public\build/manifest.json` | Le design n'a pas été compilé : lancer `npm install` puis `npm run build`. Vite 8 exige Node.js 20.19 ou plus récent (ou 22.12+) : vérifier avec `node -v`. |
| `php artisan migrate` parle de SQLite ou de `database.sqlite` | Le fichier `.env` vient d'une ancienne version : y mettre `DB_CONNECTION=mysql` et les lignes `DB_*` de `.env.example`, puis `php artisan config:clear`. |
| Images du back-office introuvables | `php artisan storage:link` non lancé, ou `APP_URL` différente de l'adresse utilisée dans le navigateur. |

### Sans Laragon (macOS, Linux, autre serveur)

Même procédure avec un serveur MySQL 8 : `cp .env.example .env`, adapter `DB_*` et `APP_URL` (par exemple `APP_URL=http://localhost:8000`), créer la base comme à l'étape 3, puis `php artisan serve` pour un serveur de développement sur http://localhost:8000.

## Compte administrateur

Le seeder `AdminUserSeeder` crée le premier compte à partir du fichier `.env` :

```dotenv
ADMIN_NAME="Fodé Idrissa SOUMARÉ"
ADMIN_EMAIL=fisoumare@soumatic.com
ADMIN_PASSWORD=
```

`ADMIN_PASSWORD` vide : le seeder génère un mot de passe aléatoire et l'affiche **une seule fois** dans la sortie de `php artisan migrate --seed` (ou `db:seed`). Notez-le, puis changez-le depuis `/admin/users`. Un `ADMIN_PASSWORD` renseigné est utilisé tel quel, sauf la valeur « password » en production, refusée (un mot de passe aléatoire est alors généré). Aucun compte n'est jamais créé avec un mot de passe par défaut connu, et le seeder ne modifie jamais un compte existant. Connexion : `/admin/login`.

Tout compte présent dans la table `users` a accès au back-office ; les comptes se créent uniquement depuis le panel.

## Structure du projet

```
app/
  Http/Controllers/     HomeController, SolutionController, ServiceController, ReferenceController,
                        PostController, PageController (à propos, contact, devis, mentions), SitemapController
  Livewire/             QuoteBuilder (estimation en 3 étapes), ContactForm, Concerns/SubmitsInquiries
                        (pot-de-miel, limitation à 5 envois / 10 min par IP, notifications e-mail)
  Mail/                 InquiryReceived (vers SOUMATIC), InquiryConfirmation (vers le client)
  Models/               Setting, ServiceCategory, Service, Solution, Reference, Post, Faq, Inquiry, User ;
                        Concerns/HasSlug (slug généré depuis le nom ou le titre, borné à 255 caractères)
  Filament/             Resources (prestations, catégories, solutions, références, actualités, FAQ,
                        demandes en lecture seule, utilisateurs), Pages/SiteSettings, Widgets, Support
  Support/              Money (gnf, short, vat, ttc), Typo (typographie française), Elision (« d'EduGes »)
                        et helpers.php (setting(), gnf(), whatsapp_url(), typo())
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
  views/errors/         404, 403 et 419 sur le layout du site ; 500 et 503 autonomes (sans base de données)
lang/fr/                messages de validation et de pagination en français
routes/web.php          routes publiques, sitemap.xml, robots.txt
tests/                  Feature (pages publiques, pages d'erreur, estimation, contact, e-mails, lecture et
                        écritures du back-office, limites de saisie pour MySQL) et Unit (Money, Typo, Elision)
docs/DESIGN.md          dossier de conception : concept « salle des serveurs », tokens, composants,
                        règles de contenu — à lire avant toute modification
```

Routes nommées : `home`, `solutions.index`, `solutions.show`, `services.index`, `services.category`, `services.show`, `references.index`, `posts.index`, `posts.show`, `about`, `contact`, `quote`, `legal`, `sitemap`, `robots`.

### Données

- Les prestations proviennent du catalogue Compiniya (`database/data/catalogue.json`) : numéro `PREST-2026-00xx`, nom, unité, prix entier en GNF HT. Un prix à 0 signifie « sur devis ».
- Les paramètres du site (coordonnées, RCCM, NIF, textes d'accueil, réseaux, facturation) sont des paires clé/valeur de la table `settings`, lues partout via `setting('clé')` et modifiables dans `/admin/parametres`.
- Les demandes reçues (`Inquiry`) portent une référence automatique `DEM-AAAA-0001` (devis) ou `MSG-AAAA-0001` (contact).
- MySQL fonctionne en mode strict : une chaîne plus longue que sa colonne (`VARCHAR(255)` par défaut, 8 pour les codes, 32 pour les numéros de prestation et les acronymes), une date de publication hors de la plage d'un `TIMESTAMP` (1970 – janvier 2038) ou un entier hors limites provoquerait une erreur SQL. Chaque champ de saisie a donc une limite cohérente avec sa colonne (`max:` dans les formulaires Livewire, `maxLength()` / `maxValue()` / `maxDate()` dans Filament ; zones de texte des paramètres limitées à 1 000 caractères) ; `tests/Feature/MySqlLimitsTest.php` le vérifie.
- La collation `utf8mb4_unicode_ci` confond majuscules et minuscules, « é » et « e » : les slugs n'acceptent que des minuscules sans accent, des chiffres et des tirets, et un slug généré depuis un titre est coupé à 255 caractères (`App\Models\Concerns\HasSlug`). Les contrôles d'unicité de Filament (slugs, e-mail des comptes) interrogent la base et suivent donc la même règle que ses index uniques.
- Typographie française : les seeders passent leurs textes par `App\Support\Typo::fr()` (espace insécable avant « : », dans les guillemets « » et entre un nombre et son unité ; espace fine insécable avant « ; ? ! » et entre les groupes de milliers). Dans les vues, `typo($texte)` fait de même pour un texte qui ne vient pas des seeders.

## Contenu à compléter après l'installation

Le site ne contient que des informations vérifiées ; ce qui manque est laissé vide et se renseigne dans le back-office :

- **Paramètres du site** (`/admin/parametres`) : vérifier l'adresse, le téléphone, le RCCM et le NIF déjà renseignés ; saisir les liens Facebook, LinkedIn, YouTube et le lien de carte (vides par défaut) ; renseigner l'**hébergeur** (`legal_hosting`) affiché dans les mentions légales ; vérifier l'e-mail de notification qui reçoit les demandes.
- **Références** (`/admin/references`) : seule la DGSST est publiée. Les autres clients sont créés non publiés avec la mention « À compléter » : rédiger leur description, ajouter un logo, puis publier.
- **Actualités** (`/admin/posts`) : ajouter des images de couverture ; les deux articles seedés n'en ont pas. Leur date de publication est celle de la mise en ligne du site (17 octobre 2026) ; elle se modifie dans le back-office.
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

1. `.env` : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://soumatic.com` (utilisé par le plan du site, `robots.txt`, les e-mails et les balises canoniques ; avec `https://`, toutes les URL générées sont forcées en https), `LOG_LEVEL=warning`, un `ADMIN_PASSWORD` fort (ou vide : généré et affiché par le seeder, voir « Compte administrateur »), les réglages `MAIL_*` et `TRUSTED_PROXIES`.
   - `TRUSTED_PROXIES` : derrière un reverse proxy ou un CDN (Nginx frontal, Plesk, Cloudflare), l'application lit les en-têtes `X-Forwarded-*` pour connaître l'adresse IP réelle du visiteur (limitation des envois de formulaires à 5 par 10 minutes, champ `ip` des demandes) et le schéma `https`. `*` (valeur par défaut) accepte tous les proxys ; indiquez l'adresse IP du proxy, ou plusieurs séparées par des virgules, pour n'accepter qu'elles ; laissez la variable vide si le serveur PHP est exposé directement, sans proxy.
   - Base de données : une base MySQL 8 créée en `utf8mb4` / `utf8mb4_unicode_ci` (`CREATE DATABASE soumatic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`), un utilisateur dédié qui n'a de droits que sur elle, et `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` dans `.env`.
   - Lancez `php artisan migrate --seed --force` **avant** `php artisan optimize` : le seeder du compte administrateur lit `ADMIN_*` dans `.env`, qui n'est plus chargé une fois la configuration mise en cache.
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
4. Sauvegarder régulièrement la base MySQL et `storage/app/public` (images envoyées depuis le back-office). Export de la base avec `mysqldump`, qui produit un fichier SQL :
   ```bash
   mysqldump -u <utilisateur> -p --single-transaction --default-character-set=utf8mb4 soumatic > soumatic-AAAA-MM-JJ.sql
   ```
   Restauration dans une base vide : `mysql -u <utilisateur> -p soumatic < soumatic-AAAA-MM-JJ.sql`. Sous Laragon, ces commandes s'utilisent telles quelles dans son terminal (`-u root`, sans `-p`).
5. File d'attente (facultatif) : `QUEUE_CONNECTION=database` est déjà prévu ; lancez `php artisan queue:work` sous un superviseur (systemd, Supervisor) si vous passez les e-mails en file d'attente. Sinon, `QUEUE_CONNECTION=sync` suffit.
6. Planificateur (facultatif, pour la purge des sessions et du cache) : `* * * * * php /chemin/artisan schedule:run`.

## Commandes utiles

| Commande | Rôle |
|---|---|
| `php artisan test` | Tests PHPUnit (SQLite en mémoire, base migrée et seedée automatiquement) ; `php artisan test --env=testing` pour les lancer sur MySQL (voir « Tests ») |
| `vendor/bin/pint` | Formatage du code PHP (`--test` pour vérifier sans modifier) |
| `npm run build` | Compilation Tailwind / Vite (obligatoire après tout changement de vue ou de CSS) |
| `npm run dev` | Vite en mode développement (rechargement à chaud) |
| `php artisan migrate` | Applique les nouvelles migrations (ne jamais `migrate:fresh` ni `db:wipe` sur la base `soumatic`) |
| `php artisan db:seed` | Rejoue les seeders sans rien dupliquer. Les textes du catalogue (prestations, familles), des solutions, de la FAQ, des références et des actualités sont **réinitialisés** à leur version du dépôt : les modifications faites dans `/admin` sur ces contenus sont perdues. Les comptes et les paramètres du site existants sont préservés. Un seul seeder : `php artisan db:seed --class=ServiceSeeder` |
| `php artisan serve --port=8080` | Serveur de développement |
| `php artisan optimize` / `optimize:clear` | Mise en cache / purge de la configuration, des routes et des vues |
| `mysqldump … soumatic > fichier.sql` | Sauvegarde de la base (voir « Mise en production ») |

## Tests

`php artisan test` lance la suite PHPUnit sur **SQLite en mémoire** (réglages de `phpunit.xml`) : rien à configurer hormis l'extension `pdo_sqlite`, et la base de développement `soumatic` n'est jamais touchée. Chaque test part d'une base migrée et seedée (trait `RefreshDatabase`).

Pour lancer les mêmes tests **sur MySQL** (utile avant une mise en production : mode strict, collation insensible à la casse), utilisez une base séparée, `soumatic_test`. **Les tests vident et recréent toutes les tables de la base visée : ne les lancez jamais sur `soumatic`.**

1. Créer la base (une fois) :
   ```bat
   mysql -u root -e "CREATE DATABASE soumatic_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   ```
2. Avec un fichier `.env.testing` (recommandé) :
   ```bat
   copy .env.testing.example .env.testing
   php artisan key:generate --env=testing
   php artisan test --env=testing
   ```
   `--env=testing` fait lire `.env.testing` (`DB_CONNECTION=mysql`, `DB_DATABASE=soumatic_test`), dont les valeurs priment sur celles de `phpunit.xml`. Une fois `.env.testing` présent, les tests le lisent à la place de `.env` ; sans `--env=testing`, `php artisan test` reste sur SQLite en mémoire.
3. Ou, sans fichier, avec des variables d'environnement le temps d'une commande :
   - invite de commandes Windows (terminal de Laragon) :
     ```bat
     set "DB_CONNECTION=mysql" && set "DB_DATABASE=soumatic_test" && php artisan test
     ```
     Les variables restent définies dans cette fenêtre : fermez-la ensuite (ou videz-les avec `set DB_CONNECTION=` et `set DB_DATABASE=`), sinon les commandes `artisan` suivantes y viseraient `soumatic_test`.
   - PowerShell : `$env:DB_CONNECTION="mysql"; $env:DB_DATABASE="soumatic_test"; php artisan test`, puis `Remove-Item Env:DB_CONNECTION, Env:DB_DATABASE` ;
   - macOS / Linux : `DB_CONNECTION=mysql DB_DATABASE=soumatic_test php artisan test`.

Les autres réglages de connexion (`DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`) viennent de `.env` (ou de `.env.testing`). Si la base de test n'existe pas, la première migration des tests la crée (`CREATE DATABASE IF NOT EXISTS`, avec la collation par défaut du serveur). Avant de livrer, la suite doit passer sur les deux moteurs, puis `vendor/bin/pint` pour le style.

## SEO et fichiers techniques

- `/sitemap.xml` est généré par `SitemapController` à partir des solutions, familles, prestations actives et actualités publiées.
- `/robots.txt` est une route (dans `routes/web.php`) : il autorise tout sauf `/admin` et `/livewire` et pointe vers le plan du site avec l'URL de `APP_URL`. Ne créez pas de fichier `public/robots.txt`, il serait servi à la place de la route.
- `public/brand/og.png` (1200 × 630) est l'image de partage déclarée dans le layout (`og:image`). Elle a été rendue depuis une page HTML aux couleurs du site ; régénérez-la si le logo change.
- Chaque page déclare un `title`, une `description`, une URL canonique, les balises Open Graph et un bloc JSON-LD (`Organization`, `BreadcrumbList`, `NewsArticle`).
- Les pages d'erreur (`resources/views/errors/`) reprennent le concept du site : une « unité » hors service, repérée par son code (`ERR-404`) et une LED ambre. Les pages 404, 403 et 419 utilisent le layout complet ; les pages 500 et 503 (maintenance, `php artisan down`) sont autonomes et ne lisent ni la base de données ni les paramètres.

## Conventions

- Lire `docs/DESIGN.md` avant toute modification (concept, tokens, composants, règles de contenu) et `AGENTS.md` pour les consignes de contribution.
- Orthographe des produits : **EduGes**, **MediGes SST**, **MediGes**, **Compiniya**, **SOUMATIC**.
- Rien d'inventé : aucun chiffre, client, témoignage ou date qui ne figure pas dans les seeders ou les paramètres.
- Montants entiers HT en GNF via `gnf()`, TVA 18 %, devis valable 30 jours.
- Tout changement de schéma passe par une nouvelle migration, jamais par `migrate:fresh` sur la base partagée.
