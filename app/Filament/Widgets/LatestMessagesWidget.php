<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Widgets\Widget;

/** The newest messages from the website's contact form. */
class LatestMessagesWidget extends Widget
{
    protected string $view = 'filament.widgets.latest-messages';

    protected static ?int $sort = 4;

    // Cheap queries: render with the page instead of loading each card separately.
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return ContactMessageResource::canViewAny();
    }

    protected function getViewData(): array
    {
        return [
            'messages' => ContactMessage::latest()->take(6)->get(),
            'url' => ContactMessageResource::getUrl('index'),
        ];
    }
}
