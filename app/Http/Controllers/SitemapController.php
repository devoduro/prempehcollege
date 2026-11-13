<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\House;
use App\Models\Page;
use App\Models\Post;
use App\Models\PrefectBoard;
use App\Models\Programme;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap.xml', 3600, function () {
            $urls = [['loc' => url('/'), 'lastmod' => now()]];
            foreach (array_keys(config('site.sections')) as $s) {
                $urls[] = ['loc' => url($s), 'lastmod' => null];
            }
            foreach (Page::published()->where('template', '!=', 'home')->get() as $p) {
                $urls[] = ['loc' => url($p->path), 'lastmod' => $p->updated_at];
            }
            $urls[] = ['loc' => url('news'), 'lastmod' => null];
            foreach (Post::published()->get() as $p) {
                $urls[] = ['loc' => url($p->path), 'lastmod' => $p->updated_at];
            }
            $urls[] = ['loc' => url('events'), 'lastmod' => null];
            foreach (Event::published()->get() as $e) {
                $urls[] = ['loc' => url($e->path), 'lastmod' => $e->updated_at];
            }
            $urls[] = ['loc' => url('announcements'), 'lastmod' => null];
            foreach (Announcement::current()->get() as $a) {
                $urls[] = ['loc' => url($a->path), 'lastmod' => $a->updated_at];
            }
            $urls[] = ['loc' => url('houses'), 'lastmod' => null];
            foreach (House::active()->get() as $h) {
                $urls[] = ['loc' => url($h->path), 'lastmod' => $h->updated_at];
            }
            $urls[] = ['loc' => url('programmes'), 'lastmod' => null];
            foreach (Programme::active()->get() as $p) {
                $urls[] = ['loc' => url($p->path), 'lastmod' => $p->updated_at];
            }
            foreach (PrefectBoard::published()->get() as $b) {
                $urls[] = ['loc' => url($b->path), 'lastmod' => $b->updated_at];
            }

            return view('sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
