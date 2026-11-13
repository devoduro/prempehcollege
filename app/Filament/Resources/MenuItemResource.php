<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationLabel = 'Menus';

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $recordTitleAttribute = 'label';

    public const MENUS = ['main' => 'Main menu', 'footer' => 'Footer'];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                Select::make('menu')->options(self::MENUS)->default('main')->required()->live(),
                Select::make('parent_id')->label('Dropdown under')
                    ->options(fn (Get $get, ?MenuItem $record) => MenuItem::where('menu', $get('menu') ?? 'main')->whereNull('parent_id')
                        ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))->orderBy('sort_order')->pluck('label', 'id'))
                    ->placeholder('Top level'),
                TextInput::make('label')->required()->maxLength(80),
                TextInput::make('sort_order')->numeric()->minValue(0)->maxValue(9999)->default(0),
                Select::make('page_id')->label('Links to page')
                    ->options(fn () => Page::orderBy('title')->get()->mapWithKeys(fn (Page $p) => [$p->id => $p->title.' ('.$p->path.')']))
                    ->searchable()
                    ->helperText('Hidden automatically while the page is a draft.'),
                TextInput::make('url')->label('…or links to address')->maxLength(255)
                    ->url()->placeholder('https://')
                    ->helperText('For outside sites. Leave both empty for a dropdown heading.'),
                Toggle::make('is_visible')->label('Show in menu')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('page')->orderBy('menu')->orderByRaw('COALESCE(parent_id, id)')->orderBy('parent_id')->orderBy('sort_order'))
            ->defaultGroup(Group::make('menu')->getTitleFromRecordUsing(fn (MenuItem $r) => self::MENUS[$r->menu] ?? $r->menu))
            ->columns([
                TextColumn::make('label')->formatStateUsing(fn (string $state, MenuItem $record) => ($record->parent_id ? '— ' : '').$state)->searchable(),
                TextColumn::make('page.title')->label('Page')->placeholder('—'),
                TextColumn::make('url')->label('Address')->placeholder('—')->limit(40),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                IconColumn::make('is_visible')->boolean()->label('Shown'),
            ])
            ->filters([SelectFilter::make('menu')->options(self::MENUS)])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
