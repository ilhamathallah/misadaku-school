<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
//  implements HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    // use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     * 
     * 
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_image'
        // => 'profile-images/kue.png'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class, 'user_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        // Kalau user punya foto sendiri → ambil
        if ($this->photo && file_exists(storage_path('app/public/' . $this->photo))) {
            return asset('storage/student-photos/' . $this->photo);
        }   

        // Kalau kosong → fallback ke default
        return asset('storage/images/misadaqu.png');
    }
}
