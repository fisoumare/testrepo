<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('pages.posts.index', [
            'posts' => Post::query()->published()->paginate(9),
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->is_published && $post->published_at?->isPast(), 404);

        return view('pages.posts.show', [
            'post' => $post,
            'more' => Post::query()->published()->whereKeyNot($post->id)->limit(3)->get(),
        ]);
    }
}
