<?php

namespace App\Filament\Resources\DenominationResource\Pages;

use App\Filament\Resources\DenominationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDenomination extends EditRecord
{
    protected static string $resource = DenominationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
