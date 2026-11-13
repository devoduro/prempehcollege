<?php

namespace App\Filament\Resources;

use App\Enums\ContentStatus;
use App\Filament\Resources\AnnouncementResource\Pages;
use App\Filament\Support\BodyEditor;
use App\Models\Announcement;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Announcement')->columnSpan(2)->schema([
                TextInput::make('title')->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get, string $operation) {
                        if ($operation === 'create' && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')->required()->maxLength(120)
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)
                    ->helperText('The address: /announcements/your-slug'),
                Textarea::make('excerpt')->rows(2)->maxLength(300),
                ...BodyEditor::make(),
                SpatieMediaLibraryFileUpload::make('files')->label('Attachments (PDF or image)')->collection('files')
                    ->multiple()->reorderable()->downloadable()->maxFiles(5)->maxSize(10240)
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']),
            ]),

            Section::make('Publishing')->columnSpan(1)->schema([
                Select::make('status')->options(PageResource::statusOptions())->default(ContentStatus::Draft->value)->required(),
                DateTimePicker::make('published_at')->label('Publish date')->native(false)->default(now()),
                DateTimePicker::make('expires_at')->label('Hide from lists after')->native(false)->after('published_at')
                    ->helperText('Optional. The notice stays reachable by its link.'),
                Toggle::make('is_pinned')->label('Pin to top'),
                SpatieMediaLibraryFileUpload::make('featured')->label('Image or flyer')->collection('featured')
                    ->image()->imageEditor()->maxSize(5120)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                IconColumn::make('is_pinned')->label('')->boolean()->trueIcon('heroicon-s-bookmark')->falseIcon('')->trueColor('warning'),
                TextColumn::make('title')->searchable()->sortable()->wrap(),
                TextColumn::make('published_at')->date('j M Y')->sortable()->label('Published'),
                TextColumn::make('expires_at')->date('j M Y')->label('Expires')->placeholder('Never'),
                TextColumn::make('status')->badge()
                    ->color(fn (ContentStatus $state) => $state === ContentStatus::Published ? 'success' : 'gray')
                    ->formatStateUsing(fn (ContentStatus $state) => ucfirst($state->value)),
            ])
            ->filters([SelectFilter::make('status')->options(PageResource::statusOptions()), TrashedFilter::make()])
            ->recordActions([
                Action::make('view')->label('View')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Announcement $record) => url($record->path), shouldOpenInNewTab: true)
                    ->visible(fn (Announcement $record) => $record->status === ContentStatus::Published && ! $record->trashed()),
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
