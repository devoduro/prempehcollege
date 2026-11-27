<?php

namespace App\Filament\Osa\Widgets;

use App\Models\OsaAttendance;
use Filament\Widgets\ChartWidget;

class OsaYearChart extends ChartWidget
{
    protected ?string $heading = 'Members by year group';

    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 2];

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $rows = OsaAttendance::selectRaw('year_completed, count(*) as total')->groupBy('year_completed')->orderBy('year_completed')->get();

        return [
            'datasets' => [['label' => 'Check-ins', 'data' => $rows->pluck('total')->all(), 'borderColor' => '#ffc53a', 'backgroundColor' => 'rgba(255,197,58,.25)', 'fill' => true, 'tension' => .3]],
            'labels' => $rows->pluck('year_completed')->map(fn ($y) => (string) $y)->all(),
        ];
    }
}
