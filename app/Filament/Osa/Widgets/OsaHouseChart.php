<?php

namespace App\Filament\Osa\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OsaHouseChart extends ChartWidget
{
    protected ?string $heading = 'Attendance by house';

    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $rows = DB::table('osa_attendances')->leftJoin('houses', 'houses.id', '=', 'osa_attendances.house_id')
            ->selectRaw('coalesce(houses.name, ?) as house, count(*) as total', ['Unknown'])
            ->groupBy('house')->orderByDesc('total')->get();

        $palette = ['#115e03', '#ffc53a', '#42a5dc', '#da4453', '#967adc', '#f5922f', '#8cc152', '#5b6f8f', '#2c8fc6', '#c12f3e', '#74a83b', '#7d62c4'];

        return [
            'datasets' => [['data' => $rows->pluck('total')->all(), 'backgroundColor' => array_slice(array_pad($palette, max(1, $rows->count()), '#999'), 0, max(1, $rows->count()))]],
            'labels' => $rows->pluck('house')->all(),
        ];
    }
}
