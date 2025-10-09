<?php

namespace App\Filament\Treasurer\Resources\BillResource\Pages;

use App\Models\Bill;
use Filament\Actions;
use App\Models\Classroom;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use App\Models\StudentProfile;
use App\Models\FinanceCategory;
use App\Exports\BillReportExport;
use Filament\Forms\Components\Grid;
use Maatwebsite\Excel\Facades\Excel;
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
                                Classroom::all()
                                    ->mapWithKeys(fn($record) => [
                                        $record->id => "{$record->kelas} {$record->name}",
                                    ])
                                    ->toArray()
                            )
                            ->required(),

                        Select::make('category_ids')
                            ->label('Kategori Pemasukan')
                            ->options(function () {
                                return FinanceCategory::where('type', 'income')
                                    ->get()
                                    ->mapWithKeys(function ($category) {
                                        // classroom_ids bentuknya array (json)
                                        $ids = is_array($category->classroom_ids)
                                            ? $category->classroom_ids
                                            : (json_decode($category->classroom_ids, true) ?? []);

                                        // ambil nama kelas dari ids
                                        $classroomNames = Classroom::whereIn('id', $ids)
                                            ->get()
                                            ->map(fn($c) => "{$c->kelas} {$c->name}")
                                            ->implode(', ');

                                        return [
                                            $category->id => Str::title("{$category->name} - {$classroomNames}"),
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->multiple()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                $totalAmount = FinanceCategory::whereIn('id', $state)->sum('amount');
                                $set('amount', $totalAmount);
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
                                'category_ids' => [$category->id], // JSON
                                'amount' => $category->amount,
                                'status' => 'Belum Lunas', // default enum
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
                        ->options(
                            Classroom::all()
                                ->mapWithKeys(fn($c) => [
                                    $c->id => "{$c->kelas} {$c->name}"
                                ])
                                ->toArray()
                        )
                        ->nullable(),

                    DatePicker::make('start_date')->label('Dari Tanggal')->nullable(),
                    DatePicker::make('end_date')->label('Sampai Tanggal')->nullable(),
                ])
                ->action(function (array $data) {
                    $fileName = 'laporan-tagihan-' . now()->format('Y-m-d') . '.xlsx';

                    return Excel::download(
                        new BillReportExport(
                            $data['start_date'] ?? null,
                            $data['end_date'] ?? null,
                            $data['classroom_id'] ?? null
                        ),
                        $fileName
                    );
                })
                ->color('success'),
        ];
    }
}
