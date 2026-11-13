<?php

namespace App\Filament\Resources\PrefectPositionResource\Pages;

use App\Filament\Resources\PrefectPositionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrefectPositions extends ListRecords
{
    protected static string $resource = PrefectPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
