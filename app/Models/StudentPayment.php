<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\StudentPaymentResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'discount_id',
        'bill_id',
        'method',
        'total_amount',
        'paid_amount',
        'payment_date',
        'reason',
        'note',
        'sum',
        'receipt_number'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'paid_amount' => 'integer',
    ];

    public function getTerbilangAttribute()
    {
        return $this->numberToWords($this->total_amount) . ' rupiah';
    }

    private function numberToWords($number)
    {
        $units = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

        if ($number < 12) {
            return $units[$number];
        } elseif ($number < 20) {
            return $this->numberToWords($number - 10) . " belas";
        } elseif ($number < 100) {
            return $this->numberToWords(intval($number / 10)) . " puluh " . $this->numberToWords($number % 10);
        } elseif ($number < 200) {
            return "seratus " . $this->numberToWords($number - 100);
        } elseif ($number < 1000) {
            return $this->numberToWords(intval($number / 100)) . " ratus " . $this->numberToWords($number % 100);
        } elseif ($number < 2000) {
            return "seribu " . $this->numberToWords($number - 1000);
        } elseif ($number < 1000000) {
            return $this->numberToWords(intval($number / 1000)) . " ribu " . $this->numberToWords($number % 1000);
        } elseif ($number < 1000000000) {
            return $this->numberToWords(intval($number / 1000000)) . " juta " . $this->numberToWords($number % 1000000);
        } else {
            return "jumlah terlalu besar";
        }
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function discount()
    {
        return $this->belongsTo(StudentDiscount::class, 'discount_id');
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function getStatusAttribute()
    {
        return $this->bill?->status ?? 'Belum Lunas';
    }

    protected static function booted()
    {
        static::creating(function ($payment) {
            $bill = $payment->bill;
            if (!$bill) return;

            $categoryIds = $bill->category_ids ?? [];

            $financeCategory = \App\Models\FinanceCategory::whereIn('id', $categoryIds)->first();
            if (!$financeCategory) return;

            $kode = $financeCategory->kode;
            $tahunAjaran = $financeCategory->tahun_ajaran;
            $bulanMap = [
                'januari' => '01',
                'februari' => '02',
                'maret' => '03',
                'april' => '04',
                'mei' => '05',
                'juni' => '06',
                'juli' => '07',
                'agustus' => '08',
                'september' => '09',
                'oktober' => '10',
                'november' => '11',
                'desember' => '12',
            ];

            $bulan = strtolower($financeCategory->bulan);
            $bulanAngka = $bulanMap[$bulan] ?? '00'; // fallback jika bulan tidak valid

            // Hitung jumlah pembayaran sebelumnya dengan kategori ini
            $jumlahPembayaran = self::whereHas('bill', function ($query) use ($categoryIds) {
                $query->whereJsonContains('category_ids', $categoryIds);
            })->count();

            $nomorUrut = str_pad($jumlahPembayaran + 1, 4, '0', STR_PAD_LEFT);

            $receiptNumber = "KW/{$kode}/{$tahunAjaran}/{$bulanAngka}/{$nomorUrut}";

            $payment->receipt_number = $receiptNumber;
        });
    }

    public function getSisaKekuranganAttribute()
    {
        return max(($this->total_amount ?? 0) - ($this->paid_amount ?? 0), 0);
    }

    public function studentProfile()
    {
        return $this->belongsTo(\App\Models\StudentProfile::class, 'student_profile_id');
    }
}
