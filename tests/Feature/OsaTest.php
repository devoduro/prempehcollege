<?php

use App\Enums\ContentStatus;
use App\Filament\Osa\Resources\OsaEventResource\Pages\EditOsaEvent;
use App\Filament\Osa\Resources\OsaEventResource\RelationManagers\AttendancesRelationManager;
use App\Mail\OsaAttendanceMail;
use App\Models\House;
use App\Models\OsaAdmin;
use App\Models\OsaAttendance;
use App\Models\OsaEvent;
use App\Models\OsaExecutive;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config(['services.speso_sms.api_key' => 'k', 'services.speso_sms.base_url' => 'https://speso.test/api/v1']);
    Http::fake(['speso.test/*' => Http::response(['success' => true])]);
    Mail::fake();
    $this->house = House::create(['name' => 'Aggrey', 'slug' => 'aggrey', 'is_active' => true]);
    $this->event = OsaEvent::create(['title' => 'AGM 2026', 'venue' => 'School Hall', 'starts_at' => now()->addDay()]);
    $this->payload = fn (array $o = []) => $o + [
        'name' => 'Kofi Mensah', 'year_completed' => 2005, 'house_id' => $this->house->id,
        'email' => 'kofi@example.com', 'phone' => '0244123456',
    ];
});

it('gives every meeting a readable registration url from its title', function () {
    $agm = OsaEvent::create(['title' => 'AGM', 'starts_at' => now()]);
    $again = OsaEvent::create(['title' => 'AGM', 'starts_at' => now()]);

    expect($this->event->slug)->toBe('agm-2026')->and($agm->slug)->toBe('agm')->and($again->slug)->toBe('agm-2')
        ->and($agm->registerUrl())->toEndWith('/old_boys/agm');
});

it('keeps an explicit slug and keeps old token QR codes working', function () {
    $e = OsaEvent::create(['title' => 'Dinner', 'slug' => 'Gala Night', 'starts_at' => now()]);

    expect($e->slug)->toBe('gala-night');
    $this->get('/old-students/meetings/'.$e->token)->assertRedirect($e->registerUrl())->assertStatus(301);
    $this->get('/old-students')->assertRedirect('/old_boys');
});

it('knows when registration is open', function () {
    expect($this->event->isOpen())->toBeTrue();

    $this->event->update(['registration_open' => false]);
    expect($this->event->fresh()->isOpen())->toBeFalse();

    $old = OsaEvent::create(['title' => 'Old', 'starts_at' => now()->subDays(3)]);
    expect($old->isOpen())->toBeFalse();
});

it('shows the registration form with houses loaded', function () {
    House::create(['name' => 'Hidden', 'slug' => 'hidden', 'is_active' => false]);

    $this->get($this->event->registerUrl())->assertOk()->assertSee('AGM 2026')->assertSee('Aggrey')->assertDontSee('Hidden');
});

it('404s for an unknown token', function () {
    $this->get('/old_boys/nope')->assertNotFound();
    $this->post('/old_boys/nope', ($this->payload)())->assertNotFound();
});

it('records attendance, sends an SMS and an email', function () {
    $this->post($this->event->registerUrl(), ($this->payload)())->assertRedirect(route('osa.thanks', $this->event->slug));

    $a = OsaAttendance::firstOrFail();
    expect($a->name)->toBe('Kofi Mensah')->and($a->house_id)->toBe($this->house->id)
        ->and($a->sms_status)->toBe('sent')->and($a->email_status)->toBe('sent')->and($a->source)->toBe('qr');
    Http::assertSent(fn ($r) => $r['phone'] === '0244123456' && str_contains($r['message'], 'AGM 2026') && str_contains($r['message'], 'Kofi'));
    Mail::assertSent(OsaAttendanceMail::class, fn ($m) => $m->hasTo('kofi@example.com'));
});

