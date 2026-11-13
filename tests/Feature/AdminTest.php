<?php

use App\Enums\ContentStatus;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Filament\Resources\PostResource\Pages\CreatePost;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Failed;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolesSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

/** An admin with two-factor already set up, so the "set up 2FA" gate does not intercept requests. */
function admin(?string $role = 'super-admin', bool $withTwoFactor = true, bool $active = true): User
{
    $user = User::factory()->create(['is_active' => $active]);
    if ($role) {
        $user->assignRole($role);
    }
    if ($withTwoFactor) {
        $user->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
    }

    // Reload so every column is present, as it is for a user signed in through the browser.
    return $user->fresh();
}

it('sends guests to the admin sign-in page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/login')->assertOk();
});

it('refuses accounts without an admin role or that are deactivated', function () {
    $this->actingAs(admin(role: null))->get('/admin')->assertForbidden();
    $this->actingAs(admin(active: false))->get('/admin')->assertForbidden();
});

it('forces two-factor setup before the panel can be used', function () {
    $this->actingAs(admin(withTwoFactor: false))->get('/admin')
        ->assertRedirect('/admin/multi-factor-authentication/set-up');
});

it('stores the authenticator secret encrypted', function () {
    $user = admin();
    $raw = DB::table('users')->where('id', $user->id)->value('app_authentication_secret');

    expect($raw)->not->toBe('JBSWY3DPEHPK3PXP')->and($user->fresh()->getAppAuthenticationSecret())->toBe('JBSWY3DPEHPK3PXP');
});

it('lets editors manage content but not users, redirects or the audit log', function () {
    $editor = admin('editor');

    foreach (['pages', 'posts', 'menu-items', 'staff-groups', 'contact-messages'] as $slug) {
        $this->actingAs($editor)->get("/admin/$slug")->assertOk();
    }
    foreach (['users', 'redirects', 'activities'] as $slug) {
        $this->actingAs($editor)->get("/admin/$slug")->assertForbidden();
    }
});

it('limits news writers to news', function () {
    $writer = admin('news-writer');

    $this->actingAs($writer)->get('/admin/posts')->assertOk();
    $this->actingAs($writer)->get('/admin/pages')->assertForbidden();
    $this->actingAs($writer)->get('/admin/contact-messages')->assertForbidden();
});

it('gives super admins everything', function () {
    $boss = admin();

    foreach (['pages', 'posts', 'users', 'redirects', 'activities', 'staff-groups'] as $slug) {
        $this->actingAs($boss)->get("/admin/$slug")->assertOk();
    }
});

it('purifies HTML typed into the admin before it is stored', function () {
    $this->actingAs(admin('news-writer'));

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Speech day',
            'slug' => 'speech-day',
            'status' => ContentStatus::Published->value,
            'edit_raw_html' => true,
            'body_raw' => '<p>Welcome</p><script>alert(1)</script><a href="javascript:alert(1)">x</a>',
        ])
        ->call('create')
        ->assertHasNoErrors();

    $body = Post::where('slug', 'speech-day')->value('body');
    expect($body)->toContain('Welcome')->not->toContain('<script')->not->toContain('javascript:');
});

it('saves the visual editor content as HTML', function () {
    $this->actingAs(admin('editor'));

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Open day',
            'slug' => 'open-day',
            'status' => ContentStatus::Draft->value,
            'body' => '<h2>Programme</h2><p>Gates open at <strong>9am</strong>.</p>',
        ])
        ->call('create')
        ->assertHasNoErrors();

    expect(Post::where('slug', 'open-day')->value('body'))->toContain('<h2>Programme</h2>')->toContain('<strong>9am</strong>');
});

it('opens migrated pages with galleries in HTML mode so nothing is lost', function () {
    $this->actingAs(admin('editor'));
    $page = Page::create(['title' => 'NSMQ', 'slug' => 'nsmq', 'status' => ContentStatus::Published,
        'body' => '<div class="pc-gallery"><figure><img src="/storage/1/a.jpg" alt=""></figure></div>']);

    Livewire::test(EditPage::class, ['record' => $page->getKey()])
        ->assertFormSet(['edit_raw_html' => true])
        ->call('save')
        ->assertHasNoErrors();

    expect($page->fresh()->body)->toContain('pc-gallery')->toContain('/storage/1/a.jpg');
});

it('rejects invalid slugs from the admin', function () {
    $this->actingAs(admin('editor'));

    Livewire::test(CreatePost::class)
        ->fillForm(['title' => 'x', 'slug' => 'Bad Slug!', 'status' => 'draft'])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('stops admins from deactivating themselves', function () {
    $boss = admin();
    $this->actingAs($boss);

    Livewire::test(EditUser::class, ['record' => $boss->getKey()])
        ->assertFormFieldIsDisabled('is_active')
        ->assertFormFieldIsDisabled('roles');
});

it('creates admins from the command line with a validated password', function () {
    $this->artisan('admin:create', ['email' => 'head@example.com', '--role' => 'editor'])
        ->expectsQuestion('Password (min 12 chars, upper and lower case, a number)', 'short')
        ->assertFailed();

    expect(User::where('email', 'head@example.com')->exists())->toBeFalse();
});

it('logs failed sign-in attempts without the password', function () {
    event(new Failed('web', null, ['email' => 'x@example.com', 'password' => 'secret-guess']));

    $log = Activity::where('log_name', 'auth')->latest('id')->first();
    expect($log->description)->toBe('Failed sign-in')
        ->and(json_encode($log->properties))->toContain('x@example.com')->not->toContain('secret-guess');
});
