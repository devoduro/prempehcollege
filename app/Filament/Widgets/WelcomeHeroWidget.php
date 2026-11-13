<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Filament\Resources\AnnouncementResource;
use App\Filament\Resources\ContactMessageResource;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\PostResource;
use App\Models\ContactMessage;
use App\Models\Event;
use App\Models\Page;
use App\Models\Post;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

/** Dark gradient welcome banner with the date, a few figures and the most useful shortcuts (jhs_portal style). */
class WelcomeHeroWidget extends Widget
{
    protected string $view = 'filament.widgets.welcome-hero';

    protected static ?int $sort = 1;

    // Cheap queries: render with the page instead of loading each card separately.
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $name = explode(' ', trim((string) Auth::user()?->name))[0] ?: 'there';

        $actions = array_filter([
            PostResource::canCreate() ? ['label' => 'Write news', 'url' => PostResource::getUrl('create'), 'primary' => true] : null,
            EventResource::canCreate() ? ['label' => 'Add an event', 'url' => EventResource::getUrl('create')] : null,
            AnnouncementResource::canCreate() ? ['label' => 'New announcement', 'url' => AnnouncementResource::getUrl('create')] : null,
            ContactMessageResource::canViewAny() ? ['label' => 'Messages', 'url' => ContactMessageResource::getUrl('index')] : null,
            ['label' => 'View website', 'url' => url('/'), 'external' => true],
        ]);

        return [
            'title' => $greeting.', '.$name,
            'date' => now()->format('l, j F Y'),
            'chips' => [
                Page::where('status', ContentStatus::Published->value)->count().' pages live',
                Post::where('status', ContentStatus::Published->value)->count().' news stories',
                Event::where('status', ContentStatus::Published->value)->upcoming()->count().' upcoming events',
                ContactMessage::whereNull('read_at')->count().' unread messages',
            ],
            'actions' => $actions,
        ];
    }
}
