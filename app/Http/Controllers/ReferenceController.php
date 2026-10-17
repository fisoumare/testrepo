<?php

namespace App\Http\Controllers;

use App\Models\Reference;
use Illuminate\Contracts\View\View;

class ReferenceController extends Controller
{
    public function index(): View
    {
        return view('pages.references', [
            'references' => Reference::query()->published()->orderByDesc('is_featured')->orderBy('position')->get(),
        ]);
    }
}
