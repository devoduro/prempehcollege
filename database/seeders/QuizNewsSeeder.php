<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Draft news stories for the NIQ and Sharks Quiz pages, written only from facts in public news reports. They are saved as
 * DRAFTS: the school should check them, add photos and publish them from the admin (News). Safe to run more than once.
 */
class QuizNewsSeeder extends Seeder
{
    public function run(): void
    {
        $niq = Category::firstOrCreate(['slug' => 'niq'], ['name' => 'NIQ']);
        $sharks = Category::firstOrCreate(['slug' => 'sharks-quiz'], ['name' => 'Sharks Quiz']);

        $stories = [
            [$niq, 'prempeh-college-wins-2023-national-investment-quiz', 'Prempeh College wins the 2023 National Investment Quiz',
                'Prempeh College beat PRESEC by a fraction of a point to be crowned National Investment Quiz champions.',
                '<p>Prempeh College won the 2023 National Investment Quiz, beating Presbyterian Boys Senior High School (PRESEC) by a fraction of a point in a closely fought final.</p>'
                .'<p>Team member Andrew Kwabena Asare later won a major ICAG award for his achievement.</p>', null],
            [$niq, 'prempeh-college-finishes-fourth-2024-national-investment-quiz', 'Prempeh College finishes fourth at the 2024 National Investment Quiz',
                'PRESEC won the 2024 edition, with Prempeh College placing fourth.',
                '<p>PRESEC won the 2024 National Investment Quiz. St. Augustine\'s College placed second and St. John\'s Grammar third, with Prempeh College taking fourth place.</p>', null],
            [$niq, 'prempeh-college-first-runners-up-2026-national-investment-quiz', 'Prempeh College are first runners-up at the 2026 National Investment Quiz',
                'Prempeh College reached the grand final and finished on 33 points behind St. Augustine\'s College.',
                '<p>Prempeh College reached the grand final of the 2026 National Investment Quiz. Seeded into the quarter-finals, the team finished second in its group with 46 points to book a semi-final place, then qualified for the final alongside Corpus Christi Senior High School.</p>'
                .'<p>In the final, defending champions St. Augustine\'s College retained the title with 40 points, ahead of Mfantsipim (36). Prempeh College finished on 33 points and Corpus Christi on 23.</p>', null],
            [$sharks, 'prempeh-college-debut-senior-sharks-season-7', 'Prempeh College make their Senior Sharks debut',
                'Prempeh College qualified from the Northern Zone and met PRESEC in the Round of 16.',
                '<p>Prempeh College qualified for the national rounds of The Sharks Quiz (Season 7) from the Northern Zone. In the Round of 16 the school met PRESEC Legon in a contest billed as the "El Classico", and lost.</p>', null],
            [$sharks, 'prempeh-college-opens-senior-sharks-season-8-with-win-over-dpsi', 'Prempeh College open Senior Sharks Season 8 with a win over DPSI',
                'A strong R Cubed round helped Prempeh College to a 140 to 135 win.',
                '<p>Prempeh College began its Season 8 campaign in The Sharks Quiz with a narrow 140 to 135 win over DPSI, recovering from a shaky start with a well-executed R Cubed round.</p>', null],
            [$sharks, 'prempeh-college-lose-to-keta-shts-on-tiebreak-senior-sharks-8', 'Prempeh College lose to Keta SHTS on a tiebreak in Senior Sharks 8',
                'After a large first-round deficit, Prempeh College fought back but lost on a tiebreak.',
                '<p>In the Round of 16 of Senior Sharks Season 8, Prempeh College ended the first round with a large deficit and fought back into the contest, but lost to Keta SHTS on a tiebreak.</p>', null],
        ];

        foreach ($stories as [$cat, $slug, $title, $excerpt, $body, $date]) {
            if (Post::withTrashed()->where('slug', $slug)->exists()) {
                continue;
            }
            Post::create([
                'category_id' => $cat->id, 'title' => $title, 'slug' => $slug, 'excerpt' => $excerpt, 'body' => $body,
                'status' => ContentStatus::Draft, 'published_at' => $date,
            ]);
        }
    }
}
