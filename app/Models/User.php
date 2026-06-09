<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'email',
        'password',
        'role_id',
        'is_active',
        'created_at',
        'photo',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function adminDetail()
    {
        return $this->hasOne(AdminDetail::class, 'id_user');
    }

    public function picDetail()
    {
        return $this->hasOne(PicDetail::class, 'id_user');
    }

    public function wasteEntries()
    {
        return $this->hasMany(WasteEntry::class, 'id_user');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'id_user');
    }
}