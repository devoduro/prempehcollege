<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Student Leadership and SRC hub. It uses the prefect boards and the 2025/2026 election story already on the site, plus two
 * public facts (the Ashanti Regional SRC lists Prempeh College as a member school; prefects carry a replica of the stool at
 * official functions). The College's own SRC rules and executives are not published anywhere found, so that section says so
 * plainly and the draft "SRC Executives" page can be completed and published from the admin. Safe to run more than once.
 */
class StudentLeadershipSeeder extends Seeder
{
    public function run(): void
    {
        if (! Page::where('section', 'leadership')->where('slug', 'student-leadership-and-src')->exists()) {
            Page::create([
                'title' => 'Student Leadership and SRC', 'slug' => 'student-leadership-and-src', 'section' => 'leadership', 'template' => 'default',
                'status' => ContentStatus::Published, 'sort_order' => 60,
                'body' => '<p>Prempeh College trains its students to lead as well as to learn. The prefectorial board and the Students\' Representative Council (SRC) give students a voice in running the College and a chance to serve their fellow students.</p>'
                    .'<h2>The prefectorial board</h2>'
                    .'<p>Each year a board of prefects, headed by the Senior College Prefect, serves the school. Prefects carry a replica of the Asante stool at official functions and academic processions. Meet the current and past boards on the <a href="/prefects">Prefects page</a>.</p>'
                    .'<h2>Electing the Senior College Prefect</h2>'
                    .'<p>The Senior College Prefect is chosen in a student election. In the 2025/2026 election, five candidates stood, and Master Ishmael Edusei of 2 Science 17 won with 443 votes, or 38.03% of the ballots. <a href="/news/ishmael-edusei-wins-the-2025-2026-senior-college-prefect-election">Read the story</a>.</p>'
                    .'<h2>The Students\' Representative Council (SRC)</h2>'
                    .'<p>Prempeh College is a member school of the Ashanti Regional SRC, the council that brings together student leaders from senior high schools across the region. Details of the College\'s own SRC, including its executives and how they are chosen, will be added here soon.</p>'
                    .'<p>Questions about student leadership? <a href="/contact">Contact the school office</a>.</p>',
            ]);
        }

        $parent = MenuItem::where('menu', 'main')->whereNull('parent_id')->where('label', 'Leadership')->first();
        if ($parent) {
            MenuItem::firstOrCreate(['menu' => 'main', 'parent_id' => $parent->id, 'url' => '/leadership/student-leadership-and-src'], ['label' => 'Student Leadership and SRC', 'sort_order' => 92, 'is_visible' => true]);
        }
    }
}
