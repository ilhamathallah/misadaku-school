<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'finance_category_id',
        'custom_category_name',
        'expense_date',
        'amount',
        'note',
    ];

    // public function category()
    // {
    //     return $this->belongsTo(FinanceCategory::class, 'finance_category_id');
    // }

    public function financeCategory()
    {
        return $this->belongsTo(FinanceCategory::class, 'finance_category_id');
    }
}
