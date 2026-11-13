<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * School Anthem page, saved as a DRAFT. The only fact found in public sources is that the lyrics of the current College
 * Anthem are credited to a Mr. Sackey (from a page on the old school website). The lyrics, the composer, the year and the
 * history of the song are not published anywhere reliable, so they are left for the school to supply, then publish the page
 * from the admin (Pages). Menu link appears automatically once the page is published. Safe to run more than once.
 */
class SchoolAnthemSeeder extends Seeder
{
    public function run(): void
    {
        if (! Page::where('section', 'about')->where('slug', 'school-anthem')->exists()) {
            Page::create([
                'title' => 'School Anthem', 'slug' => 'school-anthem', 'section' => 'about', 'template' => 'default',
                'status' => ContentStatus::Draft, 'sort_order' => 46,
                'body' => '<p>Every Prempeh College gathering, from morning assembly to Speech and Prize-Giving Day, is sung in with the College Anthem. It carries the spirit of the school, and every Amanfoɔ knows it by heart.</p>'
                    .'<h2>About the anthem</h2>'
                    .'<p>The lyrics of the current College Anthem are credited to Mr. Sackey.</p>'
                    .'<h2>History of the school song</h2>'
                    .'<p>[To be added by the school: who composed the music, when the anthem was first sung, and whether there was an earlier song.]</p>'
                    .'<h2>Lyrics</h2>'
                    .'<p>[To be added by the school: the full words of the anthem.]</p>',
            ]);
        }

        $parent = MenuItem::where('menu', 'main')->whereNull('parent_id')->where('label', 'About')->first();
        if ($parent) {
            MenuItem::firstOrCreate(['menu' => 'main', 'parent_id' => $parent->id, 'url' => '/about/school-anthem'], ['label' => 'School Anthem', 'sort_order' => 97, 'is_visible' => true]);
        }
    }
}
