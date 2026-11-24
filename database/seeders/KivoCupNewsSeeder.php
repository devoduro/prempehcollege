<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Draft story on the KIVO U17 Colts Regional Champions Cup final, from the Ghana FA and GhanaSoccernet reports. Those reports name
 * the winners "Elite Academy – Prempeh" and do not say how that team relates to the College, so the editor must confirm that
 * before publishing. Saved as a DRAFT; add the venue, scorers, coach, captain, quotes and photos in the admin (News).
 */
class KivoCupNewsSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'prempeh-win-kivo-u17-colts-regional-champions-cup';
        if (Post::withTrashed()->where('slug', $slug)->exists()) {
            return;
        }

        $cat = Category::firstOrCreate(['slug' => 'sports'], ['name' => 'Sports']);

        Post::create([
            'category_id' => $cat->id,
            'title' => 'Prempeh crowned champions of the KIVO U17 Colts Regional Champions Cup',
            'slug' => $slug,
            'excerpt' => 'Prempeh beat Sogakope Cule FC 3-1 on penalties after a 2-2 draw in the final on 15 November 2026.',
            'body' => '<p>Prempeh are champions of the 2026 KIVO U17 Colts Regional Champions Cup. In the final on Sunday, 15 November 2026, they drew 2-2 with Sogakope Cule FC after regulation time and won 3-1 on penalties.</p>'
                .'<p>Prempeh topped Group A to reach the final, where they met Sogakope Cule FC, the winners of Group B.</p>'
                .'<p><em>Editor: the Ghana FA and GhanaSoccernet reports call the winners "Elite Academy – Prempeh" and do not say how the team is linked to the College. Confirm that, then add the venue, goalscorers, coach and captain, quotes, the group-stage run and photos before publishing.</em></p>',
            'status' => ContentStatus::Draft,
            'published_at' => now(),
        ]);
    }
}
