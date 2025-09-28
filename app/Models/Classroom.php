<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kelas',
        'jenjang',
        'isSMK'
    ];

    public function student()
    {
        return $this->hasMany(StudentProfile::class);
    }
}
