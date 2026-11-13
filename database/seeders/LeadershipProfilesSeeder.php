<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\StaffGroup;
use App\Models\StaffMember;
use Illuminate\Database\Seeder;

/**
 * Turns the poster-only leadership pages into real profiles. Names and roles were read from the captions printed on
 * each poster (in the same order as the photos on the page) and can be corrected in the admin under People > Staff profiles.
 * Safe to run more than once: existing groups are left alone.
 */
class LeadershipProfilesSeeder extends Seeder
{
    /** page slug => [group name, [[name, role], ...] in the order the posters appear on the page] */
    private const DATA = [
        'management' => ['Management', [
            ['Very Rev. Lewis Asare', 'Headmaster'],
            ['James Owusu-Achiaw', 'Assistant Headmaster, Academics'],
            ['Roland Owusu Sekyere Achiaw', 'Assistant Headmaster, Admin'],
            ['Christine Ababio Trovel', 'Assistant Headmaster, Domestic'],
            ['Arthur Henry Ayirebi', 'Senior House Master'],
            ['Rev. Ebenezer Agyei-Misah', 'College Chaplain'],
            ['Rev. Samuel Bentum Arthur', 'College Counsellor'],
        ]],
        'hods-and-hos' => ['Heads of Department and Heads of Section', [
            ['Beatrice Agyeiwaa', 'ICT HoD'],
            ['Emmanuel Owusu', 'Visual Arts HoD'],
            ['Kwadwo Karikari', 'English Language HoD'],
            ['Samuel Oti', 'Maths HoD'],
            ['Helena Akowuah', 'Business HoD'],
            ['Francis Donkoh', 'General Science HoD'],
            ['Godfred Bonsu', 'General Arts HoD'],
        ]],
        'administration' => ['Administration', [
            ['Peter Kwanin', 'Chief Accountant'],
            ['Edna Sem-Brako', 'Administrative Officer'],
            ['Regina Gyamfi', 'Administrative Officer'],
            ['Adom Kwadwo', 'Accountant'],
            ['Anastasia Catherine Aikins', 'Typist'],
            ['Rose Appiah', 'Typist'],
            ['Edward Kyei Akotia', 'Administrator'],
            ['Severin Asamoah', 'Secretary'],
            ['Stephen Osae', 'Administrative Officer'],
        ]],
        'welfare-executives' => ['Welfare Executives', [
            ['Eric Osei-Owusu', 'Welfare Chairman'],
            ['Ghandi Indira Nketia', 'Welfare Vice Chairperson'],
            ['Felix Fobi', 'Welfare Secretary'],
            ['Emmanuel Owusu', 'Welfare Financial Secretary'],
            ['Eric Kwame Boateng', 'Welfare Organizer'],
        ]],
        'board-of-governors' => ['Board of Governors', [
            ['Benjamin Acolatse', 'Board Chairman'],
        ]],
    ];

    public function run(): void
    {
        foreach (self::DATA as $slug => [$groupName, $people]) {
            $page = Page::where('section', 'leadership')->where('slug', $slug)->first();
            if (! $page || StaffGroup::where('page_id', $page->id)->exists()) {
                continue;
            }

            $photos = $page->getMedia('content')->values();
            $group = StaffGroup::create(['name' => $groupName, 'slug' => 'page-'.$slug, 'kind' => 'leadership', 'page_id' => $page->id, 'sort_order' => 0]);

            foreach ($people as $i => [$name, $role]) {
                $member = StaffMember::create(['staff_group_id' => $group->id, 'name' => $name, 'role' => $role, 'sort_order' => $i, 'is_published' => true]);
                if ($photo = $photos->get($i)) {
                    $member->addMedia($photo->getPath())->preservingOriginal()->withCustomProperties(['alt' => $name.', '.$role])->toMediaCollection('photo');
                }
            }
        }
    }
}
