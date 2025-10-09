<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type',
        'amount',
        'classroom_id',
        'classroom_ids',
        'tahun_ajaran',
        'bulan'
    ];

    protected $casts = [
        'classroom_ids' => 'array',
        'bulan' => 'array',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode) && !empty($model->name)) {
                $model->kode = self::generateKode($model->name);
            }
        });

        static::updating(function ($model) {
            if (empty($model->kode) && !empty($model->name)) {
                $model->kode = self::generateKode($model->name);
            }
        });
    }

    public static function generateKode(string $name): string
    {
        $words = explode(' ', preg_replace('/[^a-zA-Z\s]/', '', $name));

        if (count($words) >= 4) {
            return strtoupper(implode('', array_slice(array_map(fn($w) => substr($w, 0, 1), $words), 0, 4)));
        } elseif (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 2) . substr($words[1], 0, 2));
        } else {
            return strtoupper(substr($words[0], 0, 4));
        }
    }
}
