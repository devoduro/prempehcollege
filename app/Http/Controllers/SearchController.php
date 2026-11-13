<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Page;
use App\Models\Post;
use App\Models\StaffMember;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $q = trim((string) ($validated['q'] ?? ''));
        $pages = $posts = $people = collect();

        if (mb_strlen($q) >= 2) {
            // Escape LIKE wildcards with an explicit ESCAPE character so input is matched literally on MySQL
            // and SQLite alike. Column names are fixed; the user value is always a bound parameter.
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q).'%';
            $match = fn ($query) => $query->where(fn ($w) => $w
                ->whereRaw("title LIKE ? ESCAPE '!'", [$like])
                ->orWhereRaw("body LIKE ? ESCAPE '!'", [$like]));

            $pages = $match(Page::published()->where('template', '!=', 'home'))->orderBy('title')->limit(20)->get();
            $posts = $match(Post::published()->with('media'))->latest('published_at')->limit(20)->get()
                ->concat($match(Announcement::published())->latest('published_at')->limit(10)->get())
                ->concat($match(Event::published())->latest('starts_at')->limit(10)->get());

            // People on leadership pages: matched by name or role, linked to the page they appear on.
            $people = StaffMember::query()->where('is_published', true)
                ->whereHas('group.page', fn ($p) => $p->where('status', ContentStatus::Published->value))
                ->where(fn ($w) => $w->whereRaw("name LIKE ? ESCAPE '!'", [$like])->orWhereRaw("role LIKE ? ESCAPE '!'", [$like]))
                ->with(['group.page', 'media'])->orderBy('name')->limit(20)->get();
        }

        return view('search', ['q' => $q, 'pages' => $pages, 'posts' => $posts, 'people' => $people]);
    }
}
