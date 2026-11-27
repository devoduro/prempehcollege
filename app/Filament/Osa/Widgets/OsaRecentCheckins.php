<?php

namespace App\Filament\Osa\Widgets;

use App\Models\OsaAttendance;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class OsaRecentCheckins extends TableWidget
{
    protected static ?int $sort = 6;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest check-ins';

    public function table(Table $table): Table
    {
        $badge = fn (string $state) => match ($state) {
            'sent' => 'success', 'failed' => 'danger', default => 'gray'
        };

        return $table
            ->query(OsaAttendance::query()->with(['event', 'house'])->latest('checked_in_at')->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('name')->weight('bold'),
                TextColumn::make('event.title')->label('Meeting'),
                TextColumn::make('year_completed')->label('Year group'),
                TextColumn::make('house.name')->label('House')->placeholder('—'),
                TextColumn::make('sms_status')->label('SMS')->badge()->color($badge),
                TextColumn::make('email_status')->label('Email')->badge()->color($badge),
                TextColumn::make('checked_in_at')->label('When')->since(),
            ])
            ->emptyStateHeading('No check-ins yet')
            ->emptyStateDescription('They appear here as members scan the QR code.');
    }
}
