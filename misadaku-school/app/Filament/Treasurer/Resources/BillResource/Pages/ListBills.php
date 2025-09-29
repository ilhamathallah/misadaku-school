<?php

namespace App\Filament\Treasurer\Resources\BillResource\Pages;

use App\Models\Bill;
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Actions\Action;
use App\Models\StudentProfile;
use App\Models\FinanceCategory;
use Filament\Forms\Components\Grid;
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
                    Grid::make(2) // ⬅️ grid 2 kolom
                        ->schema([
                            TextInput::make('nama_tagihan')
                                ->label('Nama Tagihan')
                                ->required(),

                            DatePicker::make('tanggal_jatuh_tempo')
                                ->label('Tanggal Jatuh Tempo')
                                ->native(false)
                                ->required(),

                            Select::make('category_ids')
                                ->label('Kategori Pemasukan (khusus Semua Siswa)')
                                ->options(function () {
                                    return \App\Models\FinanceCategory::where('type', 'income')
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
                                ->multiple()
                                ->searchable()
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
                    $categories = FinanceCategory::whereIn('id', $data['category_ids'])->get();

                    foreach ($categories as $category) {
                        $studentsQuery = StudentProfile::query();

                        if (!is_null($category->classroom_id)) {
                            $studentsQuery->where('classroom_id', $category->classroom_id);
                        }

                        $studentIds = $studentsQuery->pluck('id');

                        foreach ($studentIds as $studentId) {
                            Bill::create([
                                'nama_tagihan' => $data['nama_tagihan'],
                                'student_profile_id' => $studentId,
                                'tanggal_jatuh_tempo' => $data['tanggal_jatuh_tempo'],
                                'category_ids' => [$category->id],
                                'amount' => $category->amount,
                                'status' => false,
                            ]);
                        }
                    }

                    Notification::make()
                        ->title('Tagihan berhasil dibuat')
                        // ->body('Tagihan massal telah berhasil ditambahkan ke seluruh siswa sesuai kategori.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
