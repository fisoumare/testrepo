<?php

namespace App\Providers;

use App\Models\ServiceCategory;
use App\Models\Solution;
use App\View\Composers\LayoutComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        View::composer(['components.layouts.app', 'components.site.header', 'components.site.footer'], LayoutComposer::class);

        // Cache::memo()->forget() vide la copie mémoïsée de la requête en cours et le cache applicatif (voir LayoutComposer).
        foreach ([Solution::class => 'nav.solutions', ServiceCategory::class => 'nav.categories'] as $model => $key) {
            $model::saved(fn () => Cache::memo()->forget($key));
            $model::deleted(fn () => Cache::memo()->forget($key));
        }
    }
}
