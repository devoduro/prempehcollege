<?php

namespace App\Filament\Osa\Resources;

use App\Filament\Osa\Resources\OsaEventResource\Pages;
use App\Filament\Osa\Resources\OsaEventResource\RelationManagers\AttendancesRelationManager;
use App\Models\OsaEvent;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
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
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class OsaEventResource extends Resource
{
    protected static ?string $model = OsaEvent::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $modelLabel = 'meeting';

    protected static ?string $navigationLabel = 'Meetings & attendance';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Meeting')->columns(2)->schema([
                TextInput::make('title')->required()->maxLength(190)->columnSpanFull()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get, string $operation) {
                        if ($operation === 'create' && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')->label('Registration link')->required()->maxLength(120)
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)
                    ->prefix(url('/old_boys').'/')->columnSpanFull()
                    ->helperText('Members scan the QR code or open this link to register. Changing it after printing posters breaks the printed QR.'),
                DateTimePicker::make('starts_at')->required()->seconds(false),
                DateTimePicker::make('ends_at')->seconds(false)->after('starts_at'),
                TextInput::make('venue')->maxLength(190)->columnSpanFull(),
                Textarea::make('description')->rows(3)->maxLength(2000)->columnSpanFull(),
                Toggle::make('registration_open')->label('QR registration open')->default(true)
                    ->helperText('Turn off to stop new check-ins. It also closes by itself a day after the meeting.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->weight('bold'),
                TextColumn::make('starts_at')->dateTime('j M Y, g:i A')->sortable(),
                TextColumn::make('venue')->placeholder('—'),
                TextColumn::make('attendances_count')->counts('attendances')->label('Present')->badge(),
                IconColumn::make('registration_open')->boolean()->label('Open'),
            ])
            ->recordActions([
                Action::make('qr')->label('QR code')->icon('heroicon-o-qr-code')
                    ->modalHeading(fn (OsaEvent $r) => 'Registration QR: '.$r->title)
                    ->modalContent(fn (OsaEvent $r): View => view('filament.osa.qr', ['event' => $r]))
                    ->modalSubmitAction(false)->modalCancelActionLabel('Close'),
                Action::make('printQr')->label('Print QR')->icon('heroicon-o-printer')
                    ->url(fn (OsaEvent $r) => route('osa.print.qr', $r).'?auto=1', shouldOpenInNewTab: true),
                Action::make('printList')->label('Attendance list')->icon('heroicon-o-clipboard-document-list')
                    ->url(fn (OsaEvent $r) => route('osa.print.attendance', $r), shouldOpenInNewTab: true),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [AttendancesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOsaEvents::route('/'),
            'create' => Pages\CreateOsaEvent::route('/create'),
            'edit' => Pages\EditOsaEvent::route('/{record}/edit'),
        ];
    }
}
