<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActivityResource;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

/** Latest sign-ins and content edits. Super admins only, like the Activity log screen. */
class RecentActivityWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-activity';

    protected static ?int $sort = 6;

    // Cheap queries: render with the page instead of loading each card separately.
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return (bool) Auth::user()?->hasRole('super-admin');
    }

    protected function getViewData(): array
    {
        return [
            'entries' => Activity::with('causer')->latest()->take(7)->get(),
            'url' => ActivityResource::getUrl('index'),
        ];
    }
}
