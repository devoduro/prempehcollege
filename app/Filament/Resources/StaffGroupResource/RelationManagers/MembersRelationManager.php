<?php

namespace App\Filament\Resources\StaffGroupResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = 'People';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->maxLength(120)->required()->helperText('Shown on the page and used by site search.'),
            TextInput::make('role')->maxLength(120)->label('Role or years')->placeholder('e.g. Assistant Headmaster, or 1949 – 1953 on a timeline'),
            Textarea::make('bio')->rows(3)->maxLength(2000)->columnSpanFull(),
            SpatieMediaLibraryFileUpload::make('photo')->collection('photo')
                ->image()->imageEditor()->maxSize(5120)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            TextInput::make('sort_order')->numeric()->minValue(0)->default(0),
            Toggle::make('is_published')->label('Show on website')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('photo')->collection('photo')->conversion('thumb')->label('')->height(56)->width(56),
                TextColumn::make('name')->placeholder('No name yet')->searchable(),
                TextColumn::make('role')->placeholder('—'),
                IconColumn::make('is_published')->boolean()->label('Shown'),
            ])
            ->filters([
                TernaryFilter::make('has_photo')->label('Photo')
                    ->queries(true: fn ($query) => $query->whereHas('media'), false: fn ($query) => $query->whereDoesntHave('media')),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
