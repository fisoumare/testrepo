<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            ServiceCategorySeeder::class,
            SolutionSeeder::class,
            ServiceSeeder::class,
            ReferenceSeeder::class,
            FaqSeeder::class,
            PostSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
