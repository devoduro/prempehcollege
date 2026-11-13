<?php

namespace App\Filament\Resources\StaffGroupResource\Pages;

use App\Filament\Resources\StaffGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStaffGroup extends EditRecord
{
    protected static string $resource = StaffGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
