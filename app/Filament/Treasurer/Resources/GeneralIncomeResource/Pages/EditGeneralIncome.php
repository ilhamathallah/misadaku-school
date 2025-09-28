<?php

namespace App\Filament\Treasurer\Resources\GeneralIncomeResource\Pages;

use App\Filament\Treasurer\Resources\GeneralIncomeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGeneralIncome extends EditRecord
{
    protected static string $resource = GeneralIncomeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
