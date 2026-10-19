# Passation : site web SOUMATIC

Document de reprise pour une nouvelle session Claude Code ouverte **sur le poste de l'utilisateur** (Laragon, Windows), dans `C:\laragon\www\soumatic`. Il fait suite à une session cloud (9 au 19 octobre 2026) qui a conçu et livré le site.

Ce dépôt GitHub est **public** : ce fichier ne contient rien de confidentiel. Les points privés (Compiniya, clients, conditions de paiement, étude de prix) sont dans un document séparé, `HANDOVER-PRIVE.md`, remis directement à l'utilisateur et **jamais versionné**.

## 1. À lire en premier

1. `CLAUDE.md` (identique à `AGENTS.md`) : règles de travail du projet.
2. `docs/DESIGN.md` : concept graphique, couleurs, composants Blade, règles de contenu.
3. `README.md` : installation Laragon, dépannage, tests, mise en production.
4. Ce fichier, puis `HANDOVER-PRIVE.md` si l'utilisateur le fournit.

## 2. L'utilisateur et sa façon de travailler

- **Fodé Idrissa SOUMARÉ**, gérant de SOUMATIC (Conakry, Guinée). Développeur backend Laravel, utilise FilamentPHP pour les back-offices, et selon les projets Bootstrap, Tailwind CSS ou W3.CSS ; frontends parfois en October CMS ou avec le plugin FBLOG de Filament.
- Répondre **en français**, de façon structurée, orientée bonnes pratiques, maintenabilité et performance, avec des exemples adaptés à ce stack.
- Quand il écrit « Confirme que tu comprends avant de procéder », **reformuler et attendre son accord** avant d'agir.
- Avant toute écriture dans un outil métier (Compiniya), **attendre « applique »**.
- **Ne jamais inventer** un fait sur l'entreprise, ses clients ou ses contrats. Ce qui manque est laissé vide ou marqué « à compléter » dans le back-office.

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
| Dépôt | `fisoumare/testrepo` (public), branche **`site-soumatic`** |
| Stack | Laravel 13.37 · Filament 5.10 · Livewire 4 · Tailwind CSS 4 · Vite 8 · PHP 8.3 ou plus |
| Base | MySQL 8, base `soumatic`, `root` sans mot de passe (Laragon), collation `utf8mb4_unicode_ci` |
| Adresse locale | http://soumatic.test et http://soumatic.test/admin |
| Node.js | 20.19 ou plus récent (exigence de Vite 8) |

### Fonctionnalités livrées

- **Pages publiques** : accueil, solutions (liste et fiche de chacun des 4 logiciels), prestations (catalogue de 120 prestations en 8 familles, recherche instantanée, fiche par prestation), références, actualités, à propos, contact, mentions légales, pages d'erreur 403, 404, 419, 500 et 503 aux couleurs du site.
- **Configurateur d'estimation** `/devis` (Livewire `App\Livewire\QuoteBuilder`) : sélection de prestations, quantités, totaux HT, TVA 18 % et TTC, envoi d'une demande numérotée `DEM-AAAA-0001`.
- **Formulaire de contact** `/contact` (Livewire `App\Livewire\ContactForm`) : demandes numérotées `MSG-AAAA-0001`, préremplissage par `?sujet=demo&solution=…`.
- **E-mails** : notification à `notification_email` et accusé de réception au visiteur (`app/Mail`). Anti-spam : pot de miel et limite de 5 envois par 10 minutes et par adresse IP.
- **Back-office Filament** `/admin` : prestations, familles, solutions, références, actualités, FAQ, demandes reçues (lecture, statut, réponse par e-mail ou WhatsApp), utilisateurs, page « Paramètres du site » (`/admin/parametres`) pour toutes les coordonnées et textes.
- **SEO** : balises par page, Open Graph, JSON-LD (Organization, BreadcrumbList, NewsArticle), `sitemap.xml` et `robots.txt` dynamiques.
- **Typographie française** automatique (`App\Support\Typo`, helper `typo()`), élision (`App\Support\Elision`), montants (`gnf()`, `App\Support\Money`).

### Données

