<?php

namespace App\Filament\Resources\PrefectBoardResource\RelationManagers;

use App\Models\PrefectPosition;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** The people on a board: pick a position (or type a custom one), name, house, class and photo. */
class PrefectsRelationManager extends RelationManager
{
    protected static string $relationship = 'prefects';

    protected static ?string $title = 'Prefects on this board';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(120),
            Select::make('prefect_position_id')->label('Position')
                ->options(fn () => PrefectPosition::active()->pluck('title', 'id'))
                ->searchable()
                ->createOptionForm([
                    TextInput::make('title')->required()->maxLength(120)->unique('prefect_positions', 'title'),
                    TextInput::make('rank')->numeric()->default(100)->helperText('Lower numbers are shown first.'),
                ])
                ->createOptionUsing(fn (array $data) => PrefectPosition::create($data)->getKey())
                ->helperText('Add new positions on the spot with the + button, or manage them under People > Prefect positions.'),
            TextInput::make('custom_title')->label('…or a custom title')->maxLength(120)->helperText('Overrides the position above when filled.'),
            Select::make('house_id')->label('House')->relationship('house', 'name')->searchable()->preload(),
            TextInput::make('programme_class')->label('Class / programme')->maxLength(80)->placeholder('e.g. 3 Science 1'),
            TextInput::make('sort_order')->numeric()->minValue(0)->default(0),
            Textarea::make('bio')->rows(3)->maxLength(1500)->columnSpanFull(),
            SpatieMediaLibraryFileUpload::make('photo')->collection('photo')
                ->image()->imageEditor()->maxSize(5120)->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            Toggle::make('is_published')->label('Show on website')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['position', 'house', 'media'])
                ->leftJoin('prefect_positions', 'prefect_positions.id', '=', 'prefects.prefect_position_id')
                ->select('prefects.*')
                ->orderByRaw('COALESCE(prefect_positions.`rank`, 999)')->orderBy('prefects.sort_order')->orderBy('prefects.name'))
            ->recordTitleAttribute('name')
            ->columns([
                SpatieMediaLibraryImageColumn::make('photo')->collection('photo')->conversion('thumb')->label('')->circular()->height(48)->width(48),
                TextColumn::make('name')->searchable(),
                TextColumn::make('position.title')->label('Position')->state(fn ($record) => $record->displayTitle())->badge()->color('warning'),
                TextColumn::make('house.name')->label('House')->placeholder('—'),
                TextColumn::make('programme_class')->label('Class')->placeholder('—'),
                IconColumn::make('is_published')->boolean()->label('Shown'),
            ])
            ->headerActions([CreateAction::make()->label('Add prefect')])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
