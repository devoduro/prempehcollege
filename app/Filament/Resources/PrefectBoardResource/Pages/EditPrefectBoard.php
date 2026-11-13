<?php

namespace App\Filament\Resources\PrefectBoardResource\Pages;

use App\Filament\Resources\PrefectBoardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrefectBoard extends EditRecord
{
    protected static string $resource = PrefectBoardResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
