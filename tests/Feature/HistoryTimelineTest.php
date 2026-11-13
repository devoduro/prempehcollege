<?php

use Database\Seeders\HistoryTimelineSeeder;

it('shows the history timeline and the correct founding year', function () {
    $this->seed(HistoryTimelineSeeder::class);
    $this->seed(HistoryTimelineSeeder::class);

    $this->get('/about/history-timeline')->assertOk()->assertSee('Founded and opened')->assertSee('75th anniversary');
    expect(config('site.founded'))->toBe(1949);
});
