<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'servicesCount' => Service::query()->active()->count(),
            'categoriesCount' => ServiceCategory::query()->where('is_active', true)->count(),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'faqs' => Faq::query()->active()->get(),
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
