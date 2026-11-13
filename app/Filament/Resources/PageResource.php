<?php

namespace App\Filament\Resources;

use App\Enums\ContentStatus;
use App\Filament\Resources\PageResource\Pages;
use App\Filament\Support\BodyEditor;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Page')->columnSpan(2)->schema([
                TextInput::make('title')->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get, string $operation) {
                        if ($operation === 'create' && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')->required()->maxLength(120)
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->helperText('Lower-case words joined by hyphens. Changing it changes the page address.')
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('section', $get('section'))),
                ...BodyEditor::make(),
            ]),

            Section::make('Publishing')->columnSpan(1)->schema([
                Select::make('status')->options(self::statusOptions())->default(ContentStatus::Draft->value)->required(),
                Select::make('section')
                    ->options(config('site.sections'))
                    ->placeholder('Site root (e.g. /contact)')
                    ->live(),
                Select::make('template')->options(['default' => 'Standard page', 'activity' => 'Club, team or activity page', 'mission' => 'Mission, vision and values page', 'home' => 'Home page'])->default('default')->required()->live()
                    ->helperText('"Club, team or activity" adds a key-facts strip, a timeline and related news. "Mission, vision and values" shows the sections headed "Vision" and "Mission" in the text as large cards, and the items below as core values.'),
                DateTimePicker::make('published_at')->label('Publish date')->native(false),
                TextInput::make('sort_order')->numeric()->minValue(0)->maxValue(9999)->default(0)
                    ->helperText('Lower numbers appear first in section lists.'),
                SpatieMediaLibraryFileUpload::make('featured')
                    ->label('Featured image')
                    ->collection('featured')
                    ->image()->imageEditor()
                    ->maxSize(5120)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            ]),

            Section::make('Club, team or activity details')->columnSpan(2)
                ->description('Shown on the page above and below the main text. Only facts you have confirmed should go here.')
                ->visible(fn (Get $get) => $get('template') === 'activity')
                ->schema([
                    Repeater::make('facts')->label('Key facts (shown as a strip of numbers)')
                        ->schema([
                            TextInput::make('value')->label('Figure or year')->required()->maxLength(24)->placeholder('1955'),
                            TextInput::make('label')->label('What it is')->required()->maxLength(80)->placeholder('Cadet Corps founded'),
                        ])->columns(2)->maxItems(6)->reorderable()->collapsible()->defaultItems(0)->addActionLabel('Add a fact'),
                    Repeater::make('highlights')->label('Timeline of achievements')
                        ->schema([
                            TextInput::make('year')->maxLength(24)->placeholder('2025'),
                            TextInput::make('title')->required()->maxLength(140),
                            Textarea::make('text')->rows(3)->maxLength(800),
                        ])->maxItems(40)->reorderable()->collapsible()->defaultItems(0)->addActionLabel('Add a milestone'),
                    TextInput::make('news_category')->label('Related news categories')->maxLength(190)
                        ->placeholder('athletics, sports')
                        ->helperText('Category slugs (comma separated). Latest stories and photos from those categories appear on this page.'),
                ]),

            Section::make('Core values')->columnSpan(2)
                ->description('Each value is shown as a numbered card. The description is optional.')
                ->visible(fn (Get $get) => $get('template') === 'mission')
                ->schema([
                    Repeater::make('highlights')->label('Core values')
                        ->schema([
                            TextInput::make('title')->label('Value')->required()->maxLength(60)->placeholder('Integrity'),
                            Textarea::make('text')->label('Description (optional)')->rows(2)->maxLength(300),
                        ])->maxItems(12)->reorderable()->collapsible()->defaultItems(0)->addActionLabel('Add a value'),
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
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->wrap(),
                TextColumn::make('section')->badge()->formatStateUsing(fn (?string $state) => config("site.sections.$state", 'Root'))->placeholder('Root')->sortable(),
                TextColumn::make('status')->badge()
                    ->color(fn (ContentStatus $state) => $state === ContentStatus::Published ? 'success' : 'gray')
                    ->formatStateUsing(fn (ContentStatus $state) => ucfirst($state->value)),
                TextColumn::make('path')->label('Address')->color('gray')->toggleable(),
                TextColumn::make('updated_at')->since()->sortable()->label('Updated'),
            ])
            ->filters([
                SelectFilter::make('section')->options(config('site.sections')),
                SelectFilter::make('status')->options(self::statusOptions()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view')->label('View')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Page $record) => url($record->path), shouldOpenInNewTab: true)
                    ->visible(fn (Page $record) => $record->status === ContentStatus::Published && ! $record->trashed()),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()]),
            ]);
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return collect(ContentStatus::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])->all();
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
