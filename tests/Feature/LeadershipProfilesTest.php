<?php

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\StaffGroup;
use App\Models\StaffMember;
use Database\Seeders\LeadershipProfilesSeeder;
use Illuminate\Database\QueryException;

beforeEach(function () {
    $this->withoutVite();
});

function managementPage(array $attrs = []): Page
{
    return Page::create($attrs + ['title' => 'Management', 'slug' => 'management', 'section' => 'leadership', 'status' => ContentStatus::Published]);
}

function profileGroup(Page $page): StaffGroup
{
    $g = StaffGroup::create(['name' => 'Management', 'slug' => 'page-management', 'kind' => 'leadership', 'page_id' => $page->id]);
    StaffMember::create(['staff_group_id' => $g->id, 'name' => 'Very Rev. Lewis Asare', 'role' => 'Headmaster', 'sort_order' => 0]);
    StaffMember::create(['staff_group_id' => $g->id, 'name' => 'Peter Kwanin', 'role' => 'Chief Accountant', 'sort_order' => 1]);
    StaffMember::create(['staff_group_id' => $g->id, 'name' => 'Hidden Person', 'role' => 'Secret', 'sort_order' => 2, 'is_published' => false]);

    return $g;
}

it('shows names and roles as real text on a leadership page', function () {
    profileGroup(managementPage());

    $this->get('/leadership/management')->assertOk()
        ->assertSeeInOrder(['Very Rev. Lewis Asare', 'Headmaster', 'Peter Kwanin', 'Chief Accountant'])
        ->assertSee('2 people')->assertDontSee('Hidden Person');
});

it('describes the page by who is on it and adds structured data', function () {
    profileGroup(managementPage());

    $this->get('/leadership/management')->assertOk()
        ->assertSee('Management: Very Rev. Lewis Asare (Headmaster)', false)
        ->assertSee('"@type":"Person"', false)->assertSee('"jobTitle":"Headmaster"', false);
});

it('falls back to the photo layout when no people are linked', function () {
    managementPage(['body' => '<figure><img src="/storage/1/a.png" alt=""></figure><figure><img src="/storage/2/b.png" alt=""></figure>']);

    $this->get('/leadership/management')->assertOk()->assertSee('is-photo-page', false)->assertDontSee('"@type":"Person"', false);
});

it('finds people by name or role in site search and links to their page', function () {
    profileGroup(managementPage());

    $this->get('/search?q=Kwanin')->assertOk()->assertSee('People')->assertSee('Peter Kwanin')->assertSee(url('/leadership/management'));
    $this->get('/search?q=Accountant')->assertOk()->assertSee('Peter Kwanin');
    $this->get('/search?q=Secret')->assertOk()->assertDontSee('Hidden Person');
});

it('does not search people on pages that are not published', function () {
    profileGroup(managementPage(['status' => ContentStatus::Draft]));

    $this->get('/search?q=Kwanin')->assertOk()->assertDontSee('Peter Kwanin');
});

it('seeds profiles for the leadership pages once, even when run twice', function () {
    foreach (['management', 'administration'] as $slug) {
        Page::create(['title' => ucfirst($slug), 'slug' => $slug, 'section' => 'leadership', 'status' => ContentStatus::Published]);
    }

    $this->seed(LeadershipProfilesSeeder::class);
    $this->seed(LeadershipProfilesSeeder::class);

    expect(StaffGroup::count())->toBe(2)
        ->and(StaffMember::where('role', 'Headmaster')->count())->toBe(1)
        ->and(StaffMember::where('name', 'Peter Kwanin')->value('role'))->toBe('Chief Accountant');
});

it('links a group to one page only', function () {
    $page = managementPage();
    StaffGroup::create(['name' => 'A', 'slug' => 'a', 'page_id' => $page->id]);

    expect(fn () => StaffGroup::create(['name' => 'B', 'slug' => 'b', 'page_id' => $page->id]))->toThrow(QueryException::class);
});
