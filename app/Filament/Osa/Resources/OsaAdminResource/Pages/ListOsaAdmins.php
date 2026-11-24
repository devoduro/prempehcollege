<?php

namespace App\Filament\Osa\Resources\OsaAdminResource\Pages;

use App\Filament\Osa\Resources\OsaAdminResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOsaAdmins extends ListRecords
{
    protected static string $resource = OsaAdminResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
