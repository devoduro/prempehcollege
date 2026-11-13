<?php

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Support\Content;

beforeEach(function () {
    $this->withoutVite();
});

it('unpublishes pages that still carry template text from another organisation', function () {
    $demo = Page::create(['title' => 'Board of Directors', 'slug' => 'board-of-directors', 'section' => 'leadership', 'status' => ContentStatus::Published,
        'body' => '<p>Ferdinand founded YAF Ghana and studied at Wesleyan University.</p>']);
    $real = Page::create(['title' => 'History', 'slug' => 'history', 'section' => 'about', 'status' => ContentStatus::Published, 'body' => '<p>Founded in 1940.</p>']);

    $this->artisan('content:fix-legacy')->assertSuccessful();

    expect($demo->fresh()->status)->toBe(ContentStatus::Draft)->and($real->fresh()->status)->toBe(ContentStatus::Published);
    $this->get('/leadership/board-of-directors')->assertNotFound();
});

it('rebuilds an empty About overview from existing Prempeh text only', function () {
    Page::create(['title' => 'About', 'slug' => 'overview', 'section' => 'about', 'status' => ContentStatus::Draft, 'body' => null]);

    $this->artisan('content:fix-legacy')->assertSuccessful();

    $this->get('/about')->assertOk()->assertSee('rich and storied history')->assertSee('1940')->assertDontSee('YAF');
});

it('is safe to run repeatedly', function () {
    $page = Page::create(['title' => 'About', 'slug' => 'overview', 'section' => 'about', 'status' => ContentStatus::Draft, 'body' => null]);

    $this->artisan('content:fix-legacy')->assertSuccessful();
    $first = $page->fresh()->body;
    $this->artisan('content:fix-legacy')->assertSuccessful();

    expect($page->fresh()->body)->toBe($first);
});

it('gives images without a description a readable alt from the file name', function () {
    $html = Content::render('<figure><img src="/storage/9/Admin-headmaster-scaled.png" alt=""></figure><img src="/storage/9/2.png" alt="">');

    expect($html)->toContain('alt="Admin headmaster"')->toContain('alt="Photograph"');
});

it('keeps a description that already exists', function () {
    expect(Content::render('<img src="/storage/9/a.png" alt="The Senior Prefect">'))->toContain('alt="The Senior Prefect"');
});

it('lays photo-only pages out as a grid with an enlarge hint', function () {
    Page::create(['title' => 'Management', 'slug' => 'management', 'section' => 'leadership', 'status' => ContentStatus::Published,
        'body' => '<figure><img src="/storage/1/a.png" alt=""></figure><figure><img src="/storage/2/b.png" alt=""></figure>']);
    Page::create(['title' => 'Board of Governors', 'slug' => 'board-of-governors', 'section' => 'leadership', 'status' => ContentStatus::Published,
        'body' => '<figure><img src="/storage/3/c.png" alt=""></figure>']);

    $this->get('/leadership/management')->assertOk()->assertSee('is-photo-page', false)->assertSee('2 photos')->assertDontSee('is-single', false);
    $this->get('/leadership/board-of-governors')->assertOk()->assertSee('is-single', false)->assertDontSee('1 photos');
});