it('blocks a second check-in with the same phone number, even in another format', function () {
    $this->post($this->event->registerUrl(), ($this->payload)());
    $this->post($this->event->registerUrl(), ($this->payload)(['phone' => '+233 244 123 456', 'name' => 'Someone Else']))
        ->assertRedirect()->assertSessionHas('osa_duplicate', true);

    expect(OsaAttendance::count())->toBe(1);
    Mail::assertSent(OsaAttendanceMail::class, 1);
});

it('lets the same person attend different meetings', function () {
    $second = OsaEvent::create(['title' => 'Dinner', 'starts_at' => now()->addDays(2)]);
    $this->post($this->event->registerUrl(), ($this->payload)());
    $this->post($second->registerUrl(), ($this->payload)());

    expect(OsaAttendance::count())->toBe(2);
});

it('keeps the check-in when the SMS and the email both fail', function () {
    config(['services.speso_sms.base_url' => 'https://broken.test/api/v1']);
    Http::fake(['broken.test/*' => Http::response(['message' => 'No credit'], 500)]);
    Mail::partialMock()->shouldReceive('to')->andThrow(new RuntimeException('smtp down'));

    $this->post($this->event->registerUrl(), ($this->payload)())->assertRedirect();

    $a = OsaAttendance::firstOrFail();
    expect($a->sms_status)->toBe('failed')->and($a->sms_note)->toContain('No credit')
        ->and($a->email_status)->toBe('failed')->and($a->email_note)->toContain('smtp down');
});

it('validates the registration form', function (array $bad, string $field) {
    $this->post($this->event->registerUrl(), ($this->payload)($bad))->assertSessionHasErrors($field);
    expect(OsaAttendance::count())->toBe(0);
})->with([
    'no name' => [['name' => ''], 'name'],
    'bad email' => [['email' => 'nope'], 'email'],
    'bad phone' => [['phone' => 'abc'], 'phone'],
    'future year' => [['year_completed' => 2999], 'year_completed'],
    'ancient year' => [['year_completed' => 1800], 'year_completed'],
    'unknown house' => [['house_id' => 9999], 'house_id'],
    'no house' => [['house_id' => ''], 'house_id'],
]);

it('rejects registrations once the meeting is closed', function () {
    $this->event->update(['registration_open' => false]);

    $this->post($this->event->registerUrl(), ($this->payload)())->assertForbidden();
    $this->get($this->event->registerUrl())->assertOk()->assertSee('closed');
    expect(OsaAttendance::count())->toBe(0);
});

it('silently drops honeypot submissions', function () {
    $this->post($this->event->registerUrl(), ($this->payload)(['website' => 'http://spam']))->assertRedirect();

    expect(OsaAttendance::count())->toBe(0);
    Http::assertNothingSent();
});

it('escapes member names in the email', function () {
    $this->post($this->event->registerUrl(), ($this->payload)(['name' => '<script>alert(1)</script>']));
    $a = OsaAttendance::firstOrFail()->load('event', 'house');

    expect((new OsaAttendanceMail($a))->render())->not->toContain('<script>alert(1)</script>')->toContain('AGM 2026');
});

it('shows the thank-you page', function () {
    $this->get(route('osa.thanks', $this->event->slug))->assertOk()->assertSee('Attendance recorded');
});

it('shows only active executives and upcoming meetings publicly', function () {
    OsaExecutive::create(['name' => 'Visible Person', 'position' => 'President', 'sort_order' => 1]);
    OsaExecutive::create(['name' => 'Hidden Person', 'position' => 'Secretary', 'is_active' => false]);
    OsaEvent::create(['title' => 'Past Meeting', 'starts_at' => now()->subWeek()]);

    $this->get('/old_boys')->assertOk()->assertSee('Visible Person')->assertSee('President')
        ->assertDontSee('Hidden Person')->assertSee('AGM 2026')->assertDontSee('Past Meeting');
});

