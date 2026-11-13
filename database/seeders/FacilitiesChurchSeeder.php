<?php

namespace Database\Seeders;

use App\Models\Denomination;
use App\Models\Facility;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * Starting entries for Facilities and Church life. Only generic, safe descriptions are used; hours, staff and photos are
 * left for the school to fill in (the public pages say "more details coming soon"). Safe to run more than once.
 */
class FacilitiesChurchSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            ['dining-hall-and-kitchen', 'Dining Hall and School Kitchen', 'dining', 'Where boarders are fed each day, from the school kitchen to the dining hall.', false],
            ['school-clinic', 'School Clinic', 'health', 'First-aid and basic health care for students and staff on campus.', false],
            ['staff-common-room', 'Staff Common Room', 'staff', 'A shared space where teachers and staff meet, plan and rest between lessons.', false],
            ['college-chapel', 'College Chapel', 'worship', 'The College chapel, a place of worship and reflection that has been part of campus life since the early years.', false],
        ];
        foreach ($facilities as $i => [$slug, $name, $cat, $summary, $planned]) {
            Facility::firstOrCreate(['slug' => $slug], ['name' => $name, 'category' => $cat, 'summary' => $summary, 'is_planned' => $planned, 'is_active' => true, 'sort_order' => $i]);
        }

        $groups = [
            ['methodist', 'Methodist', 'Prempeh College was founded in partnership with the Methodist Church Ghana.'],
            ['presbyterian', 'Presbyterian', 'Prempeh College was founded in partnership with the Presbyterian Church of Ghana.'],
        ];
        foreach ($groups as $i => [$slug, $name, $summary]) {
            Denomination::firstOrCreate(['slug' => $slug], ['name' => $name, 'summary' => $summary, 'is_active' => true, 'sort_order' => $i]);
        }

        $parent = MenuItem::where('menu', 'main')->whereNull('parent_id')->where('label', 'Student Life')->first();
        if ($parent) {
            foreach ([['Facilities', '/facilities'], ['Church life', '/church-life']] as $i => [$label, $url]) {
                MenuItem::firstOrCreate(['menu' => 'main', 'parent_id' => $parent->id, 'url' => $url], ['label' => $label, 'sort_order' => 95 + $i, 'is_visible' => true]);
            }
        }
    }
}
