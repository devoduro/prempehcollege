<?php

return [
    'name' => 'Prempeh College',
    'founded' => 1949,
    'location' => 'Kumasi, Ghana',
    // Facts for the home page, taken from https://en.wikipedia.org/wiki/Prempeh_College
    'facts' => [
        'type' => 'All-boys public boarding senior high school',
        'motto' => 'Suban ne Nimdeɛ',
        'nickname' => 'Amanfoɔ',
        'colours' => 'Green and Gold',
        'houses' => 11,
        'students' => 4520,
        'namesake' => 'Asantehene Sir Osei Tutu Agyeman Prempeh II',
        'founding_bodies' => 'Asanteman, the British Colonial Government, the Methodist Church Ghana and the Presbyterian Church of Ghana',
        'architects' => 'Maxwell Fry and Jane Drew',
        'achievements' => [
            ['value' => '5', 'label' => 'National Science & Maths Quiz titles', 'detail' => '1994, 1996, 2015, 2017 and 2021'],
            ['value' => '5', 'label' => 'National Robotics Championships', 'detail' => 'A record five wins between 2013 and 2021'],
            ['value' => '2016', 'label' => 'Robofest Toyota Innovation Award', 'detail' => 'World Championships in Michigan, USA'],
            ['value' => '2', 'label' => 'National Debate Championships', 'detail' => 'Won in 1997 and 2004'],
            ['value' => '296', 'label' => 'Students admitted to KNUST in 2012', 'detail' => 'The most from any one senior high school'],
        ],
    ],

    'phone' => env('SITE_PHONE', '+233 20 463 1807'),
    'email' => env('SITE_EMAIL'),

    // URL section => display label. Order is the order used in navigation fallbacks and the sitemap.
    'sections' => [
        'about' => 'About',
        'leadership' => 'Leadership',
        'academics' => 'Academics',
        'student-life' => 'Student Life',
    ],

    // Section => page slugs whose first person is featured at the top of the section page.
    'featured_leaders' => [
        'leadership' => ['board-of-governors', 'management'],
    ],

    // Section => news category slugs shown as "latest" on the section page.
    'section_news' => [
        'student-life' => ['nsmq', 'robotics', 'superzonals', 'luv-fm-high-schools-debate', 'sports', 'athletics'],
    ],

    // Section => [[card title, route name, blurb]]: dynamic features listed on the section page.
    'section_extras' => [
        'about' => [['Our Houses', 'houses.index', 'The house system: histories, colours and housemasters.']],
        'leadership' => [['Prefects', 'prefects.index', 'Meet the prefectorial board, current and past.']],
        'academics' => [['Programmes & Course Combinations', 'programmes.index', 'Core subjects and elective combinations for every programme.']],
        'student-life' => [['Our Houses', 'houses.index', 'Houses compete in sports, academics and service.'], ['Prefects', 'prefects.index', 'Student leaders who serve the College community.'], ['Facilities', 'facilities.index', 'Dining hall, clinic, staff common room, chapel and more.'], ['Church life', 'church.index', 'Denominations, worship and church activities.']],
    ],

    // News posts in this category fill the "Headmaster's Desk" column on the home page.
    // Create the category in the admin (News > Category > create) using this slug.
    'desk' => [
        'category_slug' => env('DESK_CATEGORY', 'headmasters-desk'),
        'label' => "Headmaster's Desk",
    ],

    // External portals. Leave a value empty to hide its link everywhere.
    'links' => [
        'online_classes' => env('LINK_ONLINE_CLASSES', 'http://onlineclass.prempehcollege.edu.gh/'),
        'admission' => env('LINK_ADMISSION'),
        'reports' => env('LINK_REPORTS'),
        'transcripts' => env('LINK_TRANSCRIPTS'),
    ],
];