it('keeps the portal behind its own login', function () {
    $this->get('/osa')->assertRedirect('/osa/login');
    $this->get('/osa/osa-events')->assertRedirect('/osa/login');
});

it('blocks an inactive portal admin', function () {
    $this->actingAs(OsaAdmin::create(['name' => 'A', 'email' => 'a@x.com', 'password' => 'Password12345', 'is_active' => false]), 'osa')
        ->get('/osa/osa-events')->assertForbidden();
});

it('sends school website admins to the portal login', function () {
    $this->actingAs(User::factory()->create(), 'web')->get('/osa/osa-events')->assertRedirect('/osa/login');
});

it('lets an active portal admin open every portal page', function () {
    $this->actingAs(OsaAdmin::create(['name' => 'B', 'email' => 'b@x.com', 'password' => 'Password12345']), 'osa');
    foreach (['/osa', '/osa/osa-events', '/osa/osa-executives', '/osa/osa-admins', '/osa/osa-events/'.$this->event->id.'/edit', '/osa/osa-executives/create'] as $url) {
        $this->get($url)->assertOk();
    }
});

it('does not let portal admins into the school website admin', function () {
    $this->actingAs(OsaAdmin::create(['name' => 'B', 'email' => 'b@x.com', 'password' => 'Password12345']), 'osa')
        ->get('/admin')->assertRedirect();
});

it('creates a portal admin from the command line with a hashed password', function () {
    $this->artisan('osa:create-admin', ['email' => 'New@Osa.com', '--password' => 'GoodPass12345'])->assertSuccessful();

    $a = OsaAdmin::where('email', 'new@osa.com')->firstOrFail();
    expect($a->password)->not->toBe('GoodPass12345')->and(Hash::check('GoodPass12345', $a->password))->toBeTrue();

    $this->artisan('osa:create-admin', ['email' => 'weak@osa.com', '--password' => 'short'])->assertFailed();
    expect(OsaAdmin::where('email', 'weak@osa.com')->exists())->toBeFalse();
});

it('deleting a house keeps attendance records', function () {
    $this->post($this->event->registerUrl(), ($this->payload)());
    $this->house->forceDelete();

    expect(OsaAttendance::firstOrFail()->house_id)->toBeNull();
});

it('keeps print pages behind the portal login', function () {
    $this->get(route('osa.print.qr', $this->event))->assertRedirect('/osa/login');
    $this->get(route('osa.print.attendance', $this->event))->assertRedirect('/osa/login');

    $this->actingAs(User::factory()->create(), 'web')->get(route('osa.print.qr', $this->event))->assertRedirect('/osa/login');
});

it('prints a QR poster with the registration link', function () {
    $this->actingAs(OsaAdmin::create(['name' => 'B', 'email' => 'b@x.com', 'password' => 'Password12345']), 'osa');

    $this->get(route('osa.print.qr', $this->event))->assertOk()->assertSee('AGM 2026')->assertSee('Scan to register your attendance')
        ->assertSee($this->event->registerUrl())->assertSee('<svg', false);
});

it('prints the attendance list with filters', function () {
    $other = House::create(['name' => 'Wesley', 'slug' => 'wesley', 'is_active' => true]);
    $this->post($this->event->registerUrl(), ($this->payload)(['name' => 'Aggrey Alumnus', 'phone' => '0201111111', 'year_completed' => 1999]));
    $this->post($this->event->registerUrl(), ($this->payload)(['name' => 'Wesley Alumnus', 'phone' => '0202222222', 'year_completed' => 2010, 'house_id' => $other->id]));
    $this->actingAs(OsaAdmin::create(['name' => 'B', 'email' => 'b@x.com', 'password' => 'Password12345']), 'osa');
    $url = fn (array $q = []) => route('osa.print.attendance', ['event' => $this->event->id] + $q);

    $this->get($url())->assertOk()->assertSee('Aggrey Alumnus')->assertSee('Wesley Alumnus');
    $this->get($url(['house' => $other->id]))->assertSee('Wesley Alumnus')->assertDontSee('Aggrey Alumnus');
    $this->get($url(['year_to' => 2000]))->assertSee('Aggrey Alumnus')->assertDontSee('Wesley Alumnus');
    $this->get($url(['year_from' => 2005]))->assertSee('Wesley Alumnus')->assertDontSee('Aggrey Alumnus');
    $this->get($url(['search' => '0202222']))->assertSee('Wesley Alumnus')->assertDontSee('Aggrey Alumnus');
    $this->get($url(['sms' => 'failed']))->assertDontSee('Wesley Alumnus')->assertSee('No one matches');
});

