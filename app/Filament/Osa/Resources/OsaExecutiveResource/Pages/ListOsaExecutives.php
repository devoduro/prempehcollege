<?php

namespace App\Filament\Osa\Resources\OsaExecutiveResource\Pages;

use App\Filament\Osa\Resources\OsaExecutiveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOsaExecutives extends ListRecords
{
    protected static string $resource = OsaExecutiveResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
