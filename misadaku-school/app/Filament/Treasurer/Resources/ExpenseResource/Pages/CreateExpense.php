<?php

namespace App\Filament\Treasurer\Resources\ExpenseResource\Pages;

use App\Filament\Treasurer\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;
}
