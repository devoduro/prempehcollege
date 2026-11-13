<?php

use Database\Seeders\OldBoysPageSeeder;

it('shows the Old Boys Association page', function () {
    $this->seed(OldBoysPageSeeder::class);
    $this->seed(OldBoysPageSeeder::class);

    $this->get('/about/old-boys-association')->assertOk()->assertSee('Amanfoɔ')->assertSee('water distribution system');
});
