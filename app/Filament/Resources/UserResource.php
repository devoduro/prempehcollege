<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'admin user';

    protected static string|UnitEnum|null $navigationGroup = 'Security';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('email')->email()->required()->maxLength(190)->unique(ignoreRecord: true),
                TextInput::make('password')->password()->revealable()
                    ->rule(Password::defaults())
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave empty to keep the current password.' : 'At least 12 characters with upper and lower case and a number. Checked against known leaked passwords.'),
                Select::make('roles')->relationship('roles', 'name')
                    ->multiple()->preload()->maxItems(1)
                    ->getOptionLabelFromRecordUsing(fn ($record) => User::ROLES[$record->name] ?? $record->name)
                    ->required()
                    ->disabled(fn (?User $record) => $record?->is(Auth::user()))
                    ->helperText('You cannot change your own role.'),
                Toggle::make('is_active')->label('Account active')->default(true)
                    ->disabled(fn (?User $record) => $record?->is(Auth::user()))
                    ->helperText('Inactive accounts cannot sign in.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('roles.name')->badge(),
                IconColumn::make('two_factor')->label('2FA')->boolean()
                    ->getStateUsing(fn (User $r) => filled($r->getAppAuthenticationSecret())),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('last_login_at')->since()->placeholder('Never')->label('Last sign-in'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('resetTwoFactor')->label('Reset 2FA')->icon('heroicon-o-shield-exclamation')->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('The user will have to set up their authenticator app again at next sign-in. Use this if they lost their phone.')
                    ->visible(fn (User $r) => filled($r->getAppAuthenticationSecret()) && ! $r->is(Auth::user()))
                    ->action(function (User $r) {
                        $r->saveAppAuthenticationSecret(null);
                        $r->saveAppAuthenticationRecoveryCodes(null);
                        activity('auth')->performedOn($r)->log('Two-factor reset by admin');
                        Notification::make()->title('Two-factor authentication reset')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
