<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Contracts\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('pages.services.index', [
            'categories' => ServiceCategory::query()->where('is_active', true)->orderBy('position')
                ->with(['services' => fn ($q) => $q->where('is_active', true)])
                ->get(),
        ]);
    }

    public function category(ServiceCategory $category): View
    {
        abort_unless($category->is_active, 404);

        $category->load(['services' => fn ($q) => $q->where('is_active', true)->with('solution')]);

        return view('pages.services.category', [
            'category' => $category,
            'categories' => ServiceCategory::query()->where('is_active', true)->orderBy('position')->get(),
        ]);
    }

    public function show(Service $service): View
    {
        abort_unless($service->is_active, 404);

        $service->load(['category', 'solution']);

        return view('pages.services.show', [
            'service' => $service,
            'related' => Service::query()->active()->where('service_category_id', $service->service_category_id)
                ->whereKeyNot($service->id)->orderBy('position')->limit(4)->get(),
        ]);
    }
}
