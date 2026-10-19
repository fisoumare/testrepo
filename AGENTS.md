# Site SOUMATIC — consignes pour les agents et les contributeurs

- Lire `docs/DESIGN.md` avant toute modification : concept, tokens, composants, règles de contenu.
- Stack : Laravel 13, Filament v5, Livewire 4, Tailwind CSS v4, Vite 8, MySQL 8 (`utf8mb4_unicode_ci`). PHP 8.3. Ne pas installer Laravel Boost ni d'autres paquets Composer sans nécessité (réseau restreint).
- Après tout changement de vue Blade ou de CSS : `npm run build` (Tailwind v4 scanne les vues).
- Base de développement partagée : `soumatic` sur MySQL (réglages dans `.env` ; Laragon : `root` sans mot de passe). Ne jamais lancer `migrate:fresh`, `db:wipe` ni les tests sur « soumatic » ; créer une nouvelle migration pour tout changement de schéma, puis `php artisan migrate`. Un seeder ciblé (`db:seed --class=…`) est permis.
- MySQL est en mode strict : une valeur plus longue que sa colonne provoque une erreur SQL (500). Tout champ de saisie qui écrit en base porte une limite cohérente avec sa colonne (`max:` côté Livewire, `maxLength()` / `maxValue()` côté Filament) ; slugs en minuscules sans accent (la collation confond « é » et « e », majuscules et minuscules).
- Tests : `php artisan test` (SQLite en mémoire, voir `phpunit.xml`) et, avant de livrer, la même suite sur MySQL dans la base séparée `soumatic_test` (`php artisan test --env=testing`, voir README § Tests). Style : `vendor/bin/pint`.
- Contenu : français, rien d'inventé (voir `docs/DESIGN.md` § 6). Orthographe : EduGes, MediGes SST, MediGes, Compiniya, SOUMATIC.
- Ne pas commiter ni pousser depuis un agent : la personne qui orchestre s'en charge.