it('downloads the filtered attendance as a safe CSV', function () {
    $this->post($this->event->registerUrl(), ($this->payload)(['name' => '=HYPERLINK("http://evil")']));
    $this->actingAs(OsaAdmin::create(['name' => 'B', 'email' => 'b@x.com', 'password' => 'Password12345']), 'osa');

    $res = $this->get(route('osa.print.attendance', ['event' => $this->event->id, 'format' => 'csv']))->assertOk();
    $csv = $res->streamedContent();

    expect($res->headers->get('content-type'))->toContain('text/csv')
        ->and($csv)->toContain('Name,"Year completed"')->toContain("'=HYPERLINK")->not->toContain(',=HYPERLINK');
});

it('renders the dashboard with real figures and charts', function () {
    $this->post($this->event->registerUrl(), ($this->payload)());
    OsaExecutive::create(['name' => 'X', 'position' => 'Pres']);
    $this->actingAs(OsaAdmin::create(['name' => 'Ama Boateng', 'email' => 'b@x.com', 'password' => 'Password12345']), 'osa');

    $this->get('/osa')->assertOk()->assertSee('Ama')->assertSee('Total check-ins')->assertSee('Unique members')
        ->assertSee('SMS delivered')->assertSee('Latest check-ins')->assertSee('AGM 2026');
});

it('renders the attendance tab with real rows and its print and CSV links', function () {
    $this->post($this->event->registerUrl(), ($this->payload)());
    $this->actingAs(OsaAdmin::create(['name' => 'B', 'email' => 'b@x.com', 'password' => 'Password12345']), 'osa');

    $this->get('/osa/osa-events/'.$this->event->id.'/edit')->assertOk();

    Livewire\Livewire::test(AttendancesRelationManager::class, [
        'ownerRecord' => $this->event, 'pageClass' => EditOsaEvent::class,
    ])->assertOk()->assertSee('Kofi Mensah')->assertSee('Print list')->assertSee('Download CSV');
});

it('lists the AGM and other meetings on the Old Boys Association page with registration links', function () {
    $page = Page::create(['section' => 'about', 'slug' => 'old-boys-association', 'title' => 'Old Boys Association', 'body' => '<p>Welcome</p>', 'status' => ContentStatus::Published]);
    $closed = OsaEvent::create(['title' => 'Closed Meeting', 'starts_at' => now()->addDays(3), 'registration_open' => false]);
    $past = OsaEvent::create(['title' => 'Last AGM', 'starts_at' => now()->subMonths(2)]);
    $this->post($past->registerUrl(), ($this->payload)());

    $this->get('/about/old-boys-association')->assertOk()->assertSee('AGM and association meetings')
        ->assertSee('AGM 2026')->assertSee(route('osa.register', $this->event->slug))->assertSee('Register my attendance')
        ->assertSee('Closed Meeting')->assertDontSee(route('osa.register', $closed->slug))
        ->assertSee('Last AGM');
});

it('does not show the meetings block on other pages', function () {
    Page::create(['section' => 'about', 'slug' => 'history', 'title' => 'History', 'body' => '<p>Long ago</p>', 'status' => ContentStatus::Published]);

    $this->get('/about/history')->assertOk()->assertDontSee('AGM and association meetings');
});
