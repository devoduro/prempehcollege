<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\StaffGroup;
use App\Models\StaffMember;
use Illuminate\Database\Seeder;

/**
 * Current Headmaster: the biography was only available as three images; it is typed out here (transcribed from those
 * images). Past Headmasters: the timeline comes from the text that already existed on the page. Safe to run twice.
 * Photos of past headmasters are not matched to names (the posters carry no names), so they are shown as portraits.
 */
class HeadmastersSeeder extends Seeder
{
    public function run(): void
    {
        $this->currentHeadmaster();
        $this->pastHeadmasters();
    }

    private function currentHeadmaster(): void
    {
        $page = Page::where('section', 'about')->where('slug', 'current-headmasters')->first();
        if (! $page || str_contains((string) $page->getRawOriginal('body'), 'Professional and work experience')) {
            return;
        }

        // The first image is a photo banner of the Headmaster; keep it as the page's featured image.
        $banner = $page->getMedia('content')->first();
        if ($banner && $page->getMedia('featured')->isEmpty()) {
            $page->addMedia($banner->getPath())->preservingOriginal()->withCustomProperties(['alt' => 'Very Rev. Lewis Asare, Headmaster'])->toMediaCollection('featured');
        }
        // The other two images were the biography itself; the text below replaces them.
        $page->getMedia('content')->each->delete();

        $rows = fn (array $r) => '<table><thead><tr><th>'.implode('</th><th>', array_keys($r[0])).'</th></tr></thead><tbody>'
            .implode('', array_map(fn ($row) => '<tr><td>'.implode('</td><td>', array_map('e', $row)).'</td></tr>', $r)).'</tbody></table>';

        $page->forceFill(['body' => '<h2>Personal profile</h2>'
            .'<p>Very Reverend Lewis Asare, a Methodist Minister and current headmaster of Prempeh College, has served in various positions that have critical bearing on the Ghana educational sector.</p>'
            .'<h2>Professional and work experience</h2>'.$rows([
                ['Position' => 'Headmaster, Prempeh College', 'Years' => '2024 – date'],
                ['Position' => 'Circuit Minister, Asuyeboah Circuit, Methodist Church Ghana', 'Years' => '2023 – date'],
                ['Position' => 'Circuit Minister, Agogo Circuit, Methodist Church Ghana', 'Years' => '2019 – 2023'],
                ['Position' => 'Circuit Minister, Juaben Circuit, Methodist Church Ghana', 'Years' => '2018 – 2019'],
                ['Position' => 'Headmaster, Kurofa Methodist Senior High School', 'Years' => '2019 – 2023'],
                ['Position' => 'Head of Science Department, Juaben SHS', 'Years' => '2013 – 2018'],
                ['Position' => 'Director, Lesmak IT Solution', 'Years' => '2012 – date'],
                ['Position' => 'Biology Tutor, Juaben Senior High School', 'Years' => '2008 – 2019'],
                ['Position' => 'Biology Tutor, Juaben Senior High School', 'Years' => '2003 – 2006'],
                ['Position' => 'Science/Mathematics Teacher, Akrofuom Methodist JHS', 'Years' => '1997 – 1999'],
            ])
            .'<h2>Meritorious awards</h2>'.$rows([
                ['Award' => 'Municipal Best Teacher, Ejisu-Juaben Municipal', 'Year' => '2006'],
                ['Award' => 'Regional Best Science Teacher, Ashanti Region', 'Year' => '2006'],
            ])
            .'<h2>Membership of professional bodies</h2><ul>'
            .'<li>Ghana National Association of Teachers (GNAT)</li>'
            .'<li>Conference of Heads of Assisted Secondary Schools (CHASS)</li>'
            .'<li>Bible Society of Ghana</li><li>Ghana for Christ</li><li>Biological Association of Ghana</li>'
            .'<li>Ghana Association of Science Teachers (consultant, national)</li></ul>'
            .'<h2>Boards and committees</h2>'.$rows([
                ['Committee or council' => 'University Governing Council, University of Education, Winneba', 'Position' => 'Member', 'Years' => '2007 – 2008'],
                ['Committee or council' => 'Maintenance Committee, Juaben SHS', 'Position' => 'Chairman', 'Years' => '2011 – 2019'],
                ['Committee or council' => 'ICT Committee, Juaben SHS', 'Position' => 'Chairman', 'Years' => '2012 – 2016'],
                ['Committee or council' => "Leaders' Meeting, Ebenezer Methodist Church, Juaben", 'Position' => 'Chairman', 'Years' => '2018 – 2019'],
                ['Committee or council' => 'Editorial Board, The Wesleyan Trumpet (quarterly newsletter of the Effiduase Diocese)', 'Position' => 'Member', 'Years' => '2020 – date'],
                ['Committee or council' => 'The Wesleyan Trumpet', 'Position' => 'Columnist', 'Years' => '2020 – date'],
                ['Committee or council' => 'Asante Akim South Municipal Education Directorate', 'Position' => 'Member', 'Years' => '2021 – 2023'],
            ])
            .'<h2>Other portfolios held</h2>'.$rows([
                ['Portfolio' => 'President, Graduate Students Association of Ghana, UEW', 'Years' => '2007 – 2008'],
                ['Portfolio' => 'Leader, St. Paul Methodist Church, Winneba', 'Years' => '2006 – 2010'],
                ['Portfolio' => 'Leader, Calvary Methodist Church, Amakom, Kumasi', 'Years' => '2010 – 2015'],
                ['Portfolio' => 'House Master, Juaben SHS', 'Years' => '2009 – 2013'],
                ['Portfolio' => 'Maintenance Officer, Juaben SHS', 'Years' => '2011 – 2019'],
                ['Portfolio' => 'Circuit Minister in charge of Ebenezer Society, Juaben Circuit', 'Years' => '2018 – 2019'],
                ['Portfolio' => 'Circuit Minister in charge of V. Rev. S. B. Afranie Society, Agogo Circuit', 'Years' => '2019 – 2023'],
                ['Portfolio' => 'Chairman, Local Council of Churches, Kurofa', 'Years' => '2020 – 2023'],
            ])
            .'<h2>Education and training</h2>'.$rows([
                ['Level' => 'Religious training, Trinity Theological Seminary', 'Years' => '2016 – 2018'],
                ['Level' => 'Master of Philosophy in Science, University of Education, Winneba', 'Years' => '2006 – 2008'],
                ['Level' => 'Bachelor of Education in Science Education, University of Education, Winneba', 'Years' => '1999 – 2003'],
                ['Level' => "Professional training, Wesley College of Education (Teachers' Certificate 'A')", 'Years' => '1994 – 1997'],
                ['Level' => 'Senior High School, Prempeh College', 'Years' => '1990 – 1993'],
            ])
            .'<h2>Publications</h2><p>He has several publications mostly on educational leadership in Ghana.</p>'.$rows([
                ['Title' => "Teaching reproductive health among junior secondary school students (long essay leading to the Teachers' Certificate 'A')", 'Year' => '1997'],
                ['Title' => 'Trends in knowledge of HIV/AIDS and other STDs among out-of-school youth in the Brong Ahafo Region (project work for the Bachelor of Education in Science Education)', 'Year' => '2003'],
                ['Title' => 'The intention of senior high school science teachers to teach HIV/AIDS (dissertation for the Master of Philosophy in Science)', 'Year' => '2008'],
                ['Title' => 'The impact of fasting on the development of servant leaders: the context of Isaiah 58 (short essay for probationer studies)', 'Year' => '2019'],
                ['Title' => "Assessment of the theology, history and practices of Charles Wesley's hymnody on contemporary Christian worship (long essay for probationer studies)", 'Year' => '2021'],
            ])
            .'<h2>Biodata</h2><p>Very Reverend Lewis Asare is a Ghanaian who hails from Hiawu Besease in the Atwima Nwabeagya Municipality of the Ashanti Region. He is the fourth of seven children born to the late Very Rev David Asare and Madam Victoria Kwarteng. Rev. Asare is married to Stella Afrifa Asare, and they are parents to three children.</p>'
            .'<h2>Conclusion</h2><p>A dedicated school headmaster committed to creating a supportive learning environment where students can flourish. He is both a teacher and a mentor. With a wealth of expertise in educational leadership, Very Rev. Lewis Asare has concentrated on developing meaningful curricula, encouraging committed teachers, and guaranteeing a secure and welcoming environment for the development of students. Cognitive abilities, devotion to constant advancement, collaboration, and strong communication skills motivate him to give a high-quality education that inspires young brains.</p>',
        ])->save();
    }

