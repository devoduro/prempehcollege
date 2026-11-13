<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Old Boys Association (Amanfoɔ) page. Facts come from the old website (the North American alumni dormitory story) and
 * public news reports (Graphic Online, GNA, GhanaWeb, MyJoyOnline). No membership details, dues or contacts are invented:
 * the page points readers to the school office until the Association supplies them. Safe to run more than once.
 */
class OldBoysPageSeeder extends Seeder
{
    public function run(): void
    {
        $alumni = Category::firstOrCreate(['slug' => 'alumni'], ['name' => 'Alumni']);
        Post::where('slug', 'north-american-alumni-to-empower-prempeh-college-with-300-bed-dormitory')->update(['category_id' => $alumni->id]);

        if (! Page::where('section', 'about')->where('slug', 'old-boys-association')->exists()) {
            Page::create([
                'title' => 'Old Boys Association (Amanfoɔ)', 'slug' => 'old-boys-association', 'section' => 'about', 'template' => 'activity',
                'status' => ContentStatus::Published, 'sort_order' => 45, 'news_category' => 'alumni,honour',
                'body' => '<p>Amanfoɔ is the family of Prempeh College old boys. From Kumasi to North America and the United Kingdom, alumni stay connected to their alma mater through year groups, reunions and projects that give today\'s students better classrooms, water and dormitories.</p>'
                    .'<p>Old boys have also gone on to lead in public life, and the College regularly celebrates their achievements.</p>'
                    .'<p><strong>Join or reconnect:</strong> contact details for the Association will be published here soon. In the meantime, the <a href="/contact">school office</a> can point you to your year group.</p>',
                'facts' => [
                    ['value' => 'Amanfoɔ', 'label' => 'The Old Boys family'],
                    ['value' => 'Worldwide', 'label' => 'Branches including North America and the UK'],
                ],
                'highlights' => [
                    ['year' => 'Leadership', 'title' => 'Global President', 'text' => 'During the College\'s 75th anniversary, Benjamin Kweku Acolatse was named as Chairman of the Board of Governors and Global President of the Prempeh College Old Students\' Association.'],
                    ['year' => 'Year groups', 'title' => 'A water distribution system for the College', 'text' => 'The 1998 year group (Amanfoɔ \'98) provided the College with a water distribution system.'],
                    ['year' => '2018', 'title' => 'Amanfoɔ \'98 20th anniversary homecoming', 'text' => 'The 1998 year group marked 20 years since leaving school with a homecoming that supported their alma mater.'],
                    ['year' => 'Year groups', 'title' => 'Amanfoɔ \'99 celebrates 25 years', 'text' => 'The 1999 year group marked its 25th anniversary by commissioning projects for the College.'],
                    ['year' => 'Overseas', 'title' => 'North American alumni and a 300-bed dormitory', 'text' => 'Alumni in North America have pledged to empower the College with a 300-bed dormitory. See the news below.'],
                ],
            ]);
        }

        $parent = MenuItem::where('menu', 'main')->whereNull('parent_id')->where('label', 'About')->first();
        if ($parent) {
            MenuItem::firstOrCreate(['menu' => 'main', 'parent_id' => $parent->id, 'url' => '/about/old-boys-association'], ['label' => 'Old Boys Association', 'sort_order' => 96, 'is_visible' => true]);
        }
    }
}
