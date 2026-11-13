<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffGroupResource\Pages;
use App\Filament\Resources\StaffGroupResource\RelationManagers\MembersRelationManager;
use App\Models\StaffGroup;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class StaffGroupResource extends Resource
{
    protected static ?string $model = StaffGroup::class;

    protected static ?string $modelLabel = 'group';

    protected static ?string $navigationLabel = 'Staff profiles';

    protected static string|UnitEnum|null $navigationGroup = 'People';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(3)->schema([
                TextInput::make('name')->required()->maxLength(80)->helperText('For example "Management" or "Administration".'),
                TextInput::make('slug')->required()->maxLength(80)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true),
                Select::make('kind')->options(['leadership' => 'Leadership page (profile cards)', 'timeline' => 'Timeline (names with years and a biography)', 'staff' => 'Other staff'])->required()->default('leadership'),
                Select::make('page_id')->label('Shown on page')
                    ->relationship('page', 'title')->searchable()->preload()
                    ->unique(ignoreRecord: true)
                    ->helperText('The page that shows these people as profile cards. Add people with the table below after saving.'),
                TextInput::make('sort_order')->numeric()->minValue(0)->default(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Year groups were converted to Prefectship boards; only profile groups are managed here.
            ->modifyQueryUsing(fn ($query) => $query->whereIn('kind', ['leadership', 'timeline', 'staff'])->with('page')->withCount([
                'members',
                'members as photos_count' => fn ($m) => $m->whereHas('media'),
                'members as named_count' => fn ($m) => $m->whereNotNull('name'),
            ]))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('page.title')->label('Page')->placeholder('Not linked'),
                TextColumn::make('members_count')->label('Records'),
                TextColumn::make('photos_count')->label('With photo'),
                TextColumn::make('named_count')->label('With name'),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [MembersRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffGroups::route('/'),
            'create' => Pages\CreateStaffGroup::route('/create'),
            'edit' => Pages\EditStaffGroup::route('/{record}/edit'),
        ];
    }
}
