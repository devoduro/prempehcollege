<?php

namespace App\Filament\Osa\Resources\OsaExecutiveResource\Pages;

use App\Filament\Osa\Resources\OsaExecutiveResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOsaExecutive extends EditRecord
{
    protected static string $resource = OsaExecutiveResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
