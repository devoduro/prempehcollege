<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrefectBoardResource\Pages;
use App\Filament\Resources\PrefectBoardResource\RelationManagers\PrefectsRelationManager;
use App\Models\PrefectBoard;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PrefectBoardResource extends Resource
{
    protected static ?string $model = PrefectBoard::class;

    protected static ?string $modelLabel = 'prefect board';

    protected static ?string $navigationLabel = 'Prefectship';

    protected static string|UnitEnum|null $navigationGroup = 'People';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Board')->columnSpan(2)->schema([
                TextInput::make('academic_year')->label('School year')->required()->maxLength(20)->placeholder('2025/2026')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get, string $operation) {
                        if ($operation === 'create' && filled($state)) {
                            $set('name', $state.' Prefectorial Board');
                            // Short, tidy address such as /prefects/2025-2026
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('slug')->required()->maxLength(120)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)
                    ->helperText('Address: /prefects/your-slug'),
                Textarea::make('description')->rows(3)->maxLength(1000),
            ]),
            Section::make('Display')->columnSpan(1)->schema([
                Toggle::make('is_current')->label('Current board')->helperText('Shown first on the Prefects page. Only one board can be current.'),
                Toggle::make('is_published')->label('Show on website')->default(true),
                SpatieMediaLibraryFileUpload::make('group_photo')->label('Group photo')->collection('group_photo')
                    ->image()->imageEditor()->maxSize(8192)->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                SpatieMediaLibraryFileUpload::make('gallery')->label('More photos')->collection('gallery')
                    ->image()->multiple()->reorderable()->maxFiles(40)->maxSize(8192)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('prefects'))
            ->defaultSort('academic_year', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('academic_year')->label('Year')->sortable(),
                TextColumn::make('prefects_count')->label('People'),
                IconColumn::make('is_current')->boolean()->label('Current'),
                IconColumn::make('is_published')->boolean()->label('Shown'),
            ])
            ->recordActions([
                Action::make('view')->label('View')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (PrefectBoard $record) => url($record->path), shouldOpenInNewTab: true)
                    ->visible(fn (PrefectBoard $record) => $record->is_published),
                EditAction::make(),
                DeleteAction::make()->modalDescription('This also deletes every prefect on the board.'),
            ]);
    }

    public static function getRelations(): array
    {
        return [PrefectsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrefectBoards::route('/'),
            'create' => Pages\CreatePrefectBoard::route('/create'),
            'edit' => Pages\EditPrefectBoard::route('/{record}/edit'),
        ];
    }
}
