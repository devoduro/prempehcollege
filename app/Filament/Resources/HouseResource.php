<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HouseResource\Pages;
use App\Filament\Resources\HouseResource\RelationManagers\YearsRelationManager;
use App\Filament\Support\RichBody;
use App\Models\House;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class HouseResource extends Resource
{
    protected static ?string $model = House::class;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home-modern';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('House')->columnSpan(2)->schema([
                TextInput::make('name')->required()->maxLength(120)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get, string $operation) {
                        if ($operation === 'create' && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')->required()->maxLength(120)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)
                    ->helperText('Address: /houses/your-slug'),
                TextInput::make('namesake')->label('Named after')->maxLength(190)->placeholder('Person the house is named after'),
                TextInput::make('motto')->maxLength(190),
                Textarea::make('summary')->rows(3)->maxLength(500)->helperText('Short introduction shown on the Houses list and cards.'),
                RichBody::field('about', 'About the house')
                    ->helperText('What the house is, its character and traditions today.'),
                RichBody::field('history', 'History of the house')
                    ->helperText('How the house began and how it grew. Year-by-year details go in the "Year by year" tab below.'),
            ]),
            Section::make('Details')->columnSpan(1)->schema([
                ColorPicker::make('colour')->label('House colour')->regex('/^#[0-9a-fA-F]{6}$/'),
                TextInput::make('housemaster')->label('Current housemaster')->maxLength(120)
                    ->helperText('Shown if no year record exists yet. The latest year in "Year by year" is used when there is one.'),
                TextInput::make('founded_year')->label('Founded')->numeric()->minValue(1900)->maxValue((int) date('Y')),
                TextInput::make('sort_order')->numeric()->minValue(0)->default(0),
                Toggle::make('is_active')->label('Show on website')->default(true),
                SpatieMediaLibraryFileUpload::make('crest')->label('House crest or badge')->collection('crest')
                    ->image()->imageEditor()->maxSize(4096)->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                SpatieMediaLibraryFileUpload::make('cover')->label('Cover photo')->collection('cover')
                    ->image()->imageEditor()->maxSize(8192)->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                SpatieMediaLibraryFileUpload::make('gallery')->label('Photo gallery')->collection('gallery')
                    ->image()->multiple()->reorderable()->maxFiles(30)->maxSize(8192)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('crest')->collection('crest')->conversion('thumb')->label('')->circular()->height(40)->width(40),
                ColorColumn::make('colour')->label(''),
                TextColumn::make('name')->searchable(),
                TextColumn::make('housemaster')->placeholder('—'),
                TextColumn::make('founded_year')->label('Founded')->placeholder('—'),
                IconColumn::make('is_active')->boolean()->label('Shown'),
            ])
            ->recordActions([
                Action::make('view')->label('View')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (House $record) => url($record->path), shouldOpenInNewTab: true)
                    ->visible(fn (House $record) => $record->is_active),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [YearsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHouses::route('/'),
            'create' => Pages\CreateHouse::route('/create'),
            'edit' => Pages\EditHouse::route('/{record}/edit'),
        ];
    }
}
