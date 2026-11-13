<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DenominationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FallbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PrefectController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

$slug = '[a-z0-9]+(?:-[a-z0-9]+)*';

Route::middleware('throttle:web')->group(function () use ($slug) {
    Route::get('/', HomeController::class)->name('home');

    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/news/{slug}', [NewsController::class, 'show'])->where('slug', $slug)->name('news.show');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{slug}', [EventController::class, 'show'])->where('slug', $slug)->name('events.show');
    Route::get('/events/{slug}/calendar.ics', [EventController::class, 'ics'])->where('slug', $slug)->name('events.ics');

    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/{slug}', [AnnouncementController::class, 'show'])->where('slug', $slug)->name('announcements.show');

    Route::get('/houses', [HouseController::class, 'index'])->name('houses.index');
    Route::get('/houses/{slug}', [HouseController::class, 'show'])->where('slug', $slug)->name('houses.show');

    Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities.index');
    Route::get('/facilities/{slug}', [FacilityController::class, 'show'])->where('slug', $slug)->name('facilities.show');

    Route::get('/church-life', [DenominationController::class, 'index'])->name('church.index');
    Route::get('/church-life/{slug}', [DenominationController::class, 'show'])->where('slug', $slug)->name('church.show');

    Route::get('/programmes', [ProgrammeController::class, 'index'])->name('programmes.index');
    Route::get('/programmes/{slug}', [ProgrammeController::class, 'show'])->where('slug', $slug)->name('programmes.show');

    Route::get('/prefects', [PrefectController::class, 'index'])->name('prefects.index');
    Route::get('/prefects/{slug}', [PrefectController::class, 'show'])->where('slug', $slug)->name('prefects.show');

    // The old year-group photo pages now live under /prefects.
    Route::redirect('/people', '/prefects', 301);
    Route::get('/people/{slug}', fn (string $slug) => redirect('/prefects/'.(ctype_digit($slug) ? ((int) $slug - 1).'-'.$slug : $slug), 301))->where('slug', $slug);

    Route::get('/contact', [ContactController::class, 'show'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

    Route::get('/search', SearchController::class)->middleware('throttle:search')->name('search');
    Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

    Route::get('/{section}/{slug}', [PageController::class, 'show'])
        ->whereIn('section', array_keys(config('site.sections')))
        ->where('slug', $slug)
        ->name('page.section');

    Route::get('/{slug}', [PageController::class, 'root'])->where('slug', $slug)->name('page.root');
});

// Old WordPress URLs (dated news paths, root page slugs) are 301-redirected; everything else is a 404.
Route::fallback(FallbackController::class)->middleware('throttle:web');
