<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Support\HandlesRawBody;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    use HandlesRawBody;

    protected static string $resource = EventResource::class;
}
