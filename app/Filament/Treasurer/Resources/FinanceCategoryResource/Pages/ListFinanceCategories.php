<?php

namespace App\Filament\Treasurer\Resources\FinanceCategoryResource\Pages;

use App\Filament\Treasurer\Resources\FinanceCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinanceCategories extends ListRecords
{
    protected static string $resource = FinanceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Buat Jenis Pembayaran'),
        ];
    }
}
