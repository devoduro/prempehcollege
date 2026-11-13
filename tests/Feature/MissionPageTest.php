<?php

use App\Enums\ContentStatus;
use App\Models\Page;
use Database\Seeders\MissionPageSeeder;

beforeEach(function () {
    $this->withoutVite();
});

function oldMissionPage(): Page
{
    return Page::create(['title' => 'Mission & Values', 'slug' => 'mission-and-values', 'section' => 'about', 'status' => ContentStatus::Published,
        'body' => '<h2>Mission &amp; Values</h2><p>The Estudiar University prepare you to launch your career.</p>'
            .'<h2>Vision</h2><p>An instistution for providing holistic education.</p>'
            .'<h2>Mission</h2><p>To provide holistic education.</p>'
            .'<h3>H.E John Agyekum Kuffour</h3><p>Former President</p><h2>Core Values</h2><ul><li>Belief</li></ul>']);
}

it('rebuilds the page with only the real vision, mission and values and drops the template text', function () {
    oldMissionPage();

    $this->seed(MissionPageSeeder::class);

    $this->get('/about/mission-and-values')->assertOk()
        ->assertSeeInOrder(['Our vision', 'holistic education to all learners to become responsible citizens', 'Our mission', 'skills, training and committed service', 'Our Core Values', 'Belief', 'Integrity', 'Perseverance', 'Teamwork'])
        ->assertDontSee('Estudiar')->assertDontSee('Kuffour');
});

it('is safe to run twice', function () {
    $page = oldMissionPage();

    $this->seed(MissionPageSeeder::class);
    $first = $page->fresh()->body;
    $this->seed(MissionPageSeeder::class);

    expect($page->fresh()->body)->toBe($first)->and($page->fresh()->highlights)->toHaveCount(4);
});

it('shows vision and mission cards from any page using the mission template', function () {
    Page::create(['title' => 'Our Ethos', 'slug' => 'ethos', 'section' => 'about', 'template' => 'mission', 'status' => ContentStatus::Published,
        'body' => '<h2>Vision</h2><p>Big idea.</p><h2>Mission</h2><p>How we do it.</p><h2>Extra</h2><p>More text.</p>',
        'highlights' => [['title' => 'Honesty', 'text' => 'We tell the truth.']]]);

    $this->get('/about/ethos')->assertOk()->assertSee('Big idea.')->assertSee('How we do it.')->assertSee('Honesty')->assertSee('We tell the truth.')->assertSee('More text.');
});

it('copes with a mission page that has no values yet', function () {
    Page::create(['title' => 'Ethos', 'slug' => 'ethos', 'section' => 'about', 'template' => 'mission', 'status' => ContentStatus::Published, 'body' => '<h2>Vision</h2><p>Only a vision.</p>']);

    $this->get('/about/ethos')->assertOk()->assertSee('Only a vision.')->assertDontSee('Our Core Values')->assertDontSee('Our mission');
});
