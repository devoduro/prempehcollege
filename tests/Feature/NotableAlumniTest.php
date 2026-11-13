<?php

use App\Models\StaffMember;
use Database\Seeders\NotableAlumniSeeder;

it('lists notable alumni once', function () {
    $this->seed(NotableAlumniSeeder::class);
    $this->seed(NotableAlumniSeeder::class);

    $this->get('/about/notable-alumni')->assertOk()->assertSee('John Agyekum Kufuor')->assertSee('Kwaku Sintim-Misa');
    expect(StaffMember::where('name', 'Fritz Baffour')->count())->toBe(1);
});
