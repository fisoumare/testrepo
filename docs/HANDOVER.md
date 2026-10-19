# Passation : site web SOUMATIC

Document de reprise pour une nouvelle session Claude Code ouverte **sur le poste de l'utilisateur** (Laragon, Windows), dans `C:\laragon\www\soumatic`. Il fait suite à une session cloud (9 au 19 octobre 2026) qui a conçu et livré le site.

Ce dépôt GitHub est **public** : ce fichier ne contient rien de confidentiel. Les points privés (Compiniya, clients, conditions de paiement, étude de prix) sont dans `HANDOVER-PRIVE.md` et `benchmark-logiciels-gestion.md`, remis directement à l'utilisateur et exclus de Git par `.gitignore`. **Rien de leur contenu ne doit être recopié dans un fichier versionné, un message de commit, une pull request ou une page publiée.**

## 1. Ordre de lecture

1. Ce fichier.
2. `docs/DESIGN.md`, en entier, avant toute modification du site.
3. `README.md`, § Installation, § Dépannage et § Tests.
4. `HANDOVER-PRIVE.md`, s'il est fourni.

`CLAUDE.md` est chargé automatiquement par Claude Code ; `AGENTS.md` en est une copie identique destinée à d'autres outils.

## 2. L'utilisateur et la façon de travailler

- **Fodé Idrissa SOUMARÉ**, gérant de SOUMATIC (Conakry, Guinée), développeur backend Laravel, qui utilise Filament pour ses back-offices. Pour **ce** site : Tailwind CSS v4 et les composants de `docs/DESIGN.md` uniquement, sans Bootstrap, W3.CSS, October CMS ni FBLOG.
- Répondre **en français**, de façon structurée, orientée bonnes pratiques, maintenabilité et performance.
- Quand il écrit « Confirme que tu comprends avant de procéder », **reformuler et attendre son accord** avant d'agir.
- Avant toute écriture dans un outil métier (Compiniya), **attendre « applique »**.
- **Ne jamais inventer** un fait sur l'entreprise, ses clients ou ses contrats. Ce qui manque reste vide ou « à compléter ».
- **Git** : ni commit, ni push, ni fusion, ni suppression de branche sans demande explicite. Proposer la commande et le message, puis attendre son accord.
- **Secrets** : l'agent n'a pas besoin des identifiants du back-office. Il ne demande, n'affiche ni ne recopie jamais un mot de passe ou le contenu de `.env`.

## 3. SOUMATIC (faits publics, déjà présents sur le site)

- Raison sociale : SOUMATIC. Signature : « Numérique de confiance ».
- Gérant : Fodé Idrissa SOUMARÉ. RCCM : GN.TCC.2026.A.06358. NIF : 484274303.
- Siège : Yattaya, Commune de Sonfonia, Conakry, Guinée. Tél. et WhatsApp : +224 628 18 79 30. E-mail : fisoumare@soumatic.com.
- Activité : conception de sites, applications web et mobiles (Laravel, Filament, October CMS, Android, iOS), hébergement, maintenance, API, analyse, conseil et audit, formation.
- Logiciels développés par SOUMATIC : **EduGes** (gestion scolaire), **MediGes SST** (services de santé au travail), **MediGes** (gestion hospitalière), **Compiniya** (gestion commerciale en ligne, compiniya.com). Orthographe exacte : jamais « EduGest ».
- Référence publiée : **DGSST** (Direction Générale de la Sécurité et de la Santé au Travail), plateforme nationale de santé au travail snst-guinee.com. L'utilisateur a explicitement autorisé à la citer.

## 4. Le projet

| Élément | Valeur |
|---|---|
| Dépôt | `fisoumare/testrepo` (public), branche **`site-soumatic`** ; `master` ne contient pas le site |
| Stack | Laravel 13.37 · Filament 5.10 · Livewire 4.4 · Tailwind CSS 4 · Vite 8 · PHP 8.3 ou plus |
| Base | MySQL 8, base `soumatic`, `root` sans mot de passe (Laragon), tables en `utf8mb4_unicode_ci` |
| Adresse locale | http://soumatic.test et http://soumatic.test/admin |
| Node.js | 20.19+ (branche 20) ou 22.12+ ; recommandé : Node 22 LTS (exigence de Vite 8) |

### Fonctionnalités livrées

