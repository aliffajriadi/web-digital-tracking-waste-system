<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PicDetail extends Model
{
    protected $table = 'pic_detail';
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'full_name',
        'nik',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
