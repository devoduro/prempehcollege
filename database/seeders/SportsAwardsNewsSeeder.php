<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Draft sports and awards stories found in public news reports that are not already on the site (the 2025 Super Zonals,
 * athletics honours, 2021 NSMQ and 2023 robotics stories already exist). Saved as DRAFTS with no date: check the facts,
 * set the date, add photos and publish from the admin. Safe to run more than once.
 */
class SportsAwardsNewsSeeder extends Seeder
{
    public function run(): void
    {
        $sports = Category::firstOrCreate(['slug' => 'sports'], ['name' => 'Sports']);
        $honour = Category::firstOrCreate(['slug' => 'honour'], ['name' => 'Honour']);

        $stories = [
            [$honour, 'niq-champion-andrew-asare-wins-icag-best-level-1-candidate-award', 'Prempeh College 2023 NIQ champion wins ICAG Overall Best Level 1 Candidate Award',
                'Andrew Kwabena Panor Asare, of the 2023 National Investment Quiz-winning team, has won a major ICAG award.',
                '<p>Andrew Kwabena Panor Asare, a member of the Prempeh College team that won the 2023 National Investment Quiz, has received the Institute of Chartered Accountants, Ghana (ICAG) Overall Best Level 1 Candidate Award.</p>'
                .'<p>The College celebrated the achievement of its former quiz champion publicly in November 2026.</p>'],
            [$honour, 'prempeh-college-wins-unknown-mission-challenge-wro-2024', 'Prempeh College wins the Unknown Mission Challenge at the World Robotics Olympiad',
                'Our robotics team won the Unknown Mission Challenge at the 2024 World Robotics Olympiad in the USA.',
                '<p>In 2024 Prempeh College won the Unknown Mission Challenge at the World Robotics Olympiad in the United States, adding to the school\'s record in international robotics, which includes being the first secondary school in Ghana and Africa to win the World Robofest, in 2016.</p>'],
            [$sports, 'prempeh-college-sprite-ball-championship-runners-up-2010', 'Prempeh College finish as runners-up in the 2010 Sprite Ball Championship',
                'The College reached the final of the national Sprite Ball basketball championship in 2010.',
                '<p>Prempeh College was runner-up in the Sprite Ball Championship, the national senior high school basketball competition, in 2010.</p>'],
        ];

        foreach ($stories as [$cat, $slug, $title, $excerpt, $body]) {
            if (Post::withTrashed()->where('slug', $slug)->exists()) {
                continue;
            }
            Post::create(['category_id' => $cat->id, 'title' => $title, 'slug' => $slug, 'excerpt' => $excerpt, 'body' => $body,
                'status' => ContentStatus::Draft, 'published_at' => null]);
        }
    }
}
