<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacilityResource\Pages;
use App\Filament\Support\RichBody;
use App\Models\Facility;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;

    protected static ?string $modelLabel = 'facility';

    protected static ?string $pluralModelLabel = 'facilities';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Facility')->columnSpan(2)->schema([
                TextInput::make('name')->required()->maxLength(140)->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get, string $operation) {
                        if ($operation === 'create' && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')->required()->maxLength(120)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)
                    ->helperText('Address: /facilities/your-slug'),
                Select::make('category')->options(Facility::CATEGORIES)->default('general')->required(),
                Textarea::make('summary')->rows(2)->maxLength(400)->helperText('Short introduction shown on the facilities list.'),
                RichBody::field('about', 'About this facility'),
                TagsInput::make('services')->label('Services and features')->placeholder('Type one and press Enter')
                    ->helperText('For example "Three meals a day" or "Nurse on duty". Shown as a checklist.')->columnSpanFull(),
            ]),
            Section::make('Practical details')->columnSpan(1)->schema([
                TextInput::make('hours')->label('Opening hours')->maxLength(190)->placeholder('Mon to Fri, 7am to 5pm'),
                TextInput::make('location')->maxLength(190)->placeholder('Where on campus'),
                TextInput::make('in_charge')->label('Person in charge')->maxLength(120),
                TextInput::make('in_charge_role')->label('Their role')->maxLength(120)->placeholder('e.g. Catering Officer, School Nurse'),
                TextInput::make('contact')->maxLength(120),
                TextInput::make('sort_order')->numeric()->minValue(0)->default(0),
                Toggle::make('is_planned')->label('Planned, not built yet')->helperText('Shown with a "Planned" label.'),
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
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('thumb')->label('')->width(64)->height(40),
                TextColumn::make('name')->searchable()->wrap(),
                TextColumn::make('category')->badge()->formatStateUsing(fn (string $state) => Facility::CATEGORIES[$state] ?? $state),
                TextColumn::make('in_charge')->label('In charge')->placeholder('—'),
                IconColumn::make('is_planned')->boolean()->label('Planned'),
                IconColumn::make('is_active')->boolean()->label('Shown'),
            ])
            ->filters([SelectFilter::make('category')->options(Facility::CATEGORIES)])
            ->recordActions([
                Action::make('view')->label('View')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Facility $record) => url($record->path), shouldOpenInNewTab: true)->visible(fn (Facility $record) => $record->is_active),
                EditAction::make(), DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacilities::route('/'),
            'create' => Pages\CreateFacility::route('/create'),
            'edit' => Pages\EditFacility::route('/{record}/edit'),
        ];
    }
}
