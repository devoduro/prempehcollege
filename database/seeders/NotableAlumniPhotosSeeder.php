<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\StaffMember;
use Illuminate\Database\Seeder;

/**
 * Portraits for three Notable Alumni, all from Wikimedia Commons under free licences (credited on the page). Other alumni
 * have no freely licensed photo; the school can upload its own in the admin (People > Staff profiles). Safe to run more than once.
 */
class NotableAlumniPhotosSeeder extends Seeder
{
    private const PHOTOS = [
        'John Agyekum Kufuor' => 'kufuor.jpg',
        'Dr. Kwabena Duffuor' => 'duffuor.jpg',
        'Kwesi Ahwoi' => 'ahwoi.jpg',
    ];

    private const CREDIT = '<h2>Photo credits</h2><p>Portraits via Wikimedia Commons: John Agyekum Kufuor, U.S. Army photo by Gregory Jones (public domain); Dr. Kwabena Duffuor, by Easyoscar10, <a href="https://creativecommons.org/licenses/by-sa/4.0/" rel="noopener">CC BY-SA 4.0</a>; Kwesi Ahwoi, U.S. Department of State (public domain).</p>';

    public function run(): void
    {
        foreach (self::PHOTOS as $name => $file) {
            $member = StaffMember::where('name', $name)->first();
            if ($member && $member->getMedia('photo')->isEmpty()) {
                $member->addMedia(database_path('seeders/assets/alumni/'.$file))->preservingOriginal()->toMediaCollection('photo');
            }
        }

        $page = Page::where('section', 'about')->where('slug', 'notable-alumni')->first();
        if ($page && ! str_contains((string) $page->getRawOriginal('body'), 'Photo credits')) {
            $page->forceFill(['body' => $page->getRawOriginal('body').self::CREDIT])->save();
        }
    }
}
