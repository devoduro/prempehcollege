<?php

return [
    // Folder holding the unzipped wp-content/uploads image files (year folders inside).
    'media_path' => env('WP_MEDIA_PATH', 'C:/xampp/htdocs/prempehcollege_website/migration-work/media'),

    'site_url' => 'https://prempehcollege.edu.gh',

    'report_path' => env('WP_REPORT_PATH', 'C:/xampp/htdocs/prempehcollege_website/migration-work/report.md'),

    // WordPress page IDs that are theme demo pages (shop, duplicate home, demo variants).
    'skip_page_ids' => [9, 748, 750, 783, 3500, 3502, 3510, 3512, 3514],

    // WordPress post IDs that are empty or non-news placeholders.
    'skip_post_ids' => [9538],

    // wp slug => [section, new slug, optional template]. Unlisted slugs import at the root with a warning.
    'pages' => [
        'home-onepage' => [null, 'home', 'home'],
        'about' => ['about', 'overview'],
        'the-crest' => ['about', 'the-crest'],
        'mission-vision' => ['about', 'mission-and-values'],
        'history' => ['about', 'history'],
        'current-headmasters' => ['about', 'current-headmasters'],
        'past-headmasters' => ['about', 'past-headmasters'],
        'management' => ['leadership', 'management'],
        'administration' => ['leadership', 'administration'],
        'board-of-governors' => ['leadership', 'board-of-governors'],
        'board-of-directors' => ['leadership', 'board-of-directors'],
        'hod-hos' => ['leadership', 'hods-and-hos'],
        'welfare-executives' => ['leadership', 'welfare-executives'],
        'old-boys-executives' => ['leadership', 'old-boys-executives'],
        'src-executives' => ['leadership', 'src-executives'],
        'pta-executives' => ['leadership', 'pta-executives'],
        'school-prefects' => ['leadership', 'school-prefects'],
        'school-prefects-2023-2024' => ['leadership', 'school-prefects-2023-2024'],
        'courses' => ['academics', 'courses'],
        'exams-report' => ['academics', 'exams-report'],
        'e-learning-platform' => ['academics', 'e-learning'],
        'faq' => ['academics', 'faq'],
        'nsmq-team' => ['student-life', 'nsmq-team'],
        'debate-club' => ['student-life', 'debate-club'],
        'robotics-club' => ['student-life', 'robotics-club'],
        'school-cadet' => ['student-life', 'school-cadet'],
        'red-cross' => ['student-life', 'red-cross'],
        'sports-athletics' => ['student-life', 'sports-and-athletics'],
        'contact' => [null, 'contact'],
        'college-prep' => [null, 'college-prep'],
        'get-involved' => [null, 'get-involved'],
    ],

    // Text that marks purchased-theme placeholder copy; such pages are imported as drafts for review.
    'demo_markers' => ['lorem ipsum', 'at vero eos', 'consecte tur', 'retail and leadership'],
];
