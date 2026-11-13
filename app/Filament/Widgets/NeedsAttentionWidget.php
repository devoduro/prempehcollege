<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Filament\Resources\AnnouncementResource;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\FacilityResource;
use App\Filament\Resources\HouseResource;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\PostResource;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Facility;
use App\Models\House;
use App\Models\Page;
use App\Models\Post;
use App\Models\StaffMember;
use Filament\Resources\Resource;
use Filament\Widgets\Widget;

/** A to-do list: drafts waiting to be published and records that still lack details. */
class NeedsAttentionWidget extends Widget
{
    protected string $view = 'filament.widgets.needs-attention';

    protected static ?int $sort = 5;

    // Cheap queries: render with the page instead of loading each card separately.
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    protected function getViewData(): array
    {
        $draft = ContentStatus::Draft->value;
        /** @var list<array{0: string, 1: int, 2: class-string<resource>|null}> $items */
        $items = [
            ['Draft pages', Page::where('status', $draft)->count(), PageResource::class],
            ['Draft news stories', Post::where('status', $draft)->count(), PostResource::class],
            ['Draft events', Event::where('status', $draft)->count(), EventResource::class],
            ['Draft announcements', Announcement::where('status', $draft)->count(), AnnouncementResource::class],
            ['Houses with no history yet', House::active()->whereNull('history')->count(), HouseResource::class],
            ['Facilities with no details yet', Facility::active()->whereNull('about')->whereNull('hours')->whereNull('in_charge')->count(), FacilityResource::class],
            ['Staff photos with no name yet', StaffMember::whereNull('name')->whereHas('group', fn ($g) => $g->whereIn('kind', ['leadership', 'timeline', 'staff']))->count(), null],
        ];

        return [
            'items' => collect($items)
                ->filter(fn ($i) => $i[2] === null || $i[2]::canViewAny())
                ->map(fn ($i) => ['label' => $i[0], 'count' => $i[1], 'url' => $i[2] ? $i[2]::getUrl('index') : null])
                ->all(),
        ];
    }
}
