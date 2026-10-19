<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Premier compte du back-office, créé une seule fois (un compte existant n'est jamais modifié).
     *
     * Le mot de passe vient de ADMIN_PASSWORD. S'il est absent, ou s'il vaut « password » en production,
     * un mot de passe aléatoire est généré et affiché une seule fois dans la sortie du seeder : aucun
     * compte n'est jamais créé avec un mot de passe par défaut connu.
     */
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'fisoumare@soumatic.com');

        if (User::query()->where('email', $email)->exists()) {
            $this->command?->info("Compte administrateur {$email} déjà présent : conservé tel quel.");

            return;
        }

        $password = (string) env('ADMIN_PASSWORD', '');
        $generated = $password === '' || ($password === 'password' && app()->isProduction());

        if ($generated) {
            $password = Str::password(16, symbols: false);
        }

        User::query()->create([
            'name' => (string) env('ADMIN_NAME', 'Fodé Idrissa SOUMARÉ'),
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        if ($generated) {
            $this->command?->newLine();
            $this->command?->warn('========================================================================');
            $this->command?->warn("  Compte administrateur créé : {$email}");
            $this->command?->warn("  Mot de passe généré (ADMIN_PASSWORD absent ou refusé) : {$password}");
            $this->command?->warn("  Notez-le maintenant : il n'est affiché qu'une seule fois.");
            $this->command?->warn('  Changez-le ensuite depuis /admin/users.');
            $this->command?->warn('========================================================================');
            $this->command?->newLine();

            return;
        }

        $this->command?->info("Compte administrateur créé : {$email} (mot de passe ADMIN_PASSWORD du fichier .env).");
    }
}
