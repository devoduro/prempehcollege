<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * National Investment Quiz (NIQ) and The Sharks Quiz pages. The old website has nothing on these, so every fact here comes
 * from public news reports (MyJoyOnline, B&FT, Yen.com.gh, The Sharks Quiz blog, 3news) and should be checked by the school.
 * News stories are added in the admin under the "NIQ" and "Sharks Quiz" categories, which these pages pull from.
 * Safe to run more than once.
 */
class QuizPagesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['NIQ', 'niq'], ['Sharks Quiz', 'sharks-quiz']] as [$name, $slug]) {
            Category::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $this->page('national-investment-quiz', 'National Investment Quiz', 'niq', 36,
            '<p>The National Investment Quiz (NIQ) is a national senior high school contest on investing, capital markets and financial literacy. Prempeh College is one of its strongest schools: champions in 2023 and first runners-up in 2026.</p>',
            [['value' => '2023', 'label' => 'NIQ champions'], ['value' => '2026', 'label' => 'First runners-up'], ['value' => '2024', 'label' => 'Fourth place']],
            [
                ['year' => '2023', 'title' => 'National Investment Quiz champions', 'text' => 'Prempeh College won the 2023 edition, beating Presbyterian Boys Senior High School (PRESEC) by a fraction of a point in a close final. Team member Andrew Kwabena Asare later won a major ICAG award.'],
                ['year' => '2024', 'title' => 'Fourth in the nation', 'text' => 'PRESEC won the 2024 edition. Prempeh College finished fourth, behind PRESEC, St. Augustine\'s College and St. John\'s Grammar.'],
                ['year' => '2026', 'title' => 'First runners-up', 'text' => 'Seeded into the quarter-finals, Prempeh College finished second in its group with 46 points to reach the semi-finals, then qualified for the final alongside Corpus Christi. In the final, St. Augustine\'s College (40) retained the title ahead of Mfantsipim (36); Prempeh College finished on 33 points and Corpus Christi on 23.'],
            ]);

        $this->page('sharks-quiz', 'The Sharks Quiz', 'sharks-quiz', 37,
            '<p>The Sharks Quiz (Senior Sharks) is a national senior high school quiz contest with a knock-out format. Prempeh College qualified from the Northern Zone and has been a competitive side since its debut in Season 7.</p>',
            [['value' => '7', 'label' => 'Season of our debut'], ['value' => '140 – 135', 'label' => 'Season 8 opening win over DPSI']],
            [
                ['year' => 'Season 7', 'title' => 'Debut through the Northern Zone', 'text' => 'Prempeh College qualified for the national rounds from the Northern Zone. In the Round of 16 it met PRESEC Legon in a contest billed as the "El Classico", and lost.'],
                ['year' => 'Season 8', 'title' => 'Opening win over DPSI', 'text' => 'Prempeh College began its Season 8 campaign with a narrow 140 to 135 win over DPSI, recovering with a strong showing in the R Cubed round.'],
                ['year' => 'Season 8', 'title' => 'Round of 16: lost on a tiebreak', 'text' => 'Prempeh College fought back from a large first-round deficit but lost to Keta SHTS on a tiebreak.'],
            ]);

        $parent = MenuItem::where('menu', 'main')->whereNull('parent_id')->where('label', 'Student Life')->first();
        if ($parent) {
            foreach ([['National Investment Quiz', '/student-life/national-investment-quiz'], ['The Sharks Quiz', '/student-life/sharks-quiz']] as $i => [$label, $url]) {
                MenuItem::firstOrCreate(['menu' => 'main', 'parent_id' => $parent->id, 'url' => $url], ['label' => $label, 'sort_order' => 97 + $i, 'is_visible' => true]);
            }
        }
    }

    /**
     * @param  list<array{value: string, label: string}>  $facts
     * @param  list<array{year: string, title: string, text: string}>  $highlights
     */
    private function page(string $slug, string $title, string $category, int $order, string $body, array $facts, array $highlights): void
    {
        if (Page::where('section', 'student-life')->where('slug', $slug)->exists()) {
            return;
        }
        Page::create([
            'title' => $title, 'slug' => $slug, 'section' => 'student-life', 'template' => 'activity',
            'status' => ContentStatus::Published, 'sort_order' => $order, 'news_category' => $category,
            'body' => $body, 'facts' => $facts, 'highlights' => $highlights,
        ]);
    }
}
