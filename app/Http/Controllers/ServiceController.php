<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Seo;
use Illuminate\Contracts\View\View;

class ServiceController extends Controller
{
    /** Cartes affichées d'emblée par famille sur /prestations ; les autres restent dans la page, dépliables et cherchables. */
    public const PREVIEW_PER_CATEGORY = 6;

    public function index(): View
    {
        $categories = ServiceCategory::query()->where('is_active', true)->orderBy('position')
            ->with(['services' => fn ($q) => $q->where('is_active', true)])
            ->get();

        $total = $categories->sum(fn (ServiceCategory $category) => $category->services->count());

        return view('pages.services.index', [
            'categories' => $categories,
            'total' => $total,
            'onQuote' => $categories->sum(fn (ServiceCategory $category) => $category->services->filter->isOnQuote()->count()),
            'preview' => self::PREVIEW_PER_CATEGORY,
            'description' => Seo::description(
                "Les {$total} prestations de SOUMATIC en {$categories->count()} familles : conception, solutions clés en main, hébergement, API, maintenance, analyse, conseil et formation. Prix en GNF.",
            ),
        ]);
    }

    public function category(ServiceCategory $category): View
    {
        abort_unless($category->is_active, 404);

        $category->load(['services' => fn ($q) => $q->where('is_active', true)->with('solution')]);

        return view('pages.services.category', [
            'category' => $category,
            'categories' => ServiceCategory::query()
                ->where('is_active', true)
                ->withCount(['services as services_count' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('position')
                ->get(),
            'onQuote' => $category->services->filter->isOnQuote()->count(),
            'title' => Seo::title($category->name.' · Prestations', $category->name),
            'description' => Seo::description($category->description, [
                'Prix hors taxes en GNF, TVA '.setting('vat_rate', '18').' % en sus.',
                'Catalogue SOUMATIC, '.setting('city', 'Conakry').'.',
            ]),
        ]);
    }

    public function show(Service $service): View
    {
        abort_unless($service->is_active, 404);

        $service->load(['category', 'solution']);

        $segments = explode(' – ', $service->name);

        return view('pages.services.show', [
            'service' => $service,
            'related' => Service::query()->active()->where('service_category_id', $service->service_category_id)
                ->whereKeyNot($service->id)->orderBy('position')->limit(4)->get(),
            // Nom entier ; sinon sans la parenthèse finale (« (Google Play ou Apple) ») ; sinon, pour un nom à trois
            // segments, sans le premier (« Maquette commune Android/iOS – Niveau 1 ») ; sinon le nom raccourci au mot.
            'title' => Seo::title(
                $service->name,
                preg_replace('/\s*\([^)]*\)\s*$/u', '', $service->name),
                count($segments) >= 3 ? implode(' – ', array_slice($segments, 1)) : null,
                $service->name,
            ),
            'description' => Seo::description($service->summary, [
                $service->isOnQuote()
                    ? 'Prix sur devis.'
                    : 'Prix : '.gnf($service->price).' HT'.($service->hasTimeUnit() ? ' / '.mb_strtolower($service->unit) : '').'.',
                'Famille '.$service->category->name.'.',
                'Devis valable '.setting('quote_validity_days', '30').' jours.',
                'Au catalogue de SOUMATIC, à '.setting('city', 'Conakry').'.',
            ]),
        ]);
    }
}
