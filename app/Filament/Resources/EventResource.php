<?php

namespace App\Filament\Resources;

use App\Enums\ContentStatus;
use App\Filament\Resources\EventResource\Pages;
use App\Filament\Support\BodyEditor;
use App\Models\Event;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Event')->columnSpan(2)->schema([
                TextInput::make('title')->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get, string $operation) {
                        if ($operation === 'create' && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')->required()->maxLength(120)
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)
                    ->helperText('The event address: /events/your-slug'),
                Textarea::make('excerpt')->rows(2)->maxLength(300)->helperText('One or two sentences for event cards.'),
                ...BodyEditor::make(),
            ]),

            Section::make('When and where')->columnSpan(1)->schema([
                DateTimePicker::make('starts_at')->label('Starts')->required()->native(false)->seconds(false),
                DateTimePicker::make('ends_at')->label('Ends')->native(false)->seconds(false)->after('starts_at'),
                Toggle::make('all_day')->label('All-day event'),
                TextInput::make('venue')->maxLength(190)->placeholder('e.g. Assembly Hall'),
                Select::make('status')->options(PageResource::statusOptions())->default(ContentStatus::Draft->value)->required(),
                DateTimePicker::make('published_at')->label('Show on site from')->native(false)->default(now()),
                SpatieMediaLibraryFileUpload::make('featured')->label('Image or flyer')->collection('featured')
                    ->image()->imageEditor()->maxSize(5120)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured')->collection('featured')->conversion('thumb')->label('')->width(64)->height(40),
                TextColumn::make('title')->searchable()->sortable()->wrap(),
                TextColumn::make('starts_at')->label('Date')->dateTime('j M Y, H:i')->sortable()
                    ->description(fn (Event $r) => $r->isPast() ? 'Past' : 'Upcoming'),
                TextColumn::make('venue')->placeholder('—')->toggleable(),
                TextColumn::make('status')->badge()
                    ->color(fn (ContentStatus $state) => $state === ContentStatus::Published ? 'success' : 'gray')
                    ->formatStateUsing(fn (ContentStatus $state) => ucfirst($state->value)),
            ])
            ->filters([
                TernaryFilter::make('when')->label('When')->placeholder('All')->trueLabel('Upcoming')->falseLabel('Past')
                    ->queries(true: fn ($query) => $query->upcoming(), false: fn ($query) => $query->past()),
                SelectFilter::make('status')->options(PageResource::statusOptions()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view')->label('View')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Event $record) => url($record->path), shouldOpenInNewTab: true)
                    ->visible(fn (Event $record) => $record->status === ContentStatus::Published && ! $record->trashed()),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()])]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
