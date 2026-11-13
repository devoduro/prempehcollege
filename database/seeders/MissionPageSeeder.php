<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Cleans the Mission & Values page. Kept from the old page: the Vision, the Mission and the four core values.
 * Removed as theme template material: three repeated paragraphs about "The Estudiar University", a stock team photo, and
 * a testimonial quote attributed to a former President that came from the template. Safe to run more than once.
 */
class MissionPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::where('section', 'about')->where('slug', 'mission-and-values')->first();
        if (! $page || $page->template === 'mission') {
            return;
        }

        // Stock photo from the template; the two genuine Prempeh photos stay on the page.
        $page->getMedia('content')->filter(fn ($m) => str_contains(strtolower($m->file_name), 'icons8') || str_contains(strtolower($m->file_name), 'unsplash'))->each->delete();

        $page->forceFill([
            'template' => 'mission',
            'status' => ContentStatus::Published,
            'body' => '<h2>Vision</h2><p>An institution for providing holistic education to all learners to become responsible citizens.</p>'
                .'<h2>Mission</h2><p>To provide holistic education through skills, training and committed service.</p>',
            'highlights' => [
                ['title' => 'Belief'],
                ['title' => 'Integrity'],
                ['title' => 'Perseverance'],
                ['title' => 'Teamwork'],
            ],
        ])->save();
    }
}
