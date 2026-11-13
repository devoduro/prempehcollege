<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static ?string $modelLabel = 'slide';

    protected static ?string $navigationLabel = 'Home slider';

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Slide text')->columnSpan(2)->schema([
                TextInput::make('eyebrow')->maxLength(80)->placeholder('Small line above the title'),
                TextInput::make('title')->required()->maxLength(160),
                TextInput::make('subtitle')->maxLength(300),
                TextInput::make('button_label')->maxLength(40),
                TextInput::make('button_url')->label('Button address')->maxLength(255)
                    ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                        // Only site paths or https links; blocks javascript: and data: URLs.
                        if (filled($value) && ! preg_match('~^(/[^\s]*|https://[^\s]+)$~', (string) $value)) {
                            $fail('Use a path such as /news or a full https:// address.');
                        }
                    })
                    ->helperText('A path on this site (/about/history) or a full https:// address.'),
            ]),
            Section::make('Slide image')->columnSpan(1)->schema([
                SpatieMediaLibraryFileUpload::make('image')->collection('image')->required()
                    ->image()->imageEditor()->imageAspectRatio('16:9')->maxSize(8192)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->helperText('Wide photos work best (1600 x 900 or larger).'),
                TextInput::make('sort_order')->numeric()->minValue(0)->default(0),
                Toggle::make('is_active')->label('Show in slider')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('image')->conversion('thumb')->label('')->width(96)->height(56),
                TextColumn::make('title')->searchable()->wrap(),
                TextColumn::make('eyebrow')->placeholder('—')->toggleable(),
                TextColumn::make('button_label')->label('Button')->placeholder('—'),
                IconColumn::make('is_active')->boolean()->label('Shown'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeroSlides::route('/'),
            'create' => Pages\CreateHeroSlide::route('/create'),
            'edit' => Pages\EditHeroSlide::route('/{record}/edit'),
        ];
    }
}
