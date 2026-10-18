<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Reference;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Solution;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->withCount(['services as services_count' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('position')
            ->get();

        return view('pages.about', [
            'servicesCount' => Service::query()->active()->count(),
            'categoriesCount' => $categories->count(),
            'categories' => $categories,
            'solutions' => Solution::query()->where('is_active', true)->orderBy('position')->get(),
            'reference' => Reference::query()->published()->where('is_featured', true)->orderBy('position')->first(),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'faqs' => Faq::query()->active()->get(),
            'servicesCount' => Service::query()->active()->count(),
        ]);
    }

    public function quote(): View
    {
        return view('pages.quote');
    }

    public function legal(): View
    {
        return view('pages.legal');
    }
}
