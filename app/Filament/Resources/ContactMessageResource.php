<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $modelLabel = 'message';

    protected static ?string $navigationLabel = 'Messages';

    protected static string|UnitEnum|null $navigationGroup = 'Inbox';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox';

    public static function getNavigationBadge(): ?string
    {
        $n = ContactMessage::whereNull('read_at')->count();

        return $n > 0 ? (string) $n : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextEntry::make('name'),
                TextEntry::make('email')->url(fn (ContactMessage $r) => 'mailto:'.$r->email)->copyable(),
                TextEntry::make('phone')->placeholder('—'),
                TextEntry::make('created_at')->label('Received')->dateTime('j M Y, H:i'),
                TextEntry::make('subject')->placeholder('—')->columnSpanFull(),
                // Plain text only: visitor input is never rendered as HTML.
                TextEntry::make('message')->columnSpanFull()->prose(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('read_at')->label('')->boolean()->getStateUsing(fn (ContactMessage $r) => $r->read_at === null)
                    ->trueIcon('heroicon-s-envelope')->falseIcon('heroicon-o-envelope-open')->trueColor('warning')->falseColor('gray'),
                TextColumn::make('name')->searchable()->weight(fn (ContactMessage $r) => $r->read_at ? null : 'bold'),
                TextColumn::make('email')->searchable(),
                TextColumn::make('subject')->limit(50)->placeholder('—'),
                TextColumn::make('created_at')->since()->label('Received')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('read_at')->label('Read')->nullable(),
            ])
            ->recordActions([
                ViewAction::make()->after(fn (ContactMessage $r) => $r->read_at ?: $r->forceFill(['read_at' => now()])->save()),
                Action::make('markUnread')->label('Mark unread')->icon('heroicon-o-envelope')
                    ->visible(fn (ContactMessage $r) => $r->read_at !== null)
                    ->action(fn (ContactMessage $r) => $r->forceFill(['read_at' => null])->save()),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'view' => Pages\ViewContactMessage::route('/{record}'),
        ];
    }
}
