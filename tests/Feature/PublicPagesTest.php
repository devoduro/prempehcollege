<?php

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\PrefectBoard;
use App\Models\Redirect;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->withoutVite();
});

function publishedPage(array $attrs = []): Page
{
    return Page::create($attrs + ['title' => 'History', 'slug' => 'history', 'section' => 'about',
        'status' => ContentStatus::Published, 'body' => '<p>Founded in 1940.</p>']);
}

it('renders the home page with the latest news', function () {
    Post::create(['title' => 'NSMQ win', 'slug' => 'nsmq-win', 'status' => ContentStatus::Published, 'published_at' => now()->subDay()]);

    $this->get('/')->assertOk()->assertSee('academic excellence')->assertSee('NSMQ win');
});

it('shows a published section page with breadcrumb and body', function () {
    publishedPage();

    $this->get('/about/history')->assertOk()
        ->assertSee('<h1', false)->assertSee('History')->assertSee('Founded in 1940.');
});

it('hides draft pages and unknown sections', function () {
    publishedPage(['status' => ContentStatus::Draft]);

    $this->get('/about/history')->assertNotFound();
    $this->get('/nonsense/history')->assertNotFound();
});

it('lists pages on a section index', function () {
    publishedPage();
    publishedPage(['title' => 'The Crest', 'slug' => 'the-crest']);

    $this->get('/about')->assertOk()->assertSee('History')->assertSee('The Crest');
});

it('301-redirects old WordPress page and dated news URLs', function () {
    publishedPage();
    Redirect::create(['from_path' => '/history/', 'to_path' => '/about/history']);
    Redirect::create(['from_path' => '/2025/08/12/old-story/', 'to_path' => '/news/old-story']);

    $this->get('/history')->assertStatus(301)->assertRedirect(url('/about/history'));
    $this->get('/2025/08/12/old-story/')->assertStatus(301)->assertRedirect(url('/news/old-story'));
    expect(Redirect::where('from_path', '/history/')->value('hits'))->toBe(1);
});

it('returns a friendly 404 for unknown URLs', function () {
    $this->get('/does-not-exist')->assertNotFound()->assertSee('find that page');
});

it('lists, filters and shows news, hiding drafts', function () {
    $cat = Category::create(['name' => 'Sports', 'slug' => 'sports']);
    Post::create(['title' => 'Athletics stars', 'slug' => 'athletics', 'category_id' => $cat->id, 'status' => ContentStatus::Published, 'published_at' => now()]);
    Post::create(['title' => 'Debate win', 'slug' => 'debate', 'status' => ContentStatus::Published, 'published_at' => now()]);
    Post::create(['title' => 'Secret draft', 'slug' => 'draft', 'status' => ContentStatus::Draft]);

    $this->get('/news')->assertOk()->assertSee('Athletics stars')->assertSee('Debate win')->assertDontSee('Secret draft');
    $this->get('/news?category=sports')->assertOk()->assertSee('Athletics stars')->assertDontSee('Debate win');
    $this->get('/news/athletics')->assertOk()->assertSee('Athletics stars');
    $this->get('/news/draft')->assertNotFound();
});

it('builds the menu from published pages only and drops empty dropdowns', function () {
    $page = publishedPage();
    $draft = publishedPage(['title' => 'Red Cross', 'slug' => 'red-cross', 'section' => 'student-life', 'status' => ContentStatus::Draft]);
    $about = MenuItem::create(['menu' => 'main', 'label' => 'About', 'sort_order' => 1]);
    MenuItem::create(['menu' => 'main', 'label' => 'History', 'page_id' => $page->id, 'parent_id' => $about->id]);
    $life = MenuItem::create(['menu' => 'main', 'label' => 'Student Life', 'sort_order' => 2]);
    MenuItem::create(['menu' => 'main', 'label' => 'Red Cross', 'page_id' => $draft->id, 'parent_id' => $life->id]);

    // "Student Life" also appears in the footer's section list, so check the draft child link instead.
    $this->get('/about/history')->assertOk()->assertSee('About')->assertDontSee('Red Cross')
        ->assertDontSee('aria-haspopup="true">'."\n".'                                Student Life', false);
});

it('stores a valid contact message and hashes the IP', function () {
    $this->post('/contact', [
        'name' => 'Ama Mensah', 'email' => 'ama@example.com', 'message' => 'Please send admission details.',
        'form_token' => Crypt::encryptString((string) (time() - 10)),
    ])->assertRedirect(route('contact'));

    $msg = ContactMessage::first();
    expect($msg->name)->toBe('Ama Mensah')->and($msg->ip_hash)->toHaveLength(64)->not->toBe('127.0.0.1');
});

it('rejects the honeypot, instant submits and forged tokens', function () {
    $base = ['name' => 'Bot', 'email' => 'bot@example.com', 'message' => 'Buy cheap things now.'];

    $this->post('/contact', $base + ['website' => 'spam', 'form_token' => Crypt::encryptString((string) (time() - 10))])->assertSessionHasErrors('website');
    $this->post('/contact', $base + ['form_token' => Crypt::encryptString((string) time())])->assertSessionHasErrors('message');
    $this->post('/contact', $base + ['form_token' => 'forged'])->assertSessionHasErrors('message');

    expect(ContactMessage::count())->toBe(0);
});

it('searches titles and bodies, treating wildcards literally', function () {
    publishedPage();
    Post::create(['title' => '100% pass rate', 'slug' => 'pass', 'status' => ContentStatus::Published, 'published_at' => now()]);

    $this->get('/search?q=1940')->assertOk()->assertSee('History');
    // "0%" must match the literal text, not act as a wildcard (History's body contains "0" but not "0%").
    $this->get('/search?q=0%25')->assertOk()->assertSee('100% pass rate')->assertSee('1 result');
    $this->get('/search?q='.str_repeat('a', 101))->assertSessionHasErrors('q');
});

it('serves an XML sitemap with published content only', function () {
    publishedPage();
    publishedPage(['title' => 'Hidden', 'slug' => 'hidden', 'status' => ContentStatus::Draft]);

    $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee(url('/about/history'))->assertDontSee('hidden');
});

it('redirects the old people pages to prefects', function () {
    $this->get('/people')->assertStatus(301)->assertRedirect(url('/prefects'));
    $this->get('/people/2025')->assertStatus(301)->assertRedirect(url('/prefects/2024-2025'));
});

it('moves old "class of" prefect addresses to the academic-year address', function () {
    PrefectBoard::create(['name' => '2026/2027 Prefectorial Board', 'slug' => '2026-2027', 'academic_year' => '2026/2027']);

    $this->get('/prefects/class-of-2027')->assertStatus(301)->assertRedirect(url('/prefects/2026-2027'));
    $this->get('/prefects/2026-2027')->assertOk()->assertSee('2026/2027');
    $this->get('/prefects/class-of-1')->assertNotFound();
});

it('renames class-of boards to academic years when the data migration runs', function () {
    DB::table('prefect_boards')->insert([
        'name' => 'Class of 2027', 'slug' => 'class-of-2027', 'academic_year' => '2027', 'is_current' => false, 'is_published' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    (require base_path('database/migrations/2026_11_13_200000_rename_class_of_boards_to_academic_years.php'))->up();

    $board = PrefectBoard::first();
    expect($board->name)->toBe('2026/2027 Prefectorial Board')->and($board->slug)->toBe('2026-2027')->and($board->academic_year)->toBe('2026/2027');
});
