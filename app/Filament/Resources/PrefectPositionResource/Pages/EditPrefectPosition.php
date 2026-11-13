<?php

namespace App\Filament\Resources\PrefectPositionResource\Pages;

use App\Filament\Resources\PrefectPositionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrefectPosition extends EditRecord
{
    protected static string $resource = PrefectPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
