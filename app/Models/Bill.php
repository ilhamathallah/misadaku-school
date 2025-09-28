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
    ];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function categories()
    {
        return $this->belongsToMany(FinanceCategory::class, 'bill_categories');
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

    public function payments()
    {
        return $this->hasMany(StudentPayment::class, 'bill_id');
    }

    /**
     * Accessor untuk status pembayaran (selalu konsisten di web & export)
     */

    // status di export excel
    public function getPaymentStatusAttribute()
    {
        $totalBill = $this->amount ?? 0;
        $totalPaid = $this->payments()->sum('paid_amount');

        return $totalPaid >= $totalBill ? 'Lunas' : 'Belum Lunas';
    }

    
}
