<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Draft story on the 2016 year group's 10th anniversary celebration. Only the one fact found in a public report is stated
 * (the donation to the school). It is saved as a DRAFT: add the date, venue, programme and photos in the admin (News), then publish.
 */
class Batch2016NewsSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'prempeh-college-2016-year-group-marks-10th-anniversary';
        if (Post::withTrashed()->where('slug', $slug)->exists()) {
            return;
        }

        $cat = Category::firstOrCreate(['slug' => 'old-boys'], ['name' => 'Old Boys']);

        Post::create([
            'category_id' => $cat->id,
            'title' => 'Prempeh College 2016 year group marks 10th anniversary',
            'slug' => $slug,
            'excerpt' => 'The 2016 year group gave back to their alma mater with a donation of essential items as part of their 10th anniversary celebrations.',
            'body' => '<p>The 2016 year group of Prempeh College (Amanfoɔ) came together this weekend to celebrate ten years since completing school.</p>'
                .'<p>As part of the anniversary activities, the year group gave back to their alma mater with a donation of essential items to the school.</p>'
                .'<p><em>Editor: add the date and venue, the programme of the weekend, who attended and spoke, the full list of items donated, and photos before publishing.</em></p>',
            'status' => ContentStatus::Draft,
            'published_at' => now(),
        ]);
    }
}
