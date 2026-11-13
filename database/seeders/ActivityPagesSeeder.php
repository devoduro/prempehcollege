<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Fills the Sports and School Cadet pages using only facts that already exist on the old website (the History page and
 * two news stories), and prepares a draft Mathematics Olympiad page. Run safely more than once: pages that already have
 * activity details are left alone.
 */
class ActivityPagesSeeder extends Seeder
{
    public function run(): void
    {
        $this->sports();
        $this->cadet();
        $this->mathsOlympiad();
        $this->governors();
    }

    private function sports(): void
    {
        $page = Page::where('section', 'student-life')->where('slug', 'sports-and-athletics')->first();
        if (! $page || $page->highlights) {
            return;
        }
        $page->forceFill([
            'template' => 'activity',
            'status' => ContentStatus::Published,
            'news_category' => 'superzonals,athletics,sports',
            'body' => '<p>Since its founding, Prempeh College has become an outstanding institution in academics, sports and other areas. On the track and field, the College has a long tradition of success and a reputation as a dominant force in Ashanti regional athletics.</p>'
                .'<p>Months of dedicated training, careful race planning and teamwork by our student-athletes, coaches and staff keep Premps at the top of the field.</p>',
            'facts' => [
                ['value' => '2025', 'label' => 'Ashanti regional athletics title defended'],
                ['value' => '1963 – 1976', 'label' => 'National Athletics Champions'],
            ],
            'highlights' => [
                ['year' => '1963 – 1976', 'title' => 'National Athletics Champions',
                    'text' => 'Under Dr. T. A. Osae, the first African headmaster, Prempeh College beat key schools academically for ten consecutive years, produced more students for the medical school than any other school, and became National Athletics Champions.'],
                ['year' => '2025', 'title' => 'Ashanti regional athletics title defended',
                    'text' => 'At the Ashanti Regional Inter-Schools and Colleges Athletics Competition at Baba Yara Sports Stadium, Prempeh College defeated long-time rivals Opoku Ware Senior High School to defend the championship, a back-to-back victory.'],
                ['year' => '2025', 'title' => 'Track & Field team honoured',
                    'text' => 'The Headmaster, Very Rev. Lewis Asare, honoured the Track & Field team at a special luncheon for defending their Ashanti Region Schools & Colleges Super Zonal title. W Sports International president Wahid Hage ("Mr. Kotoko"), an alumnus of the College, was among the guests.'],
            ],
        ])->save();
    }

    private function cadet(): void
    {
        $page = Page::where('section', 'student-life')->where('slug', 'school-cadet')->first();
        if (! $page || $page->highlights) {
            return;
        }
        // On the old site this page showed a copy of the NSMQ photo gallery, not cadet photos; those images already
        // appear on the NSMQ Team page, so they are removed here and cadet photos can be added from the admin.
        $page->getMedia('content')->each->delete();

        $page->forceFill([
            'template' => 'activity',
            'status' => ContentStatus::Published,
            'body' => '<p>The Prempeh College Cadet Corps was formed in 1955, during the headship of Mr. T. C. Sims (1954 to 1958). For generations it has given students a place to learn discipline, leadership and service.</p>',
            'facts' => [['value' => '1955', 'label' => 'Cadet Corps formed']],
            'highlights' => [
                ['year' => '1955', 'title' => 'The Cadet Corps is formed',
                    'text' => 'Mr. T. C. Sims, Headmaster from 1954 to 1958, is credited with the formation of the Cadet Corps. The sixth form was also introduced during his time as Headmaster.'],
                ['year' => 'Legacy', 'title' => 'From the Corps to the Armed Forces',
                    'text' => 'Rear Admiral Ohene-Kwapong and a host of others who joined the Corps rose to become distinguished commissioned officers in the Ghana Armed Forces.'],
            ],
        ])->save();
    }

    /** No Mathematics Olympiad content exists on the old site, so the page is prepared as a draft for the school to complete. */
    private function mathsOlympiad(): void
    {
        if (Page::where('section', 'student-life')->where('slug', 'maths-olympiad')->exists()) {
            return;
        }
        Page::create([
            'title' => 'Mathematics Olympiad',
            'slug' => 'maths-olympiad',
            'section' => 'student-life',
            'template' => 'activity',
            'status' => ContentStatus::Draft,
            'sort_order' => 35,
            'news_category' => 'maths-olympiad',
        ]);
    }

    private function governors(): void
    {
        $page = Page::where('section', 'leadership')->where('slug', 'board-of-governors')->first();
        if (! $page || filled($page->getRawOriginal('body'))) {
            return;
        }
        $page->forceFill(['body' => '<p>The Board of Governors is chaired by Benjamin Acolatse.</p>'])->save();
    }
}
