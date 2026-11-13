<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DenominationResource\Pages;
use App\Filament\Support\RichBody;
use App\Models\Denomination;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
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

class DenominationResource extends Resource
{
    protected static ?string $model = Denomination::class;

    protected static ?string $navigationLabel = 'Church life';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Denomination or fellowship')->columnSpan(2)->schema([
                TextInput::make('name')->required()->maxLength(140)->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get, string $operation) {
                        if ($operation === 'create' && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')->required()->maxLength(120)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)
                    ->helperText('Address: /church-life/your-slug'),
                Textarea::make('summary')->rows(2)->maxLength(400),
                RichBody::field('about', 'About'),
                Repeater::make('activities')->label('Church activities')
                    ->schema([
                        TextInput::make('title')->required()->maxLength(120)->placeholder('Sunday worship'),
                        Textarea::make('text')->rows(2)->maxLength(500),
                    ])->maxItems(30)->reorderable()->collapsible()->defaultItems(0)->addActionLabel('Add an activity')->columnSpanFull(),
            ]),
            Section::make('Details')->columnSpan(1)->schema([
                TextInput::make('patron')->label('Chaplain, patron or leader')->maxLength(120),
                TextInput::make('patron_role')->label('Their role')->maxLength(120),
                TextInput::make('meeting_times')->maxLength(190)->placeholder('Sundays, 8am'),
                TextInput::make('venue')->maxLength(190)->placeholder('College Chapel'),
                TextInput::make('sort_order')->numeric()->minValue(0)->default(0),
                Toggle::make('is_active')->label('Show on website')->default(true),
                SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image()->imageEditor()->maxSize(8192)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                SpatieMediaLibraryFileUpload::make('gallery')->collection('gallery')->label('Photo gallery')
                    ->image()->multiple()->reorderable()->maxFiles(30)->maxSize(8192)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('patron')->placeholder('—'),
                TextColumn::make('meeting_times')->label('Meets')->placeholder('—'),
                IconColumn::make('is_active')->boolean()->label('Shown'),
            ])
            ->recordActions([
                Action::make('view')->label('View')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Denomination $record) => url($record->path), shouldOpenInNewTab: true)->visible(fn (Denomination $record) => $record->is_active),
                EditAction::make(), DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDenominations::route('/'),
            'create' => Pages\CreateDenomination::route('/create'),
            'edit' => Pages\EditDenomination::route('/{record}/edit'),
        ];
    }
}
