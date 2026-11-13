<?php

use App\Models\Page;
use Database\Seeders\QuizPagesSeeder;

it('shows the National Investment Quiz and Sharks Quiz pages', function () {
    $this->seed(QuizPagesSeeder::class);
    $this->seed(QuizPagesSeeder::class);

    $this->get('/student-life/national-investment-quiz')->assertOk()->assertSee('NIQ champions')->assertSee('First runners-up');
    $this->get('/student-life/sharks-quiz')->assertOk()->assertSee('Keta SHTS');
    expect(Page::where('slug', 'sharks-quiz')->count())->toBe(1);
});
