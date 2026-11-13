<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrefectPositionResource\Pages;
use App\Models\PrefectPosition;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PrefectPositionResource extends Resource
{
    protected static ?string $model = PrefectPosition::class;

    protected static ?string $navigationLabel = 'Prefect positions';

    protected static string|UnitEnum|null $navigationGroup = 'People';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('title')->required()->maxLength(120)->unique(ignoreRecord: true),
                TextInput::make('rank')->numeric()->minValue(0)->maxValue(9999)->default(100)->required()
                    ->helperText('Display order on the website: lower numbers first.'),
                Textarea::make('description')->rows(2)->maxLength(500)->columnSpanFull(),
                Toggle::make('is_active')->label('Available when adding prefects')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('prefects'))
            ->reorderable('rank')
            ->defaultSort('rank')
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('rank')->sortable(),
                TextColumn::make('prefects_count')->label('Used by'),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (PrefectPosition $record, DeleteAction $action) {
                        if ($record->prefects()->exists()) {
                            Notification::make()->title('Position is in use')->body('Deactivate it instead, or reassign the prefects first.')->danger()->send();
                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrefectPositions::route('/'),
            'create' => Pages\CreatePrefectPosition::route('/create'),
            'edit' => Pages\EditPrefectPosition::route('/{record}/edit'),
        ];
    }
}
