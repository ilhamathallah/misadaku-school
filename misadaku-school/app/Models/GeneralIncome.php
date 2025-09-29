<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GeneralIncome extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'income_date',
        'amount',
        'description'
    ];

    protected $casts = [
        'income_date' => 'date',
        'amount' => 'float',
    ];
}
