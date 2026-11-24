<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

/** Story on the ANA/ANAPEF-funded dining hall refurbishment, written from The Seniors Zone report of 4 May 2023. Safe to run more than once. */
class AnapefDiningHallNewsSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'anapef-funded-dining-hall-refurbishment-commissioned-2023';
        if (Post::withTrashed()->where('slug', $slug)->exists()) {
            return;
        }

        $cat = Category::firstOrCreate(['slug' => 'old-boys'], ['name' => 'Old Boys']);

        $body = '<p>The refurbished Dining Hall of Prempeh College was set to be commissioned on Saturday, 20 May 2023 at the College campus. The project, funded through the ANA/ANAPEF refurbishment programme, was reported complete on 4 May 2023, and all Seniors and supporters were invited, especially those in Ghana at the time.</p>'
            .'<h2>Furniture</h2><ul><li>94 new tables with solid silestone (quartz) tops</li><li>376 new benches with solid mahogany tops</li></ul>'
            .'<h2>Refurbishment works in the Dining Hall and Pantry</h2><ul>'
            .'<li>Painting of the dining hall and the pantry</li>'
            .'<li>A 10,000-litre Rambo 1000 water storage tank for the pantry, with a reinforced concrete plinth and plumbing</li>'
            .'<li>Panel mesh fencing to enclose the pantry backyard</li></ul>'
            .'<h2>Kitchen and dining supplies</h2><ul>'
            .'<li>2 large refrigerators and 2 large deep freezers</li>'
            .'<li>13 new cooking pots: 4 of size 40, 4 of size 50 and 5 of size 60</li>'
            .'<li>2 peanut roasting machines</li><li>10 large and 10 medium rubber containers</li><li>A new public address system with a cordless microphone for the dining hall</li></ul>'
            .'<h2>Murals</h2><p>Mural paintings on the dining hall walls honour five personalities associated with the College: Otumfuo Sir Osei Agyeman Prempeh II, the founder; Rev. S. N. Pearson, the founding Headmaster; Dr. T. A. Osae; R. T. Sackey, who is credited with the renaissance of the College and the College anthem; and Otumfuo Osei Tutu II, the custodian of Prempeh College.</p>'
            .'<p><em>Source: <a href="https://theseniorszone.wordpress.com/2023/05/04/full-list-of-items-funded-by-anapef-to-be-commissioned-for-prempeh-college-on-may-20-2023/" rel="noopener nofollow" target="_blank">The Seniors Zone, 4 May 2023</a>.</em></p>';

        Post::create([
            'category_id' => $cat->id,
            'title' => 'Full list of items funded by ANAPEF for the refurbished Prempeh College Dining Hall',
            'slug' => $slug,
            'excerpt' => 'New tables, benches, freezers, cooking pots, a water tank and murals: the full list of what the ANA/ANAPEF refurbishment gave the Dining Hall, commissioned on 20 May 2023.',
            'body' => $body,
            'status' => ContentStatus::Published,
            'published_at' => '2023-05-04 09:00:00',
        ]);
    }
}
