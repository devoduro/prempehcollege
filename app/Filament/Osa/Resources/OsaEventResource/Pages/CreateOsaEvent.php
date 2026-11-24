<?php

namespace App\Filament\Osa\Resources\OsaEventResource\Pages;

use App\Filament\Osa\Resources\OsaEventResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateOsaEvent extends CreateRecord
{
    protected static string $resource = OsaEventResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()->success()->title('Meeting created')
            ->body('Open it and use "Print QR poster" so members can scan to register.');
    }
}
