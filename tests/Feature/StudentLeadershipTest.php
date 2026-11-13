<?php

use App\Models\Page;
use Database\Seeders\StudentLeadershipSeeder;

it('shows the student leadership and SRC page', function () {
    $this->seed(StudentLeadershipSeeder::class);
    $this->seed(StudentLeadershipSeeder::class);

    $this->get('/leadership/student-leadership-and-src')->assertOk()->assertSee('Ishmael Edusei')->assertSee('Representative Council');
    expect(Page::where('slug', 'student-leadership-and-src')->count())->toBe(1);
});