- **Pages publiques** : accueil, solutions (liste et fiche de chacun des 4 logiciels), prestations (catalogue de 120 prestations en 8 familles, recherche instantanée, fiche par prestation), références, actualités, à propos, contact, mentions légales, pages d'erreur 403, 404, 419, 500 et 503 aux couleurs du site.
- **Configurateur d'estimation** `/devis` (Livewire `App\Livewire\QuoteBuilder`) : sélection de prestations, quantités, totaux HT, TVA 18 % et TTC, envoi d'une demande numérotée `DEM-AAAA-0001`.
- **Formulaire de contact** `/contact` (Livewire `App\Livewire\ContactForm`) : demandes numérotées `MSG-AAAA-0001`, préremplissage par `?sujet=demo&solution=…`.
- **E-mails** : notification à `notification_email` et accusé de réception au visiteur (`app/Mail`). Anti-spam : pot de miel et limite de 5 envois par 10 minutes et par adresse IP.
- **Back-office Filament** `/admin` : prestations, familles, solutions, références, actualités, FAQ, demandes reçues (lecture, statut, réponse par e-mail ou WhatsApp), utilisateurs, profil, page « Paramètres du site » (`/admin/parametres`).
- **SEO** : balises par page, Open Graph, JSON-LD (Organization, BreadcrumbList, NewsArticle), `sitemap.xml` et `robots.txt` dynamiques.
- **Typographie française** automatique (`App\Support\Typo`, helper `typo()`), élision (`App\Support\Elision`), montants (`gnf()`, `App\Support\Money`).

### Données et seeders

- `database/data/catalogue.json` : export du catalogue Compiniya du 17 octobre 2026 (120 prestations, prix HT en GNF), typographie retouchée le 19 (« e-mail » dans 10 articles, prix identiques). En cas de nouvel export, reprendre ces retouches.
- **Les seeders écrasent les modifications faites dans le back-office.** `ServiceSeeder` remet textes, prix, activation et mise en avant des prestations ; `ReferenceSeeder` remet textes et publication des références ; `PostSeeder` remet textes, dates et publication des actualités ; `SolutionSeeder`, `ServiceCategorySeeder` et `FaqSeeder` réactivent et réécrivent leurs contenus. Seuls les comptes et les paramètres du site sont conservés. Donc : **aucun seeder, `migrate --seed` ni `composer setup` sur `soumatic` sans l'accord de l'utilisateur**, et toujours après une sauvegarde (`mysqldump -u root soumatic > soumatic-AAAA-MM-JJ.sql`).
- Aucune synchronisation avec Compiniya : un changement de prix se fait dans le back-office (`/admin/services`) et, pour qu'il survive à un futur seed, aussi dans `catalogue.json`.
- Compte administrateur : il existe déjà en local. En cas de mot de passe oublié : lien « Mot de passe oublié » sur `/admin/login` ; en local (`MAIL_MAILER=log`), le lien de réinitialisation est écrit dans `storage/logs/laravel.log`.

### Qualité vérifiée en fin de session cloud

- 225 tests (PHPUnit), verts sur SQLite en mémoire et sur MySQL 8 (base `soumatic_test`).
- `tests/TestCase.php` **refuse** de lancer les tests sur une base MySQL dont le nom ne se termine pas par `_test`, pour protéger `soumatic`.
- `vendor/bin/pint --test` conforme. Aucune violation d'accessibilité « critical » ou « serious » (axe-core) sur les pages principales, en 1440 et 390 px. Aucun débordement horizontal sur mobile.
- Quatre revues indépendantes (design, code, contenu et SEO, accessibilité) puis corrections.

## 5. Décisions déjà prises (ne pas revenir dessus sans l'utilisateur)

