<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'fisoumare@soumatic.com');
        $password = (string) env('ADMIN_PASSWORD', 'password');

        User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Fodé Idrissa SOUMARÉ'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );

        $this->command?->warn("Compte administrateur : {$email} (mot de passe défini par ADMIN_PASSWORD, « password » par défaut — à changer).");
    }
}
