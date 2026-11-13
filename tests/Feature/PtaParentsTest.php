<?php

use App\Models\Page;
use Database\Seeders\PtaParentsSeeder;

it('shows the PTA and parents page', function () {
    $this->seed(PtaParentsSeeder::class);
    $this->seed(PtaParentsSeeder::class);

    $this->get('/about/pta-and-parents')->assertOk()->assertSee('Parent-Teacher Association');
    expect(Page::where('slug', 'pta-and-parents')->count())->toBe(1);
});
