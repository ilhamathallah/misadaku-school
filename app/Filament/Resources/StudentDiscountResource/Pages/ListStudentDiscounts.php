<?php

namespace App\Filament\Resources\StudentDiscountResource\Pages;

use App\Filament\Resources\StudentDiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentDiscounts extends ListRecords
{
    protected static string $resource = StudentDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Buat Diskon'),
        ];
    }
}
