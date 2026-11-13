<?php

namespace App\Filament\Resources\HouseResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

/** One record per school year: who led the house, what it achieved, and the photos from that year. */
class YearsRelationManager extends RelationManager
{
    protected static string $relationship = 'years';

    protected static ?string $title = 'Year by year';

    public function form(Schema $schema): Schema
    {
        $photo = fn (string $name, string $label) => SpatieMediaLibraryFileUpload::make($name)->label($label)->collection($name)
            ->image()->imageEditor()->maxSize(6144)->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']);

        return $schema->components([
            Section::make('School year')->columns(2)->schema([
                TextInput::make('academic_year')->label('School year')->required()->maxLength(9)->placeholder('2025/2026')
                    ->regex('/^\d{4}\/\d{4}$/')->validationMessages(['regex' => 'Use the form 2025/2026.'])
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule->where('house_id', $this->getOwnerRecord()->getKey())),
                Textarea::make('achievements')->rows(4)->maxLength(3000)->columnSpanFull()
                    ->helperText('One achievement per line, for example "Overall winner of the inter-house athletics".'),
            ]),
            Section::make('Leadership that year')->columns(2)->schema([
                TextInput::make('housemaster')->maxLength(120),
                TextInput::make('assistant_housemaster')->label('Assistant housemaster')->maxLength(120),
                $photo('housemaster_photo', 'Housemaster photo'),
                TextInput::make('house_prefect')->label('House prefect')->maxLength(120),
                TextInput::make('assistant_house_prefect')->label('Assistant house prefect')->maxLength(120),
                $photo('house_prefect_photo', 'House prefect photo'),
            ]),
            Section::make('Photos')->schema([
                $photo('group_photo', 'House group photo for the year'),
                SpatieMediaLibraryFileUpload::make('gallery')->label('More photos from the year')->collection('gallery')
                    ->image()->multiple()->reorderable()->maxFiles(30)->maxSize(8192)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                Textarea::make('notes')->label('Notes (not shown on the website)')->rows(2)->maxLength(1000),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('academic_year')
            ->defaultSort('academic_year', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('group_photo')->collection('group_photo')->conversion('thumb')->label('')->width(72)->height(48),
                TextColumn::make('academic_year')->label('Year')->sortable()->weight('bold'),
                TextColumn::make('housemaster')->placeholder('—'),
                TextColumn::make('house_prefect')->label('House prefect')->placeholder('—'),
                TextColumn::make('achievements')->label('Achievements')->state(fn ($record) => count($record->achievementLines()))->suffix(' listed'),
            ])
            ->headerActions([CreateAction::make()->label('Add a year')])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
