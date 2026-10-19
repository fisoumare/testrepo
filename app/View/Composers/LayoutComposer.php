<?php

namespace App\View\Composers;

use App\Models\ServiceCategory;
use App\Models\Solution;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LayoutComposer
{
    public function compose(View $view): void
    {
        // Le cache ne sérialise que des tableaux (Laravel 13 restreint les classes désérialisables) : on réhydrate les modèles.
        // Cache::memo() : le composeur est appelé trois fois par page (layout, en-tête, pied de page), le cache n'est lu qu'une fois.
        $view->with('navSolutions', Solution::hydrate(Cache::memo()->remember('nav.solutions', 600, fn () => Solution::query()->where('is_active', true)->orderBy('position')->get(['id', 'name', 'slug', 'tagline', 'code', 'accent'])->toArray())));
        $view->with('navCategories', ServiceCategory::hydrate(Cache::memo()->remember('nav.categories', 600, fn () => ServiceCategory::query()->where('is_active', true)->orderBy('position')->get(['id', 'name', 'slug', 'code'])->toArray())));
    }
}
