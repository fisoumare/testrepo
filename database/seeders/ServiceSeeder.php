<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Solution;
use App\Support\Typo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    /**
     * Identifiants Compiniya des prestations mises en avant sur le site.
     *
     * @var list<int>
     */
    public const FEATURED = [24, 28, 33, 11, 105, 90, 98, 115, 123, 108, 13, 29, 54, 81];

    public function run(): void
    {
        $catalogue = json_decode((string) file_get_contents(database_path('data/catalogue.json')), true, flags: JSON_THROW_ON_ERROR);

        $categories = ServiceCategory::query()->pluck('id', 'name');
        $solutions = Solution::query()->get()->sortByDesc(fn (Solution $s) => strlen($s->name));

        $positions = [];

        foreach ($catalogue as $article) {
            $categoryId = $categories[$article['categorie']] ?? null;

            if (! $categoryId) {
                continue;
            }

            $solutionId = null;
            foreach ($solutions as $solution) {
                if (Str::startsWith($article['nom'], $solution->name.' –')) {
                    $solutionId = $solution->id;
                    break;
                }
            }

            $positions[$categoryId] = ($positions[$categoryId] ?? 0) + 1;

            Service::query()->updateOrCreate(
                ['compiniya_id' => $article['id']],
                [
                    'service_category_id' => $categoryId,
                    'solution_id' => $solutionId,
                    'number' => $article['numero'],
                    'name' => Typo::fr($article['nom']),
                    'slug' => self::uniqueSlug($article['nom'], (int) $article['id']),
                    'unit' => $article['unite'],
                    'price' => (int) $article['prix_unitaire_ht'],
                    'summary' => Typo::fr(self::summary($article['description'])),
                    'description' => Typo::fr($article['description']),
                    'is_featured' => in_array($article['id'], self::FEATURED, true),
                    'is_active' => true,
                    'position' => $positions[$categoryId],
                ],
            );
        }
    }

    /** Slug lisible, unique même pour des noms proches (« Haute performance » / « Haute performance + »). */
    public static function uniqueSlug(string $name, int $compiniyaId): string
    {
        $base = Str::slug(str_replace('+', ' plus', $name));
        $slug = $base;

        while (Service::query()->where('slug', $slug)->where('compiniya_id', '!=', $compiniyaId)->exists()) {
            $slug = $base.'-'.$compiniyaId;
        }

        return $slug;
    }

    /** Première phrase de la description, limitée à 170 caractères. */
    public static function summary(string $description): string
    {
        $firstSentence = preg_split('/(?<=[.!?])\s+(?=[A-ZÀ-Ý«])/u', trim($description), 2)[0] ?? $description;

        return Str::limit(trim($firstSentence), 170, '…');
    }
}
