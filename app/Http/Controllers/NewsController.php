<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $category = null;
        if ($request->filled('category')) {
            // Only existing slugs are accepted; anything else simply shows all news.
            $category = Category::where('slug', (string) $request->query('category'))->first();
        }

        $posts = Post::published()->with(['category', 'media'])
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->latest('published_at')
            ->paginate(12)->withQueryString();

        return view('news.index', [
            'posts' => $posts,
            'category' => $category,
            'categories' => Category::whereHas('posts', fn ($q) => $q->published())->orderBy('name')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::published()->with(['category', 'tags', 'media'])->where('slug', $slug)->firstOrFail();

        $related = Post::published()->with(['category', 'media'])
            ->whereKeyNot($post->id)
            ->when($post->category_id, fn ($q) => $q->orderByRaw('category_id = ? desc', [$post->category_id]))
            ->latest('published_at')->take(3)->get();

        return view('news.show', ['post' => $post, 'related' => $related]);
    }
}
