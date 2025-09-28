<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ClassroomResource;
use App\Filament\Widgets\StudentsPerClassChart;

class ListClassrooms extends ListRecords
{
    protected static string $resource = ClassroomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ruang Kelas')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StudentsPerClassChart::class,
        ];
    }
}
