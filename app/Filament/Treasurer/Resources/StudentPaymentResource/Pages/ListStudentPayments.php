<?php

namespace App\Filament\Treasurer\Resources\StudentPaymentResource\Pages;

use Filament\Forms;
use Filament\Actions;
use App\Models\Classroom;
use App\Models\FinanceCategory;
use Filament\Forms\Components\Grid;
use App\Exports\FinanceReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\StudentPaymentResource;

class ListStudentPayments extends ListRecords
{
    protected static string $resource = StudentPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Pembayaran')
                ->icon('heroicon-s-wallet'),

            Actions\Action::make('export_excel')
                ->label('Export ke Excel')
                ->color('success')
                ->icon('heroicon-s-document-arrow-down')
                ->form([
                    Grid::make(2) // 👉 Grid 2 kolom
                        ->schema([
                            DatePicker::make('startDate')
                                ->label('Tanggal Mulai')
                                ->required(),

                            DatePicker::make('endDate')
                                ->label('Tanggal Selesai')
                                ->required(),

                            Select::make('categories')
                                ->label('Kategori Pembayaran')
                                ->multiple()
                                ->searchable()
                                ->options(
                                    FinanceCategory::with('classroom')->get()->mapWithKeys(function ($category) {
                                        $kelasName = $category->classroom
                                            ? "{$category->classroom->kelas} {$category->classroom->name}"
                                            : '-';
                                        return [$category->id => "{$category->name} - {$kelasName}"];
                                    })->toArray()
                                ),

                            Select::make('classroom_id')
                                ->label('Pilih Kelas')
                                ->searchable()
                                ->options(
                                    \App\Models\Classroom::all()->mapWithKeys(function ($classroom) {
                                        return [
                                            $classroom->id => "{$classroom->kelas} {$classroom->name}",
                                        ];
                                    })->toArray()
                                )
                                ->required(),
                        ])
                ])
                ->action(function (array $data) {
                    $startDate   = $data['startDate'];
                    $endDate     = $data['endDate'];
                    $categories  = $data['categories'] ?? [];
                    $classroomId = $data['classroom_id'];

                    $classroom = \App\Models\Classroom::find($classroomId);
                    $classroomName = $classroom
                        ? "{$classroom->kelas}_{$classroom->jenjang}_{$classroom->name}"
                        : 'semua_kelas';

                    $start = \Carbon\Carbon::parse($startDate)->format('d-m-Y');
                    $end   = \Carbon\Carbon::parse($endDate)->format('d-m-Y');

                    $classroomName = str_replace([' ', '/'], '_', strtolower($classroomName));
                    $fileName = "rekap_pembayaran_{$classroomName}_{$start}_sd_{$end}.xlsx";

                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\FinanceReportExport($startDate, $endDate, $categories, $classroomId),
                        $fileName
                    );
                })
        ];
    }
}
