<?php

namespace App\Exports;

use App\Models\GeneralIncome;
use App\Models\Expense;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GeneralExpenseExport implements FromCollection, WithHeadings, WithStyles, WithCustomStartCell, WithEvents
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    public function collection()
    {
        $incomes = GeneralIncome::whereBetween('income_date', [$this->startDate, $this->endDate])
            ->get()
            ->map(function ($income) {
                return [
                    'Tanggal'     => Carbon::parse($income->income_date)->format('d-m-Y'),
                    'Kategori'    => ucwords(strtolower($income->name)),
                    'Keterangan'  => $income->description ? ucwords(strtolower($income->description)) : '-',
                    'Jumlah'      => $income->amount,
                    'Tipe'        => 'Pemasukan',
                ];
            });

        $expenses = Expense::whereBetween('expense_date', [$this->startDate, $this->endDate])
            ->get()
            ->map(function ($expense) {
                return [
                    'Tanggal'     => Carbon::parse($expense->expense_date)->format('d-m-Y'),
                    'Kategori'    => ucwords(strtolower($expense->financeCategory?->name ?? $expense->custom_category_name)),
                    'Keterangan'  => $expense->note ? ucwords(strtolower($expense->note)) : '-',
                    'Jumlah'      => $expense->amount,
                    'Tipe'        => 'Pengeluaran',
                ];
            });

        return $incomes->merge($expenses)->sortBy('Tanggal')->values();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kategori',
            'Keterangan',
            'Jumlah',
            'Tipe',
        ];
    }

    public function startCell(): string
    {
        return 'A5'; // Heading mulai dari baris ke-5
    }

    public function styles(Worksheet $sheet)
    {
        // Style header tabel tanpa warna
        $sheet->getStyle('A5:E5')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Judul laporan
                $sheet->setCellValue('A1', 'Laporan Keuangan');
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // Periode
                $sheet->setCellValue('A2', 'Periode: ' . Carbon::parse($this->startDate)->format('d-m-Y') . ' s/d ' . Carbon::parse($this->endDate)->format('d-m-Y'));
                $sheet->mergeCells('A2:E2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // Jumlah baris data
                $rowCount = $this->collection()->count();
                $lastDataRow = 5 + $rowCount;

                // Border semua data
                $sheet->getStyle("A5:E{$lastDataRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Autofit kolom
                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
