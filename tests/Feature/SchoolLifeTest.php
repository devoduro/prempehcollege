<?php

use App\Filament\Resources\HeroSlideResource\Pages\CreateHeroSlide;
use App\Filament\Resources\PrefectBoardResource\Pages\EditPrefectBoard;
use App\Filament\Resources\PrefectBoardResource\RelationManagers\PrefectsRelationManager;
use App\Models\CourseCombination;
use App\Models\HeroSlide;
use App\Models\House;
use App\Models\Prefect;
use App\Models\PrefectBoard;
use App\Models\PrefectPosition;
use App\Models\Programme;
use App\Models\User;
use App\Support\Content;
use Database\Seeders\RolesSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->withoutVite();
});

function makeBoard(array $attrs = []): PrefectBoard
{
    return PrefectBoard::create($attrs + ['name' => '2025/2026 Prefectorial Board', 'slug' => '2025-2026', 'academic_year' => '2025/2026', 'is_current' => true]);
}

it('shows the home slider from active slides in order and hides inactive ones', function () {
    HeroSlide::create(['title' => 'Second slide', 'sort_order' => 2]);
    HeroSlide::create(['title' => 'First slide', 'sort_order' => 1, 'button_label' => 'Go', 'button_url' => '/news']);
    HeroSlide::create(['title' => 'Hidden slide', 'sort_order' => 3, 'is_active' => false]);

    $this->get('/')->assertOk()->assertSeeInOrder(['First slide', 'Second slide'])->assertDontSee('Hidden slide')
        ->assertSee('data-hero-slider', false);
});

it('lists houses, shows a house history and hides inactive houses', function () {
    House::create(['name' => 'Aggrey House', 'slug' => 'aggrey', 'colour' => '#ff0000', 'motto' => 'Lead on', 'history' => '<p>Founded long ago.</p><script>x()</script>', 'housemaster' => 'Mr Mensah']);
    House::create(['name' => 'Secret House', 'slug' => 'secret', 'is_active' => false]);

    $this->get('/houses')->assertOk()->assertSee('Aggrey House')->assertDontSee('Secret House');
    $this->get('/houses/aggrey')->assertOk()->assertSee('Founded long ago.')->assertSee('Mr Mensah')->assertDontSee('<script>x()', false);
    $this->get('/houses/secret')->assertNotFound();
});

it('only ever writes safe colours into house styles', function () {
    $house = House::create(['name' => 'Odd House', 'slug' => 'odd', 'colour' => 'red;} body{display:none']);

    expect($house->cssColour())->toBe('#115e03');
    $this->get('/houses/odd')->assertOk()->assertDontSee('display:none');
});

it('shows programmes with core subjects and elective combinations', function () {
    $p = Programme::create(['name' => 'General Science', 'slug' => 'general-science', 'summary' => 'Science and maths.', 'core_subjects' => ['English Language', 'Core Mathematics']]);
    CourseCombination::create(['programme_id' => $p->id, 'name' => 'Combination A', 'subjects' => ['Physics', 'Chemistry', 'Biology', 'Elective Mathematics']]);

    $this->get('/programmes')->assertOk()->assertSee('General Science')->assertSee('Core Mathematics')->assertSee('Elective Mathematics');
    $this->get('/programmes/general-science')->assertOk()->assertSee('Combination A')->assertSee('Physics');
    $this->get('/programmes/nope')->assertNotFound();
});

it('redirects /prefects to the current board and lists boards for switching', function () {
    makeBoard();
    makeBoard(['name' => '2024/2025 Board', 'slug' => '2024-2025', 'academic_year' => '2024/2025', 'is_current' => false]);

    $this->get('/prefects')->assertRedirect(route('prefects.show', '2025-2026'));
    $this->get('/prefects/2025-2026')->assertOk()->assertSee('2025/2026')->assertSee('2024/2025');
});

