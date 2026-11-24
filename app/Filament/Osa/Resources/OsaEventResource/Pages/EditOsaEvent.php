<?php

namespace App\Filament\Osa\Resources\OsaEventResource\Pages;

use App\Filament\Osa\Resources\OsaEventResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

class EditOsaEvent extends EditRecord
{
    protected static string $resource = OsaEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('qr')->label('QR code')->icon('heroicon-o-qr-code')
                ->modalHeading('Registration QR')
                ->modalContent(fn (): View => view('filament.osa.qr', ['event' => $this->getRecord()]))
                ->modalSubmitAction(false)->modalCancelActionLabel('Close'),
            Action::make('printQr')->label('Print QR poster')->icon('heroicon-o-printer')
                ->url(fn () => route('osa.print.qr', $this->getRecord()).'?auto=1', shouldOpenInNewTab: true),
            DeleteAction::make(),
        ];
    }
}