    private function pastHeadmasters(): void
    {
        $page = Page::where('section', 'about')->where('slug', 'past-headmasters')->first();
        if (! $page || StaffGroup::where('page_id', $page->id)->exists()) {
            return;
        }

        // Keep the page's own introduction; the archive portraits move to a gallery below the timeline.
        $intro = '';
        if (preg_match_all('~<p>.*?</p>~s', (string) $page->getRawOriginal('body'), $m)) {
            $intro = implode('', array_slice($m[0], 0, 4));
        }
        $portraits = $page->getMedia('content')->map(fn ($media) => '<figure><img src="/storage/'.$media->getKey().'/'.$media->file_name.'" alt="Archive portrait of a past headmaster"></figure>')->join('');
        $page->forceFill(['body' => $intro.($portraits ? '<h2>Archive portraits</h2><div class="pc-gallery">'.$portraits.'</div>' : '')])->save();

        $group = StaffGroup::create(['name' => 'Past Headmasters', 'slug' => 'page-past-headmasters', 'kind' => 'timeline', 'page_id' => $page->id]);

        $shared = 'He and Mr. Arthur Clark were the last two expatriate headmasters; both are said to have overseen further physical development and established a system of administration that became a model for their successors.';
        $people = [
            ['Reverend Sidney Nelson Pearson', '1949 – 1953', 'The first, founding Headmaster. In his four-year term he set the trail by making discipline and academic work his principal preoccupation. Most of the permanent buildings were constructed and in use before he left. Rev. Pearson is said to have built Prempeh College physically, educationally and morally.'],
            ['Mr. T. C. Sims', '1954 – 1958', 'Successor of Rev. S. N. Pearson. He proved himself an effective school administrator and a level-headed man. He is credited with forming the Cadet Corps in 1955; Rear Admiral Ohene-Kwapong and a host of others who joined the Corps rose to become distinguished commissioned officers in the Ghana Armed Forces. The sixth form was introduced during his time as Headmaster.'],
            ['Rev. A. D. Lewis', 'From 1958', $shared.' He was known as "Osiadan" because he built nine staff bungalows in two years.'],
            ['Mr. Arthur Clark', '1961 – 1963', $shared.' He was known as "Oyeafuo" for the cultivation of large farms. At the end of this era a sound foundation had been laid and Prempeh College was on course for continued progress.'],
            ['Dr. T. A. Osae', '1963 – 1976', 'The first African headmaster. He brought the work of his predecessors to fruition and built on it. Under him Prempeh College was placed on a high pedestal: beating key schools academically for ten consecutive years, producing more students for the medical school than any other school, and becoming National Athletics Champions. He is fondly remembered by Amanfoo, who instituted the annual Pearson-Osae Appreciation Lecture in honour of the main pivots of Prempeh College\'s success story.'],
            ['Mr. M. K. Atiemo', '1977 – 1984', 'His period coincided with severe economic difficulties in the nation, especially between 1980 and 1983. An exodus of teachers and shortages of essential inputs for teaching and learning affected academic work and standards. Just after he was transferred to headquarters in 1984, Prempeh College produced the best "A" level results in the country in 1985.'],
            ['Mr. R. T. Sackey', '1985 – 1995', 'A remarkable recovery took place as Government gradually released funds for the rehabilitation of the College. Dormitories, main classroom blocks and staff bungalows were renovated. Academic work improved significantly, with Prempeh College winning the maiden edition of the National Science & Maths Quiz in 1994. Mr. Sackey is credited with writing the lyrics of the current College Anthem.'],
            ['The Long Interregnum', '1995 – 1998', 'A period described as "The Long Interregnum", which saw two Acting Headmasters, Mr. Twumasi Amponsah and Mr. Fred Asamoah.'],
            ['Mr. Ebenezer Afoakwa Sekyere', '1998 – 2002', 'Took over as substantive Headmaster in 1998. Well known as "Sadam", "Boss" and "Millennium Headmaster", he ensured that the campus resumed its traditional orderliness and cleanliness and embarked on the beautification of the compound. Academic excellence peaked in 2000 when the overall best WAEC student and the best Science student were both from the College.'],
            ['Mr. A. Owusu-Achiaw', '2002 – 2009', 'Took over from Mr. Afoakwa Sekyere and continued his good works. Academic excellence was maintained, and the College matriculated over 400 students to KNUST alone, not to mention other universities. The College won the National Debate in 2004. He also initiated the building of a new Assembly Hall Complex and an 18-unit classroom block, among other projects.'],
            ['Mr. E. K. Yeboah', null, null],
        ];
        foreach ($people as $i => [$name, $term, $bio]) {
            StaffMember::create(['staff_group_id' => $group->id, 'name' => $name, 'role' => $term, 'bio' => $bio, 'sort_order' => $i, 'is_published' => true]);
        }
    }
}