it('orders prefects by position rank and supports custom titles', function () {
    $board = makeBoard();
    $senior = PrefectPosition::create(['title' => 'Senior Prefect', 'rank' => 10]);
    $library = PrefectPosition::create(['title' => 'Library Prefect', 'rank' => 50]);
    Prefect::create(['prefect_board_id' => $board->id, 'prefect_position_id' => $library->id, 'name' => 'Kofi Library']);
    Prefect::create(['prefect_board_id' => $board->id, 'prefect_position_id' => $senior->id, 'name' => 'Ama Senior']);
    Prefect::create(['prefect_board_id' => $board->id, 'custom_title' => 'Mascot Captain', 'name' => 'Yaw Custom']);
    Prefect::create(['prefect_board_id' => $board->id, 'prefect_position_id' => $senior->id, 'name' => 'Hidden Person', 'is_published' => false]);

    $this->get('/prefects/2025-2026')->assertOk()
        ->assertSeeInOrder(['Ama Senior', 'Kofi Library', 'Yaw Custom'])
        ->assertSee('Mascot Captain')->assertDontSee('Hidden Person');
});

it('keeps only one current board', function () {
    $a = makeBoard();
    $b = makeBoard(['name' => 'Newer', 'slug' => 'newer', 'academic_year' => '2026/2027', 'is_current' => true]);

    expect($a->fresh()->is_current)->toBeFalse()->and($b->fresh()->is_current)->toBeTrue();
});

it('does not show unpublished boards', function () {
    makeBoard(['is_published' => false]);

    $this->get('/prefects/2025-2026')->assertNotFound();
    $this->get('/prefects')->assertOk()->assertSee('will appear here');
});

it('lets editors build a board with positions and prefects in the admin', function () {
    $this->seed(RolesSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $editor = User::factory()->create(['is_active' => true]);
    $editor->assignRole('editor');
    $editor->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
    $this->actingAs($editor->fresh());

    $board = makeBoard();
    $senior = PrefectPosition::create(['title' => 'Senior Prefect', 'rank' => 10]);
    $house = House::create(['name' => 'Aggrey House', 'slug' => 'aggrey']);

    Livewire::test(PrefectsRelationManager::class, ['ownerRecord' => $board, 'pageClass' => EditPrefectBoard::class])
        ->callTableAction('create', data: [
            'name' => 'Ama Mensah', 'prefect_position_id' => $senior->id, 'house_id' => $house->id,
            'programme_class' => '3 Science 1', 'is_published' => true,
        ])
        ->assertHasNoTableActionErrors();

    $p = Prefect::where('name', 'Ama Mensah')->first();
    expect($p->prefect_board_id)->toBe($board->id)->and($p->displayTitle())->toBe('Senior Prefect');
});

it('rejects unsafe slide button addresses', function () {
    $this->seed(RolesSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $editor = User::factory()->create(['is_active' => true]);
    $editor->assignRole('editor');
    $editor->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
    $this->actingAs($editor->fresh());

    Livewire::test(CreateHeroSlide::class)
        ->fillForm(['title' => 'Test', 'button_label' => 'Go', 'button_url' => 'javascript:alert(1)'])
        ->call('create')
        ->assertHasFormErrors(['button_url']);
});

it('includes houses, programmes and prefect boards in the sitemap', function () {
    House::create(['name' => 'Aggrey House', 'slug' => 'aggrey']);
    Programme::create(['name' => 'Business', 'slug' => 'business']);
    makeBoard();

    $this->get('/sitemap.xml')->assertSee(url('/houses/aggrey'))->assertSee(url('/programmes/business'))->assertSee(url('/prefects/2025-2026'));
});

it('adds a table of contents to long pages', function () {
    $html = '<h2>The Beginning</h2><p>a</p><h2>The Crest</h2><p>b</p><h2>The Beginning</h2><p>c</p>';
    $r = Content::withToc($html);

    expect($r['toc'])->toHaveCount(3)->and($r['html'])->toContain('<h2 id="the-beginning">')->toContain('<h2 id="the-beginning-2">');
});
