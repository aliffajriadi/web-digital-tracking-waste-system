<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminDetail extends Model
{
    protected $table = 'admin_detail';

    protected $primaryKey = 'id_user';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'full_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
