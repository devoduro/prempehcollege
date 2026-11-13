<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * History timeline, built only from facts already on the site (History, Past Headmasters, Cadet, Sports, NSMQ, news) plus
 * the 1994 first NSMQ title from public reports. The school may want to add more milestones in the admin
 * (Pages > History Timeline > Highlights). Safe to run more than once.
 */
class HistoryTimelineSeeder extends Seeder
{
    public function run(): void
    {
        if (! Page::where('section', 'about')->where('slug', 'history-timeline')->exists()) {
            Page::create([
                'title' => 'History Timeline', 'slug' => 'history-timeline', 'section' => 'about', 'template' => 'activity',
                'status' => ContentStatus::Published, 'sort_order' => 12,
                'body' => '<p>From a school opened in Kumasi in 1949 to one of Ghana\'s best-known secondary schools, here are the moments that shaped Prempeh College. For the full story, read our <a href="/about/history">History</a> and the list of <a href="/leadership/past-headmasters">past headmasters</a>.</p>',
                'facts' => [
                    ['value' => '1949', 'label' => 'College opened'],
                    ['value' => '75', 'label' => 'Years celebrated in 2024'],
                ],
                'highlights' => [
                    ['year' => 'Early 1940s', 'title' => 'A school for the middle belt', 'text' => 'The British Colonial Government invites the Presbyterian and Methodist Churches, which already ran schools in the south, to help set up a school in the middle belt of Ghana to serve the northern sector of the country.'],
                    ['year' => '1948', 'title' => 'The campus is designed', 'text' => 'After delays caused by the Second World War, the campus is designed by the British modernist architects Maxwell Fry and Jane Drew.'],
                    ['year' => '1949', 'title' => 'Founded and opened', 'text' => 'Prempeh College is founded by the Asanteman traditional authority, the British Colonial Government, the Methodist Church Ghana and the Presbyterian Church of Ghana, and named after the Asantehene, Sir Osei Tutu Agyeman Prempeh II, who donated the land. The opening ceremony is held on 5 February 1949, with an address by Major C. O. Butler, Chief Commissioner of Ashanti.'],
                    ['year' => '1949 – 1953', 'title' => 'The first headmaster', 'text' => 'Reverend Sidney Nelson Pearson leads the College through its first years.'],
                    ['year' => '1954 – 1958', 'title' => 'Sixth form and the Cadet Corps', 'text' => 'During the headship of Mr. T. C. Sims the sixth form is introduced, and in 1955 the Cadet Corps is formed.'],
                    ['year' => '1963 – 1976', 'title' => 'The first African headmaster', 'text' => 'Dr. T. A. Osae leads the College. For ten consecutive years it beats key schools academically, sends more students to medical school than any other school, and becomes National Athletics Champions.'],
                    ['year' => '1977 – 1995', 'title' => 'Continuing leadership', 'text' => 'Mr. M. K. Atiemo (1977 to 1984) and Mr. R. T. Sackey (1985 to 1995) serve as headmasters.'],
                    ['year' => '1994', 'title' => 'The first NSMQ title', 'text' => 'In the year the National Science and Maths Quiz begins, Prempeh College is crowned its first champion, a feat repeated in 1996.'],
                    ['year' => '1998 – 2009', 'title' => 'A new era', 'text' => 'Mr. Ebenezer Afoakwa Sekyere (1998 to 2002) and Mr. A. Owusu-Achiaw (2002 to 2009) lead the College.'],
                    ['year' => '2016', 'title' => 'World Robofest champions', 'text' => 'Prempeh College becomes the first secondary school in Africa to win the World Robofest.'],
                    ['year' => '2021', 'title' => 'Fifth NSMQ championship', 'text' => 'The College wins the National Science and Maths Quiz for a fifth time.'],
                    ['year' => '2023', 'title' => 'National Investment Quiz champions', 'text' => 'Prempeh College wins the National Investment Quiz and the National Robotics Competition in Accra.'],
                    ['year' => '2024', 'title' => '75th anniversary', 'text' => 'The College celebrates 75 years under the theme "75 years of outstanding leadership and service to society".'],
                    ['year' => '2025', 'title' => 'Titles on the track and on the stage', 'text' => 'The College defends its Ashanti regional athletics title and is crowned champion of the Luv FM High Schools Debate.'],
                    ['year' => '2026', 'title' => 'National Investment Quiz runners-up', 'text' => 'Prempeh College reaches the final of the National Investment Quiz and finishes as first runners-up.'],
                ],
            ]);
        }

        $parent = MenuItem::where('menu', 'main')->whereNull('parent_id')->where('label', 'About')->first();
        if ($parent) {
            MenuItem::firstOrCreate(['menu' => 'main', 'parent_id' => $parent->id, 'url' => '/about/history-timeline'], ['label' => 'History Timeline', 'sort_order' => 12, 'is_visible' => true]);
        }
    }
}
