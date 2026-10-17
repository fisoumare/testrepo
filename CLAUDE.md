# Site SOUMATIC — consignes pour les agents et les contributeurs

- Lire `docs/DESIGN.md` avant toute modification : concept, tokens, composants, règles de contenu.
- Stack : Laravel 13, Filament v5, Livewire 4, Tailwind CSS v4, Vite 8, SQLite. PHP 8.3. Ne pas installer Laravel Boost ni d'autres paquets Composer sans nécessité (réseau restreint).
- Après tout changement de vue Blade ou de CSS : `npm run build` (Tailwind v4 scanne les vues).
- Base locale partagée : `database/database.sqlite`. Ne jamais lancer `migrate:fresh` ; créer une nouvelle migration pour tout changement de schéma, puis `php artisan migrate`.
- Tests : `php artisan test` (SQLite en mémoire, voir `phpunit.xml`). Style : `vendor/bin/pint`.
- Contenu : français, rien d'inventé (voir `docs/DESIGN.md` § 6). Orthographe : EduGes, MediGes SST, MediGes, Compiniya, SOUMATIC.
- Ne pas commiter ni pousser depuis un agent : la personne qui orchestre s'en charge.
