<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Solution;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('solutions.index'), 'priority' => '0.9'],
            ['loc' => route('services.index'), 'priority' => '0.9'],
            ['loc' => route('references.index'), 'priority' => '0.6'],
            ['loc' => route('posts.index'), 'priority' => '0.6'],
            ['loc' => route('about'), 'priority' => '0.5'],
            ['loc' => route('contact'), 'priority' => '0.7'],
            ['loc' => route('quote'), 'priority' => '0.8'],
        ]);

        Solution::query()->where('is_active', true)->get()->each(fn ($s) => $urls->push(['loc' => route('solutions.show', $s), 'priority' => '0.8']));
        ServiceCategory::query()->where('is_active', true)->get()->each(fn ($c) => $urls->push(['loc' => route('services.category', $c), 'priority' => '0.7']));
        Service::query()->active()->get()->each(fn ($s) => $urls->push(['loc' => route('services.show', $s), 'priority' => '0.5']));
        Post::query()->published()->get()->each(fn ($p) => $urls->push(['loc' => route('posts.show', $p), 'priority' => '0.5', 'lastmod' => $p->updated_at?->toAtomString()]));

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }
}
