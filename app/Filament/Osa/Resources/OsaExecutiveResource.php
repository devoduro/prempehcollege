<?php

namespace App\Filament\Osa\Resources;

use App\Filament\Osa\Resources\OsaExecutiveResource\Pages;
use App\Models\House;
use App\Models\OsaExecutive;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OsaExecutiveResource extends Resource
{
    protected static ?string $model = OsaExecutive::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'executive';

    protected static ?string $navigationLabel = 'Global executives';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Executive')->columnSpan(2)->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(150),
                TextInput::make('position')->required()->maxLength(150)->placeholder('National President'),
                TextInput::make('year_completed')->label('Year group')->numeric()->minValue(1930)->maxValue((int) date('Y')),
                Select::make('house_id')->label('House')->options(fn () => House::orderBy('name')->pluck('name', 'id'))->searchable(),
                TextInput::make('chapter')->maxLength(100)->placeholder('Global')->helperText('Optional grouping, for example Global, Accra or UK.'),
                TextInput::make('sort_order')->numeric()->minValue(0)->default(0),
                Textarea::make('bio')->rows(4)->maxLength(2000)->columnSpanFull(),
            ]),
            Section::make('Photo')->columnSpan(1)->schema([
                SpatieMediaLibraryFileUpload::make('photo')->collection('photo')->image()->imageEditor()->maxSize(6144)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                Toggle::make('is_active')->label('Show on the website')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('photo')->collection('photo')->conversion('thumb')->label('')->circular()->height(44)->width(44),
                TextColumn::make('name')->searchable()->weight('bold'),
                TextColumn::make('position')->searchable(),
                TextColumn::make('chapter')->placeholder('—'),
                TextColumn::make('year_completed')->label('Year group')->placeholder('—'),
                TextColumn::make('house.name')->label('House')->placeholder('—'),
                IconColumn::make('is_active')->boolean()->label('Shown'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOsaExecutives::route('/'),
            'create' => Pages\CreateOsaExecutive::route('/create'),
            'edit' => Pages\EditOsaExecutive::route('/{record}/edit'),
        ];
    }
}
