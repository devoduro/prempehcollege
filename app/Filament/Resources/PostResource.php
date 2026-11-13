<?php

namespace App\Filament\Resources;

use App\Enums\ContentStatus;
use App\Filament\Resources\PostResource\Pages;
use App\Filament\Support\BodyEditor;
use App\Models\Post;
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
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $modelLabel = 'news post';

    protected static ?string $navigationLabel = 'News';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        $slugField = fn () => TextInput::make('slug')->required()->maxLength(120);

        return $schema->columns(3)->components([
            Section::make('Story')->columnSpan(2)->schema([
                TextInput::make('title')->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get, string $operation) {
                        if ($operation === 'create' && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                $slugField()->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)
                    ->helperText('The story address: /news/your-slug'),
                Textarea::make('excerpt')->rows(3)->maxLength(400)->helperText('Short summary shown on news cards and in search results.'),
                ...BodyEditor::make(),
            ]),

            Section::make('Publishing')->columnSpan(1)->schema([
                Select::make('status')->options(PageResource::statusOptions())->default(ContentStatus::Draft->value)->required(),
                DateTimePicker::make('published_at')->label('Publish date')->native(false)->default(now()),
                Select::make('category_id')->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(80)
                            ->live(onBlur: true)->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')->required()->maxLength(80)->unique('categories', 'slug'),
                    ]),
                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()->searchable()->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(80)
                            ->live(onBlur: true)->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')->required()->maxLength(80)->unique('tags', 'slug'),
                    ]),
                SpatieMediaLibraryFileUpload::make('featured')
                    ->label('Featured image')
                    ->collection('featured')
                    ->image()->imageEditor()
                    ->maxSize(5120)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            ]),

            Section::make('Search engines')->columnSpan(2)->collapsed()->schema([
                TextInput::make('seo_title')->label('SEO title')->maxLength(70),
                Textarea::make('seo_description')->label('SEO description')->maxLength(320)->rows(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured')->collection('featured')->conversion('thumb')->label('')->width(64)->height(40),
                TextColumn::make('title')->searchable()->sortable()->wrap()->limit(90),
                TextColumn::make('category.name')->badge()->color('warning')->placeholder('None'),
                TextColumn::make('status')->badge()
                    ->color(fn (ContentStatus $state) => $state === ContentStatus::Published ? 'success' : 'gray')
                    ->formatStateUsing(fn (ContentStatus $state) => ucfirst($state->value)),
                TextColumn::make('published_at')->date('j M Y')->sortable()->label('Published'),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),
                SelectFilter::make('status')->options(PageResource::statusOptions()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view')->label('View')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Post $record) => url($record->path), shouldOpenInNewTab: true)
                    ->visible(fn (Post $record) => $record->status === ContentStatus::Published && ! $record->trashed()),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()]),
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
