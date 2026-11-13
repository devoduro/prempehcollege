<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Filament\Resources\PostResource;
use App\Models\Post;
use Filament\Widgets\ChartWidget;

/** Published news stories per month over the last six months. */
class NewsChartWidget extends ChartWidget
{
    protected ?string $heading = 'News published, last 6 months';

    protected static ?int $sort = 3;

    // Cheap queries: render with the page instead of loading each card separately.
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 2];

    protected ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return PostResource::canViewAny();
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $labels = [];
        $counts = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $labels[] = $month->format('M Y');
            $counts[] = Post::where('status', ContentStatus::Published->value)
                ->whereBetween('published_at', [$month, $month->copy()->endOfMonth()])->count();
        }

        return [
            'datasets' => [['label' => 'Stories', 'data' => $counts, 'backgroundColor' => '#115e03', 'borderRadius' => 4]],
            'labels' => $labels,
        ];
    }

    /** @return array<string, mixed> */
    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
