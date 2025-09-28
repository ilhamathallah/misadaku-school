<?php

namespace App\Filament\Resources\StudentPaymentResource\Pages;

use App\Filament\Resources\StudentPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentPayment extends EditRecord
{
    protected static string $resource = StudentPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
