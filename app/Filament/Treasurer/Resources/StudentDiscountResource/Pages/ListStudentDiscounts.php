<?php

namespace App\Filament\Treasurer\Resources\StudentDiscountResource\Pages;

use App\Filament\Treasurer\Resources\StudentDiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentDiscounts extends ListRecords
{
    protected static string $resource = StudentDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
