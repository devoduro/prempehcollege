<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Filament\Resources\AnnouncementResource;
use App\Filament\Resources\ContactMessageResource;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\HouseResource;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\PostResource;
use App\Filament\Resources\PrefectBoardResource;
use App\Filament\Resources\ProgrammeResource;
use App\Filament\Resources\StaffGroupResource;
use App\Models\Announcement;
use App\Models\ContactMessage;
use App\Models\Event;
use App\Models\House;
use App\Models\Page;
use App\Models\Post;
use App\Models\PrefectBoard;
use App\Models\Programme;
use App\Models\StaffMember;
use Filament\Resources\Resource;
use Filament\Widgets\Widget;

/** Flat two-tone coloured tiles with an icon block, copied from the jhs_portal dashboard. */
class StatTilesWidget extends Widget
{
    protected string $view = 'filament.widgets.stat-tiles';

    protected static ?int $sort = 2;

    // Cheap queries: render with the page instead of loading each card separately.
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $published = ContentStatus::Published->value;
        $drafts = fn (string $model) => $model::where('status', ContentStatus::Draft->value)->count();
        $unread = ContactMessage::whereNull('read_at')->count();

        /** @var list<array{0: string, 1: string, 2: string, 3: string, 4: string, 5: class-string<resource>}> $tiles */
        $tiles = [
            ['blue', 'heroicon-o-document-text', 'Pages', (string) Page::where('status', $published)->count(), $drafts(Page::class).' drafts', PageResource::class],
            ['green', 'heroicon-o-newspaper', 'News stories', (string) Post::where('status', $published)->count(), $drafts(Post::class).' drafts', PostResource::class],
            ['orange', 'heroicon-o-calendar-days', 'Upcoming events', (string) Event::where('status', $published)->upcoming()->count(), Event::where('status', $published)->past()->count().' past', EventResource::class],
            ['purple', 'heroicon-o-megaphone', 'Announcements', (string) Announcement::current()->count(), 'current notices', AnnouncementResource::class],
            ['red', 'heroicon-o-inbox', 'Unread messages', (string) $unread, ContactMessage::count().' in total', ContactMessageResource::class],
            ['slate', 'heroicon-o-academic-cap', 'Programmes', (string) Programme::active()->count(), 'with course combinations', ProgrammeResource::class],
            ['blue', 'heroicon-o-home-modern', 'Houses', (string) House::active()->count(), 'on the website', HouseResource::class],
            ['green', 'heroicon-o-star', 'Prefect boards', (string) PrefectBoard::where('is_published', true)->count(), 'school years', PrefectBoardResource::class],
            ['orange', 'heroicon-o-user-group', 'Staff profiles', (string) StaffMember::where('is_published', true)->whereHas('group', fn ($g) => $g->whereIn('kind', ['leadership', 'timeline', 'staff']))->count(), 'people with photos and roles', StaffGroupResource::class],
        ];

        // Only show tiles the signed-in user may open.
        return [
            'tiles' => collect($tiles)->filter(fn ($t) => $t[5]::canViewAny())
                ->map(fn ($t) => ['color' => $t[0], 'icon' => $t[1], 'title' => $t[2], 'value' => $t[3], 'sub' => $t[4], 'url' => $t[5]::getUrl('index')])
                ->values()->all(),
        ];
    }
}
