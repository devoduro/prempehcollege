<?php

use App\Enums\ContentStatus;
use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Programme;
use App\Models\StaffGroup;
use App\Models\StaffMember;
use App\Models\User;
use Database\Seeders\ActivityPagesSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\StudentLifeSeeder;

beforeEach(function () {
    $this->withoutVite();
});

function activityPage(array $attrs = []): Page
{
    return Page::create($attrs + ['title' => 'Sports & Athletics', 'slug' => 'sports-and-athletics', 'section' => 'student-life',
        'template' => 'activity', 'status' => ContentStatus::Published, 'news_category' => 'athletics']);
}

it('shows key facts, a timeline and only the related news on an activity page', function () {
    activityPage([
        'body' => '<p>A proud record.</p>',
        'facts' => [['value' => '2025', 'label' => 'Title defended']],
        'highlights' => [['year' => '1963', 'title' => 'National Champions', 'text' => 'A great era.']],
    ]);
    $cat = Category::create(['name' => 'Athletics', 'slug' => 'athletics']);
    $other = Category::create(['name' => 'Debate', 'slug' => 'debate']);
    Post::create(['title' => 'Track team wins', 'slug' => 'track', 'category_id' => $cat->id, 'status' => ContentStatus::Published, 'published_at' => now()]);
    Post::create(['title' => 'Debate team wins', 'slug' => 'debate-win', 'category_id' => $other->id, 'status' => ContentStatus::Published, 'published_at' => now()]);

    $this->get('/student-life/sports-and-athletics')->assertOk()
        ->assertSeeInOrder(['2025', 'Title defended', 'A proud record.', 'Highlights', 'National Champions', 'Track team wins'])
        ->assertDontSee('Debate team wins');
});

it('hides empty sections and shows a friendly note when nothing is filled in yet', function () {
    activityPage(['news_category' => null]);

    $this->get('/student-life/sports-and-athletics')->assertOk()
        ->assertSee('will be published here soon')->assertDontSee('Highlights')->assertDontSee('Key facts');
});

it('keeps an unpublished activity page out of public view', function () {
    activityPage(['status' => ContentStatus::Draft, 'slug' => 'maths-olympiad', 'title' => 'Mathematics Olympiad']);

    $this->get('/student-life/maths-olympiad')->assertNotFound();
});

it('fills sports and cadet with only existing facts and prepares a draft maths olympiad page', function () {
    foreach ([['sports-and-athletics', 'Sports & Athletics'], ['school-cadet', 'School Cadet']] as [$slug, $title]) {
        Page::create(['title' => $title, 'slug' => $slug, 'section' => 'student-life', 'status' => ContentStatus::Draft]);
    }

    $this->seed(ActivityPagesSeeder::class);
    $this->seed(ActivityPagesSeeder::class);

    $this->get('/student-life/school-cadet')->assertOk()->assertSee('1955')->assertSee('Rear Admiral Ohene-Kwapong');
    $this->get('/student-life/sports-and-athletics')->assertOk()->assertSee('Opoku Ware')->assertSee('Baba Yara');
    $olympiad = Page::where('slug', 'maths-olympiad')->first();
    expect($olympiad->status)->toBe(ContentStatus::Draft)->and(Page::where('slug', 'maths-olympiad')->count())->toBe(1);
    $this->get('/student-life/maths-olympiad')->assertNotFound();
});

it('shows a single person as a spotlight with their role and links to other leadership pages', function () {
    $page = Page::create(['title' => 'Board of Governors', 'slug' => 'board-of-governors', 'section' => 'leadership', 'status' => ContentStatus::Published, 'body' => '<p>Chaired by Benjamin Acolatse.</p>']);
    Page::create(['title' => 'Management', 'slug' => 'management', 'section' => 'leadership', 'status' => ContentStatus::Published]);
    $g = StaffGroup::create(['name' => 'Board', 'slug' => 'page-board', 'kind' => 'leadership', 'page_id' => $page->id]);
    StaffMember::create(['staff_group_id' => $g->id, 'name' => 'Benjamin Acolatse', 'role' => 'Board Chairman']);

    $this->get('/leadership/board-of-governors')->assertOk()
        ->assertSee('Board Chairman')->assertSee('Benjamin Acolatse')
        ->assertSee('Meet the rest of the College leadership')->assertSee('Management');
});

it('lets editors save facts and highlights from the admin', function () {
    $this->seed(RolesSeeder::class);
    Filament\Facades\Filament::setCurrentPanel(Filament\Facades\Filament::getPanel('admin'));
    $editor = User::factory()->create(['is_active' => true]);
    $editor->assignRole('editor');
    $editor->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
    $this->actingAs($editor->fresh());

    Livewire\Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Debate', 'slug' => 'debate', 'status' => 'published', 'template' => 'activity', 'section' => 'student-life',
            'facts' => [['value' => '2004', 'label' => 'National Debate won']],
            'highlights' => [['year' => '2004', 'title' => 'National Debate', 'text' => 'Winners.']],
            'news_category' => 'debate',
        ])
        ->call('create')->assertHasNoErrors();

    $page = Page::where('slug', 'debate')->first();
    expect($page->facts[0]['value'])->toBe('2004')->and($page->highlights[0]['title'])->toBe('National Debate')->and($page->newsCategorySlugs())->toBe(['debate']);
});

