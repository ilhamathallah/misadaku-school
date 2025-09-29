<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Classroom;
use App\Models\StudentProfile;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class BillReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $classroomId;

    protected $students;
    protected $rowNumber = 1;

    public function __construct($startDate = null, $endDate = null, $classroomId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->classroomId = $classroomId;
    }

    public function collection()
    {
        $this->students = StudentProfile::with(['user', 'classroom', 'bills'])
            ->when($this->classroomId, fn($q) => $q->where('classroom_id', $this->classroomId))
            ->get();

        return $this->students;
    }

    public function map($student): array
    {
        $months = [
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
        ];

        $row = [
            $this->rowNumber++,
            $student->user->name ?? '-',
            $student->gender ?? '-',
            $student->nis ?? '-',
        ];

        foreach ($months as $monthNum => $monthName) {
            // Filter tagihan siswa untuk bulan itu + dalam rentang tanggal jika ada
            $bills = $student->bills
                ->filter(fn($bill) => Carbon::parse($bill->tanggal_jatuh_tempo)->month === $monthNum)
                ->filter(
                    fn($bill) => (!$this->startDate || Carbon::parse($bill->tanggal_jatuh_tempo)->gte(Carbon::parse($this->startDate))) &&
                        (!$this->endDate   || Carbon::parse($bill->tanggal_jatuh_tempo)->lte(Carbon::parse($this->endDate)))
                );

            $total = $bills->sum('amount');

            $row[] = $this->formatRupiah($total);
        }

        return $row;
    }

    public function headings(): array
    {
        $months = [
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni'
        ];

        return array_merge(
            ['No', 'Nama Siswa', 'L/P', 'NIS'],
            $months
        );
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert 4 baris sebelum data: Judul, Kelas, Periode, dan Spacer
                $sheet->insertNewRowBefore(1, 4);

                $highestColumn = $sheet->getHighestColumn();

                // Kolom "No" jangan terlalu lebar
                $sheet->getColumnDimension('A')->setAutoSize(false);
                $sheet->getColumnDimension('A')->setWidth(5);

                // Judul (Baris 1)
                $sheet->setCellValue('A1', 'LAPORAN TAGIHAN SISWA');
                $sheet->mergeCells("A1:{$highestColumn}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Kelas (Baris 2)
                $kelasText = 'Kelas: ';

                if ($this->classroomId) {
                    $classroom = \App\Models\Classroom::find($this->classroomId);

                    if ($classroom) {
                        $kelasText .= trim("{$classroom->kelas} {$classroom->name}");
                    } else {
                        $kelasText .= "-";
                    }
                } else {
                    $kelasText .= "Semua Kelas";
                }

                $sheet->setCellValue('A2', $kelasText);
                $sheet->mergeCells("A2:{$highestColumn}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Periode (Baris 3)
                $periodeText = ($this->startDate && $this->endDate)
                    ? Carbon::parse($this->startDate)->format('d-m-Y') . ' s/d ' . Carbon::parse($this->endDate)->format('d-m-Y')
                    : 'Semua Periode';
                $sheet->setCellValue('A3', "Periode: {$periodeText}");
                $sheet->mergeCells("A3:{$highestColumn}3");
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Spacer baris ke-4 (kosong)

                // Bold Header (Baris ke-5)
                $sheet->getStyle("A5:{$highestColumn}5")->applyFromArray([
                    'font' => ['bold' => true],
                ]);

                // Baris total tagihan (setelah data siswa)
                $lastRow = $sheet->getHighestRow(); // setelah insert baris
                $rowTotal = $lastRow + 2;

                $sheet->setCellValue("A{$rowTotal}", "TOTAL TAGIHAN PER BULAN");
                $sheet->getStyle("A{$rowTotal}")->applyFromArray([
                    'font' => ['bold' => true],
                ]);

                // Hitung total tagihan tiap bulan
                $colStart = 5; // Kolom "Juli" di kolom E = index 5
                $monthsIndex = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];

                foreach ($monthsIndex as $i => $monthNum) {
                    $colLetter = Coordinate::stringFromColumnIndex($colStart + $i);

                    $totalBulan = 0;
                    foreach ($this->students as $student) {
                        $bills = $student->bills
                            ->filter(fn($bill) => Carbon::parse($bill->tanggal_jatuh_tempo)->month === $monthNum)
                            ->filter(
                                fn($bill) => (!$this->startDate || Carbon::parse($bill->tanggal_jatuh_tempo)->gte(Carbon::parse($this->startDate))) &&
                                    (!$this->endDate || Carbon::parse($bill->tanggal_jatuh_tempo)->lte(Carbon::parse($this->endDate)))
                            );
                        $totalBulan += $bills->sum('amount');
                    }

                    $sheet->setCellValue("{$colLetter}{$rowTotal}", $this->formatRupiah($totalBulan));
                }
            }
        ];
    }


    protected function formatRupiah($number): string
    {
        if ($number <= 0) {
            return "Rp 0,00";
        }
        // Format dengan ribuan dan desimal
        return "Rp " . number_format($number, 2, ',', '.');
    }
}
