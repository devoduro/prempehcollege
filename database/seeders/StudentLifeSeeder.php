<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Student Life landing text, and a clean NSMQ Team page. The old NSMQ text was damaged in an earlier export (overwritten
 * and repeated fragments); this keeps every result and year that could be read reliably. Team names are as printed on
 * the old page and should be checked by the school. Safe to run more than once.
 */
class StudentLifeSeeder extends Seeder
{
    public function run(): void
    {
        $this->overview();
        $this->nsmq();
    }

    private function overview(): void
    {
        if (Page::where('section', 'student-life')->where('slug', 'overview')->exists()) {
            return;
        }
        Page::create([
            'title' => 'Student Life',
            'slug' => 'overview',
            'section' => 'student-life',
            'status' => ContentStatus::Published,
            'body' => '<p>Students have access to engaging and productive extra-curricular activities. From national quiz champions and robotics pioneers to track-and-field winners and the Cadet Corps, there is a place at Prempeh College for every talent.</p>',
            'facts' => [
                ['value' => '5', 'label' => 'NSMQ championships'],
                ['value' => '2016', 'label' => 'World Robofest champions'],
                ['value' => '2025', 'label' => 'Ashanti athletics title defended'],
                ['value' => '1955', 'label' => 'Cadet Corps formed'],
            ],
        ]);
    }

    private function nsmq(): void
    {
        $page = Page::where('section', 'student-life')->where('slug', 'nsmq-team')->first();
        if (! $page || $page->highlights) {
            return;
        }

        // Keep the photos, but as a gallery rather than a stack inside the text.
        foreach ($page->getMedia('content') as $media) {
            $page->addMedia($media->getPath())->preservingOriginal()->toMediaCollection('gallery');
            $media->delete();
        }

        $page->forceFill([
            'template' => 'activity',
            'news_category' => 'nsmq',
            'body' => '<p>The National Science and Maths Quiz (NSMQ) is a highly competitive national contest that showcases the academic strength of Ghana\'s senior high schools. Since its inception in 1994, Prempeh College has been one of the most successful schools in the competition, with a record built on preparation, hard work, and quick thinking under pressure.</p>'
                .'<p>The current team coordinator is Mr. Paul Kwadwo Damoah, who took over from Mr. George Amankwaah in 2022. The 2024 squad had 23 members, 10 of them forming the core team.</p>'
                .'<p>Our participation in the NSMQ highlights our academic abilities and inspires our peers and the Amanfoo fraternity.</p>',
            'facts' => [
                ['value' => '5', 'label' => 'NSMQ championships'],
                ['value' => '1994', 'label' => 'Maiden edition won'],
                ['value' => '2021', 'label' => 'Latest title'],
            ],
            'highlights' => [
                ['year' => '1994', 'title' => 'Champions of the maiden edition', 'text' => 'Prempeh College won the first National Science and Maths Quiz.'],
                ['year' => '1996', 'title' => 'Champions again', 'text' => 'A second title for the College.'],
                ['year' => '2015', 'title' => 'NSMQ champions', 'text' => 'Led by Daniel Aboagye Danso and Ransford Bioh.'],
                ['year' => '2017', 'title' => 'NSMQ champions', 'text' => 'Led by Daniel Osei Badu, Sarto Ansah Wonder and Boakye Konadu.'],
                ['year' => '2021', 'title' => 'Fifth championship', 'text' => 'Nana Obeng Kyei Eden, Evans Oppong and Abdul Majeed Abdullah won the title in Kumasi, beating Presbyterian Boys Senior High School Legon (53 points to 49), with Keta Senior High and Technical School third.'],
                ['year' => '2022', 'title' => 'Runners-up in the final', 'text' => 'The team led through every round until the last, and was beaten by Presbyterian Boys Senior High School Legon in the final round.'],
                ['year' => '2023', 'title' => 'Semi-finalists', 'text' => 'The team was eliminated in the semi-final by academic rivals Opoku Ware School.'],
                ['year' => 'Finals', 'title' => 'Finalists in 1994, 1995, 1996, 2015, 2017, 2021 and 2022', 'text' => null],
            ],
        ])->save();
    }
}
