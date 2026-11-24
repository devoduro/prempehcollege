<?php

namespace App\Filament\Osa\Resources;

use App\Filament\Osa\Resources\OsaAdminResource\Pages;
use App\Models\OsaAdmin;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;

class OsaAdminResource extends Resource
{
    protected static ?string $model = OsaAdmin::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?string $modelLabel = 'admin';

    protected static ?string $navigationLabel = 'Portal admins';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('email')->email()->required()->maxLength(190)->unique(ignoreRecord: true),
            TextInput::make('password')->password()->revealable()
                ->rule(Password::min(10)->mixedCase()->numbers())
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn (?string $state) => filled($state))
                ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave blank to keep the current password.' : null),
            Toggle::make('is_active')->label('Can sign in')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('email')->searchable(),
            IconColumn::make('is_active')->boolean()->label('Active'),
        ])->recordActions([EditAction::make(), DeleteAction::make()->hidden(fn (OsaAdmin $r) => $r->is(auth('osa')->user()))]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOsaAdmins::route('/'),
            'create' => Pages\CreateOsaAdmin::route('/create'),
            'edit' => Pages\EditOsaAdmin::route('/{record}/edit'),
        ];
    }
}