- **Concept graphique** « salle des serveurs », tiré du logo provisoire (un S formé de trois serveurs aux LED vertes) : unités à coins marqués, LED, codes monospace (U01–U08, S01–S04), barre d'état. Détails dans `docs/DESIGN.md`.
- **Couleurs** : bleu marine `#0F2D52`, vert émeraude `#0E9F6E` (couleur du logo), ardoise `#52637A`, fond sombre `#0F172A`. Boutons principaux verts avec un **texte sombre** (le blanc sur ce vert n'atteint pas le contraste minimal).
- **Conditions affichées sur le site** : prix fixes hors taxes en GNF, TVA 18 % en sus, devis valable 30 jours ; créations, licences, formations et conseil : 50 % à la commande, solde à la validation ; abonnements, hébergement et maintenance payés d'avance ; garantie de 3 mois ; première réponse au plus tard le jour ouvré suivant. **Aucune pénalité de retard** : l'utilisateur l'a refusée.
- **Propriété du code source** : dépend de chaque contrat. Ne jamais affirmer autre chose.
- **Dates des actualités** fixes (17 octobre 2026), jamais relatives.
- **MySQL** par défaut (choix de l'utilisateur), tests aussi exécutables sur SQLite.

## 6. Reste à faire

### Avec l'utilisateur, dans le back-office (`/admin/parametres`)

- Renseigner l'hébergeur du site (`legal_hosting`) : tant qu'il est vide, la section « Hébergement » des mentions légales est masquée.
- Renseigner les liens Facebook, LinkedIn, YouTube et le lien de carte (`map_url`) s'ils existent.

### Contenu

- Quatre références sont créées mais **non publiées**, faute de description fournie par l'utilisateur. Ne rien rédiger sans ses informations.
- Le logo est **provisoire**. Sources dans `docs/brand/logo-provisoire/` (`soumatic-logo-master.svg` à texte éditable) ; fichiers utilisés par le site dans `public/brand/` et `public/favicon*` ; l'image de partage `public/brand/og.png` est à régénérer si le logo change.
- Les prix du catalogue sont une **première proposition** que l'utilisateur veut revoir (voir `HANDOVER-PRIVE.md`).

### Technique

- **Mise en production** non faite : choix de l'hébergement, `APP_ENV=production`, `APP_DEBUG=false`, configuration `MAIL_*` réelle, HTTPS, `php artisan optimize` **sur le serveur seulement**. Procédure dans le README § Mise en production.
- **GitHub** : l'utilisateur peut supprimer l'ancienne branche `claude/compiniya-identity-hnhn0i` depuis https://github.com/fisoumare/testrepo/branches. Elle est entièrement contenue dans `site-soumatic`, qui a en plus le commit de passation ; vérifier d'abord que le dossier local est bien sur `site-soumatic` (`git branch --show-current`). Fusion dans `master` : seulement à sa demande. Envisager un dépôt privé dédié (le dépôt actuel est public et s'appelle « testrepo »).

## 7. Commandes de référence

Les commandes ci-dessous sont écrites pour le **terminal de Laragon** (invite de commandes Windows). Claude Code sous Windows exécute les siennes dans **Git Bash** : il écrit `vendor/bin/pint`, `cp` et `/c/laragon/www/soumatic`. Pour que `php`, `composer`, `mysql` et `node` y soient trouvés, lancer Claude Code depuis le terminal de Laragon, ou ajouter Laragon au PATH (menu Laragon › Tools › Path). Premier contrôle : `php -v`, `composer -V`, `mysql --version`, `node -v`.

### Relier le dossier à GitHub (une seule fois)

Si `C:\laragon\www\soumatic` vient d'une archive zip (pas de dossier `.git`) :

```
cd C:\laragon\www\soumatic
git init
git remote add origin https://github.com/fisoumare/testrepo.git
git fetch origin site-soumatic
git checkout -f -B site-soumatic --track origin/site-soumatic
```

`.env`, `vendor` et `node_modules`, ignorés par Git, sont conservés. Si le dossier est déjà un clone mais sur une autre branche : `git fetch origin` puis `git checkout -B site-soumatic --track origin/site-soumatic`. Si `docs/HANDOVER.md` est absent du dossier, la branche extraite n'est pas `site-soumatic`.

### Mettre à jour le code et l'application

```
cd C:\laragon\www\soumatic
git status
git pull --ff-only
composer install
php artisan migrate
npm install
npm run build
php artisan optimize:clear
```

`git status` doit être propre ; sinon, s'arrêter et demander à l'utilisateur.

### Contrôles avant de livrer une modification

```
php artisan test
vendor\bin\pint --test app database routes tests config
```

Tests sur MySQL, **seulement si `.env.testing` existe** (fichier ignoré par Git, donc absent d'un clone neuf). Mise en place, une seule fois :

```
mysql -u root -e "CREATE DATABASE soumatic_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
copy .env.testing.example .env.testing
php artisan key:generate --env=testing
```

Puis : `php artisan test --env=testing`. La protection de `tests/TestCase.php` refuse toute autre base que `…_test`.

Pièges déjà rencontrés : « Index of / » (hôte virtuel Laragon pointé sur la racine au lieu de `public`), « Vite manifest not found » (`npm run build` non lancé ou Node trop ancien), `migrate` qui parle de SQLite (ancien `.env`). Solutions dans le README § Dépannage.

## 8. À ne jamais faire sur le poste local

- `migrate:fresh`, `migrate:refresh`, `migrate:reset` ou `db:wipe` sur `soumatic`.
- Un seeder, `migrate --seed` ou `composer setup` sans l'accord de l'utilisateur et sans sauvegarde.
- `php artisan optimize` ou `config:cache` en local. En cas de doute : `php artisan optimize:clear`.
- Mettre `DB_DATABASE=soumatic` dans `.env.testing`.
- Écrire dans Compiniya sans « applique ».
- Commit, push, fusion ou suppression de branche sans demande explicite.
- Recopier le contenu de `HANDOVER-PRIVE.md`, de `benchmark-logiciels-gestion.md` ou de `.env` dans un fichier versionné, un commit ou une pull request.
- Ajouter un paquet Composer ou npm sans l'accord de l'utilisateur.

## 9. Premier message suggéré pour la session locale

> Tu reprends le projet du site web de mon entreprise SOUMATIC, dans ce dossier. Lis `docs/HANDOVER.md` (il renvoie aux autres documents), puis `HANDOVER-PRIVE.md` que je te joins. Fais uniquement ces vérifications en lecture : `git status`, `git branch --show-current` (attendu : site-soumatic), `php -v`, `node -v`, `php artisan migrate:status`, `php artisan test` (SQLite en mémoire, sans risque pour la base `soumatic`), présence de `.env.testing`, puis réponse de http://soumatic.test et http://soumatic.test/admin/login. Ne lance aucun seeder, ni `--env=testing`, ni commande qui écrit en base ou dans Git. Résume-moi l'état du projet et ce qui reste à faire, puis attends mes instructions.
