<?php

namespace App\Filament\Treasurer\Resources\GeneralIncomeResource\Pages;

use App\Filament\Treasurer\Resources\GeneralIncomeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGeneralIncomes extends ListRecords
{
    protected static string $resource = GeneralIncomeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
