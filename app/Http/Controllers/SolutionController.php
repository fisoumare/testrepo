<?php

namespace App\Http\Controllers;

use App\Models\Solution;
use Illuminate\Contracts\View\View;

class SolutionController extends Controller
{
    public function index(): View
    {
        return view('pages.solutions.index', [
            'solutions' => Solution::query()->where('is_active', true)->orderBy('position')->with(['services' => fn ($q) => $q->where('is_active', true)])->get(),
        ]);
    }

    public function show(Solution $solution): View
    {
        abort_unless($solution->is_active, 404);

        $solution->load(['services' => fn ($q) => $q->where('is_active', true)]);

        return view('pages.solutions.show', [
            'solution' => $solution,
            'others' => Solution::query()->where('is_active', true)->whereKeyNot($solution->id)->orderBy('position')->get(),
        ]);
    }
}
