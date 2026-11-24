<?php

namespace App\Filament\Osa\Widgets;

use App\Models\OsaEvent;
use Filament\Widgets\ChartWidget;

class OsaCheckinsChart extends ChartWidget
{
    protected ?string $heading = 'Attendance per meeting (last 10)';

    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 2];

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $events = OsaEvent::withCount('attendances')->orderByDesc('starts_at')->limit(10)->get()->reverse()->values();

        return [
            'datasets' => [['label' => 'Present', 'data' => $events->pluck('attendances_count')->all(), 'backgroundColor' => '#115e03', 'borderRadius' => 4]],
            'labels' => $events->map(fn ($e) => str($e->title)->limit(18)->toString().' ('.$e->starts_at->format('j M').')')->all(),
        ];
    }
}
