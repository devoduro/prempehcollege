<?php

namespace App\Filament\Osa\Widgets;

use App\Filament\Osa\Resources\OsaEventResource;
use App\Filament\Osa\Resources\OsaExecutiveResource;
use App\Models\OsaAttendance;
use App\Models\OsaEvent;
use App\Models\OsaExecutive;
use Filament\Widgets\Widget;

/** Coloured metric tiles in the same style as the main admin dashboard. */
class OsaStatTiles extends Widget
{
    protected string $view = 'filament.osa.widgets.stat-tiles';

    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $total = OsaAttendance::count();
        $rate = fn (string $col) => $total ? round(OsaAttendance::where($col, 'sent')->count() / $total * 100).'%' : '–';
        $events = OsaEvent::count();
        $upcoming = OsaEvent::where('starts_at', '>=', now())->count();
        $avg = $events ? round($total / $events) : 0;

        return ['tiles' => [
            ['#42a5dc', '#2c8fc6', 'heroicon-o-qr-code', 'Meetings', (string) $events, $upcoming.' upcoming', OsaEventResource::getUrl('index')],
            ['#8cc152', '#74a83b', 'heroicon-o-check-badge', 'Total check-ins', (string) $total, OsaAttendance::whereDate('checked_in_at', today())->count().' today', OsaEventResource::getUrl('index')],
            ['#f5922f', '#de7a14', 'heroicon-o-users', 'Unique members', (string) OsaAttendance::distinct('phone')->count('phone'), $avg.' average per meeting', OsaEventResource::getUrl('index')],
            ['#967adc', '#7d62c4', 'heroicon-o-user-group', 'Executives', (string) OsaExecutive::where('is_active', true)->count(), 'shown on the website', OsaExecutiveResource::getUrl('index')],
            ['#5b6f8f', '#4a5c7a', 'heroicon-o-chat-bubble-left-right', 'SMS delivered', $rate('sms_status'), OsaAttendance::where('sms_status', 'failed')->count().' failed', OsaEventResource::getUrl('index')],
            ['#da4453', '#c12f3e', 'heroicon-o-envelope', 'Emails delivered', $rate('email_status'), OsaAttendance::where('email_status', 'failed')->count().' failed', OsaEventResource::getUrl('index')],
        ]];
    }
}
