<?php

use App\Models\Page;
use Database\Seeders\ClubsSocietiesSeeder;

it('shows the clubs and societies page', function () {
    $this->seed(ClubsSocietiesSeeder::class);
    $this->seed(ClubsSocietiesSeeder::class);

    $this->get('/student-life/clubs-and-societies')->assertOk()->assertSee('Robotics Club')->assertSee('Debate Club');
    expect(Page::where('slug', 'clubs-and-societies')->count())->toBe(1);
});
