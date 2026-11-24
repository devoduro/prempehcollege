<?php

namespace App\Filament\Osa\Widgets;

use App\Models\OsaAttendance;
use App\Models\OsaEvent;
use Filament\Widgets\Widget;

class OsaWelcome extends Widget
{
    protected string $view = 'filament.osa.widgets.welcome';

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $hour = (int) now()->format('G');
        $next = OsaEvent::where('starts_at', '>=', now())->orderBy('starts_at')->first();

        return [
            'greeting' => $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening'),
            'name' => explode(' ', trim((string) auth('osa')->user()?->name))[0] ?: 'there',
            'next' => $next,
            'today' => OsaAttendance::whereDate('checked_in_at', today())->count(),
        ];
    }
}
