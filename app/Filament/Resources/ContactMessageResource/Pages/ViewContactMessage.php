<?php

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Filament\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $message = $this->getRecord();
        // Opening a message marks it read.
        if ($message instanceof ContactMessage && $message->read_at === null) {
            $message->forceFill(['read_at' => now()])->save();
        }
    }
}
