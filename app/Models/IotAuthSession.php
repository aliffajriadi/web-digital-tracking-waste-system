<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IotAuthSession extends Model
{
    protected $table = 'iot_auth_sessions';

    protected $fillable = [
        'code',
        'id_user',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