it('features the board chairman and headmaster and counts people on the leadership landing page', function () {
    foreach ([['board-of-governors', 'Board of Governors', 'Benjamin Acolatse', 'Board Chairman'], ['management', 'Management', 'Very Rev. Lewis Asare', 'Headmaster']] as [$slug, $title, $name, $role]) {
        $page = Page::create(['title' => $title, 'slug' => $slug, 'section' => 'leadership', 'status' => ContentStatus::Published]);
        $g = StaffGroup::create(['name' => $title, 'slug' => 'page-'.$slug, 'kind' => 'leadership', 'page_id' => $page->id]);
        StaffMember::create(['staff_group_id' => $g->id, 'name' => $name, 'role' => $role, 'sort_order' => 0]);
    }

    $this->get('/leadership')->assertOk()->assertSee('Meet our leaders')
        ->assertSeeInOrder(['Board Chairman', 'Benjamin Acolatse', 'Headmaster', 'Very Rev. Lewis Asare'])
        ->assertSee('1 person')->assertSee('Meet the team');
});

it('builds the academics hub from programmes, facts and milestones and hides unpublished pages', function () {
    config(['site.links.online_classes' => 'https://classes.example.com/']);
    Page::create(['title' => 'Academics', 'slug' => 'overview', 'section' => 'academics', 'status' => ContentStatus::Published, 'body' => '<p>Intro text.</p>',
        'facts' => [['value' => '1994', 'label' => 'Maiden NSMQ title won']],
        'highlights' => [['year' => '2016', 'title' => 'World Robofest champions', 'text' => 'First in Africa.']]]);
    Page::create(['title' => 'Exams Report', 'slug' => 'exams-report', 'section' => 'academics', 'status' => ContentStatus::Draft]);
    Programme::create(['name' => 'General Science', 'slug' => 'general-science', 'summary' => 'Science and maths.']);

    $this->get('/academics')->assertOk()
        ->assertSeeInOrder(['Maiden NSMQ title won', 'Intro text.', 'Programmes', 'General Science', 'e-Learning', 'Academic milestones', 'World Robofest champions'])
        ->assertSee('https://classes.example.com/')->assertDontSee('Exams Report');
    $this->get('/academics/overview')->assertStatus(301)->assertRedirect('/academics');
});

it('treats the consulting-template sign-up form as demo content', function () {
    $page = Page::create(['title' => 'Exams Report', 'slug' => 'exams-report', 'section' => 'academics', 'status' => ContentStatus::Published,
        'body' => '<ul><li>Essential Package</li><li>Premier Package</li></ul>']);

    $this->artisan('content:fix-legacy')->assertSuccessful();

    expect($page->fresh()->status)->toBe(ContentStatus::Draft);
});

it('builds the student life hub with facts, team cards with a headline fact, and related news', function () {
    Page::create(['title' => 'Student Life', 'slug' => 'overview', 'section' => 'student-life', 'status' => ContentStatus::Published, 'body' => '<p>Something for everyone.</p>',
        'facts' => [['value' => '5', 'label' => 'NSMQ championships']]]);
    Page::create(['title' => 'School Cadet', 'slug' => 'school-cadet', 'section' => 'student-life', 'template' => 'activity', 'status' => ContentStatus::Published,
        'facts' => [['value' => '1955', 'label' => 'Cadet Corps formed']]]);
    Page::create(['title' => 'Debate Club', 'slug' => 'debate-club', 'section' => 'student-life', 'status' => ContentStatus::Draft]);
    $cat = Category::create(['name' => 'NSMQ', 'slug' => 'nsmq']);
    Post::create(['title' => 'NSMQ quarter-final', 'slug' => 'nsmq-qf', 'category_id' => $cat->id, 'status' => ContentStatus::Published, 'published_at' => now()]);

    $this->get('/student-life')->assertOk()
        ->assertSeeInOrder(['NSMQ championships', 'Something for everyone.', 'School Cadet', '1955', 'Cadet Corps formed', 'Latest from Student Life', 'NSMQ quarter-final'])
        ->assertDontSee('Debate Club');
});

it('rebuilds a clean NSMQ page and keeps its photos as a gallery', function () {
    Page::create(['title' => 'NSMQ Team', 'slug' => 'nsmq-team', 'section' => 'student-life', 'status' => ContentStatus::Published, 'body' => '<p>schoub arus auruss Ohane garbled</p>']);

    $this->seed(StudentLifeSeeder::class);
    $this->seed(StudentLifeSeeder::class);

    $this->get('/student-life/nsmq-team')->assertOk()->assertSee('Fifth championship')->assertSee('Mr. Paul Kwadwo Damoah')->assertDontSee('schoub');
    expect(Page::where('slug', 'overview')->where('section', 'student-life')->count())->toBe(1);
});
