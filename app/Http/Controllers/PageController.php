<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Models\Programme;
use App\Support\LegacyRedirects;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /** /{section}/{slug}, e.g. /about/history */
    public function show(string $section, string $slug): View|RedirectResponse
    {
        // The "overview" page is the section's landing text; it lives at /{section}, not /{section}/overview.
        if ($slug === 'overview') {
            return redirect('/'.$section, 301);
        }

        $page = Page::published()->with('media')->where('section', $section)->where('slug', $slug)->firstOrFail();

        return $this->render($page);
    }

    /** /{slug}: a root page (contact, college-prep), a section index (/about), or an old WordPress URL. */
    public function root(Request $request, string $slug): View|RedirectResponse
    {
        if (array_key_exists($slug, config('site.sections'))) {
            return $this->section($slug);
        }

        $page = Page::published()->with('media')->whereNull('section')->where('slug', $slug)
            ->where('template', '!=', 'home')->first();
        if ($page) {
            return $this->render($page);
        }

        if ($redirect = LegacyRedirects::find($request->path())) {
            return LegacyRedirects::respond($redirect);
        }

        abort(404);
    }

    private function section(string $section): View
    {
        $pages = Page::published()->with(['media', 'staffGroup.members' => fn ($q) => $q->where('is_published', true)->with('media')])
            ->where('section', $section)->orderBy('sort_order')->orderBy('title')->get();
        abort_if($pages->isEmpty(), 404);

        // Featured leaders: the head of each group listed in config (e.g. the Board Chairman and the Headmaster).
        $featured = collect(config("site.featured_leaders.$section", []))
            ->map(function (string $slug) use ($pages) {
                $page = $pages->firstWhere('slug', $slug);
                $person = $page?->staffGroup?->members->first();

                return $page && $person ? ['page' => $page, 'person' => $person] : null;
            })->filter()->values();

        // Club and team pages with no photos of their own borrow a photo from their related news, so every card has a picture.
        $covers = [];
        foreach ($pages->where('template', 'activity') as $p) {
            $media = $p->getFirstMedia('gallery') ?? $p->getFirstMedia('featured');
            if (! $media && ($slugs = $p->newsCategorySlugs())) {
                $post = Post::published()->with('media')->whereHas('category', fn ($q) => $q->whereIn('slug', $slugs))->latest('published_at')->first();
                $media = $post?->getFirstMedia('featured') ?? $post?->getFirstMedia('content');
            }
            if ($media) {
                $covers[$p->id] = $media;
            }
        }

        // Latest stories from the categories listed for this section (e.g. quiz, robotics and athletics news).
        $newsSlugs = config("site.section_news.$section", []);
        $news = $newsSlugs === [] ? collect() : Post::published()->with(['category', 'media'])
            ->whereHas('category', fn ($q) => $q->whereIn('slug', $newsSlugs))->latest('published_at')->take(3)->get();

        return view('pages.section', [
            'covers' => $covers,
            'news' => $news,
            'section' => $section,
            'label' => config("site.sections.$section"),
            'overview' => $pages->firstWhere('slug', 'overview'),
            'pages' => $pages->where('slug', '!=', 'overview')->values(),
            'featured' => $featured,
            // The Academics hub lists every active programme with its core subjects and elective combinations.
            'programmes' => $section === 'academics' ? Programme::active()->with(['combinations', 'media'])->get() : collect(),
        ]);
    }

    private function render(Page $page): View
    {
        $siblings = $page->section
            ? Page::published()->where('section', $page->section)->orderBy('sort_order')->orderBy('title')->get(['id', 'title', 'slug', 'section', 'template'])
            : collect();

        // Leadership pages list their people as profile cards (name and role as text) when a staff group is linked.
        $people = $page->staffGroup?->members()->where('is_published', true)->with('media')->get() ?? collect();

        $data = ['page' => $page, 'siblings' => $siblings, 'people' => $people, 'groupKind' => $page->staffGroup?->kind];

        if ($page->template === 'mission') {
            // The sections headed "Vision" and "Mission" become the two large cards; anything else stays as normal text.
            $parts = preg_split('~(<h2[^>]*>.*?</h2>)~is', (string) $page->body, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];
            $sections = [];
            $other = '';
            for ($i = 0; $i < count($parts); $i++) {
                if (preg_match('~^<h2[^>]*>(.*?)</h2>$~is', $parts[$i], $m)) {
                    $key = strtolower(trim(strip_tags($m[1])));
                    $content = $parts[$i + 1] ?? '';
                    if (in_array($key, ['vision', 'mission'], true)) {
                        $sections[$key] = trim(strip_tags($content, '<p><strong><em><a><br>'));
                        $i++;
                    } else {
                        $other .= $parts[$i].$content;
                        $i++;
                    }
                } elseif (trim(strip_tags($parts[$i])) !== '') {
                    $other .= $parts[$i];
                }
            }
            $data += ['vision' => $sections['vision'] ?? null, 'mission' => $sections['mission'] ?? null, 'other' => $other];

            return view('pages.mission', $data);
        }

        if ($page->template === 'activity') {
            // Latest stories (and their photos) from the categories this page follows.
            $slugs = $page->newsCategorySlugs();
            $news = $slugs === [] ? collect() : Post::published()->with(['category', 'media'])
                ->whereHas('category', fn ($q) => $q->whereIn('slug', $slugs))
                ->latest('published_at')->take(6)->get();
            $data['news'] = $news;
            $data['photos'] = $news->flatMap(fn ($p) => $p->getMedia('featured')->concat($p->getMedia('content')))->unique('id')->take(12)->values();

            return view('pages.activity', $data);
        }

        return view('pages.show', $data);
    }
}
