<?php

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

it('sends hardening headers on every response', function () {
    $r = $this->get('/');
    $r->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

it('rate limits the contact endpoint with Retry-After', function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
    foreach (range(1, 3) as $i) {
        $this->post('/contact')->assertRedirect(); // fails validation, but still counts towards the limit
    }
    $this->post('/contact')->assertStatus(429)->assertHeader('Retry-After');
});

it('never echoes a foreign origin back on the api', function () {
    $r = $this->getJson('/api/v1/ping', ['Origin' => 'https://evil.example']);
    $r->assertOk();
    expect($r->headers->get('Access-Control-Allow-Origin'))->not->toBe('https://evil.example')->not->toBe('*');
});

it('protects state-changing web routes with CSRF middleware', function () {
    // The framework skips CSRF checks while running tests, so assert the middleware is attached.
    $this->get('/'); // boots the HTTP kernel so middleware groups are registered
    $route = app('router')->getRoutes()->getByName('contact.store');
    $stack = app('router')->gatherRouteMiddleware($route);
    expect($stack)->toContain(ValidateCsrfToken::class);
});

it('purifies script tags out of rich text on write', function () {
    $page = Page::create([
        'title' => 'T', 'slug' => 't', 'section' => 'about',
        'body' => '<p>Hello</p><script>alert(1)</script><img src=x onerror=alert(1)>',
        'status' => ContentStatus::Published,
    ]);
    expect($page->fresh()->body)->not->toContain('<script')->not->toContain('onerror');
})->uses(RefreshDatabase::class);

it('is safe against SQL injection in slug lookups', function () {
    Page::create(['title' => 'A', 'slug' => 'a', 'status' => ContentStatus::Published]);
    $payload = "a' OR '1'='1";
    expect(Page::where('slug', $payload)->count())->toBe(0);
})->uses(RefreshDatabase::class);
