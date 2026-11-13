<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\HeroSlide;
use App\Models\House;
use App\Models\Page;
use App\Models\Post;
use App\Models\PrefectBoard;
use App\Models\Programme;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $home = Page::where('template', 'home')->first();

        // Source data repeats the stats block; keep the first row per programme.
        $stats = collect(json_decode((string) Setting::get('home.admission_stats', '[]'), true) ?: [])
            ->unique('programme')->values();

        $programmes = collect(json_decode((string) Setting::get('home.programmes', '[]'), true) ?: [])
            ->map(fn ($p) => trim(str_replace("\u{200B}", '', (string) $p)))->filter()->unique()->values();

        $deskSlug = (string) config('site.desk.category_slug');
        $desk = Post::published()->with(['category', 'media'])
            ->whereHas('category', fn ($q) => $q->where('slug', $deskSlug))
            ->latest('published_at')->first();

        // Keep the Headmaster's Desk story out of the NEWS row so it is not shown twice.
        $news = Post::published()->with(['category', 'media'])
            ->when($desk, fn ($q) => $q->whereKeyNot($desk->id))
            ->latest('published_at')->take(4)->get();

        return view('pages.home', [
            'page' => $home,
            'hero' => $home?->getMedia('gallery') ?? collect(),
            'news' => $news,
            'desk' => $desk,
            'announcements' => Announcement::current()->with('media')
                ->paginate(2, ['*'], 'announcements_page')->fragment('events-announcements'),
            'events' => Event::published()->with('media')->upcoming()
                ->paginate(2, ['*'], 'events_page')->fragment('events-announcements'),
            'slides' => HeroSlide::active()->with('media')->get(),
            'houses' => House::active()->with('media')->get(),
            'programmeCards' => Programme::active()->with('media')->get(),
            'board' => PrefectBoard::published()->with('media')->orderByDesc('is_current')->orderByDesc('academic_year')->first(),
            'stats' => $stats,
            'programmes' => $programmes,
            'videoId' => Setting::get('home.video_id'),
        ]);
    }
}
