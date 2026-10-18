<?php

namespace App\Http\Controllers;

use App\Models\Reference;
use Illuminate\Contracts\View\View;

class ReferenceController extends Controller
{
    public function index(): View
    {
        $references = Reference::query()->published()->orderByDesc('is_featured')->orderBy('position')->get();

        // La référence mise en avant ouvre la page ; à défaut, la première publiée.
        $featured = $references->first(fn (Reference $reference) => $reference->is_featured) ?? $references->first();

        return view('pages.references', [
            'featured' => $featured,
            'others' => $featured ? $references->where('id', '!=', $featured->id)->values() : $references,
        ]);
    }
}
