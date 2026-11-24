<?php

namespace App\Filament\Osa\Resources\OsaEventResource\Pages;

use App\Filament\Osa\Resources\OsaEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOsaEvents extends ListRecords
{
    protected static string $resource = OsaEventResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
