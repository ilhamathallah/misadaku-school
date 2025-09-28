<?php

namespace App\Filament\Treasurer\Resources\ExpenseResource\Pages;

use App\Filament\Treasurer\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Pengeluaran'),
        ];
    }
}
