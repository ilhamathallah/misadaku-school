<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'student_profile_id',
        'category_ids',
        'nama_tagihan',
        'amount',
        'tanggal_jatuh_tempo',
        'status',
    ];

    protected $casts = [
        'category_ids' => 'array',
        'tanggal_jatuh_tempo' => 'date',
    ];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function payments()
    {
        return StudentPayment::whereJsonContains('bill_ids', $this->id);
    }

    public static function calculateAmount(array $categoryIds): int
    {
        return FinanceCategory::whereIn('id', $categoryIds)->sum('amount');
    }

    protected static function booted()
    {
        static::saving(function ($bill) {
            $bill->amount = self::calculateAmount($bill->category_ids ?? []);
        });
    }

    public function totalPaid(): int
    {
        return (int) StudentPayment::whereJsonContains('bill_ids', $this->id)->sum('paid_amount');
    }

    public function refreshStatus(): void
    {
        $totalBill = (int) $this->amount;
        $totalPaid = $this->totalPaid();

        $status = 'Belum Lunas';
        if ($totalPaid > 0 && $totalPaid < $totalBill) {
            $status = 'Kurang';
        } elseif ($totalPaid >= $totalBill) {
            $status = 'Lunas';
        }

        $this->updateQuietly(['status' => $status]);
    }

    public function getStatusRealtimeAttribute(): string
    {
        $totalBill = (int) $this->amount;
        $totalPaid = $this->totalPaid();

        if ($totalPaid === 0) return 'Belum Lunas';
        if ($totalPaid < $totalBill) return 'Kurang';
        return 'Lunas';
    }
}
