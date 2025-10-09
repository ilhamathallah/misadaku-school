<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'discount_id',
        'bill_ids',
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
        'bill_ids' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function discount()
    {
        return $this->belongsTo(StudentDiscount::class, 'discount_id');
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    // BUKAN RELASI, ini accessor yang aman
    public function getBillsAttribute()
    {
        $ids = $this->bill_ids;

        if (is_null($ids)) {
            return collect();
        }

        if (!is_array($ids)) {
            $ids = json_decode($ids, true);
        }

        if (empty($ids)) {
            return collect();
        }

        return Bill::whereIn('id', $ids)->get();
    }

    public function getStatusAttribute()
    {
        if (empty($this->bill_ids)) {
            return 'Belum Lunas';
        }

        $bills = $this->bills;
        $totalTagihan = $bills->sum('amount');
        $totalBayar = (int) $this->paid_amount;

        if ($totalBayar === 0) return 'Belum Lunas';
        if ($totalBayar < $totalTagihan) return 'Kurang';
        if ($totalBayar == $totalTagihan) return 'Lunas';
        if ($totalBayar > $totalTagihan) return 'Lebih';

        return 'Belum Lunas';
    }

    public function getDueDateAttribute()
    {
        return $this->bills->min('tanggal_jatuh_tempo');
    }
    

    public function getSisaKekuranganAttribute()
    {
        return max(($this->total_amount ?? 0) - ($this->paid_amount ?? 0), 0);
    }

    protected static function booted()
    {
        static::creating(function ($payment) {
            if (empty($payment->receipt_number)) {
                $payment->receipt_number = 'KW-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
            }
        });

        static::saved(function ($payment) {
            $billIds = $payment->bill_ids ?? [];

            if (!is_array($billIds)) {
                $billIds = json_decode($billIds, true) ?? [];
            }

            foreach ($billIds as $billId) {
                $bill = Bill::find($billId);
                if (!$bill) continue;

                $totalPaid = StudentPayment::whereJsonContains('bill_ids', $billId)->sum('paid_amount');

                $newStatus = 'Belum Lunas';
                if ($totalPaid === 0) {
                    $newStatus = 'Belum Lunas';
                } elseif ($totalPaid < $bill->amount) {
                    $newStatus = 'Kurang';
                } else {
                    $newStatus = 'Lunas';
                }

                DB::table('bills')->where('id', $billId)->update([
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
