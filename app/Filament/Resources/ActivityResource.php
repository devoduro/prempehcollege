<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

/** Read-only audit trail: sign-ins, failed sign-ins and content changes. */
class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $modelLabel = 'activity';

    protected static ?string $navigationLabel = 'Activity log';

    protected static string|UnitEnum|null $navigationGroup = 'Security';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('causer'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->dateTime('j M Y, H:i')->label('When')->sortable(),
                TextColumn::make('log_name')->badge()->label('Type'),
                TextColumn::make('description')->label('What')->wrap(),
                TextColumn::make('subject_type')->label('Item')->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—')
                    ->description(fn (Activity $r) => $r->subject_id ? '#'.$r->subject_id : null),
                TextColumn::make('causer.email')->label('By')->placeholder('System / visitor'),
                TextColumn::make('properties')->label('Details')->limit(60)
                    ->formatStateUsing(fn ($state) => json_encode($state, JSON_UNESCAPED_SLASHES))->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([SelectFilter::make('log_name')->label('Type')->options(['default' => 'Content changes', 'auth' => 'Sign-ins'])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListActivities::route('/')];
    }
}
