<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RedirectResource\Pages;
use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-right';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(3)->schema([
                TextInput::make('from_path')->label('Old address')->required()->maxLength(500)
                    ->regex('~^/[^\s]*$~')->placeholder('/old-page/')
                    ->unique(ignoreRecord: true)
                    ->dehydrateStateUsing(fn (string $state) => '/'.trim(strtolower($state), '/').(trim($state, '/') === '' ? '' : '/')),
                TextInput::make('to_path')->label('New address')->required()->maxLength(500)
                    ->regex('~^(/|https://)[^\s]*$~')->placeholder('/about/history')
                    ->helperText('A path on this site, or a full https:// address.'),
                Select::make('status_code')->options([301 => '301 Permanent', 302 => '302 Temporary'])->default(301)->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('hits', 'desc')
            ->columns([
                TextColumn::make('from_path')->label('Old address')->searchable()->copyable(),
                TextColumn::make('to_path')->label('New address')->searchable(),
                TextColumn::make('status_code')->label('Type'),
                TextColumn::make('hits')->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRedirects::route('/'),
            'create' => Pages\CreateRedirect::route('/create'),
            'edit' => Pages\EditRedirect::route('/{record}/edit'),
        ];
    }
}
