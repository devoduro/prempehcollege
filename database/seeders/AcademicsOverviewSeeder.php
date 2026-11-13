<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Creates the Academics overview page. Every milestone below is taken from text already published on the old website
 * (the home page, the Past Headmasters biographies and the NSMQ news story). Safe to run more than once.
 */
class AcademicsOverviewSeeder extends Seeder
{
    public function run(): void
    {
        if (Page::where('section', 'academics')->where('slug', 'overview')->exists()) {
            return;
        }

        Page::create([
            'title' => 'Academics',
            'slug' => 'overview',
            'section' => 'academics',
            'status' => ContentStatus::Published,
            'sort_order' => 0,
            'body' => '<p>Prempeh College has a rich and storied history of providing quality education in Ghana. Our programmes are designed to shape each student\'s path for a rapidly changing world, and the College has long been recognised for academic excellence, in the classroom and in national competitions.</p>',
            'facts' => [
                ['value' => '1994', 'label' => 'Maiden NSMQ title won'],
                ['value' => '5', 'label' => 'NSMQ championships'],
                ['value' => '2016', 'label' => 'World Robofest champions'],
            ],
            'highlights' => [
                ['year' => '1963 – 1976', 'title' => 'Ahead of key schools for ten consecutive years', 'text' => 'Under Dr. T. A. Osae, Prempeh College beat key schools academically for ten consecutive years and produced more students for the medical school than any other school.'],
                ['year' => '1985', 'title' => 'Best "A" level results in the country', 'text' => 'Prempeh College produced the best "A" level results in the country.'],
                ['year' => '1994', 'title' => 'Maiden National Science & Maths Quiz winners', 'text' => 'The College won the maiden edition of the NSMQ.'],
                ['year' => '2000', 'title' => 'Overall best WAEC student and best Science student', 'text' => 'Academic excellence peaked when both awards were won by students of the College.'],
                ['year' => '2002 – 2009', 'title' => 'Over 400 students to KNUST', 'text' => 'The College matriculated over 400 students to KNUST alone, not to mention other universities.'],
                ['year' => '2004', 'title' => 'National Debate winners', 'text' => 'Prempeh College won the National Debate.'],
                ['year' => '2016', 'title' => 'First in Africa to win the World Robofest', 'text' => 'The first secondary institution in Ghana and Africa to win the World Robofest, and the only school from Africa to win the Toyota Innovation Award that year.'],
                ['year' => '2021', 'title' => 'NSMQ champions again', 'text' => 'Prempeh College won the National Science and Maths Quiz, bringing its championship tally to five.'],
            ],
        ]);
    }
}
