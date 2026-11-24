<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PrefectBoard;
use App\Models\PrefectPosition;
use App\Models\Programme;
use App\Models\StaffGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Starting content for the new school-life sections. Safe to run more than once.
 * Only facts already present on the old website are used; everything is editable in the admin.
 */
class SchoolLifeSeeder extends Seeder
{
    public function run(): void
    {
        $this->positions();
        $this->programmes();
        $this->heroSlides();
        $this->legacyBoards();
        $this->menuLinks();
    }

    /** Adds Houses, Programmes and Prefects to the main menu under their sections (editable under Site > Menus). */
    private function menuLinks(): void
    {
        $links = [['About', 'Houses', '/houses'], ['Academics', 'Programmes & Combinations', '/programmes'], ['Leadership', 'Prefects', '/prefects']];
        foreach ($links as $i => [$parentLabel, $label, $url]) {
            $parent = MenuItem::where('menu', 'main')->whereNull('parent_id')->where('label', $parentLabel)->first();
            if ($parent) {
                MenuItem::firstOrCreate(['menu' => 'main', 'parent_id' => $parent->id, 'url' => $url], ['label' => $label, 'sort_order' => 90 + $i, 'is_visible' => true]);
            }
        }
    }

    /** Typical prefect positions in a Ghanaian senior high school; rename or remove them in the admin. */
    private function positions(): void
    {
        $titles = ['Senior Prefect', 'Assistant Senior Prefect', 'Chapel Prefect', 'Dining Hall Prefect', 'Compound Prefect',
            'Library Prefect', 'Sports Prefect', 'Entertainment Prefect', 'Health Prefect', 'Time Keeper', 'Class Prefect', 'House Prefect'];
        foreach ($titles as $rank => $title) {
            PrefectPosition::firstOrCreate(['title' => $title], ['rank' => ($rank + 1) * 10]);
        }
    }

    /** The four programmes named on the old home page. Core subjects are the national SHS core. */
    private function programmes(): void
    {
        $core = ['English Language', 'Core Mathematics', 'Integrated Science', 'Social Studies'];
        $rows = [
            ['General Science', 'Science and mathematics for future scientists, engineers and medics.'],
            ['General Arts', 'Languages, humanities and social sciences for future leaders and communicators.'],
            ['Business', 'Accounting, economics and management for the world of enterprise.'],
            ['Visual Arts', 'Creative design, drawing and making for artists and designers.'],
        ];
        foreach ($rows as $i => [$name, $summary]) {
            Programme::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name, 'summary' => $summary, 'core_subjects' => $core, 'sort_order' => $i, 'is_active' => true,
            ]);
        }
    }

    /** Builds the slider from the photos captured from the old home page carousel. */
    private function heroSlides(): void
    {
        if (HeroSlide::exists()) {
            return;
        }
        $photos = Page::where('template', 'home')->first()?->getMedia('gallery') ?? collect();
        $captions = [
            ['Est. 1940 · Kumasi, Ghana', 'A storied tradition of academic excellence', 'Since 1940 Prempeh College has redrawn the boundaries of intellectual and creative thought.', 'Our history', '/about/history'],
            ['Champions', 'NSMQ champions', 'Prempeh College has won the National Science and Maths Quiz five times.', 'Read the news', '/news'],
            ['Innovation', 'Robotics pioneers', 'The first secondary school in Africa to win the World Robofest, in 2016.', 'Student life', '/student-life'],
            ['Leaders of Ghana', 'The Premps', 'Generations of leaders, thinkers and builders, and a proud alumni family.', 'Meet the prefects', '/prefects'],
            ['Join us', 'Learn, lead, serve', 'Explore our programmes and the life of the College.', 'Our programmes', '/programmes'],
        ];
        foreach ($captions as $i => [$eyebrow, $title, $subtitle, $label, $url]) {
            $slide = HeroSlide::create(['eyebrow' => $eyebrow, 'title' => $title, 'subtitle' => $subtitle, 'button_label' => $label, 'button_url' => $url, 'sort_order' => $i]);
            if ($photo = $photos->get($i)) {
                $slide->addMedia($photo->getPath())->preservingOriginal()->toMediaCollection('image');
            }
        }
    }

    /** Year-group photo records from WordPress become boards whose photos form the board gallery. */
    private function legacyBoards(): void
    {
        foreach (StaffGroup::where('kind', 'year')->with('members.media')->get() as $group) {
            $media = $group->members->flatMap(fn ($m) => $m->getMedia('photo'));
            if ($media->isEmpty()) {
                continue;
            }
            // A group named "2027" is the board finishing in 2027, i.e. the 2026/2027 academic year.
            $end = (int) $group->name;
            $year = $end > 1900 ? ($end - 1).'/'.$end : $group->name;
            $board = PrefectBoard::firstOrCreate(['slug' => str_replace('/', '-', $year)], [
                'name' => $year.' Prefectorial Board',
                'academic_year' => $year,
                'description' => 'Names, positions and photographs of the prefects will be published here soon.',
                'is_current' => false,
                'is_published' => true,
            ]);
            if ($board->getMedia('gallery')->isEmpty()) {
                foreach ($media as $m) {
                    $board->addMedia($m->getPath())->preservingOriginal()->toMediaCollection('gallery');
                }
            }
        }
    }
}
