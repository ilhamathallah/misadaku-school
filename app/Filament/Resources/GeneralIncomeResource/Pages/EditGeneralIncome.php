<?php

namespace App\Filament\Resources\GeneralIncomeResource\Pages;

use App\Filament\Resources\GeneralIncomeResource;
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
