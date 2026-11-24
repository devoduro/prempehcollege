<?php

use App\Enums\ContentStatus;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Programme;
use App\Models\User;
use Database\Seeders\ProgrammeImagesSeeder;
use Database\Seeders\RolesSeeder;
use Filament\Facades\Filament;

beforeEach(function () {
    $this->withoutVite();
});

function dashboardUser(string $role): User
{
    test()->seed(RolesSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create(['is_active' => true, 'name' => 'Ama Mensah']);
    $user->assignRole($role);
    $user->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');

    return $user->fresh();
}

it('shows the portal-style dashboard with greeting, tiles and shortcuts to a super admin', function () {
    ContactMessage::create(['name' => 'Kofi', 'email' => 'k@example.com', 'message' => 'Hello, please call me back.', 'subject' => 'Admissions']);
    Post::create(['title' => 'Draft story', 'slug' => 'draft', 'status' => ContentStatus::Draft]);

    $this->actingAs(dashboardUser('super-admin'))->get('/admin')->assertOk()
        ->assertSee('Ama')->assertSee('Write news')->assertSee('View website')
        ->assertSee('Unread messages')->assertSee('News stories')->assertSee('Needs attention')
        ->assertSee('Latest messages')->assertSee('Admissions')->assertSee('Recent activity');
});

it('hides messages, activity and tiles an editor or news writer may not open', function () {
    $this->actingAs(dashboardUser('news-writer'))->get('/admin')->assertOk()
        ->assertSee('News stories')->assertDontSee('Latest messages')->assertDontSee('Recent activity')->assertDontSee('Unread messages');
});

it('adds STEM as a programme with a photo, and covers for every programme', function () {
    foreach ([['General Science', 'general-science'], ['Business', 'business'], ['Visual Arts', 'visual-arts']] as $i => [$n, $s]) {
        Programme::create(['name' => $n, 'slug' => $s, 'sort_order' => $i]);
    }

    $this->seed(ProgrammeImagesSeeder::class);
    $this->seed(ProgrammeImagesSeeder::class);

    expect(Programme::where('slug', 'stem')->count())->toBe(1)
        ->and(Programme::orderBy('sort_order')->pluck('slug')->first())->toBe('general-science');
    foreach (Programme::all() as $p) {
        expect($p->getMedia('cover'))->toHaveCount(1);
    }
    $this->get('/programmes')->assertOk()->assertSee('STEM')->assertSee('Visual Arts')->assertSee('programme', false);
});

it('keeps the admin login out of the public footer', function () {
    $this->get('/')->assertOk()->assertDontSee('Staff login')->assertDontSee(url('/admin'), false);
    $this->get('/admin/login')->assertOk();
});