- `database/data/catalogue.json` : export du catalogue Compiniya au 17 octobre 2026 (120 prestations, prix HT en GNF). `ServiceSeeder` l'importe par `compiniya_id`.
- `php artisan db:seed` **réécrit** les textes du catalogue, des solutions, de la FAQ, des références et des actualités à partir des seeders. Les comptes et les paramètres déjà présents sont conservés. Pour une seule table : `php artisan db:seed --class=NomDuSeeder`.
- Il n'existe aucune synchronisation automatique avec Compiniya : un changement de prix dans Compiniya doit être reporté dans `catalogue.json` (puis réimport) ou directement dans le back-office.
- Compte administrateur : créé par `AdminUserSeeder` avec `ADMIN_EMAIL` et `ADMIN_PASSWORD` du `.env`. Si `ADMIN_PASSWORD` est vide, un mot de passe aléatoire est généré et affiché une seule fois.

### Qualité vérifiée en fin de session cloud

- 225 tests (PHPUnit), verts sur SQLite en mémoire et sur MySQL 8 (`php artisan test --env=testing`, base `soumatic_test`, voir README § Tests).
- `vendor/bin/pint --test` conforme. Aucune violation d'accessibilité « critical » ou « serious » (axe-core) sur les pages principales, en 1440 et 390 px. Aucun débordement horizontal sur mobile.
- Quatre revues indépendantes (design, code, contenu et SEO, accessibilité) puis corrections : typographie, contrastes, sécurité des formulaires et des e-mails, limites de saisie pour le mode strict de MySQL.

## 5. Décisions déjà prises (ne pas revenir dessus sans l'utilisateur)

- **Concept graphique** « salle des serveurs », tiré du logo provisoire (un S formé de trois serveurs aux LED vertes) : unités à coins marqués, LED, codes monospace (U01–U08, S01–S04), barre d'état. Détails dans `docs/DESIGN.md`.
- **Couleurs** : bleu marine `#0F2D52`, vert émeraude `#0E9F6E` (couleur du logo), ardoise `#52637A`, fond sombre `#0F172A`. Les boutons principaux sont verts avec un **texte sombre** (le blanc sur ce vert n'atteint pas le contraste minimal).
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
- Le logo est **provisoire**. Les sources sont dans `docs/brand/logo-provisoire/` (`soumatic-logo-master.svg` à texte éditable). Les fichiers utilisés par le site sont dans `public/brand/` et `public/favicon*` ; l'image de partage `public/brand/og.png` est à régénérer si le logo change.
- Les prix du catalogue sont une **première proposition** : l'utilisateur veut les revoir avec Claude (voir `HANDOVER-PRIVE.md`).

### Technique

- **Mise en production** non faite : choix de l'hébergement, `APP_ENV=production`, `APP_DEBUG=false`, configuration `MAIL_*` réelle, HTTPS, `php artisan optimize`. Procédure dans le README § Mise en production.
- **GitHub** : supprimer l'ancienne branche `claude/compiniya-identity-hnhn0i` (identique à `site-soumatic`) depuis https://github.com/fisoumare/testrepo/branches ; fusionner `site-soumatic` dans `master` quand l'utilisateur le demande. Envisager un dépôt privé dédié (le dépôt actuel est public et s'appelle « testrepo »).

## 7. Commandes de référence (terminal Laragon)

Mise à jour du code puis de l'application :

```
cd C:\laragon\www\soumatic
git pull
composer install
php artisan migrate
npm install
npm run build
php artisan optimize:clear
```

Contrôles avant de livrer une modification :

```
php artisan test
php artisan test --env=testing
vendor\bin\pint --test app database routes tests config
```

Pièges déjà rencontrés : « Index of / » (hôte virtuel Laragon pointé sur la racine au lieu de `public`), « Vite manifest not found » (`npm run build` non lancé), `migrate` qui parle de SQLite (ancien `.env`). Solutions dans le README § Dépannage.

## 8. Premier message suggéré pour la session locale

> Tu reprends le projet du site web de mon entreprise SOUMATIC, dans ce dossier. Lis d'abord `docs/HANDOVER.md`, `CLAUDE.md`, `docs/DESIGN.md` et `README.md`, puis `HANDOVER-PRIVE.md` que je te fournis. Vérifie que le site tourne (`php artisan test`, http://soumatic.test). Résume-moi en quelques lignes l'état du projet et la liste de ce qui reste à faire, puis attends mes instructions. Confirme que tu comprends avant de procéder.
