<?php

use App\Models\Denomination;
use App\Models\Facility;
use Database\Seeders\FacilitiesChurchSeeder;

beforeEach(fn () => $this->seed(FacilitiesChurchSeeder::class));

it('lists facilities and shows each one', function () {
    $this->get('/facilities')->assertOk()->assertSee('Dining Hall and School Kitchen')->assertSee('School Clinic')->assertSee('Staff Common Room')->assertSee('Basketball Court');
    $this->get('/facilities/school-clinic')->assertOk()->assertSee('More details coming soon');
    $this->get('/facilities/nope')->assertNotFound();
});

it('lists church life and denominations', function () {
    $this->get('/church-life')->assertOk()->assertSee('Methodist')->assertSee('Presbyterian');
    $this->get('/church-life/methodist')->assertOk();
});

it('hides inactive entries and is idempotent', function () {
    $this->seed(FacilitiesChurchSeeder::class);
    expect(Denomination::count())->toBe(2);
    Denomination::where('slug', 'methodist')->update(['is_active' => false]);
    $this->get('/church-life/methodist')->assertNotFound();
});

it('puts facilities on the home page and in the sitemap', function () {
    $this->get('/')->assertOk()->assertSee('Campus Facilities');
    $this->get('/sitemap.xml')->assertSee('/facilities/school-clinic')->assertSee('/church-life/methodist');
});

it('purifies facility text', function () {
    $f = Facility::where('slug', 'school-clinic')->first();
    $f->update(['about' => '<p>Ok</p><script>alert(1)</script>']);
    $this->get('/facilities/school-clinic')->assertDontSee('<script>alert(1)', false);
});
