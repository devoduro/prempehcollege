<?php

namespace App\Filament\Resources\PrefectBoardResource\Pages;

use App\Filament\Resources\PrefectBoardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrefectBoards extends ListRecords
{
    protected static string $resource = PrefectBoardResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
