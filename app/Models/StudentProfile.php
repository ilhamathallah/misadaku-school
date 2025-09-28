<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nis',
        'gender',
        'classroom_id',
        'generation',
        'is_active',
        'photo',
        'parent_phones',
    ];

    protected $casts = [
        'parent_phones' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function payments()
    {
        return $this->hasMany(StudentPayment::class, 'student_id', 'user_id');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'student_profile_id');
        // pastikan foreign key di tabel bills itu `student_id`
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }
}
