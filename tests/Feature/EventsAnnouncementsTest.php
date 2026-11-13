<?php

use App\Enums\ContentStatus;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Event;
use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

function makeEvent(array $attrs = []): Event
{
    return Event::create($attrs + ['title' => 'Speech and Prize Giving Day', 'slug' => 'speech-day', 'starts_at' => now()->addWeek()->setTime(9, 0),
        'venue' => 'Assembly Hall', 'status' => ContentStatus::Published, 'published_at' => now()->subDay()]);
}

function makeNotice(array $attrs = []): Announcement
{
    return Announcement::create($attrs + ['title' => 'Reopening date for second term', 'slug' => 'reopening',
        'status' => ContentStatus::Published, 'published_at' => now()->subDay()]);
}

it('shows the news row and the three-column events and announcements block on the home page', function () {
    $desk = Category::create(['name' => "Headmaster's Desk", 'slug' => 'headmasters-desk']);
    Post::create(['title' => 'A word from the Headmaster', 'slug' => 'hm-word', 'category_id' => $desk->id, 'status' => ContentStatus::Published, 'published_at' => now()]);
    Post::create(['title' => 'NSMQ quarter-final win', 'slug' => 'nsmq', 'status' => ContentStatus::Published, 'published_at' => now()->subDay()]);
    makeEvent();
    makeNotice();

    $this->get('/')->assertOk()
        ->assertSeeInOrder(['NSMQ quarter-final win', 'Events & Announcements', "Headmaster's Desk", 'A word from the Headmaster', 'Reopening date for second term', 'Speech and Prize Giving Day'])
        ->assertDontSee('&amp;amp;', false);
});

it('keeps the Headmaster\'s Desk story out of the news row', function () {
    $desk = Category::create(['name' => "Headmaster's Desk", 'slug' => 'headmasters-desk']);
    Post::create(['title' => 'Desk only story', 'slug' => 'desk', 'category_id' => $desk->id, 'status' => ContentStatus::Published, 'published_at' => now()]);

    $html = $this->get('/')->assertOk()->getContent();
    expect(substr_count($html, 'Desk only story'))->toBe(1);
});

it('lists upcoming events, hides past ones unless asked, and drafts always', function () {
    makeEvent();
    makeEvent(['title' => 'Old Founders Day', 'slug' => 'old', 'starts_at' => now()->subMonth()]);
    makeEvent(['title' => 'Draft rehearsal', 'slug' => 'draft', 'status' => ContentStatus::Draft]);

    $this->get('/events')->assertOk()->assertSee('Speech and Prize Giving Day')->assertDontSee('Old Founders Day')->assertDontSee('Draft rehearsal');
    $this->get('/events?when=past')->assertOk()->assertSee('Old Founders Day')->assertDontSee('Speech and Prize Giving Day');
    $this->get('/events/speech-day')->assertOk()->assertSee('Assembly Hall')->assertSee('Add to calendar');
    $this->get('/events/draft')->assertNotFound();
});

it('offers a valid calendar file for an event', function () {
    makeEvent(['title' => 'Open Day, Kumasi; all welcome']);

    $r = $this->get('/events/speech-day/calendar.ics')->assertOk()
        ->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
    expect($r->getContent())->toContain('BEGIN:VEVENT')->toContain('SUMMARY:Open Day\, Kumasi\; all welcome')->toContain('LOCATION:Assembly Hall');
});

it('lists current announcements with pinned first and hides expired ones from lists', function () {
    makeNotice(['title' => 'Older notice', 'slug' => 'older', 'published_at' => now()->subDays(5)]);
    makeNotice(['title' => 'Pinned notice', 'slug' => 'pinned', 'is_pinned' => true, 'published_at' => now()->subDays(9)]);
    makeNotice(['title' => 'Expired notice', 'slug' => 'expired', 'expires_at' => now()->subHour()]);

    $this->get('/announcements')->assertOk()
        ->assertSeeInOrder(['Pinned notice', 'Older notice'])
        ->assertDontSee('Expired notice');

    // Still reachable by link, with a note.
    $this->get('/announcements/expired')->assertOk()->assertSee('expired on');
});

it('paginates the home page columns independently', function () {
    foreach (range(1, 3) as $i) {
        makeNotice(['title' => "Notice $i", 'slug' => "n$i", 'published_at' => now()->subDays($i)]);
    }

    $this->get('/')->assertSee('Notice 1')->assertSee('Notice 2')->assertDontSee('Notice 3');
    $this->get('/?announcements_page=2')->assertSee('Notice 3')->assertDontSee('Notice 1');
});

it('includes events and announcements in search and the sitemap', function () {
    makeEvent();
    makeNotice();

    $this->get('/search?q=Prize')->assertOk()->assertSee('Speech and Prize Giving Day');
    $this->get('/search?q=Reopening')->assertOk()->assertSee('Reopening date for second term');
    $this->get('/sitemap.xml')->assertSee(url('/events/speech-day'))->assertSee(url('/announcements/reopening'));
});

it('purifies event and announcement bodies', function () {
    $e = makeEvent(['body' => '<p>Programme</p><script>alert(1)</script>']);

    expect($e->fresh()->body)->toContain('Programme')->not->toContain('<script');
});
