<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Models\Bill;
use BillReportExport;
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Classroom;
use Filament\Actions\Action;
use Maatwebsite\Excel\Excel;
use App\Models\StudentProfile;
use App\Models\FinanceCategory;
use Filament\Forms\Components\Grid;
use App\Exports\FinanceReportExport;
use Filament\Forms\Components\Select;
use App\Filament\Resources\BillResource;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;

class ListBills extends ListRecords
{
    protected static string $resource = BillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-s-user')
                ->label('Siswa'),

            Action::make('buatTagihanMassal')
                ->icon('heroicon-s-user-group')
                ->label('Seluruh Siswa')
                ->form([
                    Grid::make(2)->schema([
                        TextInput::make('nama_tagihan')
                            ->label('Nama Tagihan')
                            ->required(),

                        DatePicker::make('tanggal_jatuh_tempo')
                            ->label('Tanggal Jatuh Tempo')
                            ->native(false)
                            ->required(),

                        Select::make('classroom_ids')
                            ->label('Pilih Kelas')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(
                                \App\Models\Classroom::all()
                                    ->mapWithKeys(fn($record) => [
                                        $record->id => "{$record->kelas} {$record->name} {$record->jenjang}",
                                    ])
                                    ->toArray()
                            )
                            ->required(),

                        Select::make('category_ids')
                            ->label('Kategori Pemasukan')
                            ->multiple()
                            ->searchable()
                            ->options(function () {
                                return FinanceCategory::where('type', 'income')
                                    ->with('classroom')
                                    ->get()
                                    ->mapWithKeys(function ($category) {
                                        $classroom = $category->classroom;
                                        $classroomName = $classroom
                                            ? "{$classroom->kelas} {$classroom->name}"
                                            : 'Semua Kelas';

                                        return [
                                            $category->id => "{$category->name} - {$classroomName}"
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->required()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $amount = \App\Models\Bill::calculateAmount($get('category_ids'));
                                $set('amount', (int) $amount);
                            }),

                        TextInput::make('amount')
                            ->label('Total Pembayaran')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ]),
                ])
                ->action(function (array $data): void {
                    $classroomIds = $data['classroom_ids'];
                    $categoryIds = $data['category_ids'];

                    $categories = FinanceCategory::whereIn('id', $categoryIds)->get();

                    // Ambil semua siswa dari kelas yang dipilih
                    $students = StudentProfile::whereIn('classroom_id', $classroomIds)->get();

                    foreach ($students as $student) {
                        foreach ($categories as $category) {
                            Bill::create([
                                'nama_tagihan' => $data['nama_tagihan'],
                                'student_profile_id' => $student->id,
                                'tanggal_jatuh_tempo' => $data['tanggal_jatuh_tempo'],
                                'category_ids' => [$category->id],
                                'amount' => $category->amount,
                                'status' => false,
                            ]);
                        }
                    }

                    Notification::make()
                        ->title('Tagihan berhasil dibuat')
                        ->success()
                        ->send();
                }),

            Action::make('export')
                ->label('Export Tagihan')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    Select::make('classroom_id')
                        ->label('Kelas')
                        ->options(\App\Models\Classroom::all()->mapWithKeys(fn($c) => [
                            $c->id => "{$c->kelas} {$c->name}"
                        ])->toArray())
                        ->nullable(),

                    DatePicker::make('start_date')->label('Dari Tanggal')->nullable(),
                    DatePicker::make('end_date')->label('Sampai Tanggal')->nullable(),
                ])
                ->action(function (array $data) {
                    $fileName = 'laporan-tagihan-' . now()->format('Y-m-d') . '.xlsx';

                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\BillReportExport(
                            $data['start_date'] ?? null,
                            $data['end_date'] ?? null,
                            $data['classroom_id'] ?? null
                        ),
                        $fileName
                    );
                })
                ->color('success')

        ];
    }
}
