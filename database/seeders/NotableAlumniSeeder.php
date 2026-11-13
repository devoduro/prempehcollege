<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\StaffGroup;
use App\Models\StaffMember;
use Illuminate\Database\Seeder;

/**
 * Notable Alumni page. Names and roles come from the old website (Kufuor, Agyare and Ohene-Kwapong appear there) and public
 * reports (Wikipedia, MyJoyOnline, GhanaWeb, Yen.com.gh). Public lists overlap only partly and spellings vary, so every
 * entry states only a role and no dates. Names that appeared in a single weak source are left out. Edit or add people
 * under People > Staff profiles (group "Notable Alumni"). Safe to run more than once.
 */
class NotableAlumniSeeder extends Seeder
{
    private const PEOPLE = [
        ['John Agyekum Kufuor', 'Former President of Ghana', 'An Amanfoɔ who became Head of State. The College has honoured him for his contribution to school development.'],
        ['Kwesi Ahwoi', 'Former Minister for the Interior of Ghana', null],
        ['Kwadwo Baah-Wiredu', 'Former Minister of Education', null],
        ['Dr. Kwabena Duffuor', 'Former Governor of the Bank of Ghana and Finance Minister', 'Founder of UniBank Ghana.'],
        ['Snr. Prof. Christian Agyare', 'Vice-Chancellor, KNUST', 'An alumnus who took over as Vice-Chancellor of the Kwame Nkrumah University of Science and Technology.'],
        ['Prof. Abednego Feehi Okoe Amartey', 'Vice-Chancellor, University of Professional Studies, Accra', null],
        ['Rear Admiral Ohene-Kwapong', 'Officer, Ghana Armed Forces', 'One of the Cadet Corps members who rose to become a distinguished commissioned officer.'],
        ['Dr. Kwaku Ohene-Frempong', 'Physician and Amanfoɔ North America honoree', 'Received the Amanfoɔ North America lifetime achievement award in 2017.'],
        ['Kwaku Sintim-Misa (KSM)', 'Satirist and talk show host', null],
        ['Fritz Baffour', 'Television producer', null],
        ['Joseph Amoah', 'Sprinter', 'Represented Ghana at the 2019 World Athletics Championships.'],
    ];

    public function run(): void
    {
        $page = Page::where('section', 'about')->where('slug', 'notable-alumni')->first();
        if (! $page) {
            $page = Page::create([
                'title' => 'Notable Alumni', 'slug' => 'notable-alumni', 'section' => 'about', 'template' => 'default',
                'status' => ContentStatus::Published, 'sort_order' => 47,
                'body' => '<p>For more than seven decades Prempeh College has sent out leaders in government, business, the academy, the armed forces, the arts and sport. Here are some of the Amanfoɔ who carry the College name into national and international life.</p>'
                    .'<p>This list is a selection, not a complete record. Know an old boy who belongs here? <a href="/contact">Tell the school office</a>. You can also read about the <a href="/about/old-boys-association">Old Boys Association</a>.</p>',
            ]);
        }

        if (StaffGroup::where('page_id', $page->id)->exists()) {
            return;
        }
        $group = StaffGroup::create(['name' => 'Notable Alumni', 'slug' => 'page-notable-alumni', 'kind' => 'staff', 'page_id' => $page->id, 'sort_order' => 0]);
        foreach (self::PEOPLE as $i => [$name, $role, $bio]) {
            StaffMember::create(['staff_group_id' => $group->id, 'name' => $name, 'role' => $role, 'bio' => $bio, 'is_published' => true, 'sort_order' => $i]);
        }

        $parent = MenuItem::where('menu', 'main')->whereNull('parent_id')->where('label', 'About')->first();
        if ($parent) {
            MenuItem::firstOrCreate(['menu' => 'main', 'parent_id' => $parent->id, 'url' => '/about/notable-alumni'], ['label' => 'Notable Alumni', 'sort_order' => 98, 'is_visible' => true]);
        }
    }
}
