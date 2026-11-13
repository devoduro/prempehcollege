<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Clubs and Societies hub. The list follows the groups named on the school's own homepage menu. Descriptions use only facts
 * already on this site; groups with no details yet (Cyber Security, College Media Team, Red Cross) are listed by name only.
 * Other clubs that appear in older third-party listings (Science, ICT, Investment, Wildlife, GUNSA, choir, dance, art) could
 * not be confirmed as current, so the school should add them in the admin (Pages > Clubs and Societies). Safe to run more than once.
 */
class ClubsSocietiesSeeder extends Seeder
{
    public function run(): void
    {
        if (! Page::where('section', 'student-life')->where('slug', 'clubs-and-societies')->exists()) {
            Page::create([
                'title' => 'Clubs and Societies', 'slug' => 'clubs-and-societies', 'section' => 'student-life', 'template' => 'default',
                'status' => ContentStatus::Published, 'sort_order' => 5,
                'body' => '<p>Learning at Prempeh College goes well beyond the classroom. Our clubs and societies give students a place to build skills, compete nationally and serve others.</p>'
                    .'<h2>Quiz and academic teams</h2><ul>'
                    .'<li><strong><a href="/student-life/nsmq-team">NSMQ Team</a></strong>: five national championships in the National Science and Maths Quiz.</li>'
                    .'<li><strong><a href="/student-life/national-investment-quiz">National Investment Quiz</a></strong>: champions in 2023 and first runners-up in 2026.</li>'
                    .'<li><strong><a href="/student-life/sharks-quiz">The Sharks Quiz</a></strong>: our team in the national Senior Sharks contest.</li>'
                    .'</ul>'
                    .'<h2>Technology and innovation</h2><ul>'
                    .'<li><strong><a href="/student-life/robotics-club">Robotics Club</a></strong>: the first secondary school in Africa to win the World Robofest, in 2016.</li>'
                    .'<li><strong>Cyber Security</strong>: more details will be added soon.</li>'
                    .'</ul>'
                    .'<h2>Debate and media</h2><ul>'
                    .'<li><strong>Debate Club</strong>: crowned 2025 champions of the Luv FM High Schools Debate.</li>'
                    .'<li><strong>College Media Team</strong>: more details will be added soon.</li>'
                    .'</ul>'
                    .'<h2>Discipline, sport and service</h2><ul>'
                    .'<li><strong><a href="/student-life/school-cadet">School Cadet</a></strong>: formed in 1955, for discipline, leadership and service.</li>'
                    .'<li><strong><a href="/student-life/sports-and-athletics">Sports and Athletics</a></strong>: a long tradition of success in Ashanti regional athletics.</li>'
                    .'<li><strong>Red Cross</strong>: more details will be added soon.</li>'
                    .'</ul>'
                    .'<p>Want to start or lead a club? <a href="/contact">Contact the school office</a>.</p>',
            ]);
        }

        $parent = MenuItem::where('menu', 'main')->whereNull('parent_id')->where('label', 'Student Life')->first();
        if ($parent) {
            MenuItem::firstOrCreate(['menu' => 'main', 'parent_id' => $parent->id, 'url' => '/student-life/clubs-and-societies'], ['label' => 'Clubs and Societies', 'sort_order' => 5, 'is_visible' => true]);
        }
    }
}
