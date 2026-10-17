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
        $view->with('navSolutions', Solution::hydrate(Cache::remember('nav.solutions', 600, fn () => Solution::query()->where('is_active', true)->orderBy('position')->get(['id', 'name', 'slug', 'tagline', 'code', 'accent'])->toArray())));
        $view->with('navCategories', ServiceCategory::hydrate(Cache::remember('nav.categories', 600, fn () => ServiceCategory::query()->where('is_active', true)->orderBy('position')->get(['id', 'name', 'slug', 'code'])->toArray())));
    }
}
