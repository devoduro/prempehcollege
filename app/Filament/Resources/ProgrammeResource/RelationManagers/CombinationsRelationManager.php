<?php

namespace App\Filament\Resources\ProgrammeResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CombinationsRelationManager extends RelationManager
{
    protected static string $relationship = 'combinations';

    protected static ?string $title = 'Elective subject combinations';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->maxLength(120)->placeholder('e.g. Combination A (optional)'),
            TagsInput::make('subjects')->required()->placeholder('Type a subject and press Enter')
                ->helperText('The elective subjects a student takes in this combination.'),
            TextInput::make('notes')->maxLength(300)->placeholder('e.g. Leads to medicine and engineering (optional)'),
            TextInput::make('sort_order')->numeric()->minValue(0)->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->placeholder('—'),
                TextColumn::make('subjects')->badge()->separator(','),
                TextColumn::make('notes')->limit(50)->placeholder('—'),
            ])
            ->headerActions([CreateAction::make()->label('Add combination')])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
