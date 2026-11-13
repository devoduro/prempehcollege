<?php

namespace App\Filament\Resources\StaffGroupResource\Pages;

use App\Filament\Resources\StaffGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaffGroups extends ListRecords
{
    protected static string $resource = StaffGroupResource::class;

    protected function getHeaderActions(): array
    {
        return static::getResource()::canCreate() ? [CreateAction::make()] : [];
    }
}
