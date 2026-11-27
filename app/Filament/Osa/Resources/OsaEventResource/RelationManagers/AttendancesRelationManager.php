<?php

namespace App\Filament\Osa\Resources\OsaEventResource\RelationManagers;

use App\Models\House;
use App\Models\OsaAttendance;
use App\Services\OsaAttendanceService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $title = 'Attendance';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(150)->columnSpanFull(),
            TextInput::make('year_completed')->label('Year group')->required()->numeric()->minValue(1930)->maxValue((int) date('Y')),
            Select::make('house_id')->label('House')->required()->options(fn () => House::orderBy('name')->pluck('name', 'id'))->searchable(),
            TextInput::make('email')->email()->required()->maxLength(190),
            TextInput::make('phone')->tel()->required()->maxLength(20)
                ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule->where('osa_event_id', $this->getOwnerRecord()->getKey())),
        ]);
    }

    public function table(Table $table): Table
    {
        $badge = fn (string $state) => match ($state) {
            'sent' => 'success', 'failed' => 'danger', default => 'gray'
        };

        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('checked_in_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->weight('bold'),
                TextColumn::make('year_completed')->label('Year group')->sortable(),
                TextColumn::make('house.name')->label('House')->placeholder('—'),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('sms_status')->label('SMS')->badge()->color($badge)->tooltip(fn (OsaAttendance $r) => $r->sms_note),
                TextColumn::make('email_status')->label('Email')->badge()->color($badge)->tooltip(fn (OsaAttendance $r) => $r->email_note),
                TextColumn::make('source')->badge()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('checked_in_at')->label('Checked in')->dateTime('j M, g:i A')->sortable(),
            ])
            ->filters([
                SelectFilter::make('house_id')->label('House')->options(fn () => House::orderBy('name')->pluck('name', 'id')),
                Filter::make('year')->label('Year group')
                    ->schema([
                        TextInput::make('year_from')->label('Year group from')->numeric()->minValue(1930),
                        TextInput::make('year_to')->label('Year group to')->numeric()->maxValue((int) date('Y')),
                    ])->columns(2)
                    ->query(fn ($query, array $data) => $query
                        ->when(filled($data['year_from'] ?? null), fn ($q) => $q->where('year_completed', '>=', (int) $data['year_from']))
                        ->when(filled($data['year_to'] ?? null), fn ($q) => $q->where('year_completed', '<=', (int) $data['year_to']))),
                SelectFilter::make('sms_status')->label('SMS')->options(['sent' => 'Sent', 'failed' => 'Failed', 'pending' => 'Pending']),
                SelectFilter::make('email_status')->label('Email')->options(['sent' => 'Sent', 'failed' => 'Failed', 'pending' => 'Pending']),
            ])
            ->headerActions([
                Action::make('print')->label('Print list')->icon('heroicon-o-printer')->color('gray')
                    ->url(fn () => $this->printUrl(['auto' => 1]), shouldOpenInNewTab: true),
                Action::make('csv')->label('Download CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->url(fn () => $this->printUrl(['format' => 'csv']), shouldOpenInNewTab: true),
                CreateAction::make()->label('Check in a member')
                    ->using(function (array $data) {
                        $result = app(OsaAttendanceService::class)->checkIn($this->getOwnerRecord(), $data, 'admin');
                        if (! $result['created']) {
                            Notification::make()->title('Already checked in')->warning()->send();
                        }

                        return $result['attendance'];
                    }),
            ])
            ->recordActions([
                Action::make('resend')->label('Resend SMS & email')->icon('heroicon-o-paper-airplane')->requiresConfirmation()
                    ->action(function (OsaAttendance $record) {
                        app(OsaAttendanceService::class)->notify($record);
                        Notification::make()->title('SMS: '.$record->sms_status.' · Email: '.$record->email_status)->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    /** Same filters and search as the table, so the printout matches what is on screen. */
    private function printUrl(array $extra = []): string
    {
        $f = $this->tableFilters ?? [];

        return route('osa.print.attendance', ['event' => $this->getOwnerRecord()->getKey()] + array_filter([
            'house' => $f['house_id']['value'] ?? null,
            'year_from' => $f['year']['year_from'] ?? null,
            'year_to' => $f['year']['year_to'] ?? null,
            'sms' => $f['sms_status']['value'] ?? null,
            'email' => $f['email_status']['value'] ?? null,
            'search' => $this->tableSearch ?? null,
        ] + $extra, fn ($v) => filled($v)));
    }
}
