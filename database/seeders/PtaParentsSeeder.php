<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * PTA and Parents page. The only public fact found is that the College has a PTA alongside the Old Boys Association; the
 * current executives, meeting dates and dues are not published anywhere reliable, so none are stated. The page points
 * parents to News, Events, Announcements and the school office. Complete the draft "PTA Executives" page and link it here
 * when the school supplies names. Safe to run more than once.
 */
class PtaParentsSeeder extends Seeder
{
    public function run(): void
    {
        if (! Page::where('section', 'about')->where('slug', 'pta-and-parents')->exists()) {
            Page::create([
                'title' => 'PTA and Parents', 'slug' => 'pta-and-parents', 'section' => 'about', 'template' => 'default',
                'status' => ContentStatus::Published, 'sort_order' => 48,
                'body' => '<p>A boarding school works best when families and teachers work together. At Prempeh College the Parent-Teacher Association (PTA) brings parents and guardians into the life of the school, alongside the <a href="/about/old-boys-association">Old Boys Association</a>, to help the College keep its focus and support its students.</p>'
                    .'<h2>Staying in touch</h2><ul>'
                    .'<li><a href="/announcements">Announcements</a>: notices for parents and students, such as dates and requirements.</li>'
                    .'<li><a href="/events">Events</a>: the school calendar, including prize-giving and anniversary celebrations.</li>'
                    .'<li><a href="/news">News</a>: what the College and its students are achieving.</li>'
                    .'<li><a href="/contact">Contact the school office</a>: for questions about your son, fees or visits.</li>'
                    .'</ul>'
                    .'<h2>PTA executives and meetings</h2>'
                    .'<p>The names of the PTA executives and the dates of PTA meetings will be published here soon. In the meantime, the school office can tell you how to join in.</p>',
            ]);
        }

        $parent = MenuItem::where('menu', 'main')->whereNull('parent_id')->where('label', 'About')->first();
        if ($parent) {
            MenuItem::firstOrCreate(['menu' => 'main', 'parent_id' => $parent->id, 'url' => '/about/pta-and-parents'], ['label' => 'PTA and Parents', 'sort_order' => 99, 'is_visible' => true]);
        }
    }
}
