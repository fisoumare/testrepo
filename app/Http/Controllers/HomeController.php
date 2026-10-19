<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Post;
use App\Models\Reference;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Solution;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->withCount(['services as services_count' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('position')
            ->get();

        $examples = Service::query()->active()->featured()->orderBy('position')->get()->groupBy('service_category_id');
        $categories->each(fn (ServiceCategory $category) => $category->setAttribute('example', $examples->get($category->id)?->first()));

        // « À partir de » mensuel : plus petit abonnement actif au mois (même règle que la famille
        // « subscription » de SolutionController::groupOffers : « Nom – Abonnement… »), en une seule requête.
        $solutions = Solution::query()
            ->where('is_active', true)
            ->withMin(['services as from_monthly' => fn ($q) => $q
                ->where('is_active', true)
                ->where('unit', 'Mois')
                ->where('price', '>', 0)
                ->where('name', 'like', '% – Abonnement%'),
            ], 'price')
            ->orderBy('position')
            ->get();

        return view('pages.home', [
            'categories' => $categories,
            'solutions' => $solutions,
            'featured' => Service::query()->active()->featured()->with('category')->orderBy('position')->get(),
            'reference' => Reference::query()->published()->where('is_featured', true)->orderBy('position')->first(),
            'faqs' => Faq::query()->active()->limit(4)->get(),
            'posts' => Post::query()->published()->limit(2)->get(),
            'servicesCount' => Service::query()->active()->count(),
        ]);
    }
}
