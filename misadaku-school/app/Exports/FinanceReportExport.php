<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Classroom;
use App\Models\StudentProfile;
use App\Models\FinanceCategory;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinanceReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $categoryIds;
    protected $classroomId;

    /** @var \Illuminate\Support\Collection */
    protected $rows;

    public function __construct($startDate = null, $endDate = null, $categoryIds = null, $classroomId = null)
    {
        $this->startDate   = $startDate;
        $this->endDate     = $endDate;
        $this->categoryIds = $categoryIds;
        $this->classroomId = $classroomId;
    }

    public function collection()
    {
        $this->rows = StudentProfile::with([
            'user',
            'classroom',
            'payments.bill' => function ($q) {
                if ($this->startDate) $q->whereDate('tanggal_jatuh_tempo', '>=', $this->startDate);
                if ($this->endDate)   $q->whereDate('tanggal_jatuh_tempo', '<=', $this->endDate);
                if (!empty($this->categoryIds)) {
                    $q->where(function ($query) {
                        foreach ($this->categoryIds as $id) {
                            $query->orWhereJsonContains('category_ids', (int) $id);
                        }
                    });
                }
            }
        ])
            ->when($this->classroomId, fn($q) => $q->where('classroom_id', $this->classroomId))
            ->get();

        return $this->rows;
    }

    protected $rowNumber = 1; // Tambahkan properti ini di class

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

        $paymentsPerMonth = [];

        foreach ($months as $num => $month) {
            $payments = ($student->payments ?? collect())
                ->filter(fn($p) => $p->bill && Carbon::parse($p->bill->tanggal_jatuh_tempo)->month === $num);

            if ($payments->count()) {
                $totalBill = $payments->sum(fn($p) => (float) ($p->total_amount ?? 0));
                $paid      = $payments->sum('paid_amount');
                $status    = $paid >= $totalBill ? 'Lunas' : ($paid > 0 ? 'Belum Lunas' : 'Belum Bayar');

                $dates = $payments
                    ->map(fn($p) => $p->payment_date ?? $p->created_at)
                    ->filter()
                    ->map(fn($d) => Carbon::parse($d)->format('j/n/y'))
                    ->implode(', ');

                $paymentsPerMonth[] = sprintf("%s (Rp%s)", $status, number_format($totalBill, 0, ',', '.'));
                $paymentsPerMonth[] = $dates ?: '-';
            } else {
                $paymentsPerMonth[] = 'Belum Bayar (Rp0)';
                $paymentsPerMonth[] = '-';
            }
        }

        return array_merge([
            $this->rowNumber++, // Nomor urut
            ucwords(strtolower((string) ($student->user->name ?? '-'))),
            (string) ($student->nis ?? '-'),
            (string) ($student->gender ?? '-'),
        ], $paymentsPerMonth);
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

        $headings = [
            'No',
            'Nama Siswa',
            'NIS',
            'L/P',
        ];

        foreach ($months as $m) {
            $headings[] = $m;
            $headings[] = "Tgl";
        }

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Dapatkan batas sheet
                $highestColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                // Sisipkan 5 baris sebelum heading
                $sheet->insertNewRowBefore(1, 6); // Tambah 6 baris (judul, kelas, periode, kategori, spasi, header)

                // Judul
                $sheet->setCellValue('A1', 'LAPORAN KEUANGAN SISWA');
                $sheet->mergeCells("A1:{$highestColumn}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Kelas
                if ($this->classroomId) {
                    $classroom = Classroom::find($this->classroomId);
                    $kelas = $classroom
                        ? "Kelas: {$classroom->kelas} {$classroom->name}"
                        : 'Kelas: -';
                } else {
                    $kelas = 'Kelas: Semua Kelas';
                }
                $sheet->setCellValue('A2', $kelas);
                $sheet->mergeCells("A2:{$highestColumn}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Periode
                $periode = ($this->startDate && $this->endDate)
                    ? Carbon::parse($this->startDate)->format('d-m-Y') . ' s/d ' . Carbon::parse($this->endDate)->format('d-m-Y')
                    : 'Semua Periode';
                $sheet->setCellValue('A3', "Periode: {$periode}");
                $sheet->mergeCells("A3:{$highestColumn}3");
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Kategori
                if (!empty($this->categoryIds)) {
                    $kategoriNames = FinanceCategory::whereIn('id', $this->categoryIds)->pluck('name')->implode(', ');
                } else {
                    $kategoriNames = 'Semua Kategori';
                }
                $sheet->setCellValue('A4', "Kategori: {$kategoriNames}");
                $sheet->mergeCells("A4:{$highestColumn}4");
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 5 kosong (spasi antar heading dan data siswa)

                // Baris 6 adalah heading tabel siswa
                $sheet->getStyle("A5:{$highestColumn}6")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                ]);

                // Border data (mulai dari baris 6 ke bawah)
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A6:{$highestColumn}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                    ],
                ]);

                // Reset font biasa
                if ($lastRow >= 7) {
                    $sheet->getStyle("A7:{$highestColumn}{$lastRow}")
                        ->applyFromArray(['font' => ['bold' => false]]);
                }

                // Baris total per bulan (setelah data siswa)
                $rowTotal = $lastRow + 2;
                $sheet->setCellValue("B{$rowTotal}", "Total Yang Telah Dibayar");
                $sheet->getStyle("B{$rowTotal}")->applyFromArray(['font' => ['bold' => true]]);

                $rowTotalTagihan = $rowTotal + 1;
                $sheet->setCellValue("B{$rowTotalTagihan}", "Total Tagihan Seluruh Siswa");
                $sheet->getStyle("B{$rowTotalTagihan}")->applyFromArray(['font' => ['bold' => true]]);

                $rowTotalBelumDibayar = $rowTotalTagihan + 1;
                $sheet->setCellValue("B{$rowTotalBelumDibayar}", "Total Yang Belum Dibayar");
                $sheet->getStyle("B{$rowTotalBelumDibayar}")->applyFromArray(['font' => ['bold' => true]]);

                $colIndex = 6; // start dari kolom bulan pertama (karena kolom kategori ada di kolom ke-5)
                $academicMonths = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];

                foreach ($academicMonths as $num) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIndex);

                    $totalDibayar = $this->rows->sum(function ($student) use ($num) {
                        $payments = ($student->payments ?? collect())
                            ->filter(fn($p) => $p->bill && Carbon::parse($p->bill->tanggal_jatuh_tempo)->month === $num);
                        return $payments->sum('paid_amount');
                    });

                    $bills = Bill::whereMonth('tanggal_jatuh_tempo', $num);

                    if ($this->startDate) $bills->whereDate('tanggal_jatuh_tempo', '>=', $this->startDate);
                    if ($this->endDate)   $bills->whereDate('tanggal_jatuh_tempo', '<=', $this->endDate);
                    if (!empty($this->categoryIds)) {
                        $bills->where(function ($query) {
                            foreach ($this->categoryIds as $id) {
                                $query->orWhereJsonContains('category_ids', (int) $id);
                            }
                        });
                    }
                    if ($this->classroomId) {
                        $studentIds = $this->rows->pluck('id');
                        $bills->whereIn('student_profile_id', $studentIds);
                    }

                    $totalTagihan = $bills->sum('amount');
                    $totalBelumDibayar = max(0, $totalTagihan - $totalDibayar);

                    $sheet->setCellValue("{$colLetter}{$rowTotal}", "Rp" . number_format($totalDibayar, 0, ',', '.'));
                    $sheet->setCellValue("{$colLetter}{$rowTotalTagihan}", "Rp" . number_format($totalTagihan, 0, ',', '.'));
                    $sheet->setCellValue("{$colLetter}{$rowTotalBelumDibayar}", "Rp" . number_format($totalBelumDibayar, 0, ',', '.'));

                    $colIndex += 2;
                }
            }
        ];
    }
}
